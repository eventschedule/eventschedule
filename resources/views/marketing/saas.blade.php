<x-marketing-layout>
    <x-slot name="title">White-Label SaaS Platform - Run Your Own Ticketing Business</x-slot>
    <x-slot name="description">Launch your own white-label ticketing SaaS platform at zero cost. Set your own prices, keep 100% of revenue through your branded platform.</x-slot>
    <x-slot name="keywords">white label ticketing, saas reseller, ticketing platform, event management saas, white label events, reseller program</x-slot>
    <x-slot name="socialImage">social/saas.png</x-slot>

    <style>
        /* Custom blue gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #4f46e5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* Enhanced feature card hover for this page */
        .feature-card:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-indigo-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-violet-600/10 rounded-full blur-[150px]"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                <span class="text-sm text-gray-300">Free White-Label Platform</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 leading-tight">
                Your platform, your brand,<br>
                <span class="text-gradient">zero cost</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12">
                Launch your own ticketing SaaS business. Set your own prices, keep 100% of what you charge your customers, and build your brand.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('marketing.docs.saas') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    View Setup Guide
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white glass border border-white/10 rounded-2xl hover:bg-white/10 transition-all">
                    <svg class="mr-2 w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    GitHub Repository
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Your Business
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Run it your way</h2>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                    Full control over pricing, branding, and customer experience.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Set Your Own Prices -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-900/50 to-indigo-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Flexible Pricing
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Set your own prices</h3>
                    <p class="text-gray-400 mb-6">
                        Charge whatever you want for your service. Offer free plans, premium tiers, or anything in between. Your pricing, your rules.
                    </p>
                    <ul class="space-y-2 text-gray-300 text-sm">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Offer free plans to your customers
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Create premium subscription tiers
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Build your own business model
                        </li>
                    </ul>
                </div>

                <!-- Configurable Trials -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-900/50 to-purple-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Trial Management
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Configurable trials</h3>
                    <p class="text-gray-400 mb-6">
                        Control how long your customers can try the platform before subscribing. Set the trial length via a simple environment variable.
                    </p>
                    <div class="bg-black/30 rounded-xl p-4 mb-4">
                        <code class="text-sm text-gray-300">
                            <span class="text-violet-400">TRIAL_DAYS</span>=<span class="text-green-400">14</span>
                        </code>
                    </div>
                    <ul class="space-y-2 text-gray-300 text-sm">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Set any trial length
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            No trial required (set to 0)
                        </li>
                    </ul>
                </div>

                <!-- White-Label Branding -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-900/50 to-teal-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        Your Brand
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">White-label branding</h3>
                    <p class="text-gray-400 mb-6">
                        Make it yours with custom logos for both dark and light mode, your own domain, and your brand identity throughout the platform.
                    </p>
                    <ul class="space-y-2 text-gray-300 text-sm">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Custom logos (dark/light mode)
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Your own domain
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Custom app name
                        </li>
                    </ul>
                </div>

                <!-- Your Revenue -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900/50 to-green-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Keep Everything
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Your revenue</h3>
                    <p class="text-gray-400 mb-6">
                        Keep 100% of what you charge your customers. No revenue share, no hidden fees, no platform commissions.
                    </p>
                    <ul class="space-y-2 text-gray-300 text-sm">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            No revenue share
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            No platform fees
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Your Stripe, your money
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Why It's Free Section -->
    <section class="bg-[#0a0a0f] py-24 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    The Catch
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Why is it free?</h2>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                    We believe in building a community together. In exchange for the free platform, we ask for simple attribution.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">AAL License</h3>
                    <p class="text-gray-400 text-sm">Follow the Attribution Assurance License requirements</p>
                </div>

                <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Small Backlink</h3>
                    <p class="text-gray-400 text-sm">Discreet link in corner of public schedule pages</p>
                </div>
            </div>
        </div>
    </section>

    <!-- What Your Customers Get -->
    <section class="bg-[#0a0a0f] py-24 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        For Your Customers
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">What your customers get</h2>
                    <p class="text-xl text-gray-400 mb-8">
                        Your customers get a full-featured event management platform with everything they need to succeed.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-white">Sell tickets via Stripe Connect</h4>
                                <p class="text-gray-400 text-sm">Let your customers sell tickets and charge their own customers</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-white">Create and manage events</h4>
                                <p class="text-gray-400 text-sm">Full event management with recurring events, groups, and more</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-white">Their own subdomains</h4>
                                <p class="text-gray-400 text-sm">Each customer gets customer.yourdomain.com</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-white">Google Calendar sync</h4>
                                <p class="text-gray-400 text-sm">Bidirectional sync with Google Calendar</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="relative">
                    <div class="animate-float bg-gradient-to-br from-amber-900/50 to-orange-900/50 border border-white/10 rounded-2xl p-6 shadow-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">YB</span>
                                </div>
                                <div>
                                    <div class="text-white font-semibold">Your Brand</div>
                                    <div class="text-gray-400 text-sm">yourdomain.com</div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="bg-black/30 rounded-xl p-3 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/30 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-white text-sm">acme.yourdomain.com</div>
                                    <div class="text-gray-500 text-xs">Pro Plan</div>
                                </div>
                            </div>
                            <div class="bg-black/30 rounded-xl p-3 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/30 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-white text-sm">startup.yourdomain.com</div>
                                    <div class="text-gray-500 text-xs">Free Plan</div>
                                </div>
                            </div>
                            <div class="bg-black/30 rounded-xl p-3 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-violet-500/30 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-violet-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-white text-sm">events.yourdomain.com</div>
                                    <div class="text-gray-500 text-xs">Trial (12 days left)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Coming Soon: Federation -->
    <section class="bg-[#0a0a0f] py-24 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-500/20 text-fuchsia-300 text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Coming Soon
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Federation</h2>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                    An optional feature that benefits the entire community.
                </p>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-900/50 to-pink-900/50 border border-white/10 p-8">
                    <div class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-4">Share virtual events</h3>
                            <p class="text-gray-400 mb-6">
                                Optionally share your customers' virtual/online events with the central eventschedule.com listing. Not required - completely opt-in.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-center gap-3 text-gray-300">
                                    <svg class="w-5 h-5 text-fuchsia-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Get SEO traffic to your installation
                                </li>
                                <li class="flex items-center gap-3 text-gray-300">
                                    <svg class="w-5 h-5 text-fuchsia-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Expose events to wider audience
                                </li>
                                <li class="flex items-center gap-3 text-gray-300">
                                    <svg class="w-5 h-5 text-fuchsia-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Completely optional
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-4">Benefits the community</h3>
                            <p class="text-gray-400 mb-6">
                                When everyone participates, everyone benefits. More events means more users, which means a better platform for all.
                            </p>
                            <div class="bg-black/30 rounded-xl p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-lg bg-fuchsia-500/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-fuchsia-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-white font-medium">Global event discovery</span>
                                </div>
                                <p class="text-gray-400 text-sm">
                                    Virtual events from participating SaaS installations appear on eventschedule.com with links back to your platform.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Community Section -->
    <section class="bg-[#0a0a0f] py-24 border-t border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white/10 mb-8">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                    Building together
                </h2>
                <p class="text-xl text-gray-400 mb-6 max-w-2xl mx-auto">
                    We're all working toward the same goal: a better event management platform. Open source means everyone benefits from shared improvements.
                </p>
                <p class="text-gray-400 mb-8 max-w-2xl mx-auto">
                    <strong class="text-gray-300">No coding experience?</strong> No problem! This project is developed using AI-assisted coding. You can contribute by describing features or fixes in plain English via <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" class="text-blue-400 hover:text-blue-300 underline">GitHub Discussions</a>—we'll handle the implementation.
                </p>

                <div class="grid sm:grid-cols-3 gap-6 mb-10">
                    <div class="bg-white/5 rounded-xl p-6 border border-white/10">
                        <div class="text-3xl font-bold text-white mb-2">100%</div>
                        <div class="text-gray-400 text-sm">Open Source</div>
                    </div>
                    <div class="bg-white/5 rounded-xl p-6 border border-white/10">
                        <div class="text-3xl font-bold text-white mb-2">Free</div>
                        <div class="text-gray-400 text-sm">Forever</div>
                    </div>
                    <div class="bg-white/5 rounded-xl p-6 border border-white/10">
                        <div class="text-3xl font-bold text-white mb-2">AAL</div>
                        <div class="text-gray-400 text-sm">Licensed</div>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center gap-4">
                    <a href="https://github.com/eventschedule/eventschedule" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        Contribute on GitHub
                    </a>
                    <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        Join Discussions
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-indigo-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to launch your SaaS?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Get started with the setup guide. Everything you need to run your own white-label ticketing platform.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('marketing.docs.saas') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                    Read Setup Guide
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white border-2 border-white/30 rounded-2xl hover:bg-white/10 transition-all">
                    Or try eventschedule.com
                </a>
            </div>
        </div>
    </section>
</x-marketing-layout>
