<x-marketing-layout>
    <x-slot name="title">Ticketing - Event Schedule</x-slot>
    <x-slot name="description">Sell tickets directly through your event schedule with QR codes, multiple ticket types, and secure payment processing.</x-slot>

    <style>
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        @keyframes scan-line {
            0%, 100% { transform: translateY(0); opacity: 1; }
            50% { transform: translateY(100px); opacity: 0.5; }
        }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-scan { animation: scan-line 2s ease-in-out infinite; }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .feature-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .feature-card:hover {
            transform: translateY(-8px);
        }
        .bento-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bento-card:hover {
            transform: scale(1.02);
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-fuchsia-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg class="w-4 h-4 text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                <span class="text-sm text-gray-300">Zero platform fees</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 leading-tight">
                Integrated<br>
                <span class="text-gradient">ticketing</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12">
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
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- QR Tickets (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-900/50 to-pink-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-500/20 text-fuchsia-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                QR Tickets
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-white mb-4">Contactless check-in</h3>
                            <p class="text-gray-400 text-lg mb-6">Every ticket comes with a unique QR code. Scan with any smartphone to check in attendees instantly. No special hardware required.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Instant validation</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Prevent duplicates</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Real-time tracking</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="relative animate-float">
                                <div class="w-48 h-64 bg-gradient-to-br from-white/10 to-white/5 rounded-2xl border border-white/20 p-4 shadow-2xl">
                                    <div class="text-center mb-3">
                                        <div class="text-white font-semibold">Jazz Night</div>
                                        <div class="text-fuchsia-300 text-sm">VIP Access</div>
                                    </div>
                                    <div class="w-32 h-32 mx-auto bg-white rounded-xl p-2 relative overflow-hidden">
                                        <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-fuchsia-500 to-violet-500 animate-scan"></div>
                                    </div>
                                    <div class="text-center mt-3 text-gray-400 text-xs">Scan to check in</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Ticket Types -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-900/50 to-indigo-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Ticket Types
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Multiple tiers</h3>
                    <p class="text-gray-400 mb-6">GA, VIP, early bird, group rates. Create as many ticket types as you need.</p>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/10 border border-white/10">
                            <div>
                                <div class="text-white font-medium">Early Bird</div>
                                <div class="text-emerald-400 text-xs">50 remaining</div>
                            </div>
                            <div class="text-xl font-bold text-white">$15</div>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/10 border border-white/10">
                            <div>
                                <div class="text-white font-medium">General</div>
                                <div class="text-gray-400 text-xs">200 remaining</div>
                            </div>
                            <div class="text-xl font-bold text-white">$25</div>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-violet-500/20 border border-violet-400/30">
                            <div>
                                <div class="text-white font-medium">VIP</div>
                                <div class="text-violet-300 text-xs">20 remaining</div>
                            </div>
                            <div class="text-xl font-bold text-white">$75</div>
                        </div>
                    </div>
                </div>

                <!-- Stripe Integration -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900/50 to-blue-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Payments
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Powered by Stripe</h3>
                    <p class="text-gray-400 mb-6">Accept credit cards, Apple Pay, Google Pay. Secure checkout built in.</p>

                    <div class="flex justify-center">
                        <div class="bg-white rounded-2xl p-6 shadow-lg">
                            <svg class="w-20 h-8" viewBox="0 0 60 25" fill="none">
                                <path fill="#635BFF" d="M59.64 14.28h-8.06c.19 1.93 1.6 2.55 3.2 2.55 1.64 0 2.96-.27 4.08-.94v2.86a10.1 10.1 0 01-4.43.91c-4.07 0-6.53-2.03-6.53-6.34 0-3.85 2.4-6.49 6.08-6.49 3.65 0 5.74 2.65 5.74 6.41 0 .38-.04.76-.08 1.04zm-5.65-4.76c-1.13 0-2.04.9-2.18 2.32h4.32c-.05-1.37-.84-2.32-2.14-2.32zM41.52 7.15c1.56 0 2.78.37 3.6 1l-.8 2.72a6.7 6.7 0 00-2.7-.62c-1.69 0-2.54.73-2.54 1.63 0 1.14 1.02 1.58 2.54 2.2 2.2.86 3.35 1.93 3.35 4.1 0 2.86-2.14 4.66-5.82 4.66-1.84 0-3.5-.45-4.55-1.17l.85-2.86c1.13.62 2.49 1.02 3.85 1.02 1.53 0 2.43-.6 2.43-1.65 0-1.02-.74-1.53-2.32-2.14-2.36-.9-3.56-2.07-3.56-4.18 0-2.5 1.97-4.71 5.67-4.71zM28.47 4.98c1.18 0 2.13.16 2.93.49v3.03a5.9 5.9 0 00-2.47-.54c-1.2 0-1.88.49-1.88 1.28 0 .76.53 1.15 1.77 1.65 2.15.86 3.14 2.03 3.14 3.97 0 2.8-2 4.48-5.33 4.48-1.54 0-2.96-.3-3.93-.82v-3.1c1.08.6 2.45.97 3.68.97 1.24 0 1.96-.46 1.96-1.3 0-.8-.53-1.2-1.89-1.75-2.04-.8-3.02-1.88-3.02-3.92 0-2.55 1.88-4.44 5.04-4.44zM13.47 7.15c2.15 0 3.62.95 4.53 2.32l-2.4 1.84c-.55-.82-1.23-1.22-2.13-1.22-1.55 0-2.67 1.27-2.67 3.19 0 1.92 1.12 3.2 2.67 3.2.9 0 1.63-.46 2.18-1.29l2.4 1.84c-.9 1.42-2.38 2.34-4.58 2.34-3.65 0-6.25-2.58-6.25-6.09s2.6-6.13 6.25-6.13zM3.68 7.37h3.68v12h-3.68v-12zm1.84-5.9c1.18 0 2.13.95 2.13 2.13a2.13 2.13 0 01-4.26 0c0-1.18.95-2.13 2.13-2.13z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Reservations (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900/50 to-teal-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Reservations
                            </div>
                            <h3 class="text-3xl font-bold text-white mb-4">Hold now, pay later</h3>
                            <p class="text-gray-400 text-lg">Let customers reserve tickets without paying upfront. Set expiration times and automatic reminders.</p>
                        </div>
                        <div class="bg-black/30 rounded-2xl p-6 border border-white/10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-white font-medium">2 tickets reserved</div>
                                    <div class="text-gray-400 text-sm">Jazz Night - VIP</div>
                                </div>
                            </div>
                            <div class="bg-emerald-500/10 rounded-xl p-4 border border-emerald-500/20">
                                <div class="flex justify-between items-center">
                                    <span class="text-emerald-300 text-sm">Expires in</span>
                                    <span class="text-white font-mono font-bold">23:45:12</span>
                                </div>
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
                    Start selling in minutes
                </h2>
                <p class="text-xl text-gray-500">
                    Four simple steps to your first ticket sale.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-fuchsia-500 to-violet-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-fuchsia-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Connect Stripe</h3>
                    <p class="text-gray-600 text-sm">
                        Link your Stripe account to receive payments directly.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-fuchsia-500 to-violet-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-fuchsia-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Add Tickets</h3>
                    <p class="text-gray-600 text-sm">
                        Create ticket types with prices and quantities for your event.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-fuchsia-500 to-violet-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-fuchsia-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Share Your Event</h3>
                    <p class="text-gray-600 text-sm">
                        Visitors can purchase tickets directly from your schedule.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-fuchsia-500 to-violet-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-fuchsia-500/25">
                        4
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Scan at Door</h3>
                    <p class="text-gray-600 text-sm">
                        Use your phone to scan QR codes and check in attendees.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Zero Fees Section -->
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border border-emerald-500/30 mb-8">
                    <span class="text-4xl font-bold text-emerald-400">$0</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    No platform fees. Ever.
                </h2>
                <p class="text-xl text-gray-400 mb-8 max-w-2xl mx-auto">
                    We don't take a cut of your ticket sales. You only pay Stripe's standard processing fees (typically 2.9% + $0.30 per transaction).
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl mx-auto">
                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                        <div class="w-12 h-12 rounded-xl bg-violet-500/20 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold mb-2">Stripe</h3>
                        <p class="text-gray-400 text-sm">Credit cards, Apple Pay, Google Pay</p>
                    </div>

                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold mb-2">Invoice Ninja</h3>
                        <p class="text-gray-400 text-sm">Professional invoicing</p>
                    </div>

                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                        <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold mb-2">Custom URL</h3>
                        <p class="text-gray-400 text-sm">Link to any payment system</p>
                    </div>
                </div>
            </div>
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
</x-marketing-layout>
