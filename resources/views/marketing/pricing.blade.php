<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.pricing_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.pricing_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Pricing</x-slot>

    @php
        // $proMonthly / $proYearly / $entMonthly / $entYearly come from the marketing.* view
        // composer, which reads PlatformPricing. Re-deriving them here would shadow the shared
        // values and quietly ignore whatever a super-admin set at /admin/settings.
        // Raw, not number_format'd: plan_price() formats to the platform currency's own
        // precision, which is zero decimals for JPY and friends.
        $proPerMonth = $proYearly / 12;
        $entPerMonth = $entYearly / 12;
        $saveMax = max(($proMonthly * 12) - $proYearly, ($entMonthly * 12) - $entYearly);

        // Curated feature lists (CLAUDE.md:43). Wording and order are fixed - style
        // them, never edit them.
        $freeFeatures = [
            'Unlimited events and schedules',
            'Mobile-optimized, professional design',
            'Custom schedule URLs',
            'Venue location maps',
            'Google Calendar sync',
            'CalDAV sync',
            'Fan videos & comments on events',
            'Embed calendar on website',
            'Recurring events',
            'Free event registration',
            'Sell up to 25 tickets a month',
            'Appointment booking (1 type)',
            'Built-in analytics',
            'Sub-schedules',
            '10 ' . __('messages.newsletters_per_month'),
        ];
        $proFeatures = [
            'Everything in Free',
            'Remove Event Schedule branding',
            'Unlimited ticket sales & check-in dashboard',
            'Passes, subscriptions & individual tickets',
            'Unlimited appointment types',
            __('messages.feature_boost'),
            'Custom fields',
            'Custom CSS styling',
            'Generate event graphics',
            'REST API & webhooks',
            'Event polls',
            'Post-event feedback',
            'Embed ticket widget',
            'Promo/discount codes',
            'Sales CSV export',
            '100 ' . __('messages.newsletters_per_month'),
        ];
        $enterpriseFeatures = [
            'Everything in Pro',
            'Allocated (reserved) seating',
            'Multiple team members per account',
            'Private & password-protected events',
            'WhatsApp event creation',
            'Custom domains',
            'Email scheduling',
            'Agenda scanning',
            'AI-powered content generation',
            'Availability management',
            'Priority support',
            '1,000 ' . __('messages.newsletters_per_month'),
        ];

        // Fee calculator defaults, computed server-side so the section is correct
        // and meaningful with JavaScript disabled. Same math as compare.blade.php.
        $calcTickets = 200;
        $calcPrice = 25;
        $calcRevenue = $calcTickets * $calcPrice;
        $calcEs = $proMonthly + ($calcRevenue * 0.029) + ($calcTickets * 0.30);
        $calcEb = ($calcRevenue * 0.037) + ($calcTickets * 1.79);
        $calcSave = $calcEb - $calcEs;
        $calcEsBar = $calcEb > 0 ? round(($calcEs / $calcEb) * 100) : 0;

        $faqs = [
            ['q' => 'Is there really a free plan?', 'a' => 'Yes! The free plan includes unlimited events, all core features, appointment booking with one appointment type, and selling up to 25 paid tickets a month with no platform fee. You only need to upgrade if you want to remove branding, sell more than 25 tickets a month, or access advanced features.'],
            ['q' => 'How does the free trial work?', 'a' => 'When you sign up for Pro or Enterprise, you get a 7-day free trial. Enter your card to start, and you won\'t be charged until the trial ends. After that, Pro is ' . plan_price($proMonthly) . '/month or ' . plan_price($proYearly) . '/year, and Enterprise is ' . plan_price($entMonthly) . '/month or ' . plan_price($entYearly) . '/year. You can cancel anytime.'],
            ['q' => 'What is the difference between Pro and Enterprise?', 'a' => 'The free plan already sells up to 25 paid tickets a month, scans tickets at the door and carries one appointment type. Pro removes both limits and adds the rest of the ticketing suite: the live check-in dashboard, passes and subscriptions, individual tickets, promo/discount codes, add-ons, waitlists and sales CSV export. It also adds white-label branding, event graphics, event boosting with ads, custom fields, custom CSS styling, REST API & webhooks, and 100 newsletter emails per month. Enterprise adds allocated (reserved) seating, custom domains, private and password-protected events, up to five team members, WhatsApp event creation, email scheduling, agenda scanning, availability management, 1,000 newsletter emails per month, and priority support.'],
            ['q' => 'Can I cancel anytime?', 'a' => 'Absolutely. You can cancel your subscription at any time and you\'ll keep access until the end of your billing period.'],
            ['q' => 'Do you take a cut of ticket sales?', 'a' => 'No! We don\'t charge any fees on ticket sales. You only pay the standard Stripe processing fees (typically 2.9% + $0.30 per transaction).'],
        ];
    @endphp

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule",
        "description": "Event scheduling and ticketing platform with zero platform fees on ticket sales.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "offers": [
            {
                "@type": "Offer",
                "name": "Free",
                "price": "0",
                "priceCurrency": "{{ platform_currency() }}",
                "description": "Unlimited events and schedules, calendar sync, analytics, free event registration, and up to 25 paid tickets a month."
            },
            {
                "@type": "Offer",
                "name": "Pro",
                "price": "{{ number_format($proMonthly, 2, '.', '') }}",
                "priceCurrency": "{{ platform_currency() }}",
                "description": "Unlimited ticket sales, live check-in dashboard, event graphics, API and webhooks. Also available at {{ plan_price($proYearly) }}/year.",
                "priceSpecification": {
                    "@type": "UnitPriceSpecification",
                    "price": "{{ number_format($proMonthly, 2, '.', '') }}",
                    "priceCurrency": "{{ platform_currency() }}",
                    "billingDuration": 1,
                    "billingIncrement": 1,
                    "unitCode": "MON"
                }
            },
            {
                "@type": "Offer",
                "name": "Enterprise",
                "price": "{{ number_format($entMonthly, 2, '.', '') }}",
                "priceCurrency": "{{ platform_currency() }}",
                "description": "Allocated seating, custom domains, private events, multiple team members, and AI content generation. Also available at {{ plan_price($entYearly) }}/year.",
                "priceSpecification": {
                    "@type": "UnitPriceSpecification",
                    "price": "{{ number_format($entMonthly, 2, '.', '') }}",
                    "priceCurrency": "{{ platform_currency() }}",
                    "billingDuration": 1,
                    "billingIncrement": 1,
                    "unitCode": "MON"
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        .text-gradient-pricing {
            background: linear-gradient(135deg, #059669 0%, #0284c7 50%, #2563eb 100%);
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

        /* Billing toggle: a single .is-annual class on #pricing-plans drives every
           state (no framework). Segmented control - the active half is a raised pill. */
        .bt-seg { color: #4b5563; transition: background-color .2s, color .2s, box-shadow .2s; }
        .dark .bt-seg { color: #9ca3af; }
        .bt-seg-month { background: #fff; color: #111827; box-shadow: 0 1px 2px rgba(0, 0, 0, .08); }
        .dark .bt-seg-month { background: #2d2d30; color: #fff; box-shadow: 0 1px 2px rgba(0, 0, 0, .4); }
        #pricing-plans.is-annual .bt-seg-month { background: transparent; color: #4b5563; box-shadow: none; }
        .dark #pricing-plans.is-annual .bt-seg-month { background: transparent; color: #9ca3af; box-shadow: none; }
        #pricing-plans.is-annual .bt-seg-year { background: #fff; color: #111827; box-shadow: 0 1px 2px rgba(0, 0, 0, .08); }
        .dark #pricing-plans.is-annual .bt-seg-year { background: #2d2d30; color: #fff; box-shadow: 0 1px 2px rgba(0, 0, 0, .4); }

        /* Price swapping */
        .bt-price-month, .bt-price-year { transition: opacity .2s ease; }
        .bt-price-year { display: none; }
        #pricing-plans.is-annual .bt-price-month { display: none; }
        #pricing-plans.is-annual .bt-price-year { display: flex; }

        /* Note swapping. Both notes stay stacked in one grid cell and are hidden with
           visibility, not display, so the row is always as tall as the LONGER note and
           the cards cannot change height when the toggle flips. */
        .bt-note-month, .bt-note-year { grid-area: 1 / 1; transition: opacity .2s ease; }
        .bt-note-year { visibility: hidden; opacity: 0; }
        #pricing-plans.is-annual .bt-note-month { visibility: hidden; opacity: 0; }
        #pricing-plans.is-annual .bt-note-year { visibility: visible; opacity: 1; }
        .bt-period-year { display: none; }
        #pricing-plans.is-annual .bt-period-month { display: none; }
        #pricing-plans.is-annual .bt-period-year { display: inline; }

        /* Mobile feature disclosure. Markup ships OPEN, so no-JS and crawlers always
           see every item; the script closes Free/Enterprise below md only. */
        .plan-disc > summary { list-style: none; }
        .plan-disc > summary::-webkit-details-marker { display: none; }
        .plan-disc > summary::marker { content: ''; }
        .plan-disc[open] > summary .plan-disc-chev { transform: rotate(180deg); }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- Hero (text only - on a pricing page the cards are the CTA)  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero relative flex min-h-[calc(46svh-4rem)] items-center overflow-hidden bg-white py-14 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(16, 185, 129, 0.28), rgba(16, 185, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.14), rgba(37, 99, 235, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-6 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">No hidden fees</span>
            </div>

            <h1 class="es-balance mb-5 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Simple, transparent</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-pricing">pricing</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Start free and upgrade when you need more. No surprises, and never a cut of your ticket sales.
            </p>

            <p class="es-fade-up es-d-3 mx-auto mt-5 max-w-3xl text-base text-gray-500 dark:text-gray-400">
                Not signed up yet? See <x-link href="{{ marketing_url('/why-create-account') }}">what a free account unlocks</x-link>.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Plans                                                       -->
    <!-- ============================================================ -->
    <section id="pricing-plans" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            <!-- Billing toggle (vanilla JS: toggles .is-annual on #pricing-plans) -->
            <div class="mb-14 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <div class="inline-flex items-center rounded-2xl border border-gray-200 bg-gray-100 p-1 dark:border-white/10 dark:bg-white/[0.06]">
                    <button id="bt-monthly" type="button" aria-pressed="true" class="bt-seg bt-seg-month rounded-xl px-5 py-2 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA]">Monthly</button>
                    <button id="bt-annual" type="button" aria-pressed="false" class="bt-seg bt-seg-year rounded-xl px-5 py-2 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA]">Annual</button>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                    <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Save up to {{ plan_price($saveMax) }} a year
                </span>
            </div>

            {{-- Vertical reveal only: the horizontal variants translate 44px sideways,
                 which overflows a full-width card at 390px. --}}
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3 md:grid-rows-[auto_1fr_auto] md:gap-y-0" data-reveal-group="90">

                <!-- Free -->
                <div class="es-bento es-tilt-inner relative flex flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] md:row-span-3 md:grid md:grid-rows-[subgrid] lg:p-8" data-tilt="2" data-reveal>
                    <div class="mb-8">
                        <!-- Desktop: banner-height container matching trial banner structure -->
                        <div class="-mx-6 -mt-6 mb-8 hidden px-4 py-3 text-center md:block lg:-mx-8 lg:-mt-8">
                            <div class="text-lg font-bold text-gray-600 dark:text-gray-300">Forever Free</div>
                            <div class="text-sm">&nbsp;</div>
                        </div>
                        <!-- Mobile: pill badge -->
                        <div class="mb-6 inline-flex items-center gap-2 self-start rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300 md:hidden">
                            Forever Free
                        </div>

                        <div>
                            <div class="mb-2 flex items-baseline gap-2">
                                <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price(0) }}</span>
                                <span class="text-gray-500 dark:text-gray-400"><span class="bt-period-month">/month</span><span class="bt-period-year">/year</span></span>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">Perfect for getting started</p>
                        </div>
                    </div>

                    <details class="plan-disc mb-10" open>
                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded-xl bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:bg-white/5 dark:text-gray-200 md:hidden">
                            What's included
                            <svg aria-hidden="true" class="plan-disc-chev h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <ul class="space-y-4">
                            @foreach ($freeFeatures as $feature)
                                <x-marketing.plan-feature accent="emerald">{{ $feature }}</x-marketing.plan-feature>
                            @endforeach
                        </ul>
                    </details>

                    <a href="{{ app_url('/sign_up') }}" class="mt-auto block w-full rounded-2xl border-2 border-emerald-300 bg-white px-6 py-4 text-center font-semibold text-emerald-700 transition-all hover:bg-emerald-50 dark:border-emerald-500/40 dark:bg-white/10 dark:text-emerald-300 dark:hover:bg-white/20">
                        Get Started Free
                    </a>
                    <div class="es-glare rounded-3xl"></div>
                </div>

                <!-- Pro (the recommendation) -->
                <div class="es-bento es-tilt-inner relative flex flex-col rounded-3xl bg-white p-6 shadow-lg ring-2 ring-blue-500/60 transition-shadow hover:shadow-2xl dark:bg-white/[0.04] dark:ring-blue-400/50 md:row-span-3 md:grid md:grid-rows-[subgrid] lg:-top-2 lg:p-8" data-tilt="2.5" data-reveal>
                    <span class="absolute -top-3 left-1/2 z-10 -translate-x-1/2 whitespace-nowrap rounded-full bg-gradient-to-r from-blue-600 to-sky-500 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-blue-500/30">Most popular</span>

                    <div class="mb-8">
                        <div class="-mx-6 -mt-6 mb-8 rounded-t-3xl bg-gradient-to-r from-blue-600 to-sky-500 px-4 py-3 text-center text-white lg:-mx-8 lg:-mt-8">
                            <div class="text-lg font-bold">7-Day Free Trial</div>
                            <div class="text-sm text-blue-50">Try all Pro features risk-free</div>
                        </div>

                        <div>
                            <div class="relative mb-2 h-[68px]">
                                <div class="bt-price-year absolute inset-0 items-baseline gap-2">
                                    <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price($proYearly) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/year</span>
                                </div>
                                <div class="bt-price-month absolute inset-0 flex items-baseline gap-2">
                                    <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price($proMonthly) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/month</span>
                                </div>
                            </div>
                            <div class="grid">
                                <p class="bt-note-year text-gray-500 dark:text-gray-400">Just {{ plan_price($proPerMonth) }}/month, billed annually after your free trial</p>
                                <p class="bt-note-month text-gray-500 dark:text-gray-400">Billed monthly after your free trial</p>
                            </div>
                        </div>
                    </div>

                    <ul class="mb-10 space-y-4">
                        @foreach ($proFeatures as $feature)
                            <x-marketing.plan-feature accent="blue">{{ $feature }}</x-marketing.plan-feature>
                        @endforeach
                    </ul>

                    <a href="{{ app_url('/sign_up') }}" class="mt-auto block w-full rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-4 text-center font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:from-blue-500 hover:to-sky-500 hover:shadow-xl">
                        Start Free Trial
                    </a>
                    <div class="es-glare rounded-3xl"></div>
                    <div class="es-ring-glow"></div>
                </div>

                <!-- Enterprise -->
                <div class="es-bento es-tilt-inner relative flex flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 shadow-sm transition-shadow hover:shadow-xl dark:border-white/10 dark:bg-white/[0.04] md:row-span-3 md:grid md:grid-rows-[subgrid] lg:p-8" data-tilt="2" data-reveal>
                    <div class="mb-8">
                        <div class="-mx-6 -mt-6 mb-8 rounded-t-3xl bg-gradient-to-r from-amber-700 to-amber-800 px-4 py-3 text-center text-white lg:-mx-8 lg:-mt-8">
                            <div class="text-lg font-bold">7-Day Free Trial</div>
                            <div class="text-sm text-amber-100">Try all Enterprise features risk-free</div>
                        </div>

                        <div>
                            <div class="relative mb-2 h-[68px]">
                                <div class="bt-price-year absolute inset-0 items-baseline gap-2">
                                    <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price($entYearly) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/year</span>
                                </div>
                                <div class="bt-price-month absolute inset-0 flex items-baseline gap-2">
                                    <span class="text-6xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">{{ plan_price($entMonthly) }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">/month</span>
                                </div>
                            </div>
                            <div class="grid">
                                <p class="bt-note-year text-gray-500 dark:text-gray-400">Just {{ plan_price($entPerMonth) }}/month, billed annually after your free trial</p>
                                <p class="bt-note-month text-gray-500 dark:text-gray-400">Billed monthly after your free trial</p>
                            </div>
                        </div>
                    </div>

                    <details class="plan-disc mb-10" open>
                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded-xl bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:bg-white/5 dark:text-gray-200 md:hidden">
                            What's included
                            <svg aria-hidden="true" class="plan-disc-chev h-4 w-4 text-gray-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <ul class="space-y-4">
                            @foreach ($enterpriseFeatures as $feature)
                                <x-marketing.plan-feature accent="amber">{{ $feature }}</x-marketing.plan-feature>
                            @endforeach
                        </ul>
                    </details>

                    <a href="{{ app_url('/sign_up') }}" class="mt-auto block w-full rounded-2xl bg-gradient-to-r from-amber-700 to-amber-800 px-6 py-4 text-center font-semibold text-white shadow-lg shadow-amber-700/25 transition-all hover:from-amber-600 hover:to-amber-700 hover:shadow-xl">
                        Start Free Trial
                    </a>
                    <div class="es-glare rounded-3xl"></div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Fees: do the arithmetic in public                           -->
    <!-- ============================================================ -->
    <section id="fees" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Zero platform fees. Here's the math.
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400" data-reveal>
                    Most ticketing platforms take a cut of every ticket. We take none. Move the numbers and see what that means for your event.
                </p>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-gray-50 p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-10"
                 data-reveal="panel" data-pro-monthly="{{ $proMonthly }}">

                <div class="mb-10 flex flex-col items-center justify-center gap-6 sm:flex-row">
                    <div class="flex items-center gap-3">
                        <label for="pf-tickets" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Tickets sold</label>
                        <input id="pf-tickets" type="number" value="{{ $calcTickets }}" min="1" max="100000" class="w-28 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                    <div class="flex items-center gap-3">
                        <label for="pf-price" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Ticket price</label>
                        <div class="relative">
                            <span class="absolute top-1/2 -translate-y-1/2 text-sm text-gray-400 ltr:left-3 rtl:right-3">$</span>
                            <input id="pf-price" type="number" value="{{ $calcPrice }}" min="1" max="10000" class="w-28 rounded-xl border border-gray-200 bg-white py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/5 dark:text-white ltr:pl-7 ltr:pr-3 rtl:pr-7 rtl:pl-3">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                        <div class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Typical ticketing platform</div>
                        <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">3.7% + $1.79 per ticket</div>
                        <div id="pf-eb" class="mb-3 text-4xl font-black tracking-tight text-gray-900 tabular-nums dark:text-white">${{ number_format($calcEb, 2) }}</div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                            <div class="h-full rounded-full bg-gray-400 dark:bg-gray-500" style="width: 100%"></div>
                        </div>
                    </div>

                    <div class="rounded-2xl border-2 border-emerald-300 bg-emerald-50/60 p-6 dark:border-emerald-500/40 dark:bg-emerald-500/10">
                        <div class="mb-1 text-sm font-semibold text-emerald-800 dark:text-emerald-300">Event Schedule Pro</div>
                        {{-- Deliberately still a dollar sign, and NOT plan_price(). This label sits
                             inside the fee calculator, whose totals ($calcEs above literally adds
                             $proMonthly to Stripe's USD per-ticket fee, and $calcEb is Eventbrite's
                             published US pricing) are a USD unit. Converting just this line would put
                             "R9/month" directly above "$247.50". Same call as compare.blade.php. --}}
                        <div class="mb-3 text-xs text-emerald-800 dark:text-emerald-400/80">${{ $proMonthly }}/month + Stripe, 0% platform fee</div>
                        <div id="pf-es" class="mb-3 text-4xl font-black tracking-tight text-emerald-700 tabular-nums dark:text-emerald-300">${{ number_format($calcEs, 2) }}</div>
                        <div class="h-2 overflow-hidden rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                            <div id="pf-bar" class="h-full rounded-full bg-emerald-500" style="width: {{ $calcEsBar }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <p class="text-lg text-gray-700 dark:text-gray-300">
                        You keep <span id="pf-save" class="text-2xl font-black text-emerald-600 tabular-nums dark:text-emerald-400">${{ number_format($calcSave, 2) }}</span> more on this one event.
                    </p>
                    <a href="{{ app_url('/sign_up') }}" class="group mt-6 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-7 py-3.5 font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                        Start your free trial
                        <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                    <p class="mt-5 text-xs text-gray-500 dark:text-gray-400">
                        Stripe processing (2.9% + $0.30 per ticket) is included on the Event Schedule side. The comparison platform bundles processing into its own rate. Payouts go straight to your own Stripe account.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Selfhost                                                    -->
    <!-- ============================================================ -->
    <section id="selfhost" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-20">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-white/10 dark:bg-white/[0.04] lg:p-10" data-reveal="panel">
                <svg aria-hidden="true" class="mx-auto mb-5 h-10 w-10 text-gray-800 dark:text-gray-200" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
                <h2 class="es-balance mb-3 text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl">
                    Or run it yourself. Free, forever.
                </h2>
                <p class="mx-auto mb-7 max-w-2xl text-gray-500 dark:text-gray-400">
                    Event Schedule is open source. Install it on your own server and every Enterprise feature is included at no cost, with your data staying entirely on your infrastructure.
                </p>
                <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ marketing_url('/selfhost') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-6 py-3 font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                        Selfhosting guide
                    </a>
                    <a href="{{ marketing_url('/open-source') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-3 font-semibold text-blue-600 transition-all hover:gap-3 dark:text-blue-400">
                        View the source
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ                                                         -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />
    <section id="faq" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Frequently asked questions
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400" data-reveal>
                    Everything you need to know about pricing.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-colors hover:border-blue-300 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/40">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Finale                                                      -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-gray-50 px-2 py-16 dark:bg-[#0f0f14] sm:px-4 lg:py-24">
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
    <section class="bg-gray-50 pb-16 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]">
                <p class="text-gray-700 dark:text-gray-300">
                    Know other organizers? <a href="{{ route('marketing.docs.referral_program') }}" class="font-medium text-blue-600 hover:underline dark:text-blue-400">Earn free months with our referral program</a>.
                </p>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- Section dot navigation (desktop) -->
    @php
        $dotSections = [
            ['top', 'Top'],
            ['pricing-plans', 'Plans'],
            ['fees', 'Zero fees'],
            ['selfhost', 'Selfhost'],
            ['faq', 'FAQ'],
            ['claim', 'Get started'],
        ];
    @endphp
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA]" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#15151c] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <!-- Billing toggle + fee calculator + mobile plan disclosure (vanilla JS) -->
    <script {!! nonce_attr() !!}>
        (function () {
            var wrap = document.getElementById('pricing-plans');
            var monthBtn = document.getElementById('bt-monthly');
            var yearBtn = document.getElementById('bt-annual');
            if (wrap && monthBtn && yearBtn) {
                var setAnnual = function (annual) {
                    wrap.classList.toggle('is-annual', annual);
                    monthBtn.setAttribute('aria-pressed', annual ? 'false' : 'true');
                    yearBtn.setAttribute('aria-pressed', annual ? 'true' : 'false');
                };
                monthBtn.addEventListener('click', function () { setAnnual(false); });
                yearBtn.addEventListener('click', function () { setAnnual(true); });
            }
        })();

        (function () {
            var ticketsEl = document.getElementById('pf-tickets');
            var priceEl = document.getElementById('pf-price');
            var esEl = document.getElementById('pf-es');
            var ebEl = document.getElementById('pf-eb');
            var saveEl = document.getElementById('pf-save');
            var barEl = document.getElementById('pf-bar');
            if (!ticketsEl || !priceEl || !esEl || !ebEl || !saveEl || !barEl) return;

            var panel = ticketsEl.closest('[data-pro-monthly]');
            // The fallback is rendered from config rather than written as a literal. A hardcoded
            // number here survived the price raise and kept quoting the old $5.
            var proMonthly = parseFloat(panel && panel.getAttribute('data-pro-monthly')) || {{ $proMonthly }};

            function fmt(n) { return '$' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

            // Same math as the /compare fee calculator so the two pages cannot disagree.
            function calc() {
                var tickets = parseFloat(ticketsEl.value) || 0;
                var price = parseFloat(priceEl.value) || 0;
                var revenue = tickets * price;
                var esTotal = proMonthly + (revenue * 0.029) + (tickets * 0.30);
                var ebTotal = (revenue * 0.037) + (tickets * 1.79);
                var save = ebTotal - esTotal;
                esEl.textContent = fmt(esTotal);
                ebEl.textContent = fmt(ebTotal);
                saveEl.textContent = fmt(Math.max(save, 0));
                barEl.style.width = (ebTotal > 0 ? Math.min(100, (esTotal / ebTotal) * 100) : 0) + '%';
            }
            ticketsEl.addEventListener('input', calc);
            priceEl.addEventListener('input', calc);
            calc();
        })();

        (function () {
            // Progressive enhancement: the lists ship open, so no-JS and crawlers see
            // every item. Below md we collapse Free and Enterprise so Pro is the only
            // expanded card - the mobile stand-in for the desktop lift.
            var discs = document.querySelectorAll('details.plan-disc');
            if (!discs.length || !window.matchMedia) return;
            var mq = window.matchMedia('(max-width: 767px)');
            function sync() {
                discs.forEach(function (d) {
                    if (mq.matches) { d.removeAttribute('open'); } else { d.setAttribute('open', ''); }
                });
            }
            if (mq.addEventListener) { mq.addEventListener('change', sync); }
            sync();
        })();
    </script>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
