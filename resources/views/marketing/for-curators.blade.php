<x-marketing-layout>
    <x-slot name="title">Event Schedule for Curators | Build Your Local Event Guide</x-slot>
    <x-slot name="description">Build the ultimate local guide. Use AI-powered import, aggregate events from multiple sources, and grow your following. Built for bloggers, community orgs, and event aggregators.</x-slot>
    <x-slot name="keywords">event curator, local event guide, event aggregator, community calendar, event blog, city events, local happenings, event discovery, curated events</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-amber-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="text-sm text-gray-300">For Bloggers, Community Orgs & Aggregators</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 leading-tight">
                Build the ultimate<br>
                <span class="text-gradient">local guide</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12">
                Aggregate events from everywhere. Curate what's happening in your city or niche.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-amber-600 to-orange-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-amber-500/25">
                    Start curating
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- AI-Powered Import (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-900/50 to-orange-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                AI-Powered
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-white mb-4">Smart event import</h3>
                            <p class="text-gray-400 text-lg mb-6">Paste a URL, drop an image, or copy text from anywhere. AI extracts event details automatically. No more manual data entry.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">URL parsing</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Image recognition</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Any language</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Input side -->
                                <div class="bg-black/40 rounded-2xl border border-white/10 p-4 mb-3 max-w-xs">
                                    <div class="text-xs text-gray-500 mb-2">Paste URL or text</div>
                                    <div class="text-sm text-gray-300 font-mono leading-relaxed">
                                        eventbrite.com/e/jazz...<br>
                                        <span class="text-amber-400">Parsing...</span>
                                    </div>
                                </div>
                                <!-- Arrow -->
                                <div class="flex justify-center my-2">
                                    <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <!-- Output side -->
                                <div class="bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-2xl border border-amber-400/30 p-4 max-w-xs">
                                    <div class="text-xs text-amber-300 mb-2">Extracted</div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-400">Name:</span><span class="text-white">Jazz Night</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">Date:</span><span class="text-white">Mar 15, 8:00 PM</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">Venue:</span><span class="text-white">Blue Note</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">Price:</span><span class="text-white">$25</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- City Search Import -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-900/50 to-blue-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        City Search
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Discover local events</h3>
                    <p class="text-gray-400 mb-6">Search for events by city. Import what you find directly to your schedule with one click.</p>

                    <div class="bg-black/30 rounded-xl p-3 border border-white/10 mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span class="text-white text-sm">Austin, TX</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-cyan-500/20 border border-cyan-400/30">
                            <div class="flex-1">
                                <div class="text-white text-sm">SXSW Panel</div>
                                <div class="text-cyan-300 text-xs">Convention Center</div>
                            </div>
                            <button class="px-2 py-1 rounded bg-cyan-500/30 text-cyan-200 text-xs">Add</button>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/5">
                            <div class="flex-1">
                                <div class="text-gray-300 text-sm">Live Music @ Stubbs</div>
                                <div class="text-gray-500 text-xs">Red River District</div>
                            </div>
                            <button class="px-2 py-1 rounded bg-white/10 text-gray-300 text-xs">Add</button>
                        </div>
                    </div>
                </div>

                <!-- Approval Workflow -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900/50 to-violet-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Approval
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Review before publishing</h3>
                    <p class="text-gray-400 mb-6">Events from external sources go to your inbox. Review, edit, and approve before they appear on your schedule.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-500/20 border border-indigo-400/30">
                            <div class="flex-1">
                                <div class="text-white text-sm font-medium">Jazz Night @ Blue Note</div>
                                <div class="text-indigo-300 text-xs">Pending review</div>
                            </div>
                            <div class="flex gap-1">
                                <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5">
                            <div class="flex-1">
                                <div class="text-gray-300 text-sm font-medium">Comedy Show @ The Roxy</div>
                                <div class="text-gray-500 text-xs">Pending review</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aggregate Multiple Sources (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-900/50 to-pink-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Aggregation
                            </div>
                            <h3 class="text-3xl font-bold text-white mb-4">Unified calendar</h3>
                            <p class="text-gray-400 text-lg">Pull events from venues, performers, and other curators. Display everything in one beautiful, searchable calendar.</p>
                        </div>
                        <div class="bg-black/30 rounded-2xl p-5 border border-white/10">
                            <div class="text-xs text-gray-500 mb-3">Sources</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-rose-500/20 border border-rose-500/30">
                                    <div class="w-8 h-8 rounded-lg bg-rose-500/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-white text-sm">Blue Note Jazz Club</div>
                                    </div>
                                    <span class="text-rose-300 text-xs">12 events</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-white/5">
                                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-gray-300 text-sm">Sarah Johnson Trio</div>
                                    </div>
                                    <span class="text-gray-500 text-xs">8 events</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-white/5">
                                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-gray-300 text-sm">Austin Music Blog</div>
                                    </div>
                                    <span class="text-gray-500 text-xs">24 events</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-900/50 to-violet-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Email schedule graphics</h3>
                    <p class="text-gray-400 mb-6">Generate shareable graphics of your weekly or monthly schedule. Perfect for newsletters and social media.</p>

                    <div class="bg-gradient-to-br from-purple-500/20 to-violet-500/20 rounded-xl border border-purple-400/30 p-3">
                        <div class="text-center text-xs text-purple-300 mb-2">This Week in Austin</div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-gray-400 w-8">Fri</span>
                                <span class="text-white">Jazz @ Blue Note</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-gray-400 w-8">Sat</span>
                                <span class="text-white">Comedy @ Roxy</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-gray-400 w-8">Sun</span>
                                <span class="text-white">Art Walk</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Build Your Following -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900/50 to-teal-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Followers
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Build your audience</h3>
                    <p class="text-gray-400 mb-6">Visitors can follow your schedule. They get notified when you add new events.</p>

                    <div class="text-center">
                        <div class="text-4xl font-bold text-emerald-400 mb-1">2,847</div>
                        <div class="text-gray-400 text-sm">followers</div>
                        <div class="flex justify-center mt-3 -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 border-2 border-[#0a0a0f] flex items-center justify-center text-white text-xs">A</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 border-2 border-[#0a0a0f] flex items-center justify-center text-white text-xs">B</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-500 border-2 border-[#0a0a0f] flex items-center justify-center text-white text-xs">C</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 border-2 border-[#0a0a0f] flex items-center justify-center text-white text-xs">+</div>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-900/50 to-indigo-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Google Calendar sync</h3>
                    <p class="text-gray-400 mb-6">Two-way sync keeps your curated calendar and Google Calendar in perfect harmony.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-300 mb-1 text-center">Guide</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-white/20 rounded"></div>
                                <div class="h-1.5 bg-white/20 rounded w-3/4"></div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <div class="bg-white/10 rounded-xl border border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-300 mb-1 text-center">Google</div>
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

    <!-- How it Works -->
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500">
                    Start curating events in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Create Your Guide</h3>
                    <p class="text-gray-600 text-sm">
                        Sign up and set up your local guide. Choose your city, niche, or theme.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Import Events</h3>
                    <p class="text-gray-600 text-sm">
                        Use AI import, city search, or aggregate from venues and performers.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Grow Your Audience</h3>
                    <p class="text-gray-600 text-sm">
                        Share your guide. Followers get notified when you add new events.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-amber-600 to-orange-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Become the go-to local guide
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start curating in minutes. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-amber-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
</x-marketing-layout>
