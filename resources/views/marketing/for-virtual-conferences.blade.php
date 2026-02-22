<x-marketing-layout>
    <x-slot name="title">Event Schedule for Virtual Conferences | Schedule, Sell & Manage Online Conferences</x-slot>
    <x-slot name="description">Schedule and sell virtual conferences with multi-day agendas, tiered ticketing, speaker lineups, and attendee email notifications. Works with Zoom, Teams, YouTube Live, and any platform. Zero platform fees.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Virtual Conferences</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Virtual Conferences",
        "description": "Schedule and sell virtual conferences with multi-day agendas, tiered ticketing, speaker lineups, and attendee email notifications. Works with Zoom, Teams, YouTube Live, and any platform. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Virtual Conference Organizers"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section - Mesh Gradient -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute bottom-0 left-[-20%] w-[70%] h-[70%] bg-sky-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-0 right-[-10%] w-[50%] h-[60%] bg-blue-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] h-[40%] bg-indigo-600/10 rounded-full blur-[100px]"></div>
        </div>

        <!-- Grid overlay for texture -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 backdrop-blur-sm">
                <div class="relative">
                    <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium tracking-wide">For Conference Organizers & Event Planners</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Host virtual conferences that feel professional.<br>
                <span class="conference-glow-text">Zero platform fees.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Multi-day virtual conference agendas, multiple ticket types, speaker lineups. Schedule your conference, sell tickets, and let attendees browse the full agenda from one link.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-sky-600 to-blue-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-lg shadow-sky-500/25">
                    Create your conference schedule
                    <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-8 text-base text-gray-400 dark:text-gray-500 max-w-2xl mx-auto">
                The virtual conference platform with built-in multi-day scheduling, tiered ticketing, attendee email notifications, and payment processing for conference organizers.
            </p>

            <!-- Type tags -->
            <div class="mt-14 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-800/50">Tech Summits</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 text-xs font-medium border border-blue-200 dark:border-blue-800/50">Industry Conferences</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 text-xs font-medium border border-indigo-200 dark:border-indigo-800/50">Company Retreats</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-800/50">Professional Summits</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-800/50">Annual Meetings</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 text-xs font-medium border border-blue-200 dark:border-blue-800/50">Panel Events</span>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-sky-400 mb-2">~73%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">of organizations now host virtual or hybrid events</div>
                </div>
                <div class="p-6 border-x border-gray-200 dark:border-white/5">
                    <div class="text-4xl font-bold text-blue-400 mb-2">3-5x</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">wider reach compared to in-person only</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-indigo-400 mb-2">$0</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">platform fees on conference tickets</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Bento Grid -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white text-center mb-12">
                Everything you need to run a virtual conference
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Multi-day conference schedule (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-sky-500/5 rounded-full blur-[100px]"></div>

                    <div class="relative flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 text-sm font-medium mb-5 border border-sky-200 dark:border-sky-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Multi-Day Agenda
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Multi-day conference schedule</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Organize keynotes, breakout sessions, and workshops across multiple days of your virtual conference. Attendees browse the full agenda and find the sessions they care about. Have a printed conference program? Scan it with AI to populate all your sessions automatically.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Keynotes</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Breakout sessions</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Workshops</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">AI agenda scanning</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-950 dark:to-blue-950 rounded-2xl border border-sky-300 dark:border-sky-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-500 to-blue-500 flex items-center justify-center text-white text-sm font-semibold">TC</div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-semibold text-sm">Tech Conference 2025</div>
                                            <div class="text-sky-600 dark:text-sky-300 text-xs">3-day agenda</div>
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="bg-gradient-to-br from-sky-600/30 to-blue-600/30 rounded-lg p-2 border border-sky-400/20">
                                            <div class="text-gray-900 dark:text-white text-[10px] font-semibold">DAY 1 - Opening Keynote</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-[9px] mt-0.5">9:00 AM - Main Stage</div>
                                        </div>
                                        <div class="bg-gradient-to-br from-blue-600/20 to-indigo-600/20 rounded-lg p-2 border border-blue-400/20">
                                            <div class="text-gray-900 dark:text-white text-[10px] font-semibold">DAY 2 - AI Workshop</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-[9px] mt-0.5">10:00 AM - Track B</div>
                                        </div>
                                        <div class="bg-gradient-to-br from-indigo-600/20 to-sky-600/20 rounded-lg p-2 border border-indigo-400/20">
                                            <div class="text-gray-900 dark:text-white text-[10px] font-semibold">DAY 3 - Closing Panel</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-[9px] mt-0.5">2:00 PM - Main Stage</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sell tiered tickets -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-blue-100 dark:from-indigo-900 dark:to-blue-900 border border-indigo-200 dark:border-white/10 p-8">
                    <div class="absolute bottom-0 right-0 w-64 h-64 bg-indigo-500/5 rounded-full blur-[80px]"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-5 border border-indigo-200 dark:border-indigo-800/30">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Tiered Tickets
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sell tiered conference tickets</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">General admission, VIP, speaker passes, early bird pricing. 100% of Stripe payments go to you. See all <a href="{{ route('marketing.ticketing') }}" class="text-indigo-600 dark:text-indigo-400 underline hover:no-underline">ticketing features</a>.</p>

                        <div class="bg-indigo-500/20 rounded-xl border border-indigo-400/30 p-4">
                            <div class="space-y-2 mb-3">
                                <div class="flex items-center justify-between p-2 rounded-lg bg-indigo-400/20">
                                    <span class="text-gray-900 dark:text-white text-xs font-medium">General Admission</span>
                                    <span class="text-indigo-600 dark:text-indigo-400 text-xs font-semibold">$49</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-blue-400/20">
                                    <span class="text-gray-900 dark:text-white text-xs font-medium">VIP Pass</span>
                                    <span class="text-blue-600 dark:text-blue-400 text-xs font-semibold">$149</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-sky-400/20">
                                    <span class="text-gray-900 dark:text-white text-xs font-medium">Early Bird</span>
                                    <span class="text-sky-600 dark:text-sky-400 text-xs font-semibold">$29</span>
                                </div>
                            </div>
                            <div class="border-t border-indigo-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold">$0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- One link for your conference -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-sky-100 dark:from-cyan-900 dark:to-sky-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-100 dark:bg-cyan-900/40 text-cyan-700 dark:text-cyan-300 text-sm font-medium mb-5 border border-cyan-200 dark:border-cyan-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Share Link
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One link for your conference</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Share a single URL that attendees use to browse the full agenda, buy tickets, and join sessions.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-cyan-500/20 border border-cyan-400/30 mb-3">
                            <svg aria-hidden="true" class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-gray-900 dark:text-white text-xs font-mono">eventschedule.com/yourconf</span>
                        </div>
                        <div class="grid grid-cols-3 gap-1 text-center">
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-cyan-700 dark:text-cyan-300 text-[10px]">Website</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-cyan-700 dark:text-cyan-300 text-[10px]">LinkedIn</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-cyan-700 dark:text-cyan-300 text-[10px]">Email</div>
                        </div>
                    </div>
                </div>

                <!-- Works with any streaming platform (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-sky-100 dark:from-teal-900 dark:to-sky-900 border border-teal-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 text-sm font-medium mb-5 border border-teal-200 dark:border-teal-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Any Platform
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Works with any streaming platform</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Zoom, Microsoft Teams, YouTube Live, or custom RTMP. Add your streaming link per session, attendees join from the agenda. Learn more about <a href="{{ route('marketing.online_events') }}" class="text-teal-600 dark:text-teal-400 underline hover:no-underline">online event features</a>.</p>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex items-center gap-4">
                                <div class="bg-teal-100 dark:bg-teal-500/20 rounded-xl border border-teal-400/30 p-4 w-36">
                                    <div class="text-teal-700 dark:text-teal-300 text-xs text-center mb-2 font-semibold">Your Agenda</div>
                                    <div class="space-y-1.5">
                                        <div class="h-2 bg-gray-300 dark:bg-white/20 rounded"></div>
                                        <div class="h-2 bg-teal-400/40 rounded w-3/4"></div>
                                    </div>
                                    <div class="mt-3 p-2 rounded-lg bg-teal-200 dark:bg-teal-400/20 border border-teal-400/30">
                                        <div class="text-[10px] text-gray-900 dark:text-white text-center font-medium">Opening Keynote</div>
                                        <div class="text-[8px] text-teal-700 dark:text-teal-300 text-center mt-0.5">Day 1 - 9:00 AM</div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <svg aria-hidden="true" class="w-6 h-6 text-teal-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    <span class="text-teal-600 dark:text-teal-400 text-[10px]">stream link</span>
                                </div>
                                <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-4 w-36">
                                    <div class="text-gray-600 dark:text-gray-300 text-xs text-center mb-2 font-semibold">Platform</div>
                                    <div class="space-y-2 text-center">
                                        <div class="p-1.5 rounded bg-blue-400/20 text-[10px] text-blue-700 dark:text-blue-300">Zoom</div>
                                        <div class="p-1.5 rounded bg-sky-400/20 text-[10px] text-sky-700 dark:text-sky-300">MS Teams</div>
                                        <div class="p-1.5 rounded bg-red-400/20 text-[10px] text-red-700 dark:text-red-300">YouTube Live</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email all attendees -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-sky-100 dark:from-amber-900 dark:to-sky-900 border border-amber-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-sm font-medium mb-5 border border-amber-200 dark:border-amber-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Email Attendees
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Email all attendees</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Send updates, schedule changes, speaker announcements, and post-conference resources directly to attendees.</p>
                        </div>
                        <div class="bg-amber-500/20 rounded-xl border border-amber-400/30 p-3">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/20">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                    <span class="text-gray-900 dark:text-white text-[10px] font-medium">Schedule update</span>
                                </div>
                                <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                    <span class="text-gray-600 dark:text-gray-300 text-[10px]">Speaker announcement</span>
                                </div>
                                <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                    <span class="text-gray-600 dark:text-gray-300 text-[10px]">Post-conference resources</span>
                                </div>
                            </div>
                        </div>
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
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Two-way sync keeps conference sessions organized alongside your other meetings and planning.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-700 dark:text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-sky-400/80 dark:bg-sky-400/40 rounded text-[6px] text-white px-1">Session</div>
                                <div class="h-1.5 bg-amber-400/80 dark:bg-amber-400/40 rounded text-[6px] text-white px-1">Prep</div>
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

                <!-- Attendees follow your events -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-100 to-sky-100 dark:from-slate-900 dark:to-sky-900 border border-slate-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-900/40 text-slate-700 dark:text-slate-300 text-sm font-medium mb-5 border border-slate-200 dark:border-slate-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Followers
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Attendees follow your events</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Followers get notified for next year's conference or related events you organize.</p>

                    <div class="flex items-center justify-center">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-500 to-blue-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">A</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">B</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-cyan-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">C</div>
                            <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-white/20 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-gray-600 dark:text-white text-xs">+520</div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-slate-600 dark:text-slate-400 text-xs">523 attendees following your conference</div>
                </div>

                <!-- Attendee Feedback (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-orange-100 dark:from-rose-900 dark:to-orange-900 border border-rose-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 text-sm font-medium mb-5 border border-rose-200 dark:border-rose-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                Attendee Feedback
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Attendee feedback</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Let attendees leave comments on individual sessions. All feedback is approved by you before going live.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Per-session comments</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Organizer approval</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="bg-gradient-to-br from-rose-50 to-orange-50 dark:from-rose-950 dark:to-orange-950 rounded-2xl border border-rose-300 dark:border-rose-400/30 p-4 max-w-xs">
                                <div class="text-xs text-gray-500 dark:text-white/70 mb-2">AI Workshop - Day 2</div>
                                <div class="space-y-2">
                                    <div class="flex items-start gap-2">
                                        <div class="w-5 h-5 rounded-full bg-rose-300 dark:bg-rose-500/40 flex-shrink-0 mt-0.5"></div>
                                        <div class="bg-white dark:bg-white/10 rounded-lg px-2 py-1 text-[10px] text-gray-600 dark:text-gray-300">Great session on LLMs!</div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <div class="w-5 h-5 rounded-full bg-orange-300 dark:bg-orange-500/40 flex-shrink-0 mt-0.5"></div>
                                        <div class="bg-white dark:bg-white/10 rounded-lg px-2 py-1 text-[10px] text-gray-600 dark:text-gray-300">Very practical demos</div>
                                    </div>
                                    <div class="flex items-center gap-1 pt-1">
                                        <svg aria-hidden="true" class="w-3 h-3 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-rose-600 dark:text-rose-400 text-[10px]">Approved by organizer</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Journey Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    From your first virtual event to a conference series
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Event Schedule grows with your conference program
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- First virtual meetup -->
                <div class="bg-gradient-to-br from-sky-100 to-sky-50 dark:from-[#0f1520] dark:to-[#0a0a0f] rounded-2xl p-6 border border-sky-200 dark:border-sky-900/20 hover:border-sky-300 dark:hover:border-sky-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">First virtual meetup</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Share a link and host your first online session. Free registration gets attendees in the door.</p>
                </div>

                <!-- Single-day summit -->
                <div class="bg-gradient-to-br from-blue-100 to-blue-50 dark:from-[#0f1520] dark:to-[#0a0a0f] rounded-2xl p-6 border border-blue-200 dark:border-blue-900/20 hover:border-blue-300 dark:hover:border-blue-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Single-day summit</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Organize multiple sessions in one day. Attendees browse the agenda and join the talks they want.</p>
                </div>

                <!-- Paid conference -->
                <div class="bg-gradient-to-br from-indigo-100 to-indigo-50 dark:from-[#0f1220] dark:to-[#0a0a0f] rounded-2xl p-6 border border-indigo-200 dark:border-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Paid conference</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Start selling tickets. Offer tiered pricing for general admission, VIP, and speaker passes.</p>
                </div>

                <!-- Multi-day conference -->
                <div class="bg-gradient-to-br from-cyan-100 to-cyan-50 dark:from-[#0f1a1c] dark:to-[#0a0a0f] rounded-2xl p-6 border border-cyan-200 dark:border-cyan-900/20 hover:border-cyan-300 dark:hover:border-cyan-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Multi-day conference</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Keynotes, breakouts, and workshops across multiple days. Organize tracks for different audiences.</p>
                </div>

                <!-- Conference series -->
                <div class="bg-gradient-to-br from-teal-100 to-teal-50 dark:from-[#0f1a1a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-teal-200 dark:border-teal-900/20 hover:border-teal-300 dark:hover:border-teal-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Conference series</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Run quarterly or annual conferences. Followers get notified when you announce the next edition.</p>
                </div>

                <!-- Hybrid events -->
                <div class="bg-gradient-to-br from-amber-100 to-amber-50 dark:from-[#1a1510] dark:to-[#0a0a0f] rounded-2xl p-6 border border-amber-200 dark:border-amber-900/20 hover:border-amber-300 dark:hover:border-amber-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Hybrid events</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Combine in-person and virtual attendance. Sell different ticket types for on-site and remote participants.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for every type of virtual conference
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether it's a tech summit or an annual meeting, Event Schedule works for conference organizers of all kinds. Also see <a href="{{ route('marketing.for_webinars') }}" class="text-gray-600 dark:text-gray-300 underline hover:no-underline">Event Schedule for Webinars</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Tech Companies -->
                <x-sub-audience-card
                    name="Tech Companies"
                    description="Product launches, developer conferences, hackathons. Share streaming links and sell tickets to a global audience."
                    icon-color="cyan"
                    blog-slug="for-tech-company-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Professional Associations -->
                <x-sub-audience-card
                    name="Professional Associations"
                    description="Annual meetings, certification events, member summits. Organize multi-day agendas with tiered access."
                    icon-color="teal"
                    blog-slug="for-professional-association-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Nonprofits & NGOs -->
                <x-sub-audience-card
                    name="Nonprofits & NGOs"
                    description="Fundraising galas, awareness conferences, volunteer summits. Reach supporters worldwide with zero platform fees."
                    icon-color="sky"
                    blog-slug="for-nonprofit-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Corporate Teams -->
                <x-sub-audience-card
                    name="Corporate Teams"
                    description="All-hands meetings, training summits, leadership retreats. One link for your entire team to follow the agenda."
                    icon-color="blue"
                    blog-slug="for-corporate-team-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Academic Institutions -->
                <x-sub-audience-card
                    name="Academic Institutions"
                    description="Research symposiums, faculty conferences, student events. Schedule sessions across days and tracks."
                    icon-color="amber"
                    blog-slug="for-academic-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Industry Groups -->
                <x-sub-audience-card
                    name="Industry Groups"
                    description="Trade shows, networking events, expert panels. Build a following and notify attendees about future events."
                    icon-color="emerald"
                    blog-slug="for-industry-group-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                    Three steps to your virtual conference
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-600 to-blue-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-sky-600/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Build your agenda</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Add sessions, speakers, and streaming links. Organize by day and track.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-600 to-blue-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-sky-600/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share your conference</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        One link for the full schedule. Sell tickets with tiered pricing.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-600 to-blue-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-sky-600/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Go live</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Attendees join sessions from the agenda. You focus on content.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white text-center mb-12">
                Frequently asked questions about virtual conferences
            </h2>

            <div class="space-y-6">
                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Can I schedule a multi-day virtual conference?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. Add sessions across as many days as you need. Organize them into groups or tracks so attendees can browse by day, topic, or session type. Your full virtual conference agenda lives on one shareable page - a complete online conference schedule your attendees can bookmark.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">What streaming platforms work with Event Schedule?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Any platform that gives you a meeting or streaming link. Zoom, Microsoft Teams, Google Meet, YouTube Live, Twitch, and any other platform. Event Schedule is platform-agnostic - just paste your link and attendees join from the conference agenda.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Can I sell different ticket types for my conference?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. Create multiple virtual conference ticket types with different prices - general admission, VIP, early bird, speaker passes, or any custom tier. You keep 100% of the revenue. Event Schedule charges zero platform fees at any plan level. Stripe charges its standard processing fee (2.9% + $0.30).</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">How do attendees access conference sessions?</h3>
                    <p class="text-gray-500 dark:text-gray-400">When attendees register or purchase a ticket, the streaming links appear on the event page and their ticket. You can also email all registered attendees directly from your dashboard with session links, schedule changes, or speaker announcements.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Is Event Schedule free for virtual conferences?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. Event Schedule is free virtual conference software. The free plan includes unlimited events, attendee email notifications, follower features, and Google Calendar sync. There are zero platform fees on payments at any plan level. You only pay Stripe's standard processing fee if you charge for tickets.</p>
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
                <a href="{{ marketing_url('/for-webinars') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Webinars</div>
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
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden border-t border-sky-200 dark:border-sky-900/20">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-[-10%] w-[50%] h-[60%] bg-sky-600/15 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 right-[-10%] w-[50%] h-[60%] bg-blue-600/15 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Your conference. Your audience. No middleman.
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                Stop paying platform fees. Start hosting virtual conferences.<br class="hidden md:block">Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-sky-600 to-blue-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-xl shadow-sky-500/20">
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
        "name": "Event Schedule for Virtual Conferences",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Virtual Conference Scheduling Software",
        "operatingSystem": "Web",
        "description": "Schedule and sell virtual conferences with multi-day agendas, tiered ticketing, attendee email notifications, and payment processing. Works with Zoom, Teams, YouTube Live, and any platform.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Multi-day conference scheduling",
            "Tiered ticket types with zero platform fees",
            "One shareable link for full conference agenda",
            "Works with Zoom, Teams, YouTube Live, any platform",
            "Email notifications to all attendees",
            "Google Calendar two-way sync",
            "Follower notifications for future conferences",
            "QR code tickets for hybrid events",
            "Attendee management dashboard",
            "Open source virtual conference platform",
            "Selfhosted conference scheduling option"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "virtual conference platform, online conference scheduling, virtual summit, conference ticketing",
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
                "name": "Can I schedule a multi-day virtual conference?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Add sessions across as many days as you need. Organize them into groups or tracks so attendees can browse by day, topic, or session type. Your full virtual conference agenda lives on one shareable page - a complete online conference schedule your attendees can bookmark."
                }
            },
            {
                "@type": "Question",
                "name": "What streaming platforms work with Event Schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a meeting or streaming link. Zoom, Microsoft Teams, Google Meet, YouTube Live, Twitch, and any other platform. Event Schedule is platform-agnostic - just paste your link and attendees join from the conference agenda."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell different ticket types for my conference?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Create multiple virtual conference ticket types with different prices - general admission, VIP, early bird, speaker passes, or any custom tier. You keep 100% of the revenue. Event Schedule charges zero platform fees at any plan level. Stripe charges its standard processing fee (2.9% + $0.30)."
                }
            },
            {
                "@type": "Question",
                "name": "How do attendees access conference sessions?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When attendees register or purchase a ticket, the streaming links appear on the event page and their ticket. You can also email all registered attendees directly from your dashboard with session links, schedule changes, or speaker announcements."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free for virtual conferences?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free virtual conference software. The free plan includes unlimited events, attendee email notifications, follower features, and Google Calendar sync. There are zero platform fees on payments at any plan level. You only pay Stripe's standard processing fee if you charge for tickets."
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
        "name": "How to host a virtual conference with Event Schedule",
        "description": "Three steps to schedule and host your virtual conference online.",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Build your agenda",
                "text": "Add sessions, speakers, and streaming links. Organize by day and track."
            },
            {
                "@type": "HowToStep",
                "name": "Share your conference",
                "text": "One link for the full schedule. Sell tickets with tiered pricing."
            },
            {
                "@type": "HowToStep",
                "name": "Go live",
                "text": "Attendees join sessions from the agenda. You focus on content."
            }
        ]
    }
    </script>

    <style {!! nonce_attr() !!}>
        .conference-glow-text {
            background: linear-gradient(135deg, #0284c7, #2563eb, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(2, 132, 199, 0.3);
        }
        .dark .conference-glow-text {
            background: linear-gradient(135deg, #38bdf8, #60a5fa, #22d3ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(56, 189, 248, 0.3);
        }
    </style>
</x-marketing-layout>
