<x-marketing-layout>
    <x-slot name="title">Event Schedule for Venues | Manage Your Event Calendar</x-slot>
    <x-slot name="description">Fill your calendar with great events. Accept booking requests, sell tickets with QR check-in, and manage multiple rooms or stages. Built for bars, clubs, and event spaces.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Venues</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Venues",
        "description": "Fill your calendar with great events. Accept booking requests, sell tickets with QR check-in, and manage multiple rooms or stages. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Event Venues"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For Bars, Clubs & Event Spaces</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Fill your calendar<br>
                <span class="text-gradient">with great events</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Showcase what's happening at your venue. Accept bookings, sell tickets, and keep your audience engaged.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-sky-600 to-cyan-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-sky-500/25">
                    Create your calendar
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

                <!-- Public Event Calendar (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-sky-700 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Public Calendar
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Beautiful event calendar</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Showcase all your upcoming events on a mobile-friendly calendar. Embed it on your website or share the link directly.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Embeddable</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Mobile-friendly</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Custom branding</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-950 dark:to-cyan-950 rounded-2xl border border-sky-300 dark:border-sky-400/30 p-4 max-w-xs">
                                    <div class="text-center mb-3">
                                        <div class="text-gray-900 dark:text-white font-semibold">The Blue Note</div>
                                        <div class="text-sky-600 dark:text-sky-300 text-sm">March 2024</div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-sky-500/20 border border-sky-400/30">
                                            <div class="text-sky-600 dark:text-sky-300 text-xs font-mono w-12">Mar 15</div>
                                            <span class="text-gray-900 dark:text-white text-sm">Jazz Night</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-12">Mar 18</div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Open Mic</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-12">Mar 22</div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">DJ Night</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-12">Mar 29</div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Live Band</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accept Booking Requests -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-sky-700 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Booking Inbox
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Accept performance requests</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Artists can request to play at your venue. Review, approve, or decline right from your dashboard.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-sky-500/20 border border-sky-400/30">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-500 to-blue-500 flex items-center justify-center text-white text-xs font-semibold">SJ</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Sarah Johnson Trio</div>
                                <div class="text-sky-700 dark:text-sky-300 text-xs">Requesting Mar 22</div>
                            </div>
                            <div class="flex gap-1">
                                <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-100 dark:bg-white/5">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-500 flex items-center justify-center text-white text-xs font-semibold">DJ</div>
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">DJ Nova</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">Requesting Apr 5</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticketing with QR -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-700 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sell tickets with QR check-in</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Multiple ticket types, Stripe payments, and contactless check-in. Use any smartphone to scan.</p>

                    <div class="flex justify-center">
                        <div class="w-24 h-24 bg-white rounded-xl p-2 relative">
                            <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%231f2937%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                            <div class="absolute inset-x-0 top-2 h-0.5 bg-gradient-to-r from-blue-500 to-sky-500 animate-pulse"></div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-gray-500 dark:text-gray-400 text-xs">Scan to check in</div>
                </div>

                <!-- Sub-schedules (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Sub-schedules
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Multiple rooms & stages</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Organize events by space. Main stage, rooftop bar, private room. Each gets its own filterable schedule.</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">Filter by room</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-emerald-500/20 border border-emerald-500/30">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                    <span class="text-gray-900 dark:text-white text-sm">Main Stage</span>
                                    <span class="ml-auto text-emerald-700 dark:text-emerald-300 text-xs">12 events</span>
                                </div>
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="w-2 h-2 rounded-full bg-teal-400"></div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Rooftop Bar</span>
                                    <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">8 events</span>
                                </div>
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Acoustic Lounge</span>
                                    <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">5 events</span>
                                </div>
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="w-2 h-2 rounded-full bg-green-400"></div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Private Room</span>
                                    <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">3 events</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Management -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Team
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Staff management</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Give your team access to manage events. Set permissions so everyone has the right level of control.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 flex items-center justify-center text-white text-xs font-semibold">MO</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm">Mike</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 text-[10px]">Owner</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white text-xs font-semibold">LR</div>
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-gray-300 text-sm">Lisa</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-700 dark:text-teal-300 text-[10px]">Manager</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center text-white text-xs font-semibold">TC</div>
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-gray-300 text-sm">Tom</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-[10px]">Door Staff</span>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-700 dark:text-blue-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Calendar Sync
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Google Calendar sync</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Two-way sync keeps your venue calendar and Google Calendar in perfect harmony. Changes flow both ways automatically.</p>
                        </div>
                        <div class="flex items-center justify-center gap-3">
                            <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                                <div class="text-[10px] text-blue-700 dark:text-blue-300 mb-1 text-center">Venue</div>
                                <div class="space-y-1">
                                    <div class="h-1.5 bg-sky-400/80 dark:bg-sky-400/40 rounded text-[6px] text-white px-1">Jazz</div>
                                    <div class="h-1.5 bg-blue-400/80 dark:bg-blue-400/40 rounded text-[6px] text-white px-1">Open Mic</div>
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
                </div>

                <!-- Event Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Event graphics</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Auto-generate promotional images for social media. Perfect for Instagram and Facebook posts.</p>

                    <div class="flex justify-center">
                        <div class="relative w-32 h-32 bg-gradient-to-br from-amber-500/30 to-orange-500/30 rounded-xl border border-amber-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-sky-600/40 to-cyan-600/40 rounded-lg flex flex-col items-center justify-center">
                                <div class="text-gray-900 dark:text-white text-[10px] font-semibold mb-1">THIS FRIDAY</div>
                                <div class="text-amber-300 text-xs font-bold">Jazz Night</div>
                                <div class="text-gray-500 dark:text-gray-400 text-[8px] mt-1">The Blue Note • 9PM</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center">
                                <svg aria-hidden="true" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fan Videos & Comments (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-orange-100 dark:from-rose-900 dark:to-orange-900 border border-rose-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-700 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                Fan Engagement
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Fan videos & comments</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">After the show, fans share YouTube clips and comments on your events. You approve what goes live. Build a gallery of great nights at your venue.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">YouTube videos</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Comments</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Organizer approval</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="bg-gradient-to-br from-rose-50 to-orange-50 dark:from-rose-950 dark:to-orange-950 rounded-2xl border border-rose-300 dark:border-rose-400/30 p-4 max-w-xs">
                                <div class="text-xs text-gray-500 dark:text-white/70 mb-2">Jazz Night</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 mb-3">Fri, Mar 15 at 9 PM</div>
                                <div class="bg-gray-800 rounded-lg p-3 mb-3 flex items-center justify-center">
                                    <div class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center">
                                        <svg aria-hidden="true" class="w-4 h-4 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-start gap-2">
                                        <div class="w-5 h-5 rounded-full bg-rose-300 dark:bg-rose-500/40 flex-shrink-0"></div>
                                        <div class="bg-white dark:bg-white/10 rounded-lg px-2 py-1 text-[10px] text-gray-600 dark:text-gray-300">Incredible show!</div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <div class="w-5 h-5 rounded-full bg-orange-300 dark:bg-orange-500/40 flex-shrink-0"></div>
                                        <div class="bg-white dark:bg-white/10 rounded-lg px-2 py-1 text-[10px] text-gray-600 dark:text-gray-300">Best venue in town</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 mt-2 pt-1">
                                    <svg aria-hidden="true" class="w-3 h-3 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-rose-600 dark:text-rose-400 text-[10px]">Approved by venue</span>
                                </div>
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
                    Perfect for all types of venues
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From intimate lounges to large event spaces, Event Schedule adapts to your needs.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Bars & Pubs -->
                <a href="{{ marketing_url('/for-bars') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Bars & Pubs</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Promote live music nights, trivia, and special events to regulars.</p>
                    <span class="inline-flex items-center text-sky-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Nightclubs -->
                <a href="{{ marketing_url('/for-nightclubs') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-cyan-200 dark:hover:border-cyan-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Nightclubs</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Manage DJ lineups, themed nights, and VIP table reservations.</p>
                    <span class="inline-flex items-center text-cyan-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Music Venues -->
                <a href="{{ marketing_url('/for-music-venues') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.464a5 5 0 01-1.414-1.414m-2.828 9.9a9 9 0 01-2.728-2.728" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Music Venues</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Book bands, sell tickets, and manage your concert calendar.</p>
                    <span class="inline-flex items-center text-blue-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Theaters -->
                <a href="{{ marketing_url('/for-theaters') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-rose-200 dark:hover:border-rose-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Theaters</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Schedule productions, manage show runs, and sell season tickets.</p>
                    <span class="inline-flex items-center text-rose-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Comedy Clubs -->
                <a href="{{ marketing_url('/for-comedy-clubs') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Comedy Clubs</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Book comedians, host open mics, and fill seats for headliners.</p>
                    <span class="inline-flex items-center text-amber-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Restaurants -->
                <a href="{{ marketing_url('/for-restaurants') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-orange-200 dark:hover:border-orange-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-orange-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Restaurants</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Promote wine tastings, chef's tables, and live entertainment.</p>
                    <span class="inline-flex items-center text-orange-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Breweries & Wineries -->
                <a href="{{ marketing_url('/for-breweries-and-wineries') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Breweries & Wineries</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Host tastings, tours, and live music in your taproom.</p>
                    <span class="inline-flex items-center text-sky-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Art Galleries & Studios -->
                <a href="{{ marketing_url('/for-art-galleries') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Art Galleries & Studios</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Announce openings, exhibitions, and artist meet-and-greets.</p>
                    <span class="inline-flex items-center text-blue-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Community Centers -->
                <a href="{{ marketing_url('/for-community-centers') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Community Centers</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Organize classes, workshops, and community gatherings.</p>
                    <span class="inline-flex items-center text-emerald-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Farmers Markets -->
                <a href="{{ marketing_url('/for-farmers-markets') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-lime-200 dark:hover:border-lime-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-lime-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-lime-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Farmers Markets</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Share your market schedule and build a loyal shopper community.</p>
                    <span class="inline-flex items-center text-lime-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Hotels & Resorts -->
                <a href="{{ marketing_url('/for-hotels-and-resorts') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-slate-200 dark:hover:border-slate-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-slate-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Hotels & Resorts</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Elevate the guest experience with activity calendars and events.</p>
                    <span class="inline-flex items-center text-slate-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Libraries -->
                <a href="{{ marketing_url('/for-libraries') }}" class="block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Libraries</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Share programs, author events, and community activities.</p>
                    <span class="inline-flex items-center text-sky-600 font-medium mt-3 text-sm">
                        Learn more
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-sky-700 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Online Events
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-300 transition-colors">Stream to the world</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Share live performances with fans worldwide. Add your streaming URL and sell tickets to viewers anywhere. No venue required.</p>
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
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-6 w-52">
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
                    Get your venue calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-sky-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-sky-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create Your Calendar</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Sign up and add your venue details. Set up rooms or stages if you have multiple spaces.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-sky-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-sky-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add Your Events</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Add events manually, import from Google Calendar, or accept booking requests from performers.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-sky-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-sky-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share & Sell</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Embed on your website, share on social, and start selling tickets with QR check-in.
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
                    name="Sub-Schedules"
                    description="Organize events into categories and groups"
                    :url="marketing_url('/features/sub-schedules')"
                    icon-color="rose"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
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
                <a href="{{ marketing_url('/for-music-venues') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Music Venues</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-comedy-clubs') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Comedy Clubs</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-theaters') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Theaters</div>
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
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-sky-600 to-cyan-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Keep your venue calendar packed
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Create your calendar in minutes. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-sky-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Venues",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Venue Management Software",
        "operatingSystem": "Web",
        "description": "Fill your calendar with great events. Accept booking requests, sell tickets with QR check-in, and manage multiple rooms or stages.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Public event calendar",
            "Booking request inbox",
            "QR code ticketing",
            "Multiple rooms/stages",
            "Team management",
            "Google Calendar sync"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
