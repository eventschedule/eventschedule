<x-marketing-layout>
    <x-slot name="title">Event Schedule for Visual Artists | Share Your Exhibitions</x-slot>
    <x-slot name="description">Share your exhibitions, open studios, and art fairs. Sell tickets directly, build your collector base with newsletters. Built for painters, sculptors, photographers, and visual artists. Zero platform fees.</x-slot>
    <x-slot name="keywords">visual artist schedule, art exhibition calendar, open studio events, art fair schedule, gallery show calendar, artist portfolio events, painting exhibition, sculpture show, photography exhibition</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Visual Artists</x-slot>

    <!-- Hero Section - Mesh Gradient -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute bottom-0 left-[-20%] w-[70%] h-[70%] bg-sky-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-0 right-[-10%] w-[50%] h-[60%] bg-cyan-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] h-[40%] bg-rose-600/10 rounded-full blur-[100px]"></div>
        </div>

        <!-- Canvas texture SVG overlay -->
        <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05]">
            <svg width="100%" height="100%">
                <defs>
                    <pattern id="canvas-weave" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
                        <path d="M0,4 L8,4 M4,0 L4,8" stroke="currentColor" stroke-width="0.5" class="text-sky-800 dark:text-sky-300"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#canvas-weave)"/>
            </svg>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge with picture frame border -->
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-sm glass artist-frame-badge mb-8 backdrop-blur-sm">
                <div class="relative">
                    <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium tracking-wide">For Visual Artists</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Your art. Your audience.<br>
                <span class="artist-glow-text">No gallery gate.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-4">
                From open studios to art fairs. One link for all your exhibitions.
            </p>
            <p class="text-lg text-gray-400 dark:text-gray-500 max-w-2xl mx-auto mb-12">
                Build your collector base directly.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="group inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-sky-600 to-cyan-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-lg shadow-sky-500/25">
                    Create your schedule
                    <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Medium tags -->
            <div class="mt-14 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-800/50">Painting</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-800/50">Sculpture</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 text-xs font-medium border border-rose-200 dark:border-rose-800/50">Photography</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 text-xs font-medium border border-blue-200 dark:border-blue-800/50">Printmaking</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-medium border border-amber-200 dark:border-amber-800/50">Mixed Media</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-800/50">Digital Art</span>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-sky-400 mb-2">80%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">of artists rely on word-of-mouth alone</div>
                </div>
                <div class="p-6 border-x border-gray-200 dark:border-white/5">
                    <div class="text-4xl font-bold text-cyan-400 mb-2">47%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">of collectors discover shows too late</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-rose-400 mb-2">$0</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">platform fees forever</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Exhibition Announcements (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-sky-500/5 rounded-full blur-[100px]"></div>

                    <div class="relative flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 text-sm font-medium mb-5 border border-sky-200 dark:border-sky-800/30">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Exhibition Announcements
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Tell collectors when you're showing</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">New exhibition opening? Art fair booth? Send beautiful announcements directly to collectors and fans. No algorithm deciding who sees your work.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Solo shows</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Group exhibitions</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Art fairs</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-950 dark:to-cyan-950 rounded-2xl border border-sky-300 dark:border-sky-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-500 to-cyan-500 flex items-center justify-center text-white text-sm font-semibold">EM</div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-semibold text-sm">Elena Martinez</div>
                                            <div class="text-sky-600 dark:text-sky-300 text-xs">Upcoming Exhibition</div>
                                        </div>
                                    </div>
                                    <div class="bg-gradient-to-br from-sky-600/30 to-cyan-600/30 rounded-xl p-3 border border-sky-400/20">
                                        <div class="text-center">
                                            <div class="text-gray-900 dark:text-white text-xs font-semibold mb-1">OPENING RECEPTION</div>
                                            <div class="text-sky-700 dark:text-sky-300 text-sm font-bold">Fragments of Light</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">Gallery Row / Sat 6-9 PM</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-4 text-xs">
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-emerald-500 dark:text-emerald-400 font-semibold">68%</span> opened</div>
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-amber-500 dark:text-amber-400 font-semibold">24%</span> clicked</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zero Fee Ticketing -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="absolute bottom-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-[80px]"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-5 border border-emerald-200 dark:border-emerald-800/30">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Ticketing
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Zero platform fees on tickets</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Opening receptions, workshops, studio visits. 100% of Stripe payments go to you.</p>

                        <div class="bg-emerald-500/20 rounded-xl border border-emerald-400/30 p-4">
                            <div class="text-center mb-3">
                                <div class="text-emerald-300 text-xs">You keep</div>
                                <div class="text-gray-900 dark:text-white text-3xl font-bold">100%</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">of ticket sales</div>
                            </div>
                            <div class="border-t border-emerald-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="text-emerald-400 font-semibold">$0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- One Link -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 text-sm font-medium mb-5 border border-sky-200 dark:border-sky-800/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Share Link
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One link for your portfolio & shows</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Put it in your website, Instagram bio, or artist statement. All your exhibitions in one place.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-sky-500/20 border border-sky-400/30 mb-3">
                            <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-gray-900 dark:text-white text-xs font-mono">eventschedule.com/yourart</span>
                        </div>
                        <div class="grid grid-cols-3 gap-1 text-center">
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-300 text-[10px]">Website</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-300 text-[10px]">Instagram</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-300 text-[10px]">CV</div>
                        </div>
                    </div>
                </div>

                <!-- Venue Sync (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-blue-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-sm font-medium mb-5 border border-blue-200 dark:border-blue-800/30">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Venue Sync
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Galleries add your show, it auto-appears</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">When a gallery adds your exhibition to their calendar, it automatically appears on yours. One listing, both schedules updated.</p>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex items-center gap-4">
                                <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-4 w-32">
                                    <div class="text-blue-300 text-xs text-center mb-2 font-semibold">Gallery</div>
                                    <div class="space-y-1.5">
                                        <div class="h-2 bg-white/20 rounded"></div>
                                        <div class="h-2 bg-blue-400/40 rounded w-3/4"></div>
                                        <div class="h-2 bg-white/10 rounded w-1/2"></div>
                                    </div>
                                    <div class="mt-3 p-2 rounded-lg bg-blue-400/20 border border-blue-400/30">
                                        <div class="text-[10px] text-white text-center font-medium">+ Your Show</div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <svg class="w-6 h-6 text-blue-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    <span class="text-blue-400 text-[10px]">auto-sync</span>
                                </div>
                                <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-4 w-32">
                                    <div class="text-gray-600 dark:text-gray-300 text-xs text-center mb-2 font-semibold">You</div>
                                    <div class="space-y-1.5">
                                        <div class="h-2 bg-white/20 rounded"></div>
                                        <div class="h-2 bg-sky-400/40 rounded w-3/4"></div>
                                        <div class="h-2 bg-white/10 rounded w-1/2"></div>
                                    </div>
                                    <div class="mt-3 p-2 rounded-lg bg-sky-400/20 border border-sky-400/30">
                                        <div class="text-[10px] text-gray-900 dark:text-white text-center font-medium">New show!</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-sm font-medium mb-5 border border-amber-200 dark:border-amber-800/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Auto-generated promo images</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Share exhibition announcements on social media instantly. No design tools needed.</p>

                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="w-32 h-32 bg-gradient-to-br from-sky-800 to-cyan-900 rounded-lg border border-sky-700 p-3 flex flex-col items-center justify-center text-center">
                                <div class="text-[8px] text-sky-300 uppercase tracking-wider mb-1">Exhibition</div>
                                <div class="text-white text-xs font-semibold">Fragments of Light</div>
                                <div class="text-sky-300 text-[8px] mt-1">Gallery Row / Mar 15</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-amber-500/80 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-sm font-medium mb-5 border border-blue-200 dark:border-blue-800/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Google Calendar for shows & deadlines</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Two-way sync. Exhibitions, install dates, openings, and submission deadlines all in one place.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-sky-400/40 rounded text-[6px] text-white px-1">Show</div>
                                <div class="h-1.5 bg-amber-400/40 rounded text-[6px] text-white px-1">Install</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                <!-- Analytics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-100 dark:bg-cyan-900/40 text-cyan-700 dark:text-cyan-300 text-sm font-medium mb-5 border border-cyan-200 dark:border-cyan-800/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Know your audience</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">See who views your schedule, which shows get the most interest, and where your collectors are.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 w-16">Views</div>
                            <div class="flex-1 h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-cyan-400/60 rounded-full" style="width: 82%"></div>
                            </div>
                            <div class="text-[10px] text-cyan-400 font-semibold">1.2k</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 w-16">Follows</div>
                            <div class="flex-1 h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-teal-400/60 rounded-full" style="width: 45%"></div>
                            </div>
                            <div class="text-[10px] text-teal-400 font-semibold">87</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 w-16">Tickets</div>
                            <div class="flex-1 h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-400/60 rounded-full" style="width: 30%"></div>
                            </div>
                            <div class="text-[10px] text-emerald-400 font-semibold">34</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Exhibition Season Calendar -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Plan your exhibition season
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400">
                    One schedule for every show throughout the year
                </p>
            </div>

            <div class="relative">
                <!-- Timeline line -->
                <div class="hidden md:block absolute top-1/2 left-0 right-0 h-0.5 bg-gradient-to-r from-sky-500/30 via-cyan-500/30 to-sky-500/30 -translate-y-1/2"></div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Spring -->
                    <div class="relative gallery-wall-card bg-gradient-to-br from-sky-50 to-cyan-50 dark:from-sky-950/50 dark:to-cyan-950/50 rounded-2xl border border-sky-200 dark:border-sky-800/30 p-6 text-center hover:rotate-0 transition-transform shadow-lg" style="transform: rotate(-1deg);">
                        <!-- Hanging wire -->
                        <div class="hidden md:block absolute -top-6 left-1/2 -translate-x-1/2 w-px h-6 bg-gray-400 dark:bg-gray-600"></div>
                        <div class="hidden md:block absolute -top-7 left-1/2 -translate-x-1/2 w-3 h-3 rounded-full border border-gray-400 dark:border-gray-600"></div>
                        <div class="text-sky-600 dark:text-sky-400 text-xs font-semibold uppercase tracking-wider mb-2">Spring</div>
                        <div class="text-gray-900 dark:text-white font-bold text-lg mb-1">Open Studio</div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Invite collectors into your creative space</div>
                    </div>

                    <!-- Summer -->
                    <div class="relative gallery-wall-card bg-gradient-to-br from-cyan-50 to-rose-50 dark:from-cyan-950/50 dark:to-rose-950/50 rounded-2xl border border-cyan-200 dark:border-cyan-800/30 p-6 text-center hover:rotate-0 transition-transform shadow-lg" style="transform: rotate(1deg);">
                        <!-- Hanging wire -->
                        <div class="hidden md:block absolute -top-6 left-1/2 -translate-x-1/2 w-px h-6 bg-gray-400 dark:bg-gray-600"></div>
                        <div class="hidden md:block absolute -top-7 left-1/2 -translate-x-1/2 w-3 h-3 rounded-full border border-gray-400 dark:border-gray-600"></div>
                        <div class="text-cyan-600 dark:text-cyan-400 text-xs font-semibold uppercase tracking-wider mb-2">Summer</div>
                        <div class="text-gray-900 dark:text-white font-bold text-lg mb-1">Art Fair</div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Booth at a regional or national fair</div>
                    </div>

                    <!-- Fall -->
                    <div class="relative gallery-wall-card bg-gradient-to-br from-rose-50 to-sky-50 dark:from-rose-950/50 dark:to-sky-950/50 rounded-2xl border border-rose-200 dark:border-rose-800/30 p-6 text-center hover:rotate-0 transition-transform shadow-lg" style="transform: rotate(-1deg);">
                        <!-- Hanging wire -->
                        <div class="hidden md:block absolute -top-6 left-1/2 -translate-x-1/2 w-px h-6 bg-gray-400 dark:bg-gray-600"></div>
                        <div class="hidden md:block absolute -top-7 left-1/2 -translate-x-1/2 w-3 h-3 rounded-full border border-gray-400 dark:border-gray-600"></div>
                        <div class="text-rose-600 dark:text-rose-400 text-xs font-semibold uppercase tracking-wider mb-2">Fall</div>
                        <div class="text-gray-900 dark:text-white font-bold text-lg mb-1">Solo Exhibition</div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Your own gallery show with opening reception</div>
                    </div>

                    <!-- Winter -->
                    <div class="relative gallery-wall-card bg-gradient-to-br from-blue-50 to-sky-50 dark:from-blue-950/50 dark:to-sky-950/50 rounded-2xl border border-blue-200 dark:border-blue-800/30 p-6 text-center hover:rotate-0 transition-transform shadow-lg" style="transform: rotate(1deg);">
                        <!-- Hanging wire -->
                        <div class="hidden md:block absolute -top-6 left-1/2 -translate-x-1/2 w-px h-6 bg-gray-400 dark:bg-gray-600"></div>
                        <div class="hidden md:block absolute -top-7 left-1/2 -translate-x-1/2 w-3 h-3 rounded-full border border-gray-400 dark:border-gray-600"></div>
                        <div class="text-blue-600 dark:text-blue-400 text-xs font-semibold uppercase tracking-wider mb-2">Winter</div>
                        <div class="text-gray-900 dark:text-white font-bold text-lg mb-1">Group Show</div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Collaborative exhibition with fellow artists</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Built for every visual medium
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether you work in oil or pixels, Event Schedule works for you
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Painters & Illustrators -->
                <x-sub-audience-card
                    name="Painters & Illustrators"
                    description="Gallery openings, studio shows, and art walks. Share your exhibition calendar and build a collector following."
                    icon-color="fuchsia"
                    blog-slug="for-painters-illustrators"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Sculptors & Installation Artists -->
                <x-sub-audience-card
                    name="Sculptors & Installation Artists"
                    description="Site-specific installations, gallery exhibitions, and public art unveilings. Show collectors where to experience your work."
                    icon-color="violet"
                    blog-slug="for-sculptors-installation-artists"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Photographers -->
                <x-sub-audience-card
                    name="Photographers"
                    description="Photo exhibitions, gallery talks, and portfolio reviews. One link for all your shows and events."
                    icon-color="blue"
                    blog-slug="for-photographers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Printmakers -->
                <x-sub-audience-card
                    name="Printmakers"
                    description="Print exhibitions, studio sales, and edition releases. Let collectors know when new work is available."
                    icon-color="rose"
                    blog-slug="for-printmakers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Mixed Media Artists -->
                <x-sub-audience-card
                    name="Mixed Media Artists"
                    description="Interdisciplinary exhibitions, pop-up shows, and collaborative projects. Manage your eclectic schedule."
                    icon-color="amber"
                    blog-slug="for-mixed-media-artists"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Digital Artists -->
                <x-sub-audience-card
                    name="Digital Artists"
                    description="Online exhibitions, NFT drops, and digital gallery shows. Share your virtual and IRL events in one place."
                    icon-color="cyan"
                    blog-slug="for-digital-artists"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
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
                    Three steps to a bigger collector base
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-600 to-cyan-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-sky-600/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your exhibitions</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Gallery shows, open studios, art fairs, pop-ups. Import from Google Calendar or add them manually.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-600 to-cyan-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-sky-600/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share your link</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Add it to your website, Instagram bio, artist CV, or email signature. One link, always current.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-sky-600 to-cyan-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-sky-600/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Build your collector base</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Collectors follow your schedule and get notified about new exhibitions. Grow your audience on your terms.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden border-t border-sky-200 dark:border-sky-900/20">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-[-10%] w-[50%] h-[60%] bg-sky-600/15 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 right-[-10%] w-[50%] h-[60%] bg-cyan-600/15 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Your art deserves an audience.<br>
                <span class="artist-glow-text">Not an algorithm.</span>
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                Stop relying on social media reach. Build your collector base directly.<br class="hidden md:block">Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-sky-600 to-cyan-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-xl shadow-sky-500/20">
                Get Started Free
                <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        "name": "Event Schedule for Visual Artists",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Visual Artist Exhibition Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your exhibitions, open studios, and art fairs. Sell tickets directly, build your collector base with newsletters. Built for painters, sculptors, photographers, and visual artists.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Exhibition announcements to collectors via email",
            "Zero-fee ticket sales for openings and events",
            "One link for all exhibitions and shows",
            "Gallery venue auto-sync",
            "Auto-generated promotional graphics",
            "Two-way Google Calendar sync",
            "Collector follower notifications",
            "Exhibition analytics and insights"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .artist-glow-text {
            background: linear-gradient(135deg, #4E81FA, #0EA5E9, #22D3EE);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(78, 129, 250, 0.3);
        }

        .artist-frame-badge {
            border: 2px solid rgba(161, 98, 7, 0.3);
            box-shadow: inset 0 0 0 2px rgba(161, 98, 7, 0.1), 0 1px 3px rgba(0,0,0,0.1);
        }

        .dark .artist-frame-badge {
            border-color: rgba(217, 119, 6, 0.3);
            box-shadow: inset 0 0 0 2px rgba(217, 119, 6, 0.1), 0 1px 3px rgba(0,0,0,0.3);
        }

        .gallery-wall-card {
            box-shadow: 4px 4px 12px rgba(0,0,0,0.1), -1px -1px 4px rgba(0,0,0,0.03);
        }

        .dark .gallery-wall-card {
            box-shadow: 4px 4px 16px rgba(0,0,0,0.3), -1px -1px 4px rgba(0,0,0,0.1);
        }
    </style>
</x-marketing-layout>
