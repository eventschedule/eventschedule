<x-marketing-layout>
    <x-slot name="title">Event Categories & Sub-Schedules | Multi-Room Venues - Event Schedule</x-slot>
    <x-slot name="description">Organize events into categories with unlimited sub-schedules. Perfect for venues with multiple rooms, stages, or event series.</x-slot>
    <x-slot name="keywords">sub-schedules, event categories, venue rooms, event series, calendar organization, event filtering, multi-room venues</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <style>
        /* Custom rose gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #f43f5e 0%, #ec4899 50%, #f472b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-rose-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-pink-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-rose-600 to-pink-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-rose-500/25">
                    Get started free
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-pink-100 dark:from-rose-900 dark:to-pink-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Unlimited
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Create as many as you need</h3>
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
                </div>

                <!-- Calendar Filtering -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-pink-100 to-fuchsia-100 dark:from-pink-900 dark:to-fuchsia-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-100 text-pink-700 dark:bg-pink-500/20 dark:text-pink-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filtering
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Easy filtering for visitors</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">When you have multiple sub-schedules, visitors see a dropdown filter on your calendar. They can quickly find events in the room or series they care about.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3">
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-white/5 rounded-lg">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Filter by schedule</span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Direct Links -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-100 to-purple-100 dark:from-fuchsia-900 dark:to-purple-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/20 dark:text-fuchsia-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Direct Links
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Shareable URLs</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Each sub-schedule gets its own URL. Share links directly to specific rooms or event series. Visitors land on exactly what they want to see.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500">/venue/</span>
                            <span class="text-fuchsia-300">main-stage</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500">/venue/</span>
                            <span class="text-fuchsia-300">jazz-lounge</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500">/venue/</span>
                            <span class="text-fuchsia-300">comedy-nights</span>
                        </div>
                    </div>
                </div>

                <!-- API Integration (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-100 to-violet-100 dark:from-purple-900 dark:to-violet-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                API Support
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Automate with the API</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Create events programmatically and assign them to sub-schedules using the API. Pass the <code class="text-purple-300 bg-purple-500/20 px-1 rounded">schedule</code> parameter when creating events.</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4 font-mono text-sm">
                            <div class="text-gray-500 mb-2">// Create event via API</div>
                            <div class="text-purple-300">POST /api/events</div>
                            <div class="text-gray-500 dark:text-gray-400 mt-2">{</div>
                            <div class="text-gray-500 dark:text-gray-400 pl-4">"name": "Jazz Night",</div>
                            <div class="text-gray-500 dark:text-gray-400 pl-4">"schedule": "<span class="text-purple-300">jazz-lounge</span>",</div>
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
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Concert Venues</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Organize by main stage, side stage, VIP area, and outdoor spaces.</p>
                </div>

                <!-- Conference Centers -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Conference Centers</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Separate tracks for different topics, workshops, and keynotes.</p>
                </div>

                <!-- Theaters -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Theaters</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Main theater, black box, rehearsal space, and special screenings.</p>
                </div>

                <!-- Event Series -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Event Series</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Group recurring events like weekly trivia, monthly open mics, or seasonal markets.</p>
                </div>

                <!-- Multi-Purpose Venues -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Multi-Purpose Venues</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Bars, restaurants, and community centers with different event types.</p>
                </div>

                <!-- Festival Stages -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        <!-- Animated background blobs matching Online Events page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-indigo-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-violet-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-indigo-100 to-violet-100 dark:from-indigo-900 dark:to-violet-900 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <!-- Text content -->
                        <div class="flex-1 text-center lg:text-left">
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-indigo-300 transition-colors">Online Events</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Toggle any event to online and add your streaming URL. Works with Zoom, YouTube, or any platform.</p>
                            <span class="inline-flex items-center text-indigo-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Mini mockup: Toggle switch with streaming URL field -->
                        <div class="flex-shrink-0">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4 w-48">
                                <!-- Toggle switch -->
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs">Online Event</span>
                                    <div class="w-10 h-5 bg-indigo-500 rounded-full relative">
                                        <div class="absolute right-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow"></div>
                                    </div>
                                </div>
                                <!-- URL field -->
                                <div>
                                    <div class="text-[10px] text-gray-500 mb-1">Streaming URL</div>
                                    <div class="bg-indigo-500/20 rounded-lg px-2 py-1.5 text-indigo-300 text-xs border border-indigo-400/30 flex items-center gap-1.5">
                                        <svg class="w-3 h-3 text-indigo-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-rose-600 to-pink-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Organize your events
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start using sub-schedules to categorize your events. Free on all plans.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-rose-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
