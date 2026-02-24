<x-marketing-layout>
    <x-slot name="title">Event Categories & Sub-Schedules | Multi-Room Venues - Event Schedule</x-slot>
    <x-slot name="description">Organize events into categories with unlimited sub-schedules. Perfect for venues with multiple rooms, stages, or event series.</x-slot>
    <x-slot name="breadcrumbTitle">Sub-Schedules</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Sub-Schedules",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Organize events into categories with unlimited sub-schedules. Perfect for venues with multiple rooms, stages, or event series.",
        "featureList": [
            "Unlimited sub-schedules",
            "Event categorization",
            "Multiple room support",
            "Stage management",
            "Event series organization",
            "Color-coded categories"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
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
                "name": "What are sub-schedules used for?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Sub-schedules let you organize events into categories within your schedule. For example, a venue might have sub-schedules for 'Live Music,' 'Comedy Nights,' and 'Open Mic.' Visitors can filter by sub-schedule to find what interests them."
                }
            },
            {
                "@type": "Question",
                "name": "Can I nest sub-schedules?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Sub-schedules work as a single level of categorization within your schedule. Each event can belong to one or more sub-schedules, making it easy to organize without overcomplicating things."
                }
            },
            {
                "@type": "Question",
                "name": "How does filtering work for visitors?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Visitors see filter buttons at the top of your schedule, one for each sub-schedule. They can click to show only events in that category, making it easy to find what they are looking for."
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
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-rose-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Sub-schedules</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Organize your<br>
                <span class="text-gradient">events</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Create sub-schedules to categorize events by room, stage, series, or any way you like. Visitors can filter to find exactly what they're looking for.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-rose-600 to-cyan-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-rose-500/25">
                    Get started free
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

                <!-- Unlimited Sub-schedules (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-cyan-100 dark:from-rose-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Unlimited
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Create as many as you need</h2>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Add unlimited sub-schedules to organize your events. Perfect for venues with multiple rooms, stages, or different event series running simultaneously.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Main Stage</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Lounge</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Rooftop</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">VIP Room</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Sub-schedule mockup -->
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 w-64">
                                    <div class="text-xs text-gray-500 mb-3">Sub-schedules</div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-rose-500/20 border border-rose-500/30">
                                            <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                                            <span class="text-gray-900 dark:text-white text-sm">Main Stage</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Acoustic Room</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-sky-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Outdoor Patio</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Jazz Lounge</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Filtering -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-sky-100 dark:from-cyan-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filtering
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Easy filtering for visitors</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-6">When you have multiple sub-schedules, visitors see a dropdown filter on your calendar. They can quickly find events in the room or series they care about.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3">
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-white/5 rounded-lg">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Filter by schedule</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Direct Links -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Direct Links
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Shareable URLs</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Each sub-schedule gets its own URL. Share links directly to specific rooms or event series. Visitors land on exactly what they want to see.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500">/venue/</span>
                            <span class="text-sky-300">main-stage</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500">/venue/</span>
                            <span class="text-sky-300">jazz-lounge</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500">/venue/</span>
                            <span class="text-sky-300">comedy-nights</span>
                        </div>
                    </div>
                </div>

                <!-- API Integration (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                API Support
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Automate with the API</h2>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Create events programmatically and assign them to sub-schedules using the API. Pass the <code class="text-blue-300 bg-blue-500/20 px-1 rounded">schedule</code> parameter when creating events.</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4 font-mono text-sm">
                            <div class="text-gray-500 mb-2">// Create event via API</div>
                            <div class="text-blue-300">POST /api/events</div>
                            <div class="text-gray-500 dark:text-gray-400 mt-2">{</div>
                            <div class="text-gray-500 dark:text-gray-400 pl-4">"name": "Jazz Night",</div>
                            <div class="text-gray-500 dark:text-gray-400 pl-4">"schedule": "<span class="text-blue-300">jazz-lounge</span>",</div>
                            <div class="text-gray-500 dark:text-gray-400 pl-4">...</div>
                            <div class="text-gray-500 dark:text-gray-400">}</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Use Cases Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Sub-schedules work great for any venue or organizer with multiple event categories.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Concert Venues -->
                <div class="bg-gradient-to-br from-rose-50 to-cyan-50 dark:from-rose-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-rose-200 dark:border-rose-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Concert Venues</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Organize by main stage, side stage, VIP area, and outdoor spaces.</p>
                </div>

                <!-- Conference Centers -->
                <div class="bg-gradient-to-br from-rose-50 to-cyan-50 dark:from-rose-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-rose-200 dark:border-rose-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Conference Centers</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Separate tracks for different topics, workshops, and keynotes.</p>
                </div>

                <!-- Theaters -->
                <div class="bg-gradient-to-br from-rose-50 to-cyan-50 dark:from-rose-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-rose-200 dark:border-rose-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Theaters</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Main theater, black box, rehearsal space, and special screenings.</p>
                </div>

                <!-- Event Series -->
                <div class="bg-gradient-to-br from-rose-50 to-cyan-50 dark:from-rose-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-rose-200 dark:border-rose-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Event Series</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Group recurring events like weekly trivia, monthly open mics, or seasonal markets.</p>
                </div>

                <!-- Multi-Purpose Venues -->
                <div class="bg-gradient-to-br from-rose-50 to-cyan-50 dark:from-rose-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-rose-200 dark:border-rose-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Multi-Purpose Venues</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Bars, restaurants, and community centers with different event types.</p>
                </div>

                <!-- Festival Stages -->
                <div class="bg-gradient-to-br from-rose-50 to-cyan-50 dark:from-rose-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-rose-200 dark:border-rose-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Festival Stages</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Multiple stages, food areas, workshop tents, and activity zones.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background blobs matching Fan Videos page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-orange-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-amber-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <a href="{{ marketing_url('/features/fan-videos') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 rounded-3xl border border-orange-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-orange-600 dark:group-hover:text-orange-300 transition-colors">Fan Videos & Comments</h3>
                        <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Fans add YouTube videos and comments to your events. All submissions need your approval.</p>
                        <span class="inline-flex items-center text-orange-500 dark:text-orange-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            Learn more
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Popular with -->
                <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 mb-6">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Venues</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-curators') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Curators</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-farmers-markets') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Farmers Markets</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

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
                    Everything you need to know about sub-schedules.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <!-- Q1: blue-to-sky -->
                <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">What are sub-schedules used for?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Sub-schedules let you organize events into categories within your schedule. For example, a venue might have sub-schedules for "Live Music," "Comedy Nights," and "Open Mic." Visitors can filter by sub-schedule to find what interests them.</p>
                    </div>
                </div>

                <!-- Q2: blue-to-cyan -->
                <div class="bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Can I nest sub-schedules?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Sub-schedules work as a single level of categorization within your schedule. Each event can belong to one or more sub-schedules, making it easy to organize without overcomplicating things.</p>
                    </div>
                </div>

                <!-- Q3: emerald-to-teal -->
                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">How does filtering work for visitors?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Visitors see filter buttons at the top of your schedule, one for each sub-schedule. They can click to show only events in that category, making it easy to find what they are looking for.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Related features</h2>
            <div class="space-y-3">
                <x-feature-link-card
                    name="Team Scheduling"
                    description="Invite team members to manage your schedule together"
                    :url="marketing_url('/features/team-scheduling')"
                    icon-color="amber"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Custom Fields"
                    description="Collect additional info from attendees with custom form fields"
                    :url="marketing_url('/features/custom-fields')"
                    icon-color="sky"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Embed Calendar"
                    description="Embed your event schedule on any website"
                    :url="marketing_url('/features/embed-calendar')"
                    icon-color="blue"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-rose-600 to-cyan-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Organize your events
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start using sub-schedules to categorize your events. Free on all plans.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-rose-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
