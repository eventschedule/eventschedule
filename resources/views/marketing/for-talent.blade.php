<x-marketing-layout>
    <x-slot name="title">Event Schedule for Musicians & Performers | Share Your Shows</x-slot>
    <x-slot name="description">Share your upcoming shows with fans. Sync with Google Calendar, sell tickets direct, and let venues add you to their schedule. Built for musicians, DJs, and performers.</x-slot>
    <x-slot name="socialImage">social/for-talent.png</x-slot>
    <x-slot name="breadcrumbTitle">For Talent</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Talent",
        "description": "Share your upcoming shows with fans. Sync with Google Calendar, sell tickets direct, and let venues add you to their schedule. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Musicians & Performers"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Is Event Schedule free for performers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your show schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I share my schedule on my website and social profiles?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Embed your schedule on any website with a single code snippet, or share your unique schedule URL on social media profiles, EPKs, and booking platforms. Your schedule is always up to date across all channels."
                }
            },
            {
                "@type": "Question",
                "name": "How do fans find out about my upcoming shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your schedule and receive email notifications when you add new shows. You can also send newsletters directly to followers with your upcoming dates and share your schedule link anywhere online."
                }
            },
            {
                "@type": "Question",
                "name": "What happens when a venue books me for a show?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When a venue adds you to their event on Event Schedule, it automatically appears on your schedule too. No need to manually add the same gig in two places. Both calendars stay in sync."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For Musicians, DJs & Performers</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Share your shows<br>
                <span class="text-gradient">with fans</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                One link. Every gig. Let your audience know where you're playing next.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    Create your schedule
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Share Your Schedule (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                Share Everywhere
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">One link for all your gigs</h2>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-6">Get a custom URL for your schedule. Share it on social media, your website, or anywhere else. Fans can see all your upcoming shows in one place.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Custom URL</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Website embed</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Social sharing</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-950 dark:to-sky-950 rounded-2xl border border-blue-300 dark:border-blue-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-2 mb-4 bg-white dark:bg-gray-900 rounded-lg px-3 py-2">
                                        <svg aria-hidden="true" class="w-4 h-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
                                        </svg>
                                        <span class="text-sm text-gray-600 dark:text-gray-300 font-mono">eventschedule.com/yourband</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-blue-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-blue-500 dark:bg-blue-400"></div>
                                            <span class="text-gray-900 dark:text-white text-sm">Fri Mar 15 - Blue Note</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-blue-100 dark:bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-sky-500 dark:bg-sky-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Sat Mar 22 - The Roxy</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-blue-100 dark:bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-blue-500 dark:bg-blue-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Fri Mar 29 - Jazz Club</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Play at Multiple Venues -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Venues
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Play at multiple venues</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Link your schedule to venue calendars. When they post your show, it appears on your schedule automatically.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-sky-200 dark:bg-sky-500/20 border border-sky-400/30">
                            <div class="w-8 h-8 rounded-lg bg-sky-200 dark:bg-sky-500/30 flex items-center justify-center">
                                <span class="text-sky-700 dark:text-sky-300 text-xs font-bold">BN</span>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Blue Note</div>
                                <div class="text-sky-600 dark:text-sky-300 text-xs">Linked</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-100 dark:bg-white/5">
                            <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-white/10 flex items-center justify-center">
                                <span class="text-gray-500 dark:text-gray-400 text-xs font-bold">TR</span>
                            </div>
                            <div>
                                <div class="text-gray-700 dark:text-gray-300 text-sm font-medium">The Roxy</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">Linked</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Two-way Google sync</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Connect your Google Calendar. Changes sync both ways automatically via real-time webhooks.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-200 dark:bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-600 dark:text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-300 dark:bg-white/20 rounded"></div>
                                <div class="h-1.5 bg-blue-300 dark:bg-white/20 rounded w-3/4"></div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg aria-hidden="true" class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-600 dark:text-gray-300 mb-1 text-center">Google</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                <div class="h-1.5 bg-green-400/40 rounded w-3/4"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sell Tickets (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Ticketing
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Sell tickets direct</h2>
                            <p class="text-gray-600 dark:text-gray-400 text-lg">No middleman. Accept payments via Stripe with zero platform fees. You keep what you earn.</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-100 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                                    <div>
                                        <div class="text-gray-900 dark:text-white font-medium">Early Bird</div>
                                        <div class="text-emerald-600 dark:text-emerald-400 text-xs">50 remaining</div>
                                    </div>
                                    <div class="text-xl font-bold text-gray-900 dark:text-white">$15</div>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-sky-500/20 border border-sky-400/30">
                                    <div>
                                        <div class="text-gray-900 dark:text-white font-medium">VIP Meet & Greet</div>
                                        <div class="text-sky-600 dark:text-sky-300 text-xs">10 remaining</div>
                                    </div>
                                    <div class="text-xl font-bold text-gray-900 dark:text-white">$75</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Track Availability -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Availability
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Track availability</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Block off dates when you're unavailable. Venues can see when you're free before reaching out.</p>

                    <div class="grid grid-cols-7 gap-1 text-center text-xs">
                        <div class="text-gray-500 dark:text-gray-400">M</div>
                        <div class="text-gray-500 dark:text-gray-400">T</div>
                        <div class="text-gray-500 dark:text-gray-400">W</div>
                        <div class="text-gray-500 dark:text-gray-400">T</div>
                        <div class="text-gray-500 dark:text-gray-400">F</div>
                        <div class="text-gray-500 dark:text-gray-400">S</div>
                        <div class="text-gray-500 dark:text-gray-400">S</div>
                        <div class="p-1 rounded bg-emerald-200 text-emerald-700 dark:bg-emerald-500/30 dark:text-emerald-300">1</div>
                        <div class="p-1 rounded bg-emerald-200 text-emerald-700 dark:bg-emerald-500/30 dark:text-emerald-300">2</div>
                        <div class="p-1 rounded bg-red-200 text-red-700 dark:bg-red-500/30 dark:text-red-300">3</div>
                        <div class="p-1 rounded bg-emerald-200 text-emerald-700 dark:bg-emerald-500/30 dark:text-emerald-300">4</div>
                        <div class="p-1 rounded bg-blue-200 text-blue-700 dark:bg-blue-500/30 dark:text-blue-300">5</div>
                        <div class="p-1 rounded bg-blue-200 text-blue-700 dark:bg-blue-500/30 dark:text-blue-300">6</div>
                        <div class="p-1 rounded bg-emerald-200 text-emerald-700 dark:bg-emerald-500/30 dark:text-emerald-300">7</div>
                    </div>
                    <div class="flex gap-4 mt-4 text-xs">
                        <div class="flex items-center gap-1"><div class="w-2 h-2 rounded bg-emerald-500"></div><span class="text-gray-500 dark:text-gray-400">Available</span></div>
                        <div class="flex items-center gap-1"><div class="w-2 h-2 rounded bg-blue-500"></div><span class="text-gray-500 dark:text-gray-400">Booked</span></div>
                        <div class="flex items-center gap-1"><div class="w-2 h-2 rounded bg-red-500"></div><span class="text-gray-500 dark:text-gray-400">Blocked</span></div>
                    </div>
                </div>

                <!-- Team Collaboration -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Team
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Band collaboration</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Invite band members to manage the schedule together. Everyone stays in sync.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-blue-500 flex items-center justify-center text-white text-xs font-semibold">JD</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm">Jake</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-[10px]">Owner</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-sky-500 flex items-center justify-center text-white text-xs font-semibold">MK</div>
                            <div class="flex-1">
                                <div class="text-gray-700 dark:text-gray-300 text-sm">Maya</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-[10px]">Admin</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-sky-500 to-blue-500 flex items-center justify-center text-white text-xs font-semibold">SR</div>
                            <div class="flex-1">
                                <div class="text-gray-700 dark:text-gray-300 text-sm">Sam</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-[10px]">Member</span>
                        </div>
                    </div>
                </div>

                <!-- Event Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Event graphics</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Auto-generate promotional images for social media. Perfect for Instagram and Facebook posts.</p>

                    <div class="flex justify-center">
                        <div class="relative w-32 h-32 bg-gradient-to-br from-amber-500/30 to-orange-500/30 rounded-xl border border-amber-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-blue-600/40 to-sky-600/40 rounded-lg flex flex-col items-center justify-center">
                                <div class="text-white text-[10px] font-semibold mb-1">LIVE TONIGHT</div>
                                <div class="text-amber-300 text-xs font-bold">The Jazz Trio</div>
                                <div class="text-gray-500 dark:text-gray-400 text-[8px] mt-1">Blue Note • 8PM</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center">
                                <svg aria-hidden="true" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section id="perfect-for" class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for all types of performers
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether you're a solo act or a full ensemble, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Musicians & Bands -->
                <a href="{{ route('marketing.for_musicians') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Musicians & Bands</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Share your tour dates and let fans know where you're playing next.</p>
                    <span class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- DJs -->
                <a href="{{ route('marketing.for_djs') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">DJs</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">List your residencies and guest spots across multiple venues.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Comedians -->
                <a href="{{ route('marketing.for_comedians') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Comedians</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Promote your stand-up shows and open mic appearances.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Circus & Acrobatics -->
                <a href="{{ route('marketing.for_circus_acrobatics') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-cyan-200 dark:hover:border-cyan-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Circus & Acrobatics</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Share your aerial shows, circus acts, and acrobatic performances.</p>
                    <span class="inline-flex items-center text-cyan-600 dark:text-cyan-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Magicians & Variety -->
                <a href="{{ route('marketing.for_magicians') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Magicians & Variety</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Book more gigs by showing your availability to event planners.</p>
                    <span class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Spoken Word & Poetry -->
                <a href="{{ route('marketing.for_spoken_word') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-rose-200 dark:hover:border-rose-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Spoken Word & Poetry</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Share your readings and open mic nights with your audience.</p>
                    <span class="inline-flex items-center text-rose-600 dark:text-rose-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Dance Groups -->
                <a href="{{ route('marketing.for_dance_groups') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-cyan-200 dark:hover:border-cyan-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Dance Groups</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Coordinate performances and rehearsals across your ensemble.</p>
                    <span class="inline-flex items-center text-cyan-600 dark:text-cyan-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Theater Performers -->
                <a href="{{ route('marketing.for_theater_performers') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Theater Performers</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">List your show runs and auditions for fans and casting directors.</p>
                    <span class="inline-flex items-center text-amber-600 dark:text-amber-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Food Trucks & Vendors -->
                <a href="{{ route('marketing.for_food_trucks_and_vendors') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Food Trucks & Vendors</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Let customers know where to find you at markets and events.</p>
                    <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Fitness & Yoga Instructors -->
                <a href="{{ route('marketing.for_fitness_and_yoga') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Fitness & Yoga Instructors</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Share your class schedule and let students follow your sessions.</p>
                    <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Workshop Instructors -->
                <a href="{{ route('marketing.for_workshop_instructors') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Workshop Instructors</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">List your workshops and courses to fill every seat.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Visual Artists -->
                <a href="{{ route('marketing.for_visual_artists') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Visual Artists</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Announce exhibitions and build your collector base.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- Stream to the World Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-sky-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-blue-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-3xl border border-sky-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Online Events
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-300 transition-colors">Stream to the world</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">Share live performances with fans worldwide. Add your streaming URL and sell tickets to viewers anywhere - no venue required.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Live streaming</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Global ticket sales</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Any platform</span>
                            </div>
                            <span class="inline-flex items-center text-sky-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 p-6 w-52">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs">Online Event</span>
                                    <div class="w-10 h-5 bg-sky-500 rounded-full relative">
                                        <div class="absolute right-0.5 top-0.5 w-4 h-4 bg-white rounded-full"></div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">Zoom</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">YouTube Live</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">Twitch</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
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
                    Get your schedule online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create Your Schedule</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Sign up and add your upcoming shows. Import from Google Calendar or add them manually.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share Your Link</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Get a custom URL for your schedule. Add it to your bio, website, or social profiles.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Build Your Audience</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Fans can follow your schedule, get notified about new shows, and share videos and comments on your events (all approved by you before going live).
                    </p>
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
                <a href="{{ marketing_url('/for-curators') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Curators</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-musicians') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Musicians</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-comedians') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Comedians</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-venues') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Venues</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gray-100 dark:bg-black/30 py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently asked questions
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Everything performers ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 rounded-2xl border border-cyan-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is Event Schedule free for performers?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Event Schedule is free forever for sharing your show schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 rounded-2xl border border-teal-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I share my schedule on my website and social profiles?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Embed your schedule on any website with a single code snippet, or share your unique schedule URL on social media profiles, EPKs, and booking platforms. Your schedule is always up to date across all channels.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-cyan-100 dark:from-emerald-900 dark:to-cyan-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How do fans find out about my upcoming shows?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Fans can follow your schedule and receive email notifications when you add new shows. You can also send newsletters directly to followers with your upcoming dates and share your schedule link anywhere online.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-2xl border border-sky-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What happens when a venue books me for a show?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            When a venue adds you to their event on Event Schedule, it automatically appears on your schedule too. No need to manually add the same gig in two places. Both calendars stay in sync.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Let fans know where you're playing
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Create your schedule in minutes. Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        "name": "Event Schedule for Musicians & Performers",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Management Software",
        "operatingSystem": "Web",
        "description": "Share your upcoming shows with fans. Sync with Google Calendar, sell tickets direct, and let venues add you to their schedule.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Custom schedule URL",
            "Google Calendar sync",
            "Direct ticket sales",
            "Venue linking",
            "Availability tracking",
            "Team collaboration",
            "Fan videos and comments on events"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
