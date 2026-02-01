<x-marketing-layout>
    <x-slot name="title">Google Calendar Sync - Event Schedule</x-slot>
    <x-slot name="description">Real-time two-way sync with Google Calendar. OAuth authentication, webhook updates, and multi-calendar support for seamless event management.</x-slot>
    <x-slot name="keywords">Google Calendar sync, calendar integration, two-way sync, OAuth, calendar webhook, automatic sync, real-time sync</x-slot>

    <style>
        /* Custom Google blue gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #4285F4 0%, #667eea 50%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-indigo-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Google Calendar</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Real-time<br>
                <span class="text-gradient">Google Calendar sync</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Two-way sync with webhook-powered instant updates. Connect with OAuth and stay synchronized automatically.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
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

                <!-- OAuth Authentication -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Secure
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">OAuth 2.0 authentication</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Connect with one click using Google's secure OAuth. No passwords stored. Automatic token refresh keeps you connected.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white dark:bg-gray-800 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Sign in with Google</div>
                                <div class="text-gray-400 dark:text-gray-400 text-xs">Secure OAuth connection</div>
                            </div>
                            <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Real-time Webhooks -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Real-time
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Instant webhook updates</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Changes in Google Calendar appear instantly. Webhook notifications mean no polling delays.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center animate-sync">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Event synced</div>
                                <div class="text-emerald-400 text-xs">Just now</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Three Sync Modes -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Flexible
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Three sync modes</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-4">Choose your direction: push events to Google, pull events from Google, or sync both ways.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Push to Google</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Pull from Google</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-violet-500/20 border border-violet-400/30">
                            <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span class="text-violet-700 dark:text-violet-300 text-sm">Both directions</span>
                        </div>
                    </div>
                </div>

                <!-- Multi-Calendar Support (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Multi-Calendar
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Choose your calendar</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Select which Google Calendar to sync with for each schedule. Perfect if you manage multiple calendars for work, personal, or different projects.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-cyan-500/20 border border-cyan-400/30">
                                <div class="w-3 h-3 rounded-full bg-cyan-400"></div>
                                <div class="text-gray-900 dark:text-white font-medium flex-1">Work Events</div>
                                <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                                <div class="w-3 h-3 rounded-full bg-violet-400"></div>
                                <div class="text-gray-600 dark:text-gray-300 font-medium flex-1">Personal</div>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                                <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                                <div class="text-gray-600 dark:text-gray-300 font-medium flex-1">Band Schedule</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Background Processing -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reliable
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Background processing</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-4">Sync jobs run in the background. Your schedule stays fast while events sync reliably behind the scenes.</p>

                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-amber-500/20 rounded-full h-2">
                            <div class="bg-amber-400 rounded-full h-2 w-3/4 animate-pulse"></div>
                        </div>
                        <span class="text-amber-700 dark:text-amber-300 text-xs">Syncing...</span>
                    </div>
                </div>

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
                    Connect once, stay in sync forever.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Connect Google</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Click "Connect Google Calendar" and authorize with OAuth. Your credentials stay secure.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Choose direction</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Push to Google, pull from Google, or sync both ways. You decide the flow.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Select calendar</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Pick which Google Calendar to use. Different schedules can sync to different calendars.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        4
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Auto-sync</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Events sync instantly via webhooks. Create, update, or delete—changes flow automatically.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- What Syncs Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    What gets synced
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    All the essential event details, perfectly synchronized.
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl p-6 border border-blue-200 dark:border-blue-500/20 text-center">
                    <svg class="w-8 h-8 text-blue-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">Event name</h3>
                    <p class="text-gray-400 dark:text-gray-400 text-sm">Title and summary</p>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl p-6 border border-blue-200 dark:border-blue-500/20 text-center">
                    <svg class="w-8 h-8 text-blue-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">Description</h3>
                    <p class="text-gray-400 dark:text-gray-400 text-sm">Full event details</p>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl p-6 border border-blue-200 dark:border-blue-500/20 text-center">
                    <svg class="w-8 h-8 text-blue-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">Date & time</h3>
                    <p class="text-gray-400 dark:text-gray-400 text-sm">Start and end times</p>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl p-6 border border-blue-200 dark:border-blue-500/20 text-center">
                    <svg class="w-8 h-8 text-blue-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">Location</h3>
                    <p class="text-gray-400 dark:text-gray-400 text-sm">Venue address</p>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl p-6 border border-blue-200 dark:border-blue-500/20 text-center">
                    <svg class="w-8 h-8 text-blue-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">Timezone</h3>
                    <p class="text-gray-400 dark:text-gray-400 text-sm">Automatic conversion</p>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-2xl p-6 border border-blue-200 dark:border-blue-500/20 text-center">
                    <svg class="w-8 h-8 text-blue-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">Event link</h3>
                    <p class="text-gray-400 dark:text-gray-400 text-sm">Back to your schedule</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Explore More Integrations -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <a href="{{ marketing_url('/features/integrations') }}" class="group block">
                <div class="bg-gradient-to-br from-gray-100 dark:from-gray-800/50 to-gray-200 dark:to-gray-900 rounded-3xl border border-gray-200 dark:border-white/10 p-8 hover:border-gray-300 dark:hover:border-white/20 transition-all">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-200 dark:bg-white/15 text-gray-600 dark:text-gray-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendars
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors">Explore more integrations</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Discover all the ways Event Schedule connects with your favorite tools.</p>
                    <span class="inline-flex items-center text-gray-600 dark:text-gray-300 font-medium group-hover:gap-3 gap-2 transition-all">
                        View all integrations
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-indigo-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Connect your Google Calendar
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Set up in seconds. Your events stay synchronized automatically.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
