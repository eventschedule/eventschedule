<x-marketing-layout>
    <x-slot name="title">Stripe Payments - Event Schedule</x-slot>
    <x-slot name="description">Accept credit cards, Apple Pay, and Google Pay for ticket sales. Secure Stripe Checkout with direct payouts and no platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">Stripe</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Stripe Payments",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Accept credit cards, Apple Pay, and Google Pay for ticket sales. Secure Stripe Checkout with direct payouts and no platform fees.",
        "featureList": [
            "Stripe Checkout integration",
            "Credit card payments",
            "Apple Pay support",
            "Google Pay support",
            "Direct payouts",
            "Zero platform fees"
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
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <div class="bg-white rounded px-2 py-0.5">
                    <span class="text-sm font-bold" style="color: #635BFF;">stripe</span>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-300">Payment Processing</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Accept payments with<br>
                <span class="text-gradient">Stripe</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Credit cards, Apple Pay, Google Pay. Secure checkout with direct payouts to your account.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    Get started free
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

                <!-- Stripe Checkout -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Checkout
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Stripe Checkout</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Hosted payment page handles everything. Customers complete payment on Stripe's secure checkout.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <div class="w-8 h-8 bg-black rounded-lg flex items-center justify-center border border-gray-300 dark:border-white/20">
                                    <svg aria-hidden="true" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.05 11.97c-.02-1.99 1.63-2.96 1.7-3-.93-1.35-2.37-1.54-2.88-1.56-1.22-.12-2.39.72-3.01.72-.62 0-1.58-.7-2.6-.68-1.34.02-2.57.78-3.26 1.97-1.4 2.41-.36 5.98.99 7.94.66.96 1.45 2.03 2.49 1.99 1-.04 1.37-.64 2.58-.64 1.2 0 1.54.64 2.59.62 1.08-.02 1.76-.97 2.41-1.93.76-1.11 1.07-2.18 1.09-2.24-.02-.01-2.09-.8-2.1-3.19z"/>
                                    </svg>
                                </div>
                                <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-4 h-4" viewBox="0 0 24 24">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                </div>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">All major payment methods</span>
                        </div>
                    </div>
                </div>

                <!-- Multiple Currencies -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Global
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">135+ currencies</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-4">Sell tickets in your local currency. Stripe handles conversion and international payments.</p>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">USD</span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">EUR</span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">GBP</span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">CAD</span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-blue-500/20 text-blue-700 dark:text-blue-300 text-sm">+130 more</span>
                    </div>
                </div>

                <!-- Webhook Integration -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Real-time
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Instant confirmation</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-4">Webhooks notify us the moment payment completes. Tickets are delivered instantly via email.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-3 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                            <span class="text-emerald-700 dark:text-emerald-300 text-xs font-mono">payment_intent.succeeded</span>
                        </div>
                    </div>
                </div>

                <!-- Stripe Connect (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                SaaS Mode
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Stripe Connect</h2>
                            <p class="text-gray-600 dark:text-white/80 text-lg">On our hosted platform, event creators connect their own Stripe accounts. Payments go directly to them - we never hold your money.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-gray-900 dark:text-white font-medium">Event creator</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-sm">Connects Stripe account</div>
                                </div>
                            </div>
                            <div class="flex justify-center">
                                <svg aria-hidden="true" class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-sky-500/20 border border-sky-400/30">
                                <div class="w-10 h-10 rounded-full bg-sky-500/30 flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-5 h-5 text-sky-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-gray-900 dark:text-white font-medium">Direct payout</div>
                                    <div class="text-sky-700 dark:text-sky-300 text-sm">Funds go to creator's account</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Portal -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Self-service
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Billing portal</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-4">Customers manage their own payment methods, view invoices, and update billing details through Stripe's portal.</p>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Update card</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">View invoices</span>
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
                    Start accepting payments in four simple steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Connect Stripe</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Link your Stripe account with one click. We use Stripe Connect for secure authorization.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create tickets</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Add ticket types to your events with prices and quantities. Set up multiple tiers if needed.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share event</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Customers buy tickets directly from your schedule page. No redirects to external sites.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        4
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Get paid</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Payments go directly to your Stripe account. Standard Stripe payouts to your bank.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Why Stripe?
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    The trusted choice for online payments worldwide.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 rounded-2xl p-8 border border-blue-200 dark:border-white/10">
                    <div class="w-14 h-14 bg-blue-500/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg aria-hidden="true" class="w-7 h-7 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">PCI Compliant</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Stripe handles all sensitive card data. We never see or store payment details.
                    </p>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl p-8 border border-emerald-200 dark:border-white/10">
                    <div class="w-14 h-14 bg-emerald-500/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg aria-hidden="true" class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">No Platform Fees</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        We don't take a cut. You only pay Stripe's standard processing fees (typically 2.9% + $0.30).
                    </p>
                </div>

                <div class="bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 rounded-2xl p-8 border border-blue-200 dark:border-white/10">
                    <div class="w-14 h-14 bg-blue-500/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg aria-hidden="true" class="w-7 h-7 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Global Reach</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Accept payments from customers worldwide. Available in 46+ countries.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stripe Link -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <a href="https://stripe.com" target="_blank" rel="noopener noreferrer" class="group block">
                <div class="bg-gradient-to-br from-blue-900/30 to-sky-900/30 rounded-3xl border border-gray-200 dark:border-white/10 p-8 hover:border-gray-300 dark:hover:border-white/20 transition-all">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-700 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Official Site
                    </div>
                    <div class="flex justify-center mb-4">
                        <div class="bg-white dark:bg-gray-800 rounded-xl px-6 py-3">
                            <span class="text-2xl font-bold" style="color: #635BFF;">stripe</span>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors">Learn more about Stripe</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Explore Stripe's documentation, features, and developer resources for payment processing.</p>
                    <span class="inline-flex items-center text-blue-700 dark:text-blue-300 font-medium group-hover:gap-3 gap-2 transition-all">
                        Visit stripe.com
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </section>

    <!-- Explore More Integrations -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <a href="{{ marketing_url('/features/integrations') }}" class="group block">
                <div class="bg-gradient-to-br from-gray-100 dark:from-gray-800/50 to-gray-200 dark:to-gray-900 rounded-3xl border border-gray-200 dark:border-white/10 p-8 hover:border-gray-300 dark:hover:border-white/20 transition-all">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-200 dark:bg-white/15 text-gray-600 dark:text-gray-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Payments
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors">Explore more integrations</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Discover all the ways Event Schedule connects with your favorite tools.</p>
                    <span class="inline-flex items-center text-gray-600 dark:text-gray-300 font-medium group-hover:gap-3 gap-2 transition-all">
                        View all integrations
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Start accepting payments
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Connect your Stripe account and sell tickets today. No setup fees.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
