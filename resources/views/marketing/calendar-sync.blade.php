<x-marketing-layout>
    <x-slot name="title">Google Calendar Sync | Two-Way Event Synchronization - Event Schedule</x-slot>
    <x-slot name="description">Two-way sync with Google Calendar and CalDAV. Real-time webhook updates. Let attendees add events to Apple, Google, or Outlook calendars instantly.</x-slot>
    <x-slot name="keywords">Google Calendar sync, CalDAV sync, calendar integration, two-way sync, Apple Calendar, Outlook calendar, event sync, calendar webhook, automatic sync</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>


    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Calendar Integration</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Two-way<br>
                <span class="text-gradient">calendar sync</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Sync with Google Calendar or any CalDAV server. Let attendees add events to their favorite calendar app.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    Get started free
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Choose Your Integration -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Choose your integration
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Two powerful options for syncing your events.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Google Calendar Card -->
                <a href="{{ marketing_url('/google-calendar') }}" class="group block">
                    <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10 h-full">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-white dark:bg-gray-800 rounded-xl flex items-center justify-center">
                                <svg class="w-7 h-7" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white group-hover:text-blue-300 transition-colors">Google Calendar</h3>
                                <p class="text-gray-400 dark:text-white/60 text-sm">Real-time sync with webhooks</p>
                            </div>
                        </div>

                        <p class="text-gray-600 dark:text-white/80 mb-6">Connect with OAuth for instant, real-time synchronization. Changes in Google Calendar appear immediately via webhook notifications.</p>

                        <div class="flex flex-wrap gap-2 mb-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm">OAuth 2.0</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm">Webhooks</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm">Instant sync</span>
                        </div>

                        <div class="flex items-center text-blue-400 font-medium group-hover:gap-3 gap-2 transition-all">
                            Learn more
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- CalDAV Card -->
                <a href="{{ marketing_url('/caldav') }}" class="group block">
                    <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10 h-full">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white group-hover:text-teal-300 transition-colors">CalDAV</h3>
                                <p class="text-gray-400 dark:text-white/60 text-sm">Open standard, any server</p>
                            </div>
                        </div>

                        <p class="text-gray-600 dark:text-white/80 mb-6">Sync with any CalDAV-compatible server--Nextcloud, Radicale, Fastmail, iCloud, and more. Perfect for selfhosted setups.</p>

                        <div class="flex flex-wrap gap-2 mb-6">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm">Open standard</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm">Selfhosted</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm">Any server</span>
                        </div>

                        <div class="flex items-center text-teal-400 font-medium group-hover:gap-3 gap-2 transition-all">
                            Learn more
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Shared Features Bento Grid -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Powerful sync features
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Available with both Google Calendar and CalDAV.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Bidirectional Sync (full width) -->
                <div class="bento-card md:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-indigo-100 dark:from-violet-900 dark:to-indigo-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                Bidirectional
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">True two-way sync</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Push events to your calendar, pull events from your calendar, or sync both ways. Choose the direction that works for your workflow.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Push only</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Pull only</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Both directions</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="flex items-center gap-4">
                                    <!-- Event Schedule side -->
                                    <div class="bg-gradient-to-br from-violet-500/20 to-indigo-500/20 rounded-2xl border border-violet-400/30 p-4 w-32">
                                        <div class="text-xs text-violet-300 mb-2 text-center">Event Schedule</div>
                                        <div class="space-y-2">
                                            <div class="h-2 bg-white/20 rounded"></div>
                                            <div class="h-2 bg-white/20 rounded w-3/4"></div>
                                            <div class="h-2 bg-white/20 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                    <!-- Sync arrows -->
                                    <div class="flex flex-col items-center gap-1">
                                        <svg class="w-6 h-6 text-violet-400 animate-sync" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                        <svg class="w-6 h-6 text-violet-400 animate-sync" style="animation-delay: 0.5s;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                    </div>
                                    <!-- Calendar side -->
                                    <div class="bg-gray-100 dark:bg-white/10 rounded-2xl border border-gray-300 dark:border-white/20 p-4 w-32">
                                        <div class="text-xs text-gray-600 dark:text-gray-300 mb-2 text-center">Your Calendar</div>
                                        <div class="space-y-2">
                                            <div class="h-2 bg-blue-400/40 rounded"></div>
                                            <div class="h-2 bg-teal-400/40 rounded w-3/4"></div>
                                            <div class="h-2 bg-violet-400/40 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendee Calendar Integration -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-pink-100 dark:from-rose-900 dark:to-pink-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        For Attendees
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One-click add</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Visitors can add any event to their personal calendar with a single click. No account required.</p>

                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm6.369 17.308c-.281.281-.663.439-1.061.439H6.692c-.398 0-.78-.158-1.061-.439-.281-.281-.439-.663-.439-1.061V7.753c0-.398.158-.78.439-1.061.281-.281.663-.439 1.061-.439h10.616c.398 0 .78.158 1.061.439.281.281.439.663.439 1.061v8.494c0 .398-.158.78-.439 1.061z"/>
                            </svg>
                            Google
                        </span>
                        <span class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                            </svg>
                            Apple
                        </span>
                        <span class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7.88 12.04q0 .45-.11.87-.1.41-.33.74-.22.33-.58.52-.37.2-.87.2t-.85-.2q-.35-.21-.57-.55-.22-.33-.33-.75-.1-.42-.1-.86t.1-.87q.1-.43.34-.76.22-.34.59-.54.36-.2.87-.2t.86.2q.35.21.57.55.22.34.31.77.1.43.1.88zM24 12v9.38q0 .46-.33.8-.33.32-.8.32H7.13q-.46 0-.8-.33-.32-.33-.32-.8V18H1q-.41 0-.7-.3-.3-.29-.3-.7V7q0-.41.3-.7Q.58 6 1 6h6.63V2.55q0-.44.3-.75.3-.3.75-.3h14.7q.44 0 .75.3.3.3.3.75V12zm-6.5 0q0-.12-.1-.28-.1-.16-.1-.35v-.7q0-.18.1-.37.1-.18.1-.26 0-.15-.08-.27-.08-.12-.27-.12h-4.76l-.06.34q-.07.31-.13.72-.07.42-.08.88v.8q0 .18.08.18h.61q.5 0 .88-.07.38-.08.58-.4.09-.17.09-.3v-.07q.24.02.43.02.68 0 1.18-.23.5-.23.84-.62.33-.4.5-.92.18-.53.18-1.13zm-3.28-4.03q.14 0 .29-.1.15-.1.29-.1h2.32q.14 0 .28.1.14.1.28.1.27 0 .45-.18.18-.19.18-.46v-4.7q0-.27-.18-.45-.18-.19-.45-.19h-7.26q-.27 0-.45.19-.19.18-.19.45v4.7q0 .27.19.46.18.18.45.18h.91q.14 0 .28-.1.14-.1.29-.1z"/>
                            </svg>
                            Outlook
                        </span>
                    </div>
                </div>

                <!-- Multi-Calendar Support -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Flexible
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Choose your calendar</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-4">Select which calendar to sync with for each schedule. Different schedules can use different calendars.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-cyan-500/20 border border-cyan-400/30">
                            <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                            <span class="text-cyan-700 dark:text-cyan-200 text-sm">Work Events</span>
                            <svg class="w-4 h-4 text-cyan-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                            <div class="w-2 h-2 rounded-full bg-violet-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Personal</span>
                        </div>
                    </div>
                </div>

                <!-- Automatic Sync (full width) -->
                <div class="bento-card md:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col md:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Automatic
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3">Set it and forget it</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Events sync automatically when created, updated, or deleted. No manual work required.</p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 space-y-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center animate-sync">
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="text-emerald-700 dark:text-emerald-300 text-sm">Event created</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">synced</div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center animate-sync" style="animation-delay: 0.3s;">
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="text-emerald-700 dark:text-emerald-300 text-sm">Event updated</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">synced</div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center animate-sync" style="animation-delay: 0.6s;">
                                        <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="text-emerald-700 dark:text-emerald-300 text-sm">Event deleted</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">synced</div>
                                </div>
                            </div>
                        </div>
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
                    How sync works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Connect once, stay in sync forever.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Connect your calendar</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Choose Google Calendar (OAuth) or any CalDAV server (username/password).
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Choose direction</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Push events out, pull events in, or sync both ways. You decide.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Select calendar</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Pick which calendar to use. Different schedules can use different calendars.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        4
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Auto-sync</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Events sync automatically when created, updated, or deleted. No manual work.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Comparison Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Which should I choose?
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Both options offer two-way sync. Here's how they differ.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="text-left py-4 px-4 text-gray-500 dark:text-gray-400 font-medium">Feature</th>
                            <th class="text-center py-4 px-4">
                                <div class="flex items-center justify-center gap-2 text-blue-700 dark:text-blue-300 font-semibold">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    Google Calendar
                                </div>
                            </th>
                            <th class="text-center py-4 px-4">
                                <div class="flex items-center justify-center gap-2 text-teal-700 dark:text-teal-300 font-semibold">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    CalDAV
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">Authentication</td>
                            <td class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">OAuth 2.0 (one-click)</td>
                            <td class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">Username/password</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">Sync speed</td>
                            <td class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">Real-time (webhooks)</td>
                            <td class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">Every 15 minutes</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">Server options</td>
                            <td class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">Google only</td>
                            <td class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">Any CalDAV server</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">Selfhosted friendly</td>
                            <td class="py-4 px-4 text-center">
                                <span class="text-gray-500 dark:text-gray-400">Requires Google API setup</span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <svg class="w-5 h-5 text-teal-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">Two-way sync</td>
                            <td class="py-4 px-4 text-center">
                                <svg class="w-5 h-5 text-blue-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <svg class="w-5 h-5 text-teal-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 text-gray-600 dark:text-gray-300">Best for</td>
                            <td class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">Google users who want instant sync</td>
                            <td class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">Selfhosted or privacy-focused users</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background blobs matching Analytics page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-emerald-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-teal-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('marketing.analytics') }}" class="group block">
                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <!-- Text content -->
                        <div class="flex-1 text-center lg:text-left">
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-emerald-300 transition-colors">Built-in Analytics</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Track page views, device breakdown, and traffic sources. Privacy-first with no external services.</p>
                            <span class="inline-flex items-center text-emerald-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Mini mockup: Bar chart visualization -->
                        <div class="flex-shrink-0">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4 w-48">
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 mb-3">Views this week</div>
                                <div class="flex items-end justify-between h-20 gap-1.5">
                                    <div class="w-4 bg-emerald-500/40 rounded-t" style="height: 40%"></div>
                                    <div class="w-4 bg-emerald-500/50 rounded-t" style="height: 55%"></div>
                                    <div class="w-4 bg-emerald-500/60 rounded-t" style="height: 45%"></div>
                                    <div class="w-4 bg-emerald-500/70 rounded-t" style="height: 70%"></div>
                                    <div class="w-4 bg-emerald-500/80 rounded-t" style="height: 60%"></div>
                                    <div class="w-4 bg-emerald-500/90 rounded-t" style="height: 85%"></div>
                                    <div class="w-4 bg-emerald-500 rounded-t" style="height: 100%"></div>
                                </div>
                                <div class="flex justify-between mt-2 text-[9px] text-gray-600 dark:text-gray-400">
                                    <span>M</span>
                                    <span>T</span>
                                    <span>W</span>
                                    <span>T</span>
                                    <span>F</span>
                                    <span>S</span>
                                    <span>S</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-cyan-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Keep your calendars in sync
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Connect Google Calendar or any CalDAV server. Your events stay synchronized automatically.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule Calendar Sync",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Calendar Synchronization Software",
        "operatingSystem": "Web",
        "description": "Two-way sync with Google Calendar and CalDAV. Real-time webhook updates. Let attendees add events to Apple, Google, or Outlook calendars instantly.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "Google Calendar two-way sync",
            "CalDAV server support",
            "Real-time webhook updates",
            "Add-to-calendar buttons",
            "Apple Calendar support",
            "Outlook integration"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
