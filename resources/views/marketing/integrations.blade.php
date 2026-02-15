<x-marketing-layout>
    <x-slot name="title">Integrations | Google Calendar, Stripe & More - Event Schedule</x-slot>
    <x-slot name="description">Connect Event Schedule with Google Calendar, CalDAV, Stripe, and Invoice Ninja for a streamlined event management experience.</x-slot>
    <x-slot name="keywords">event schedule integrations, Google Calendar sync, CalDAV sync, Stripe payments, Invoice Ninja, calendar integration, payment processing</x-slot>
    <x-slot name="socialImage">social/integrations.png</x-slot>
    <x-slot name="breadcrumbTitle">Integrations</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Integrations",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Connect Event Schedule with Google Calendar, CalDAV, Stripe, and Invoice Ninja for a streamlined event management experience.",
        "featureList": [
            "Google Calendar two-way sync",
            "CalDAV calendar support",
            "Stripe payment processing",
            "Invoice Ninja invoicing"
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
                <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Connect your tools</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Powerful<br>
                <span class="text-gradient">integrations</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Connect with the tools you already use. Sync calendars and accept payments with ease.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    Get started free
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Calendar Integrations Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-6">
                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Calendar Integrations</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Keep your events in sync</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-4">Sync across all your calendars automatically</p>
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium transition-colors">
                    Learn about all calendar options
                    <svg class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Calendar Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Google Calendar -->
                <a href="{{ marketing_url('/google-calendar') }}" class="bento-card group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8 h-full flex flex-col hover:border-blue-500/30 transition-all" aria-label="Learn more about Google Calendar integration">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4 w-fit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-blue-600 dark:group-hover:text-blue-300 transition-colors">Google Calendar</h3>

                    <div class="flex-grow">
                        <p class="text-gray-600 dark:text-white/80 mb-6">Two-way sync with Google Calendar. Import events automatically or push your schedule to Google. Changes sync in real-time.</p>

                        <div class="relative animate-float mb-6">
                            <div class="w-full bg-gradient-to-br from-gray-100 dark:from-white/10 to-gray-50 dark:to-white/5 rounded-2xl border border-gray-300 dark:border-white/20 p-4 shadow-2xl">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                            </svg>
                                        </div>
                                        <span class="text-gray-900 dark:text-white font-medium text-sm">Google Calendar</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        <span class="text-emerald-700 dark:text-emerald-300 text-[10px] font-medium">Synced</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-0 p-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-400/30">
                                        <div class="w-1 h-8 rounded-full bg-blue-500 mr-2.5 shrink-0"></div>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-gray-900 dark:text-white text-xs font-medium block">Jazz Night</span>
                                            <span class="text-gray-500 dark:text-gray-400 text-[10px]">Fri, 8:00 PM - 11:00 PM</span>
                                        </div>
                                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="flex items-center gap-0 p-2 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-400/30">
                                        <div class="w-1 h-8 rounded-full bg-emerald-500 mr-2.5 shrink-0"></div>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-gray-900 dark:text-white text-xs font-medium block">Open Mic</span>
                                            <span class="text-gray-500 dark:text-gray-400 text-[10px]">Sat, 7:00 PM - 9:30 PM</span>
                                        </div>
                                        <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="flex items-center gap-0 p-2 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-400/30">
                                        <div class="w-1 h-8 rounded-full bg-amber-500 mr-2.5 shrink-0"></div>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-gray-900 dark:text-white text-xs font-medium block">Comedy Show</span>
                                            <span class="text-gray-500 dark:text-gray-400 text-[10px]">Sun, 6:00 PM - 8:00 PM</span>
                                        </div>
                                        <svg class="w-3.5 h-3.5 text-blue-500 shrink-0 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="flex flex-wrap gap-3 mb-4 min-h-[40px]">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Two-way sync</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Auto import</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Real-time updates</span>
                        </div>
                        <span class="inline-flex items-center text-blue-600 group-hover:text-blue-700 dark:text-blue-300 dark:group-hover:text-blue-200 text-sm font-medium transition-colors group-hover:gap-2 gap-1 transition-all">
                            Learn more
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- CalDAV -->
                <a href="{{ marketing_url('/caldav') }}" class="bento-card group relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 h-full flex flex-col hover:border-teal-500/30 transition-all" aria-label="Learn more about CalDAV integration">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4 w-fit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-teal-600 dark:group-hover:text-teal-300 transition-colors">CalDAV</h3>

                    <div class="flex-grow">
                        <p class="text-gray-600 dark:text-white/80 mb-6">Sync with any CalDAV server: Nextcloud, Radicale, Fastmail, and more. Push, pull, or sync both ways to keep events in harmony.</p>

                        <div class="relative animate-float mb-6">
                            <div class="bg-gradient-to-br from-gray-100 dark:from-white/10 to-gray-50 dark:to-white/5 rounded-2xl border border-gray-300 dark:border-white/20 p-4 shadow-2xl">
                                <!-- URL Bar -->
                                <div class="flex items-center gap-2 bg-white dark:bg-white/10 rounded-lg border border-gray-200 dark:border-white/15 px-3 py-2 mb-3">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span class="text-gray-500 dark:text-gray-400 text-[10px] font-mono truncate">https://cloud.example.com/dav</span>
                                </div>
                                <!-- Calendars found -->
                                <div class="text-gray-500 dark:text-gray-400 text-[10px] font-medium uppercase tracking-wider mb-2">Calendars found</div>
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2 p-1.5 rounded-lg bg-teal-50 dark:bg-teal-500/15 border border-teal-200 dark:border-teal-400/30">
                                        <div class="w-2.5 h-2.5 rounded-full bg-teal-500 shrink-0"></div>
                                        <span class="text-gray-900 dark:text-white text-xs font-medium flex-1">My Events</span>
                                        <div class="flex items-center gap-0.5 text-teal-600 dark:text-teal-300">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18M17 8l4 4m0 0l-4 4"/></svg>
                                            <span class="text-[9px] font-medium">Both</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 p-1.5 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                                        <div class="w-2.5 h-2.5 rounded-full bg-blue-400 shrink-0"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs flex-1">Work</span>
                                        <div class="flex items-center gap-0.5 text-gray-400 dark:text-gray-500">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                            <span class="text-[9px] font-medium">Push</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 p-1.5 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                                        <div class="w-2.5 h-2.5 rounded-full bg-amber-400 shrink-0"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs flex-1">Shared</span>
                                        <div class="flex items-center gap-0.5 text-gray-400 dark:text-gray-500">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                                            <span class="text-[9px] font-medium">Pull</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="flex flex-wrap gap-2 mb-4 min-h-[40px]">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Two-way sync</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Selfhosted friendly</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Auto-discovery</span>
                        </div>
                        <span class="inline-flex items-center text-teal-600 group-hover:text-teal-700 dark:text-teal-300 dark:group-hover:text-teal-200 text-sm font-medium transition-colors group-hover:gap-2 gap-1 transition-all">
                            Learn more
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Payment Integrations Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-6">
                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Payment Integrations</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Accept payments and manage invoices</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">Get paid directly with secure payment processing</p>
            </div>

            <!-- Payment Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Stripe -->
                <a href="{{ marketing_url('/stripe') }}" class="bento-card group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8 h-full flex flex-col hover:border-blue-500/30 transition-all" aria-label="Learn more about Stripe payment integration">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4 w-fit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Payments
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-blue-600 dark:group-hover:text-blue-300 transition-colors">Stripe</h3>

                    <div class="flex-grow">
                        <p class="text-gray-600 dark:text-white/80 mb-6">Accept credit cards, Apple Pay, and Google Pay. Payments go directly to your Stripe account with no platform fees.</p>

                        <div class="relative animate-float mb-6">
                            <div class="bg-gradient-to-br from-gray-100 dark:from-white/10 to-gray-50 dark:to-white/5 rounded-2xl border border-gray-300 dark:border-white/20 p-4 shadow-2xl">
                                <!-- Header with Stripe badge -->
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-gray-900 dark:text-white text-xs font-semibold">Checkout</span>
                                    <div class="flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-500/20">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span class="text-[10px] font-bold" style="color: #635BFF;">stripe</span>
                                    </div>
                                </div>
                                <!-- Order summary -->
                                <div class="bg-white dark:bg-white/10 rounded-lg border border-gray-200 dark:border-white/10 p-2.5 mb-3">
                                    <div class="flex justify-between items-start mb-1.5">
                                        <div>
                                            <span class="text-gray-900 dark:text-white text-xs font-medium block">Jazz Night</span>
                                            <span class="text-gray-400 dark:text-gray-500 text-[10px]">2x VIP Ticket</span>
                                        </div>
                                        <span class="text-gray-900 dark:text-white text-xs font-semibold">$150.00</span>
                                    </div>
                                    <div class="border-t border-gray-100 dark:border-white/10 pt-1.5 flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400 text-[10px]">Total</span>
                                        <span class="text-gray-900 dark:text-white text-xs font-bold">$150.00</span>
                                    </div>
                                </div>
                                <!-- Payment method icons -->
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg border border-blue-200 dark:border-blue-400/30 bg-blue-50 dark:bg-blue-500/10">
                                        <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        <span class="text-blue-700 dark:text-blue-300 text-[10px] font-medium">Card</span>
                                    </div>
                                    <div class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                                        <svg class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
                                        <span class="text-gray-600 dark:text-gray-400 text-[10px] font-medium">Apple</span>
                                    </div>
                                    <div class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                                        <svg class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/></svg>
                                        <span class="text-gray-600 dark:text-gray-400 text-[10px] font-medium">Google</span>
                                    </div>
                                </div>
                                <!-- Pay button -->
                                <div class="w-full py-2 rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 text-center">
                                    <span class="text-white text-xs font-semibold">Pay $150.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="flex flex-wrap gap-3 mb-4 min-h-[40px]">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Credit cards</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Apple Pay</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Google Pay</span>
                        </div>
                        <span class="inline-flex items-center text-blue-600 group-hover:text-blue-700 dark:text-blue-300 dark:group-hover:text-blue-200 text-sm font-medium transition-colors group-hover:gap-2 gap-1 transition-all">
                            Learn more
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Invoice Ninja -->
                <a href="{{ marketing_url('/invoiceninja') }}" class="bento-card group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8 h-full flex flex-col hover:border-emerald-500/30 transition-all" aria-label="Learn more about Invoice Ninja integration">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4 w-fit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Invoicing
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-emerald-600 dark:group-hover:text-emerald-300 transition-colors">Invoice Ninja</h3>

                    <div class="flex-grow">
                        <p class="text-gray-600 dark:text-white/80 mb-6">Generate professional invoices for ticket purchases. Perfect for corporate events and B2B sales.</p>

                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-emerald-600 dark:text-emerald-400 text-xs font-medium">INVOICE #1042</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-xs">Paid</span>
                            </div>
                            <div class="text-gray-900 dark:text-white font-semibold mb-2">Corporate Event Package</div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-gray-500 dark:text-gray-400 text-sm">10 x VIP Ticket</span>
                                <span class="text-gray-700 dark:text-gray-300 text-sm">$500.00</span>
                            </div>
                            <div class="flex justify-between items-center mb-2.5">
                                <span class="text-gray-500 dark:text-gray-400 text-sm">5 x General Admission</span>
                                <span class="text-gray-700 dark:text-gray-300 text-sm">$250.00</span>
                            </div>
                            <div class="border-t border-gray-300 dark:border-white/10 pt-2 flex justify-between items-center">
                                <span class="text-gray-900 dark:text-white text-sm font-medium">Total</span>
                                <span class="text-gray-900 dark:text-white font-bold">$750.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="flex flex-wrap gap-3 mb-4 min-h-[40px]">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Professional invoices</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Corporate events</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Selfhosted option</span>
                        </div>
                        <span class="inline-flex items-center text-emerald-600 group-hover:text-emerald-700 dark:text-emerald-300 dark:group-hover:text-emerald-200 text-sm font-medium transition-colors group-hover:gap-2 gap-1 transition-all">
                            Learn more
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Why Integrate Section -->
    <section class="relative bg-gray-50 dark:bg-[#0a0a0f] py-24 overflow-hidden">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-sky-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 glass px-4 py-2 rounded-full mb-6">
                    <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Seamless workflow</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Why integrate?
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Keep your workflow smooth with tools that work together.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="feature-card bg-white/80 dark:bg-white/5 rounded-2xl p-8 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-xl hover:border-blue-300 dark:hover:border-blue-500/30 transition-all duration-300">
                    <div class="w-12 h-1 rounded-full bg-gradient-to-r from-blue-500 to-sky-500 mb-6"></div>
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-sky-500 shadow-lg shadow-blue-500/25 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Automatic Sync</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Changes in one place reflect everywhere. No manual updates needed.
                    </p>
                </div>

                <div class="feature-card bg-white/80 dark:bg-white/5 rounded-2xl p-8 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-xl hover:border-emerald-300 dark:hover:border-emerald-500/30 transition-all duration-300">
                    <div class="w-12 h-1 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 mb-6"></div>
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-500 shadow-lg shadow-emerald-500/25 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Secure & Private</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Your data stays yours. We use OAuth and encrypted connections.
                    </p>
                </div>

                <div class="feature-card bg-white/80 dark:bg-white/5 rounded-2xl p-8 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-xl hover:border-blue-300 dark:hover:border-blue-500/30 transition-all duration-300">
                    <div class="w-12 h-1 rounded-full bg-gradient-to-r from-blue-500 to-sky-500 mb-6"></div>
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-sky-500 shadow-lg shadow-blue-500/25 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Quick Setup</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Connect in minutes. Most integrations are just a few clicks.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-600 dark:to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Connect your tools today
            </h2>
            <p class="text-xl text-gray-600 dark:text-white/80 mb-10 max-w-2xl mx-auto">
                Get started for free and integrate with your favorite services.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 dark:text-blue-600 dark:bg-white dark:bg-none rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
