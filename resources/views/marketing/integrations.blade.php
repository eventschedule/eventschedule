<x-marketing-layout>
    <x-slot name="title">Integrations | Google Calendar, Stripe & More - Event Schedule</x-slot>
    <x-slot name="description">Connect Event Schedule with Google Calendar, CalDAV, Stripe, and Invoice Ninja for a seamless event management experience.</x-slot>
    <x-slot name="keywords">event schedule integrations, Google Calendar sync, CalDAV sync, Stripe payments, Invoice Ninja, calendar integration, payment processing</x-slot>
    <x-slot name="socialImage">social/integrations.png</x-slot>


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
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Connect your tools</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Powerful<br>
                <span class="text-gradient">integrations</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Connect with the tools you already use. Sync calendars and accept payments seamlessly.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-cyan-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
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
                <a href="{{ marketing_url('/calendar-sync') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium transition-colors">
                    Learn about all calendar options
                    <svg class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Calendar Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Google Calendar -->
                <a href="{{ marketing_url('/google-calendar') }}" class="bento-card group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 border border-gray-200 dark:border-white/10 p-8 h-full flex flex-col hover:border-blue-500/30 transition-all" aria-label="Learn more about Google Calendar integration">
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
                            <div class="w-full h-40 bg-gradient-to-br from-gray-100 dark:from-white/10 to-gray-50 dark:to-white/5 rounded-2xl border border-gray-300 dark:border-white/20 p-4 shadow-2xl">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                    </div>
                                    <span class="text-gray-900 dark:text-white font-medium text-sm">Google Calendar</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-blue-100 dark:bg-blue-500/20 border border-blue-200 dark:border-blue-400/30">
                                        <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                        <span class="text-blue-700 dark:text-blue-200 text-xs">Jazz Night - 8pm</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-400/30">
                                        <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                        <span class="text-emerald-700 dark:text-emerald-200 text-xs">Open Mic - 7pm</span>
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
                        <p class="text-gray-600 dark:text-white/80 mb-6">Sync with any CalDAV server—Nextcloud, Radicale, Fastmail, and more. Two-way sync keeps events in harmony.</p>

                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-teal-500/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="w-8 h-8 rounded-lg bg-teal-500/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m7-7l-7 7 7 7" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-center text-teal-600 dark:text-teal-300 text-xs font-medium">Two-way sync</div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="flex flex-wrap gap-2 mb-4 min-h-[40px]">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Two-way sync</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Selfhosted friendly</span>
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
                    <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
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
                <a href="{{ marketing_url('/stripe') }}" class="bento-card group relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-indigo-100 dark:from-violet-900 dark:to-indigo-900 border border-gray-200 dark:border-white/10 p-8 h-full flex flex-col hover:border-violet-500/30 transition-all" aria-label="Learn more about Stripe payment integration">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4 w-fit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Payments
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-violet-600 dark:group-hover:text-violet-300 transition-colors">Stripe</h3>

                    <div class="flex-grow">
                        <p class="text-gray-600 dark:text-white/80 mb-6">Accept credit cards, Apple Pay, and Google Pay. Payments go directly to your Stripe account.</p>

                        <div class="flex justify-center mb-6">
                            <div class="bg-white dark:bg-gray-800 rounded-2xl px-5 py-3 shadow-lg">
                                <span class="text-2xl font-bold" style="color: #635BFF;">stripe</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="flex flex-wrap gap-3 mb-4 min-h-[40px]">
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Credit cards</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Apple Pay</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Google Pay</span>
                        </div>
                        <span class="inline-flex items-center text-violet-600 group-hover:text-violet-700 dark:text-violet-300 dark:group-hover:text-violet-200 text-sm font-medium transition-colors group-hover:gap-2 gap-1 transition-all">
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
                            <div class="text-gray-900 dark:text-white font-semibold mb-1">Corporate Event Package</div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 dark:text-gray-400 text-sm">10 VIP Tickets</span>
                                <span class="text-gray-900 dark:text-white font-bold">$750.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="min-h-[40px] mb-4"></div>
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
    <section class="bg-gray-50 dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Why integrate?
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Keep your workflow seamless with tools that work together.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-gray-900/50 rounded-2xl p-8 border border-gray-200 dark:border-white/10 shadow-sm">
                    <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Automatic Sync</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Changes in one place reflect everywhere. No manual updates needed.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-900/50 rounded-2xl p-8 border border-gray-200 dark:border-white/10 shadow-sm">
                    <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Secure & Private</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Your data stays yours. We use OAuth and encrypted connections.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-900/50 rounded-2xl p-8 border border-gray-200 dark:border-white/10 shadow-sm">
                    <div class="w-14 h-14 bg-violet-100 dark:bg-violet-900/30 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
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
    <section class="relative bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-600 dark:to-cyan-700 py-24 overflow-hidden">
        <div class="absolute inset-0 hidden dark:block bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Connect your tools today
            </h2>
            <p class="text-xl text-gray-600 dark:text-white/80 mb-10 max-w-2xl mx-auto">
                Get started for free and integrate with your favorite services.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-cyan-600 dark:text-blue-600 dark:bg-white dark:bg-none rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
