<x-marketing-layout>
    <x-slot name="title">Event Schedule for Curators | Build Your Local Event Guide</x-slot>
    <x-slot name="description">Build the ultimate local guide. Use AI-powered import, aggregate events from multiple sources, and grow your following. Built for bloggers, community orgs, and event aggregators.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Curators</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Curators",
        "description": "Build the ultimate local guide. Use AI-powered import, aggregate events from multiple sources, and grow your following. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Event Curators"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-amber-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For Bloggers, Community Orgs & Aggregators</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Build the ultimate<br>
                <span class="text-gradient">local guide</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Aggregate events from everywhere. Curate what's happening in your city or niche.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-amber-600 to-orange-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-amber-500/25">
                    Start curating
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

                <!-- AI-Powered Import (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                AI-Powered
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Smart event import</h2>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-6">Paste a URL, drop an image of a flyer or agenda, or copy text from anywhere. AI extracts event details automatically, even multiple events from a single image. No more manual data entry.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">URL parsing</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Image recognition</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Agenda scanning</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Any language</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Input side -->
                                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-3 max-w-xs">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Paste URL or text</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300 font-mono leading-relaxed">
                                        eventbrite.com/e/jazz...<br>
                                        <span class="text-amber-500 dark:text-amber-400">Parsing...</span>
                                    </div>
                                </div>
                                <!-- Arrow -->
                                <div class="flex justify-center my-2">
                                    <svg aria-hidden="true" class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <!-- Output side -->
                                <div class="bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-950 dark:to-orange-950 rounded-2xl border border-amber-300 dark:border-amber-400/30 p-4 max-w-xs">
                                    <div class="text-xs text-amber-600 dark:text-amber-300 mb-2">Extracted</div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Name:</span><span class="text-gray-900 dark:text-white">Jazz Night</span></div>
                                        <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Date:</span><span class="text-gray-900 dark:text-white">Mar 15, 8:00 PM</span></div>
                                        <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Venue:</span><span class="text-gray-900 dark:text-white">Blue Note</span></div>
                                        <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Price:</span><span class="text-gray-900 dark:text-white">$25</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- City Search Import -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900 dark:to-blue-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        City Search
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Discover local events</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Search for events by city. Import what you find directly to your schedule with one click.</p>

                    <div class="bg-white dark:bg-gray-900 rounded-xl p-3 border border-gray-200 dark:border-white/10 mb-3">
                        <div class="flex items-center gap-2">
                            <svg aria-hidden="true" class="w-4 h-4 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span class="text-gray-900 dark:text-white text-sm">Austin, TX</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-cyan-500/20 border border-cyan-400/30">
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm">SXSW Panel</div>
                                <div class="text-cyan-600 dark:text-cyan-300 text-xs">Convention Center</div>
                            </div>
                            <button class="px-2 py-1 rounded bg-cyan-500/30 text-cyan-700 dark:text-cyan-200 text-xs">Add</button>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-gray-300 text-sm">Live Music @ Stubbs</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">Red River District</div>
                            </div>
                            <button class="px-2 py-1 rounded bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Add</button>
                        </div>
                    </div>
                </div>

                <!-- Approval Workflow -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Approval
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Review before publishing</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Events from external sources go to your inbox. Review, edit, and approve before they appear on your schedule.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-sky-500/20 border border-sky-400/30">
                            <div class="flex-1">
                                <div class="text-white text-sm font-medium">Jazz Night @ Blue Note</div>
                                <div class="text-sky-300 text-xs">Pending review</div>
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
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5">
                            <div class="flex-1">
                                <div class="text-gray-300 text-sm font-medium">Comedy Show @ The Roxy</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">Pending review</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aggregate Multiple Sources (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-cyan-100 dark:from-rose-900 dark:to-cyan-900 border border-rose-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Aggregation
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Unified calendar</h2>
                            <p class="text-gray-600 dark:text-gray-400 text-lg">Pull events from venues, performers, and other curators. Display everything in one clean, searchable calendar.</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">Sources</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-rose-500/20 border border-rose-500/30">
                                    <div class="w-8 h-8 rounded-lg bg-rose-500/30 flex items-center justify-center">
                                        <svg aria-hidden="true" class="w-4 h-4 text-rose-600 dark:text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-gray-900 dark:text-white text-sm">Blue Note Jazz Club</div>
                                    </div>
                                    <span class="text-rose-600 dark:text-rose-300 text-xs">12 events</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-white/10 flex items-center justify-center">
                                        <svg aria-hidden="true" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-gray-600 dark:text-gray-300 text-sm">Sarah Johnson Trio</div>
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400 text-xs">8 events</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-white/10 flex items-center justify-center">
                                        <svg aria-hidden="true" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-gray-600 dark:text-gray-300 text-sm">Austin Music Blog</div>
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400 text-xs">24 events</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Email schedule graphics</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Generate shareable graphics of your weekly or monthly schedule. Perfect for newsletters and social media.</p>

                    <div class="bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-950 dark:to-blue-950 rounded-xl border border-blue-300 dark:border-blue-400/30 p-3">
                        <div class="text-center text-xs text-blue-600 dark:text-blue-300 mb-2">This Week in Austin</div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-gray-500 dark:text-gray-400 w-8">Fri</span>
                                <span class="text-gray-900 dark:text-white">Jazz @ Blue Note</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-gray-500 dark:text-gray-400 w-8">Sat</span>
                                <span class="text-gray-900 dark:text-white">Comedy @ Roxy</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-gray-500 dark:text-gray-400 w-8">Sun</span>
                                <span class="text-gray-900 dark:text-white">Art Walk</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Build Your Following -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Followers
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Build your audience</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Visitors can follow your schedule. They get notified when you add new events.</p>

                    <div class="text-center">
                        <div class="text-4xl font-bold text-emerald-400 mb-1">2,847</div>
                        <div class="text-gray-400 text-sm">followers</div>
                        <div class="flex justify-center mt-3 -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">A</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">B</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">C</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-sky-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">+</div>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Google Calendar sync</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Two-way sync keeps your curated calendar and Google Calendar in perfect harmony.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-300 mb-1 text-center">Guide</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-white/20 rounded"></div>
                                <div class="h-1.5 bg-white/20 rounded w-3/4"></div>
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
                        <div class="bg-white/10 rounded-xl border border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-600 dark:text-gray-300 mb-1 text-center">Google</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                <div class="h-1.5 bg-green-400/40 rounded w-3/4"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Already Have a Community Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/3 w-[300px] h-[300px] bg-emerald-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/3 w-[250px] h-[250px] bg-amber-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Already have a community?
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    If you're sharing events in a WhatsApp group, Facebook group, or anywhere else, you're already a curator.
                </p>
            </div>

            <div class="bg-gradient-to-br from-emerald-900/30 to-amber-900/30 dark:from-emerald-900 dark:to-amber-900 rounded-3xl border border-white/10 p-8 lg:p-10">
                <div class="flex flex-col lg:flex-row gap-8 items-center">
                    <!-- Social platforms side -->
                    <div class="flex-1">
                        <div class="flex flex-wrap justify-center lg:justify-start gap-4 mb-6">
                            <!-- WhatsApp -->
                            <div class="w-14 h-14 rounded-2xl bg-[#25D366]/20 border border-[#25D366]/30 flex items-center justify-center">
                                <svg aria-hidden="true" class="w-7 h-7 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <!-- Facebook -->
                            <div class="w-14 h-14 rounded-2xl bg-[#1877F2]/20 border border-[#1877F2]/30 flex items-center justify-center">
                                <svg aria-hidden="true" class="w-7 h-7 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <!-- Telegram -->
                            <div class="w-14 h-14 rounded-2xl bg-[#0088cc]/20 border border-[#0088cc]/30 flex items-center justify-center">
                                <svg aria-hidden="true" class="w-7 h-7 text-[#0088cc]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                </svg>
                            </div>
                            <!-- Instagram -->
                            <div class="w-14 h-14 rounded-2xl bg-[#E4405F]/20 border border-[#E4405F]/30 flex items-center justify-center">
                                <svg aria-hidden="true" class="w-7 h-7 text-[#E4405F]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.757-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                                </svg>
                            </div>
                            <!-- Email Newsletter -->
                            <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center">
                                <svg aria-hidden="true" class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 text-center lg:text-left">
                            Your group is great for community, but posts get buried and new members miss old events.
                        </p>
                    </div>

                    <!-- Arrow/Plus -->
                    <div class="flex items-center justify-center">
                        <div class="hidden lg:flex flex-col items-center gap-2">
                            <svg aria-hidden="true" class="w-8 h-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div class="lg:hidden">
                            <svg aria-hidden="true" class="w-8 h-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </div>
                    </div>

                    <!-- Event Schedule side -->
                    <div class="flex-1">
                        <div class="flex justify-center lg:justify-end mb-6">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/25">
                                <svg aria-hidden="true" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 text-center lg:text-right">
                            A dedicated event website is a permanent, searchable home anyone can discover.
                        </p>
                    </div>
                </div>

                <!-- Complementary message -->
                <p class="text-center text-amber-400 font-medium mt-6">
                    Your community + a professional event website
                </p>

                <!-- Benefits -->
                <div class="mt-10 pt-8 border-t border-white/10">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center mx-auto mb-3">
                                <svg aria-hidden="true" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <h4 class="text-gray-900 dark:text-white font-semibold mb-1">One link to share</h4>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Share a single link instead of posting events repeatedly</p>
                        </div>
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center mx-auto mb-3">
                                <svg aria-hidden="true" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <h4 class="text-gray-900 dark:text-white font-semibold mb-1">Searchable archive</h4>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Followers can browse past and upcoming events anytime</p>
                        </div>
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center mx-auto mb-3">
                                <svg aria-hidden="true" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-gray-900 dark:text-white font-semibold mb-1">Reach new people</h4>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Anyone can discover your events, even outside your group</p>
                        </div>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="mt-8 text-center">
                    <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl hover:scale-105 transition-all shadow-lg shadow-amber-500/25">
                        Get Started Free
                        <svg aria-hidden="true" class="ml-2 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
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
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition-colors">Stream to the world</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">Share live performances with fans worldwide. Add your streaming URL and sell tickets to viewers anywhere. No venue required.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Live streaming</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Global ticket sales</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Any platform</span>
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
                                        <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">Zoom</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-red-400"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">YouTube Live</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-blue-400"></div>
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
                    Start curating events in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create Your Guide</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Sign up and set up your local guide. Choose your city, niche, or theme.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Import Events</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Use AI import, city search, or aggregate from venues and performers.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Grow Your Audience</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Share your guide. Followers get notified when you add new events.
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
                <a href="{{ marketing_url('/for-talent') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Talent</div>
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
    <section class="relative bg-gradient-to-br from-amber-600 to-orange-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Become the go-to local guide
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start curating in minutes. Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-amber-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Curators",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Curation Software",
        "operatingSystem": "Web",
        "description": "Build the ultimate local guide. Use AI-powered import, aggregate events from multiple sources, and grow your following.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "AI-powered event import",
            "City search discovery",
            "Approval workflow",
            "Event aggregation",
            "Schedule graphics",
            "Follower notifications"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style {!! nonce_attr() !!}>
        @media (prefers-reduced-motion: reduce) {
            .animate-pulse-slow,
            .animate-float {
                animation: none;
            }
        }
    </style>
</x-marketing-layout>
