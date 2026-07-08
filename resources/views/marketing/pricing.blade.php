<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.pricing_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.pricing_description') }}</x-slot>
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
                "description": "Professional event calendars, mobile-optimized design, custom schedule URLs, team collaboration, venue maps, Google Calendar sync, built-in analytics, and sub-schedules.",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Pro",
                "price": "5.00",
                "priceCurrency": "USD",
                "billingIncrement": "MON",
                "description": "Everything in Free plus remove branding, full ticketing suite with check-in dashboard, Stripe payments, promo/discount codes, sales CSV export, event graphics, event boosting with ads, event polls, post-event feedback, custom fields, custom CSS styling, REST API and webhooks, and embed ticket widget.",
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
                "description": "Everything in Pro plus custom domains, private events, WhatsApp event creation, email scheduling, agenda scanning, AI-powered content generation, availability management, multiple team members, 1,000 newsletter emails per month, and priority support.",
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
        ]
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
                    "text": "Yes! The free plan includes unlimited events and all core features. You only need to upgrade if you want to remove branding, sell tickets, or access advanced features."
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
                    "text": "Pro includes a full ticketing suite with check-in dashboard, Stripe payments, white-label branding, event graphics, event boosting with ads, custom fields, custom CSS styling, REST API and webhooks, and 100 newsletter emails per month. Enterprise adds custom domains, private and password-protected events, multiple team members, WhatsApp event creation, email scheduling, agenda scanning, availability management, 1,000 newsletter emails per month, and priority support."
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


    <style {!! nonce_attr() !!}>
        .text-gradient-pricing {
            background: linear-gradient(135deg, #10b981 0%, #0ea5e9 50%, #2563eb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-pricing {
            background: linear-gradient(135deg, #34d399 0%, #38bdf8 50%, #60a5fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-pricing {
            background: linear-gradient(135deg, #34d399 0%, #38bdf8 50%, #60a5fa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Billing toggle: a single .is-annual class on #pricing-plans drives every state (no framework) */
        .bt-track { background: #d1d5db; transition: background .2s; }
        .dark .bt-track { background: #4b5563; }
        #pricing-plans.is-annual .bt-track { background: #3b82f6; }
        .bt-knob { transform: translateX(0); transition: transform .2s; }
        #pricing-plans.is-annual .bt-knob { transform: translateX(1.5rem); }
        .bt-lbl-month { color: #111827; }
        .dark .bt-lbl-month { color: #fff; }
        .bt-lbl-year { color: #9ca3af; }
        .dark .bt-lbl-year { color: #6b7280; }
        #pricing-plans.is-annual .bt-lbl-month { color: #9ca3af; }
        .dark #pricing-plans.is-annual .bt-lbl-month { color: #6b7280; }
        #pricing-plans.is-annual .bt-lbl-year { color: #111827; }
        .dark #pricing-plans.is-annual .bt-lbl-year { color: #fff; }
        .bt-save { opacity: 0; transition: opacity .2s; }
        #pricing-plans.is-annual .bt-save { opacity: 1; }
        /* Price + note swapping */
        .bt-price-year, .bt-note-year { display: none; }
        #pricing-plans.is-annual .bt-price-month, #pricing-plans.is-annual .bt-note-month { display: none; }
        #pricing-plans.is-annual .bt-price-year { display: flex; }
        #pricing-plans.is-annual .bt-note-year { display: block; }
        .bt-period-year { display: none; }
        #pricing-plans.is-annual .bt-period-month { display: none; }
        #pricing-plans.is-annual .bt-period-year { display: inline; }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- Hero Section -->
    <section class="es-hero relative flex min-h-[calc(58svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(16, 185, 129, 0.28), rgba(16, 185, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.14), rgba(37, 99, 235, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">No hidden fees</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Simple, transparent</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-pricing">pricing</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Start free and upgrade when you need more. No surprises.
            </p>
        </div>
    </section>

    <!-- Pricing Cards -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24" id="pricing-plans">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Monthly/Annual Toggle (vanilla JS: toggles .is-annual on #pricing-plans) -->
            <div class="relative flex items-center justify-center gap-3 mb-16">
                <span class="bt-lbl-month text-sm font-semibold">Monthly</span>
                <button id="billing-toggle" type="button" role="switch" aria-checked="false" aria-label="Toggle annual billing" class="bt-track relative w-14 h-8 rounded-full cursor-pointer flex-shrink-0">
                    <span class="bt-knob absolute top-1 left-1 w-6 h-6 bg-white rounded-full shadow-md"></span>
                </button>
                <span class="bt-lbl-year text-sm font-semibold">Annual</span>
                <span class="bt-save absolute left-1/2 ml-[108px] px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300">Save up to $30</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 md:grid-rows-[auto_1fr_auto] gap-8 md:gap-y-0">

                <!-- Free Plan -->
                <div class="pricing-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800/50 dark:to-gray-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10 flex flex-col md:grid md:grid-rows-[subgrid] md:row-span-3">
                    <div class="mb-8">
                    <!-- Desktop: banner-height container matching trial banner structure -->
                    <div class="hidden md:block -mx-8 lg:-mx-10 -mt-8 lg:-mt-10 mb-8 py-3 px-4 text-center">
                        <div class="text-lg font-bold text-gray-600 dark:text-gray-300">Forever Free</div>
                        <div class="text-sm">&nbsp;</div>
                    </div>
                    <!-- Mobile: pill badge -->
                    <div class="md:hidden inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-200 dark:bg-gray-500/20 text-gray-600 dark:text-gray-300 text-sm font-medium mb-6 self-start">
                        Forever Free
                    </div>

                    <div>
                        <div class="flex items-baseline gap-2 mb-2">
                            <span class="text-6xl font-bold text-gray-900 dark:text-white">$0</span>
                            <span class="text-gray-500 dark:text-gray-400"><span class="bt-period-month">/month</span><span class="bt-period-year">/year</span></span>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">Perfect for getting started</p>
                    </div>
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
                            <span class="text-gray-600 dark:text-gray-300">CalDAV sync</span>
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
                            <span class="text-gray-600 dark:text-gray-300">Embed calendar on website</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Recurring events</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Free event registration</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Built-in analytics</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Sub-schedules</span>
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
                <div class="pricing-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border-2 border-blue-300 dark:border-blue-500/50 p-8 lg:p-10 flex flex-col md:grid md:grid-rows-[subgrid] md:row-span-3">
                    <div class="mb-8">
                    <!-- Free Trial Banner -->
                    <div class="bg-gradient-to-r from-blue-500 to-sky-500 text-white text-center py-3 px-4 -mx-8 lg:-mx-10 -mt-8 lg:-mt-10 mb-8">
                        <div class="text-lg font-bold">7-Day Free Trial</div>
                        <div class="text-sm text-blue-100">Try all Pro features risk-free</div>
                    </div>

                    <div>
                        <div class="mb-2 relative h-[68px]">
                            <div class="bt-price-year absolute inset-0 items-baseline gap-2">
                                <span class="text-6xl font-bold text-gray-900 dark:text-white">$50</span>
                                <span class="text-gray-500 dark:text-gray-400">/year</span>
                            </div>
                            <div class="bt-price-month absolute inset-0 flex items-baseline gap-2">
                                <span class="text-6xl font-bold text-gray-900 dark:text-white">$5</span>
                                <span class="text-gray-500 dark:text-gray-400">/month</span>
                            </div>
                        </div>
                        <p class="bt-note-year text-gray-500 dark:text-gray-400">Just $4.17/month, billed annually after your free trial</p>
                        <p class="bt-note-month text-gray-500 dark:text-gray-400">Billed monthly after your free trial</p>
                    </div>
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
                            <span class="text-gray-600 dark:text-gray-300">Full ticketing suite & check-in dashboard</span>
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
                            <span class="text-gray-600 dark:text-gray-300">{{ __('messages.feature_boost') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Custom fields</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Custom CSS styling</span>
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
                            <span class="text-gray-600 dark:text-gray-300">REST API & webhooks</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Event polls</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Post-event feedback</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Embed ticket widget</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Promo/discount codes</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Sales CSV export</span>
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
                <div class="pricing-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-50 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border-2 border-amber-300 dark:border-amber-500/50 p-8 lg:p-10 flex flex-col md:grid md:grid-rows-[subgrid] md:row-span-3">
                    <div class="mb-8">
                    <!-- Free Trial Banner -->
                    <div class="bg-gradient-to-r from-amber-500 to-yellow-500 text-white text-center py-3 px-4 -mx-8 lg:-mx-10 -mt-8 lg:-mt-10 mb-8">
                        <div class="text-lg font-bold">7-Day Free Trial</div>
                        <div class="text-sm text-amber-100">Try all Enterprise features risk-free</div>
                    </div>

                    <div>
                        <div class="mb-2 relative h-[68px]">
                            <div class="bt-price-year absolute inset-0 items-baseline gap-2">
                                <span class="text-6xl font-bold text-gray-900 dark:text-white">$150</span>
                                <span class="text-gray-500 dark:text-gray-400">/year</span>
                            </div>
                            <div class="bt-price-month absolute inset-0 flex items-baseline gap-2">
                                <span class="text-6xl font-bold text-gray-900 dark:text-white">$15</span>
                                <span class="text-gray-500 dark:text-gray-400">/month</span>
                            </div>
                        </div>
                        <p class="bt-note-year text-gray-500 dark:text-gray-400">Just $12.50/month, billed annually after your free trial</p>
                        <p class="bt-note-month text-gray-500 dark:text-gray-400">Billed monthly after your free trial</p>
                    </div>
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
                            <span class="text-gray-600 dark:text-gray-300">Multiple team members per account</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Private & password-protected events</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">WhatsApp event creation</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Custom domains</span>
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
                            <span class="text-gray-600 dark:text-gray-300">AI-powered content generation</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg aria-hidden="true" class="w-3 h-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">Availability management</span>
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

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['Is there really a free plan?', 'Yes! The free plan includes unlimited events and all core features. You only need to upgrade if you want to remove branding, sell tickets, or access advanced features.'],
                        ['How does the free trial work?', 'When you sign up for Pro or Enterprise, you get a 7-day free trial. Enter your card to start, and you won\'t be charged until the trial ends. After that, Pro is $5/month or $50/year, and Enterprise is $15/month or $150/year. You can cancel anytime.'],
                        ['What is the difference between Pro and Enterprise?', 'Pro includes a full ticketing suite with check-in dashboard, Stripe payments, promo/discount codes, sales CSV export, white-label branding, event graphics, event boosting with ads, custom fields, custom CSS styling, REST API & webhooks, and 100 newsletter emails per month. Enterprise adds custom domains, private and password-protected events, multiple team members, WhatsApp event creation, email scheduling, agenda scanning, availability management, 1,000 newsletter emails per month, and priority support.'],
                        ['Can I cancel anytime?', 'Absolutely. You can cancel your subscription at any time and you\'ll keep access until the end of your billing period.'],
                        ['Do you take a cut of ticket sales?', 'No! We don\'t charge any fees on ticket sales. You only pay the standard Stripe processing fees (typically 2.9% + $0.30 per transaction).'],
                    ];
                @endphp
                @foreach ($faqs as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Finale                                                      -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-gray-100 px-2 py-16 dark:bg-black/30 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Start sharing your events <span class="text-gradient-pricing">today</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your free schedule in seconds. Start your free trial today.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Referral CTA --}}
    <section class="py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-gray-700 dark:text-gray-300">
                    Know other organizers? <a href="{{ route('marketing.docs.referral_program') }}" class="text-[#4E81FA] hover:underline font-medium">Earn free months with our referral program</a>.
                </p>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- Billing toggle (vanilla JS, no framework dependency) -->
    <script {!! nonce_attr() !!}>
        (function () {
            var wrap = document.getElementById('pricing-plans');
            var btn = document.getElementById('billing-toggle');
            if (!wrap || !btn) return;
            btn.addEventListener('click', function () {
                var annual = wrap.classList.toggle('is-annual');
                btn.setAttribute('aria-checked', annual ? 'true' : 'false');
            });
        })();
    </script>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
