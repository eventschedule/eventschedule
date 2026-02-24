<x-marketing-layout>
    <x-slot name="title">Event Schedule for Hotels & Resorts | Guest Activity Calendar</x-slot>
    <x-slot name="description">Elevate the guest experience with one calendar for every activity. Pool parties, wine tastings, live entertainment, and more. Keep guests engaged and delighted. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Hotels & Resorts</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Hotels & Resorts",
        "description": "Elevate the guest experience with one calendar for every activity. Pool parties, wine tastings, live entertainment, and more. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Hotels & Resorts"
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
                "name": "Is Event Schedule free for hotels and resorts?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your activity calendar, building a guest following, and syncing with Google Calendar. Advanced features and newsletters are available on the Pro plan."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage guest activities, entertainment, and conferences together?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by category - pool activities, spa sessions, live entertainment, kids clubs, dining events, and conference schedules. Guests see a unified calendar of everything happening at your property."
                }
            },
            {
                "@type": "Question",
                "name": "How do guests discover activities during their stay?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Share your activity calendar via QR codes at check-in, embed it on your hotel website, or include the link in pre-arrival emails. Guests can also follow your schedule and receive notifications for new activities."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to special events and experiences?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account to sell tickets for special dinners, spa packages, excursions, and entertainment shows. Create different pricing for hotel guests and external visitors. Zero platform fees on all sales."
                }
            }
        ]
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Hotels & Resorts",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Hotel Activity Management Software",
        "operatingSystem": "Web",
        "description": "Elevate the guest experience with one calendar for every activity. Pool parties, wine tastings, live entertainment, conferences, and more. Keep guests engaged and delighted. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Guest activity newsletter",
            "Multi-space management",
            "Ticketed events and experiences",
            "Weekly activity planner",
            "Concierge sharing link",
            "Google Calendar sync",
            "Team management with roles",
            "Activity analytics and insights"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background with slate/gold tones -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-slate-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-amber-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <!-- Gold accent -->
            <div class="absolute top-40 right-1/3 w-[200px] h-[200px] bg-yellow-500/10 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>

        <!-- Marble/terrazzo texture SVG overlay -->
        <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.04]">
            <svg aria-hidden="true" width="100%" height="100%">
                <defs>
                    <pattern id="marble-speckle" x="0" y="0" width="120" height="120" patternUnits="userSpaceOnUse">
                        <circle cx="15" cy="20" r="0.8" fill="#94a3b8"/>
                        <circle cx="45" cy="10" r="0.5" fill="#d97706"/>
                        <circle cx="80" cy="35" r="0.7" fill="#94a3b8"/>
                        <circle cx="25" cy="60" r="0.4" fill="#d97706"/>
                        <circle cx="65" cy="75" r="0.9" fill="#94a3b8"/>
                        <circle cx="100" cy="50" r="0.6" fill="#d97706"/>
                        <circle cx="35" cy="95" r="0.5" fill="#94a3b8"/>
                        <circle cx="75" cy="105" r="0.7" fill="#d97706"/>
                        <circle cx="110" cy="85" r="0.4" fill="#94a3b8"/>
                        <circle cx="10" cy="110" r="0.6" fill="#d97706"/>
                        <circle cx="55" cy="45" r="0.3" fill="#94a3b8"/>
                        <circle cx="90" cy="15" r="0.5" fill="#94a3b8"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#marble-speckle)"/>
            </svg>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass hotel-brass-badge mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">For Hotels & Resorts</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-4 leading-tight">
                Elevate the<br>
                <span class="text-gradient-slate-gold">guest experience.</span>
            </h1>

            <!-- Gold foil shimmer line -->
            <div class="flex justify-center mb-8">
                <div class="hotel-shimmer-line w-48 h-px"></div>
            </div>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                From pool parties to wine tastings. One calendar for every guest activity. Keep guests engaged and delighted.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-slate-700 to-amber-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-amber-500/25">
                    Create your activity calendar
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Hotel/resort type tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300 text-xs font-medium border border-slate-200 dark:border-slate-500/30">Pool Parties</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-200 dark:border-amber-500/30">Live Entertainment</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-500/30">Conferences</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-xs font-medium border border-teal-200 dark:border-teal-500/30">Spa Events</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-xs font-medium border border-blue-200 dark:border-blue-500/30">Wine Tastings</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-xs font-medium border border-rose-200 dark:border-rose-500/30">Wedding Receptions</span>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Guest Updates Newsletter - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-100 to-amber-100 dark:from-slate-900 dark:to-amber-900 border border-slate-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Newsletter
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Guests know what's happening before they ask.</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Pool party tonight, wine tasting tomorrow, live jazz this weekend - one click emails every subscribed guest. Keep your property buzzing with activity.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Direct to guests</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">No app download needed</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-slate-100 to-amber-100 dark:from-slate-950 dark:to-amber-950 rounded-2xl border border-amber-300 dark:border-amber-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-slate-500 to-amber-600 rounded-xl flex items-center justify-center">
                                            <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white text-sm font-medium">This Week's Activities</div>
                                            <div class="text-amber-700 dark:text-amber-300 text-xs">Sending to 1,287 guests...</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Sunset Pool Party - Fri 6PM</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Wine Tasting - Sat 7PM</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-teal-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Live Jazz Brunch - Sun 11AM</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multi-Space Management -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Spaces
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Every space, one dashboard</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Pool, ballroom, spa, restaurant. Filter by space and avoid double-bookings across your property.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-sky-500/20 border border-sky-500/30">
                            <div class="w-2 h-2 rounded-full bg-sky-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Pool Deck</span>
                            <span class="ml-auto text-sky-700 dark:text-sky-300 text-xs">8 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Grand Ballroom</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">5 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-teal-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Spa & Wellness</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">12 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Restaurant Terrace</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">6 events</span>
                        </div>
                    </div>
                </div>

                <!-- Ticketed Events -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Tickets
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sell tickets, keep 100%</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Ticketed wine dinners, cooking classes, spa packages. Sell directly to guests with no platform fees.</p>

                    <!-- Ticket visual -->
                    <div class="flex justify-center">
                        <div class="bg-gradient-to-br from-emerald-100 to-green-50 rounded-xl border border-emerald-300/50 p-4 w-44 shadow-lg transform -rotate-2 hover:rotate-0 transition-transform">
                            <div class="text-center">
                                <div class="text-emerald-800 text-[10px] tracking-widest uppercase">VIP Experience</div>
                                <div class="text-emerald-900 text-sm font-serif font-semibold mt-1">Wine Dinner</div>
                                <div class="text-emerald-700 text-xl font-bold mt-2">$120<span class="text-xs font-normal">/guest</span></div>
                                <div class="text-emerald-600 text-[10px] mt-1">Saturday &bull; 7 PM</div>
                                <div class="text-emerald-500 text-[9px] mt-1">12 seats remaining</div>
                                <div class="mt-3 w-12 h-12 bg-emerald-900/10 rounded-lg mx-auto p-1.5">
                                    <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%23065f46%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Activity Planner (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border border-amber-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Activity Calendar
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">A full week of guest activities</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Plan every day of your guests' stay. From morning yoga to evening entertainment, keep the schedule fresh and engaging.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Recurring events</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Mobile-friendly</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Embeddable</span>
                            </div>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                            <div class="text-center mb-3">
                                <div class="text-gray-900 dark:text-white font-semibold">The Grand Resort</div>
                                <div class="text-amber-700 dark:text-amber-300 text-sm">This Week's Activities</div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-amber-500/20 border border-amber-400/30">
                                    <div class="text-amber-700 dark:text-amber-300 text-xs font-mono w-10">Mon</div>
                                    <span class="text-gray-900 dark:text-white text-sm">Pool Yoga &bull; 9 AM</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-10">Tue</div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Guided Hike &bull; 8 AM</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-10">Wed</div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Spa Special &bull; All Day</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-10">Thu</div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Cooking Class &bull; 4 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Google Calendar
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Syncs with Google Calendar</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Two-way sync keeps your property calendar and Google Calendar in perfect harmony. Update once, reflected everywhere.</p>

                    <div class="flex justify-center">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-white dark:bg-gray-800 rounded-xl shadow-lg flex items-center justify-center">
                                <svg aria-hidden="true" class="w-7 h-7" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12s4.48 10 10 10 10-4.48 10-10z" opacity="0.1"/>
                                    <path fill="#4285F4" d="M12 7v5l4.28 2.54.72-1.21-3.5-2.08V7H12z"/>
                                    <path fill="#EA4335" d="M12 2C6.48 2 2 6.48 2 12h2c0-4.42 3.58-8 8-8V2z"/>
                                    <path fill="#FBBC05" d="M2 12c0 5.52 4.48 10 10 10v-2c-4.42 0-8-3.58-8-8H2z"/>
                                    <path fill="#34A853" d="M12 22c5.52 0 10-4.48 10-10h-2c0 4.42-3.58 8-8 8v2z"/>
                                    <path fill="#4285F4" d="M22 12c0-5.52-4.48-10-10-10v2c4.42 0 8 3.58 8 8h2z"/>
                                </svg>
                            </div>
                            <div class="flex flex-col items-center gap-1">
                                <svg aria-hidden="true" class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span class="text-blue-700 dark:text-blue-300 text-[10px]">Two-way sync</span>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-slate-600 to-amber-500 rounded-xl shadow-lg flex items-center justify-center">
                                <svg aria-hidden="true" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Management -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 border border-teal-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Team
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Your whole team, connected</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Events manager, concierge, spa director, F&B coordinator. Everyone updates the same calendar.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-teal-500/20 border border-teal-400/30">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white text-[10px] font-semibold">EM</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-xs font-medium">Events Manager</div>
                                <div class="text-teal-700 dark:text-teal-300 text-[10px]">Full access</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-500 to-yellow-500 flex items-center justify-center text-white text-[10px] font-semibold">CD</div>
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-gray-300 text-xs font-medium">Concierge Desk</div>
                                <div class="text-gray-500 dark:text-gray-400 text-[10px]">View &amp; share</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-blue-500 flex items-center justify-center text-white text-[10px] font-semibold">SD</div>
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-gray-300 text-xs font-medium">Spa Director</div>
                                <div class="text-gray-500 dark:text-gray-400 text-[10px]">Spa events only</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-sky-100 dark:from-cyan-900 dark:to-sky-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Know what guests love</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">See which activities get the most views, RSVPs, and ticket sales. Double down on what works.</p>

                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600 dark:text-gray-300">Pool Party</span>
                                <span class="text-cyan-700 dark:text-cyan-300">847 views</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2">
                                <div class="bg-gradient-to-r from-cyan-500 to-sky-500 h-2 rounded-full" style="width: 92%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600 dark:text-gray-300">Wine Tasting</span>
                                <span class="text-cyan-700 dark:text-cyan-300">623 views</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2">
                                <div class="bg-gradient-to-r from-cyan-500 to-sky-500 h-2 rounded-full" style="width: 68%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600 dark:text-gray-300">Spa Morning</span>
                                <span class="text-cyan-700 dark:text-cyan-300">412 views</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2">
                                <div class="bg-gradient-to-r from-cyan-500 to-sky-500 h-2 rounded-full" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Guest Activity Weekly Planner - UNIQUE TO HOTELS -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Your guests' week at a glance
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Morning yoga by the pool. Sunset cocktails on the terrace. Every day offers something special for your guests.
                </p>
            </div>

            <!-- Weekly Calendar Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                <!-- Monday -->
                <div class="bg-gradient-to-br from-slate-900/40 to-amber-900/40 rounded-xl border border-amber-500/20 p-4 relative overflow-hidden group hover:border-amber-500/40 transition-colors">
                    <div class="text-amber-700 dark:text-amber-300 text-xs font-semibold tracking-wider uppercase mb-3">Mon</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Pool Yoga</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">9:00 AM</span>
                        </div>
                    </div>
                </div>

                <!-- Tuesday -->
                <div class="bg-gradient-to-br from-emerald-900/40 to-green-900/40 rounded-xl border border-emerald-500/20 p-4 relative overflow-hidden group hover:border-emerald-500/40 transition-colors">
                    <div class="text-emerald-700 dark:text-emerald-300 text-xs font-semibold tracking-wider uppercase mb-3">Tue</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Guided Hike</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-green-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">8:00 AM</span>
                        </div>
                    </div>
                </div>

                <!-- Wednesday -->
                <div class="bg-gradient-to-br from-teal-900/40 to-cyan-900/40 rounded-xl border border-teal-500/20 p-4 relative overflow-hidden group hover:border-teal-500/40 transition-colors">
                    <div class="text-teal-700 dark:text-teal-300 text-xs font-semibold tracking-wider uppercase mb-3">Wed</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-teal-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Spa Special</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-cyan-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">All Day</span>
                        </div>
                    </div>
                </div>

                <!-- Thursday -->
                <div class="bg-gradient-to-br from-orange-900/40 to-amber-900/40 rounded-xl border border-orange-500/20 p-4 relative overflow-hidden group hover:border-orange-500/40 transition-colors">
                    <div class="text-orange-700 dark:text-orange-300 text-xs font-semibold tracking-wider uppercase mb-3">Thu</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Cooking Class</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">4:00 PM</span>
                        </div>
                    </div>
                </div>

                <!-- Friday - Concierge Recommends -->
                <div class="bg-gradient-to-br from-blue-900/40 to-blue-900/40 rounded-xl border border-blue-500/20 p-4 relative overflow-hidden group hover:border-blue-500/40 transition-colors">
                    <!-- Concierge Recommends badge -->
                    <div class="absolute -top-1 -right-1 w-8 h-8">
                        <svg aria-hidden="true" viewBox="0 0 32 32" fill="none" class="w-full h-full">
                            <path d="M16,2 L19,11 L28,11 L21,17 L23,26 L16,21 L9,26 L11,17 L4,11 L13,11 Z" fill="#d97706" opacity="0.8"/>
                        </svg>
                    </div>
                    <div class="text-blue-700 dark:text-blue-300 text-xs font-semibold tracking-wider uppercase mb-3">Fri</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Wine Tasting</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">7:00 PM</span>
                        </div>
                        <div class="text-amber-600 dark:text-amber-400 text-[8px] font-medium mt-1">Concierge Pick</div>
                    </div>
                </div>

                <!-- Saturday -->
                <div class="bg-gradient-to-br from-rose-900/40 to-cyan-900/40 rounded-xl border border-rose-500/20 p-4 relative overflow-hidden group hover:border-rose-500/40 transition-colors">
                    <div class="text-rose-700 dark:text-rose-300 text-xs font-semibold tracking-wider uppercase mb-3">Sat</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-rose-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Beach Party</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-cyan-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">3:00 PM</span>
                        </div>
                    </div>
                </div>

                <!-- Sunday -->
                <div class="bg-gradient-to-br from-sky-900/40 to-blue-900/40 rounded-xl border border-sky-500/20 p-4 relative overflow-hidden group hover:border-sky-500/40 transition-colors">
                    <div class="text-sky-700 dark:text-sky-300 text-xs font-semibold tracking-wider uppercase mb-3">Sun</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-sky-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Sunday Brunch</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">10:00 AM</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity note -->
            <div class="mt-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                    <svg aria-hidden="true" class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Set it once - recurring activities appear automatically every week</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for every type of property
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    From boutique hotels to sprawling resorts.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Boutique Hotels -->
                <x-sub-audience-card
                    name="Boutique Hotels"
                    description="Curated experiences, intimate tastings, and local excursions. Give guests a reason to stay in and explore."
                    icon-color="slate"
                    blog-slug="for-boutique-hotels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Beach Resorts -->
                <x-sub-audience-card
                    name="Beach Resorts"
                    description="Pool parties, water sports, sunset yoga, beach bonfires. Keep the vacation vibes going all week long."
                    icon-color="amber"
                    blog-slug="for-beach-resorts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Conference Hotels -->
                <x-sub-audience-card
                    name="Conference Hotels"
                    description="Manage conference sessions, breakout rooms, networking events, and corporate dinners in one place."
                    icon-color="indigo"
                    blog-slug="for-conference-hotels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Spa & Wellness Resorts -->
                <x-sub-audience-card
                    name="Spa & Wellness Resorts"
                    description="Meditation sessions, wellness workshops, spa treatments, and mindfulness classes. Nurture your guests' wellbeing."
                    icon-color="teal"
                    blog-slug="for-spa-resorts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Mountain Lodges -->
                <x-sub-audience-card
                    name="Mountain Lodges"
                    description="Guided hikes, ski lessons, fireside gatherings, and nature excursions. Adventure awaits your guests every day."
                    icon-color="emerald"
                    blog-slug="for-mountain-lodges"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21l6-6m0 0l4-8 4 8m-4-8l6 6M3 21h18" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Casino Hotels -->
                <x-sub-audience-card
                    name="Casino Hotels"
                    description="Shows, tournaments, dining events, and nightlife. Keep the entertainment calendar packed and visible."
                    icon-color="violet"
                    blog-slug="for-casino-hotels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
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
                    Get your property's activity calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-600 to-amber-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Set up your property</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Sign up and add your hotel or resort details. Set up spaces like pool, ballroom, spa, and restaurant.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-600 to-amber-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Build your activity calendar</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Add activities, entertainment, classes, and events. Set up recurring activities once and they appear every week.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-slate-600 to-amber-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Delight your guests</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Share the link at check-in, print QR codes for rooms, embed on your website. Guests always know what's happening.
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
                <a href="{{ marketing_url('/for-restaurants') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Restaurants</div>
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
                <a href="{{ marketing_url('/for-community-centers') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Community Centers</div>
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

    <!-- FAQ Section -->
    <section class="bg-gray-100 dark:bg-black/30 py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently asked questions
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Everything hotels and resorts ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 rounded-2xl border border-cyan-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is Event Schedule free for hotels and resorts?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Event Schedule is free forever for sharing your activity calendar, building a guest following, and syncing with Google Calendar. Advanced features and newsletters are available on the Pro plan.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 rounded-2xl border border-teal-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I manage guest activities, entertainment, and conferences together?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Use sub-schedules to organize by category - pool activities, spa sessions, live entertainment, kids clubs, dining events, and conference schedules. Guests see a unified calendar of everything happening at your property.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-cyan-100 dark:from-emerald-900 dark:to-cyan-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How do guests discover activities during their stay?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Share your activity calendar via QR codes at check-in, embed it on your hotel website, or include the link in pre-arrival emails. Guests can also follow your schedule and receive notifications for new activities.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-2xl border border-sky-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I sell tickets to special events and experiences?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Connect your Stripe account to sell tickets for special dinners, spa packages, excursions, and entertainment shows. Create different pricing for hotel guests and external visitors. Zero platform fees on all sales.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-slate-700 to-amber-600 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>
        <!-- Gold accent glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-yellow-500/10 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Give guests a reason to stay in
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                One calendar for every activity at your property. Keep guests engaged, informed, and delighted. Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-slate-700 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>


    <style {!! nonce_attr() !!}>
        .text-gradient-slate-gold {
            background: linear-gradient(135deg, #64748b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-slate-gold {
            background: linear-gradient(135deg, #94a3b8, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hotel-brass-badge {
            border: 1.5px solid rgba(217, 119, 6, 0.35);
            box-shadow: inset 0 0 6px rgba(217, 119, 6, 0.1), 0 1px 3px rgba(0,0,0,0.05);
        }

        .dark .hotel-brass-badge {
            border-color: rgba(245, 158, 11, 0.3);
            box-shadow: inset 0 0 8px rgba(245, 158, 11, 0.08), 0 1px 3px rgba(0,0,0,0.2);
        }

        .hotel-shimmer-line {
            background: linear-gradient(90deg, transparent, #d97706, transparent);
            position: relative;
            overflow: hidden;
        }

        .hotel-shimmer-line::after {
            content: '';
            position: absolute;
            top: -1px;
            left: -100%;
            width: 60%;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
            animation: shimmer 5s ease-in-out infinite;
        }
        .dark .hotel-shimmer-line::after {
            background: linear-gradient(90deg, transparent, rgba(251,191,36,0.4), transparent);
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 200%; }
        }

        @media (prefers-reduced-motion: reduce) {
            .hotel-shimmer-line::after {
                animation: none;
            }
        }
    </style>
</x-marketing-layout>
