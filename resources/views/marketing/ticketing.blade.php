<x-marketing-layout>
    <x-slot name="title">Event Ticketing with No Platform Fees | QR Check-in - Event Schedule</x-slot>
    <x-slot name="description">Sell tickets directly through your event schedule with QR codes, multiple ticket types, and secure payment processing.</x-slot>
    <x-slot name="keywords">event ticketing, sell tickets online, QR code tickets, Stripe ticketing, no platform fees tickets, event check-in, ticket reservations, multiple ticket types</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <style>
        /* Page-specific scan animation */
        @keyframes scan-line {
            0%, 100% { transform: translateY(0); opacity: 1; }
            50% { transform: translateY(100px); opacity: 0.5; }
        }
        .animate-scan { animation: scan-line 2s ease-in-out infinite; }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-fuchsia-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Zero platform fees</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Integrated<br>
                <span class="text-gradient">ticketing</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Sell tickets directly through your schedule. No plugins, no complicated setup.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-fuchsia-600 to-violet-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-fuchsia-500/25">
                    Start selling tickets
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

                <!-- QR Tickets (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-100 to-pink-100 dark:from-fuchsia-900 dark:to-pink-900 border border-fuchsia-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-500/20 text-fuchsia-700 dark:text-fuchsia-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                QR Tickets
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Contactless check-in</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Every ticket comes with a unique QR code. Scan with any smartphone to check in attendees instantly. No special hardware required.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Instant validation</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Prevent duplicates</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Real-time tracking</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="relative animate-float">
                                <div class="w-48 h-64 bg-gradient-to-br from-gray-200 dark:from-white/10 to-gray-100 dark:to-white/5 rounded-2xl border border-gray-300 dark:border-white/20 p-4 shadow-2xl">
                                    <div class="text-center mb-3">
                                        <div class="text-gray-900 dark:text-white font-semibold">Jazz Night</div>
                                        <div class="text-fuchsia-600 dark:text-fuchsia-300 text-sm">VIP Access</div>
                                    </div>
                                    <div class="w-32 h-32 mx-auto bg-white rounded-xl p-2 relative overflow-hidden">
                                        <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-fuchsia-500 to-violet-500 animate-scan"></div>
                                    </div>
                                    <div class="text-center mt-3 text-gray-500 dark:text-gray-400 text-xs">Scan to check in</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Ticket Types -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-indigo-100 dark:from-violet-900 dark:to-indigo-900 border border-violet-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-700 dark:text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Ticket Types
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Multiple tiers</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">GA, VIP, early bird, group rates. Create as many ticket types as you need.</p>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">Early Bird</div>
                                <div class="text-emerald-400 text-xs">50 remaining</div>
                            </div>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">$15</div>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">General</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">200 remaining</div>
                            </div>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">$25</div>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-violet-500/20 border border-violet-400/30">
                            <div>
                                <div class="text-gray-900 dark:text-white font-medium">VIP</div>
                                <div class="text-violet-600 dark:text-violet-300 text-xs">20 remaining</div>
                            </div>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">$75</div>
                        </div>
                    </div>
                </div>

                <!-- Payment Integration -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-blue-100 dark:from-indigo-900 dark:to-blue-900 border border-indigo-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Payments
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Flexible payments</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Choose Stripe for instant checkout or Invoice Ninja for B2B invoicing.</p>

                    <div class="flex justify-center gap-4">
                        <a href="{{ marketing_url('/stripe') }}" class="bg-white dark:bg-gray-800 rounded-xl px-4 py-3 shadow-lg hover:scale-105 transition-transform">
                            <span class="text-lg font-bold" style="color: #635BFF;">stripe</span>
                        </a>
                        <a href="{{ marketing_url('/invoiceninja') }}" class="bg-emerald-500/20 border border-emerald-400/30 rounded-xl px-4 py-3 hover:scale-105 transition-transform flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-emerald-600 dark:text-emerald-300 font-medium text-sm">Invoicing</span>
                        </a>
                    </div>
                </div>

                <!-- Reservations (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Reservations
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Hold now, pay later</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Let customers reserve tickets without paying upfront. Set expiration times and automatic reminders.</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white font-medium">2 tickets reserved</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-sm">Jazz Night - VIP</div>
                                </div>
                            </div>
                            <div class="bg-emerald-500/10 rounded-xl p-4 border border-emerald-500/20">
                                <div class="flex justify-between items-center">
                                    <span class="text-emerald-600 dark:text-emerald-300 text-sm">Expires in</span>
                                    <span class="text-gray-900 dark:text-white font-mono font-bold">23:45:12</span>
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
                    Start selling in minutes
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Four simple steps to your first ticket sale.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-fuchsia-500 to-violet-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-fuchsia-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Connect Payments</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Link Stripe or Invoice Ninja to receive payments directly.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-fuchsia-500 to-violet-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-fuchsia-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add Tickets</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Create ticket types with prices and quantities for your event.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-fuchsia-500 to-violet-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-fuchsia-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share Your Event</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Visitors can purchase tickets directly from your schedule.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-fuchsia-500 to-violet-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-fuchsia-500/25">
                        4
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Scan at Door</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Use your phone to scan QR codes and check in attendees.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Zero Fees Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border border-emerald-500/30 mb-8">
                    <span class="text-4xl font-bold text-emerald-400">$0</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    No platform fees. Ever.
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                    We don't take a cut of your ticket sales. You only pay Stripe's standard processing fees (typically 2.9% + $0.30 per transaction).
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto">
                    <a href="{{ marketing_url('/stripe') }}" class="bg-gray-100 dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 hover:bg-gray-200 dark:hover:bg-white/10 hover:border-violet-500/30 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-violet-500/20 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="text-gray-900 dark:text-white font-semibold mb-2 group-hover:text-violet-300 transition-colors">Stripe</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Credit cards, Apple Pay, Google Pay</p>
                        <span class="inline-flex items-center text-violet-400 text-xs font-medium">
                            Learn more
                            <svg class="ml-1 w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>

                    <a href="{{ marketing_url('/invoiceninja') }}" class="bg-gray-100 dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 hover:bg-gray-200 dark:hover:bg-white/10 hover:border-emerald-500/30 transition-all group">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-gray-900 dark:text-white font-semibold mb-2 group-hover:text-emerald-300 transition-colors">Invoice Ninja</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Professional invoicing for B2B</p>
                        <span class="inline-flex items-center text-emerald-400 text-xs font-medium">
                            Learn more
                            <svg class="ml-1 w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>

                    <div class="bg-gray-100 dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <h3 class="text-gray-900 dark:text-white font-semibold mb-2">Custom URL</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Link to any payment system</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background blobs matching AI page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-violet-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-fuchsia-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/ai') }}" class="group block">
                <div class="bg-gradient-to-br from-violet-100 to-indigo-100 dark:from-violet-900 dark:to-indigo-900 rounded-3xl border border-violet-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <!-- Text content -->
                        <div class="flex-1 text-center lg:text-left">
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-violet-300 transition-colors">AI-Powered Import</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Paste text or drop an image. AI extracts event details automatically.</p>
                            <span class="inline-flex items-center text-violet-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Mini mockup: Input→Output text parsing -->
                        <div class="flex-shrink-0">
                            <div class="flex flex-col items-center gap-2">
                                <!-- Input -->
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-3 w-48">
                                    <div class="text-[10px] text-gray-400 dark:text-gray-400 mb-1">Raw text</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono leading-relaxed">
                                        Jazz Night<br>
                                        Friday 8pm<br>
                                        Blue Note
                                    </div>
                                </div>
                                <!-- Arrow -->
                                <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                                <!-- Output -->
                                <div class="bg-violet-500/20 rounded-xl border border-violet-400/30 p-3 w-48">
                                    <div class="text-[10px] text-violet-600 dark:text-violet-300 mb-1">Extracted</div>
                                    <div class="space-y-1 text-xs">
                                        <div class="flex justify-between"><span class="text-gray-400 dark:text-gray-400">Name:</span><span class="text-gray-900 dark:text-white">Jazz Night</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400 dark:text-gray-400">Time:</span><span class="text-gray-900 dark:text-white">Fri 8 PM</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400 dark:text-gray-400">Venue:</span><span class="text-gray-900 dark:text-white">Blue Note</span></div>
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
    <section class="relative bg-gradient-to-br from-fuchsia-600 to-violet-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Start selling tickets today
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Set up ticketing for your events in minutes. No credit card required.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-fuchsia-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule Ticketing",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Ticketing Software",
        "operatingSystem": "Web",
        "description": "Sell tickets directly through your event schedule with QR codes, multiple ticket types, and secure Stripe payment processing. Zero platform fees.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free with Pro plan"
        },
        "featureList": [
            "Zero platform fees",
            "QR code tickets",
            "Multiple ticket types",
            "Stripe payment processing",
            "Mobile check-in",
            "Automatic confirmation emails"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
