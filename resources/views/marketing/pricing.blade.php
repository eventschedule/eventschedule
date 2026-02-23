<x-marketing-layout>
    <x-slot name="title">Free Event Calendar & Ticketing Pricing Plans - Event Schedule</x-slot>
    <x-slot name="description">Start free with unlimited events. Upgrade to Pro for ticketing, event boosting, and white-label branding, or Enterprise for AI features, custom CSS, and priority support. No hidden fees.</x-slot>
    <x-slot name="socialImage">social/pricing.png</x-slot>
    <x-slot name="breadcrumbTitle">Pricing</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "offers": [
            {
                "@type": "Offer",
                "name": "Free",
                "price": "0",
                "priceCurrency": "USD",
                "description": "Professional event calendars, mobile-optimized design, custom schedule URLs, team collaboration, venue maps, and Google Calendar sync.",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Pro",
                "price": "5.00",
                "priceCurrency": "USD",
                "billingIncrement": "MON",
                "description": "Everything in Free plus remove branding, ticketing with QR check-ins, Stripe payments, event graphics, event boosting with ads, and REST API access.",
                "availability": "https://schema.org/InStock",
                "priceSpecification": [
                    {
                        "@type": "UnitPriceSpecification",
                        "price": "5.00",
                        "priceCurrency": "USD",
                        "unitText": "MONTH",
                        "referenceQuantity": {
                            "@type": "QuantitativeValue",
                            "value": "1",
                            "unitCode": "MON"
                        }
                    },
                    {
                        "@type": "UnitPriceSpecification",
                        "price": "50.00",
                        "priceCurrency": "USD",
                        "unitText": "YEAR",
                        "referenceQuantity": {
                            "@type": "QuantitativeValue",
                            "value": "1",
                            "unitCode": "ANN"
                        }
                    }
                ]
            },
            {
                "@type": "Offer",
                "name": "Enterprise",
                "price": "15.00",
                "priceCurrency": "USD",
                "billingIncrement": "MON",
                "description": "Everything in Pro plus AI text transformation, email scheduling, agenda scanning, custom CSS styling, multiple team members, 1,000 newsletters per month, and priority support.",
                "availability": "https://schema.org/InStock",
                "priceSpecification": [
                    {
                        "@type": "UnitPriceSpecification",
                        "price": "15.00",
                        "priceCurrency": "USD",
                        "unitText": "MONTH",
                        "referenceQuantity": {
                            "@type": "QuantitativeValue",
                            "value": "1",
                            "unitCode": "MON"
                        }
                    },
                    {
                        "@type": "UnitPriceSpecification",
                        "price": "150.00",
                        "priceCurrency": "USD",
                        "unitText": "YEAR",
                        "referenceQuantity": {
                            "@type": "QuantitativeValue",
                            "value": "1",
                            "unitCode": "ANN"
                        }
                    }
                ]
            }
        ],
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "156",
            "bestRating": "5",
            "worstRating": "1"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Is there really a free plan?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes! The free plan includes unlimited events and all core features. You only need to upgrade if you want custom domains or want to remove branding."
                }
            },
            {
                "@type": "Question",
                "name": "How does the free trial work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When you sign up for Pro or Enterprise, you get a 7-day free trial. Enter your card to start, and you won't be charged until the trial ends. After that, Pro is $5/month or $50/year, and Enterprise is $15/month or $150/year. You can cancel anytime."
                }
            },
            {
                "@type": "Question",
                "name": "What is the difference between Pro and Enterprise?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Pro includes ticketing, white-label branding, event graphics, event boosting with ads, and REST API access. Enterprise adds multiple team members, AI text transformation, email scheduling, agenda scanning, custom CSS styling, 1,000 newsletters per month, and priority support."
                }
            },
            {
                "@type": "Question",
                "name": "Can I cancel anytime?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Absolutely. You can cancel your subscription at any time and you'll keep access until the end of your billing period."
                }
            },
            {
                "@type": "Question",
                "name": "Do you take a cut of ticket sales?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No! We don't charge any fees on ticket sales. You only pay the standard Stripe processing fees (typically 2.9% + $0.30 per transaction)."
                }
            }
        ]
    }
    </script>
    </x-slot>


    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">No hidden fees</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Simple, transparent<br>
                <span class="text-gradient">pricing</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto">
                Start free and upgrade when you need more. No surprises.
            </p>
        </div>
    </section>

    <!-- Pricing Cards -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24" x-data="{ annual: false }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Monthly/Annual Toggle -->
            <div class="relative flex items-center justify-center gap-3 mb-16">
                <span class="text-sm font-semibold transition-colors" :class="annual ? 'text-gray-400 dark:text-gray-500' : 'text-gray-900 dark:text-white'">Monthly</span>
                <button @click="annual = !annual" role="switch" :aria-checked="annual.toString()" aria-label="Toggle annual billing" class="relative w-14 h-8 rounded-full transition-colors cursor-pointer flex-shrink-0" :class="annual ? 'bg-blue-500' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full shadow-md transition-transform duration-200" :class="annual ? 'translate-x-6' : 'translate-x-0'"></span>
                </button>
                <span class="text-sm font-semibold transition-colors" :class="annual ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'">Annual</span>
                <span class="absolute left-1/2 ml-[108px] px-2.5 py-1 text-xs font-semibold rounded-full transition-opacity duration-200" :class="annual ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 opacity-100' : 'opacity-0'">Save up to $30</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <!-- Free Plan -->
                <div class="pricing-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800/50 dark:to-gray-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10 flex flex-col">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-200 dark:bg-gray-500/20 text-gray-600 dark:text-gray-300 text-sm font-medium mb-6 self-start">
                        Forever Free
                    </div>

                    <div class="mb-8">
                        <div class="flex items-baseline gap-2 mb-2">
                            <span class="text-6xl font-bold text-gray-900 dark:text-white">$0</span>
                            <span class="text-gray-500 dark:text-gray-400" x-text="annual ? '/year' : '/month'">/year</span>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">Perfect for getting started</p>
                    </div>

                    <ul class="space-y-4 mb-10">
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Unlimited events and schedules</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Mobile-optimized, professional design</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Custom schedule URLs</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Venue location maps</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Google Calendar sync</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Fan videos & comments on events</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">10 {{ __('messages.newsletters_per_month') }}</span>
                        </li>
                    </ul>

                    <a href="{{ app_url('/sign_up') }}" class="mt-auto block w-full text-center px-6 py-4 bg-white dark:bg-white/10 hover:bg-emerald-50 dark:hover:bg-white/20 border-2 border-emerald-300 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 font-semibold rounded-2xl transition-all">
                        Get Started Free
                    </a>
                </div>

                <!-- Pro Plan -->
                <div class="pricing-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border-2 border-blue-300 dark:border-blue-500/50 p-8 lg:p-10 flex flex-col">
                    <!-- Free Trial Banner -->
                    <div class="bg-gradient-to-r from-blue-500 to-sky-500 text-white text-center py-3 px-4 -mx-8 lg:-mx-10 -mt-8 lg:-mt-10 mb-8">
                        <div class="text-lg font-bold">7-Day Free Trial</div>
                        <div class="text-sm text-blue-100">Try all Pro features risk-free</div>
                    </div>

                    <div class="mb-8">
                        <div class="mb-2 relative h-[68px]">
                            <div x-show="annual" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-1" class="absolute inset-0 flex items-baseline gap-2">
                                <span class="text-6xl font-bold text-gray-900 dark:text-white">$50</span>
                                <span class="text-gray-500 dark:text-gray-400">/year</span>
                            </div>
                            <div x-show="!annual" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-1" class="absolute inset-0 flex items-baseline gap-2" x-cloak>
                                <span class="text-6xl font-bold text-gray-900 dark:text-white">$5</span>
                                <span class="text-gray-500 dark:text-gray-400">/month</span>
                            </div>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400" x-show="annual">Just $4.17/month, billed annually after your free trial</p>
                        <p class="text-gray-500 dark:text-gray-400" x-show="!annual" x-cloak>Billed monthly after your free trial</p>
                    </div>

                    <ul class="space-y-4 mb-10">
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Everything in Free</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Remove Event Schedule branding</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Ticketing & QR code check-ins</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Sell online via Stripe</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Private & password-protected events</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Generate event graphics</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">REST API access</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">{{ __('messages.feature_boost') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">100 {{ __('messages.newsletters_per_month') }}</span>
                        </li>
                    </ul>

                    <a href="{{ app_url('/sign_up') }}" class="mt-auto block w-full text-center px-6 py-4 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 text-white font-semibold rounded-2xl transition-all shadow-lg shadow-blue-500/25">
                        Start Free Trial
                    </a>
                </div>

                <!-- Enterprise Plan -->
                <div class="pricing-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-50 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border-2 border-amber-300 dark:border-amber-500/50 p-8 lg:p-10 flex flex-col">
                    <!-- Free Trial Banner -->
                    <div class="bg-gradient-to-r from-amber-500 to-yellow-500 text-white text-center py-3 px-4 -mx-8 lg:-mx-10 -mt-8 lg:-mt-10 mb-8">
                        <div class="text-lg font-bold">7-Day Free Trial</div>
                        <div class="text-sm text-amber-100">Try all Enterprise features risk-free</div>
                    </div>

                    <div class="mb-8">
                        <div class="mb-2 relative h-[68px]">
                            <div x-show="annual" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-1" class="absolute inset-0 flex items-baseline gap-2">
                                <span class="text-6xl font-bold text-gray-900 dark:text-white">$150</span>
                                <span class="text-gray-500 dark:text-gray-400">/year</span>
                            </div>
                            <div x-show="!annual" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-1" class="absolute inset-0 flex items-baseline gap-2" x-cloak>
                                <span class="text-6xl font-bold text-gray-900 dark:text-white">$15</span>
                                <span class="text-gray-500 dark:text-gray-400">/month</span>
                            </div>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400" x-show="annual">Just $12.50/month, billed annually after your free trial</p>
                        <p class="text-gray-500 dark:text-gray-400" x-show="!annual" x-cloak>Billed monthly after your free trial</p>
                    </div>

                    <ul class="space-y-4 mb-10">
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Everything in Pro</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">AI text transformation</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Email scheduling</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Agenda scanning</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Custom CSS styling</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Priority support</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Multiple team members per account</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">1,000 {{ __('messages.newsletters_per_month') }}</span>
                        </li>
                    </ul>

                    <a href="{{ app_url('/sign_up') }}" class="mt-auto block w-full text-center px-6 py-4 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-400 hover:to-yellow-400 text-white font-semibold rounded-2xl transition-all shadow-lg shadow-amber-500/25">
                        Start Free Trial
                    </a>
                </div>

            </div>

            <!-- No fees callout -->
            <div class="mt-12 text-center space-y-4">
                <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl glass border border-gray-200 dark:border-white/10">
                    <svg aria-hidden="true" class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-gray-600 dark:text-gray-300">No platform fees on ticket sales. Ever.</span>
                </div>
                <div class="flex items-center justify-center gap-2 text-gray-500 dark:text-gray-400 text-sm">
                    <svg aria-hidden="true" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Secure payments powered by</span>
                    <span class="font-semibold text-gray-600 dark:text-gray-300">Stripe</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gray-100 dark:bg-black/30 py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently asked questions
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Everything you need to know about pricing.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is there really a free plan?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes! The free plan includes unlimited events and all core features. You only need to upgrade if you want custom domains or want to remove branding.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How does the free trial work?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            When you sign up for Pro or Enterprise, you get a 7-day free trial. Enter your card to start, and you won't be charged until the trial ends. After that, Pro is $5/month or $50/year, and Enterprise is $15/month or $150/year. You can cancel anytime.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 rounded-2xl border border-amber-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What is the difference between Pro and Enterprise?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Pro includes ticketing, white-label branding, event graphics, event boosting with ads, and REST API access. Enterprise adds multiple team members, AI text transformation, email scheduling, agenda scanning, custom CSS styling, 1,000 newsletters per month, and priority support.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I cancel anytime?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Absolutely. You can cancel your subscription at any time and you'll keep access until the end of your billing period.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-2xl border border-sky-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 5 ? null : 5" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Do you take a cut of ticket sales?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 5 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 5" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            No! We don't charge any fees on ticket sales. You only pay the standard Stripe processing fees (typically 2.9% + $0.30 per transaction).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Start sharing your events today
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Create your free schedule in seconds. Start your free trial today.
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
