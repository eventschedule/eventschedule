<x-marketing-layout>
    <x-slot name="title">Stripe Payments & Ticketing - Event Schedule</x-slot>
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
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Are there platform fees on Stripe payments?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No, Event Schedule charges zero platform fees on ticket sales. You only pay Stripe's standard processing fees (typically 2.9% + $0.30 per transaction). 100% of the ticket price goes to you."
                }
            },
            {
                "@type": "Question",
                "name": "Can selfhosted users accept Stripe payments?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, selfhosted users can connect their own Stripe account directly using their Stripe API keys. Full setup instructions are available in the selfhosted documentation."
                }
            },
            {
                "@type": "Question",
                "name": "Does Stripe support Apple Pay and Google Pay?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, Stripe Checkout automatically supports credit cards, Apple Pay, and Google Pay. Customers can pay using whichever method they prefer."
                }
            },
            {
                "@type": "Question",
                "name": "How do payouts work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Payments go directly to your Stripe account via Stripe Connect. Standard Stripe payout schedules apply, typically depositing funds to your bank account within 2 business days."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (blue to sky to cyan) */
        .text-gradient-stripe {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-stripe {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* On always-dark surfaces, keep the accent bright regardless of page mode */
        .es-finale-panel .text-gradient-stripe {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live payment-success card */
        .es-stripe-float { animation: es-stripe-bob 5.5s ease-in-out infinite; }
        @keyframes es-stripe-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a row of currency symbols pulsing (135+ currencies) */
        .es-currency {
            flex: 0 0 auto;
            font-weight: 800;
            line-height: 1;
            color: rgba(37, 99, 235, 0.8);
            animation: es-currency-pulse var(--cu-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--cu-delay, 0s);
        }
        @keyframes es-currency-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-stripe-float, .es-currency, .animate-pulse-slow, .animate-pulse { animation: none !important; }
            .es-currency { opacity: 0.55; transform: none; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- 1. Hero                                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Currency motif along the bottom edge -->
            <div class="es-currencies absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @php $symbols = ['$', '€', '£', '¥', '₹', '₩', '₪', '฿', '₴', '₦', 'kr', '₫']; @endphp
                @foreach ($symbols as $i => $sym)
                    @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-currency" style="font-size: {{ $sz }}px; --cu-dur: {{ $dur }}s; --cu-delay: {{ $delay }}s;">{{ $sym }}</span>
                @endforeach
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="rounded bg-white px-2 py-0.5 text-sm font-bold" style="color: #635BFF;">stripe</span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Payment Processing</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Accept payments with</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-stripe">Stripe</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Credit cards, Apple Pay, Google Pay. Secure checkout with direct payouts to your account.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#how-it-works" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it works
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento grid                                               -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Stripe Checkout -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            Checkout
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Stripe Checkout</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Hosted payment page handles everything. Customers complete payment on Stripe's secure checkout.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="flex items-center gap-3">
                                <div class="flex -space-x-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600">
                                        <svg aria-hidden="true" class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 bg-black dark:border-white/20">
                                        <svg aria-hidden="true" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.05 11.97c-.02-1.99 1.63-2.96 1.7-3-.93-1.35-2.37-1.54-2.88-1.56-1.22-.12-2.39.72-3.01.72-.62 0-1.58-.7-2.6-.68-1.34.02-2.57.78-3.26 1.97-1.4 2.41-.36 5.98.99 7.94.66.96 1.45 2.03 2.49 1.99 1-.04 1.37-.64 2.58-.64 1.2 0 1.54.64 2.59.62 1.08-.02 1.76-.97 2.41-1.93.76-1.11 1.07-2.18 1.09-2.24-.02-.01-2.09-.8-2.1-3.19z"/>
                                        </svg>
                                    </div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white">
                                        <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24">
                                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">All major payment methods</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multiple Currencies -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Global
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">135+ currencies</h2>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Sell tickets in your local currency. Stripe handles conversion and international payments.</p>
                        <div class="mt-auto flex flex-wrap gap-2" aria-hidden="true">
                            <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">USD</span>
                            <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">EUR</span>
                            <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">GBP</span>
                            <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">CAD</span>
                            <span class="inline-flex items-center rounded bg-blue-500/20 px-2 py-1 text-sm text-blue-700 dark:text-blue-300">+130 more</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Webhook Integration -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Real-time
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Instant confirmation</h2>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Webhooks notify us the moment payment completes. Tickets are delivered instantly via email.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></div>
                                <span class="font-mono text-xs text-emerald-700 dark:text-emerald-300">payment_intent.succeeded</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Stripe Connect (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    SaaS Mode
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">Stripe Connect</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">On our hosted platform, event creators connect their own Stripe accounts. Payments go directly to them - we never hold your money.</p>
                            </div>
                            <div class="space-y-3" aria-hidden="true">
                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500/20">
                                        <svg aria-hidden="true" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white">Event creator</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Connects Stripe account</div>
                                    </div>
                                </div>
                                <div class="flex justify-center">
                                    <svg aria-hidden="true" class="h-5 w-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-sky-400/30 bg-sky-500/20 p-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-500/30">
                                        <svg aria-hidden="true" class="h-5 w-5 text-sky-500 dark:text-sky-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white">Direct payout</div>
                                        <div class="text-sm text-sky-700 dark:text-sky-300">Funds go to creator's account</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Billing Portal -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Self-service
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Billing portal</h2>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Customers manage their own payment methods, view invoices, and update billing details through Stripe's portal.</p>
                        <div class="mt-auto flex flex-wrap gap-2" aria-hidden="true">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Update card</span>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">View invoices</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. How it works                                             -->
    <!-- ============================================================ -->
    <section id="how-it-works" class="scroll-mt-24 bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>How it <span class="text-gradient-stripe">works</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Start accepting payments in four simple steps.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-4" data-reveal-group="90">
                @php
                    $steps = [
                        ['Connect Stripe', 'Link your Stripe account with one click. We use Stripe Connect for secure authorization.'],
                        ['Create tickets', 'Add ticket types to your events with prices and quantities. Set up multiple tiers if needed.'],
                        ['Share event', 'Customers buy tickets directly from your schedule page. No redirects to external sites.'],
                        ['Get paid', 'Payments go directly to your Stripe account. Standard Stripe payouts to your bank.'],
                    ];
                @endphp
                @foreach ($steps as $si => $step)
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 text-2xl font-bold text-white shadow-lg shadow-blue-500/25">
                            {{ $si + 1 }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $step[0] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $step[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Why Stripe                                               -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Why <span class="text-gradient-stripe">Stripe?</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">The trusted choice for online payments worldwide.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                <div data-reveal class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04]">
                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-500/10 border border-blue-500/20">
                        <svg aria-hidden="true" class="h-7 w-7 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-white">PCI Compliant</h3>
                    <p class="text-gray-500 dark:text-gray-400">Stripe handles all sensitive card data. We never see or store payment details.</p>
                </div>

                <div data-reveal class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04]">
                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/10 border border-emerald-500/20">
                        <svg aria-hidden="true" class="h-7 w-7 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-white">No Platform Fees</h3>
                    <p class="text-gray-500 dark:text-gray-400">We don't take a cut. You only pay Stripe's standard processing fees (typically 2.9% + $0.30).</p>
                </div>

                <div data-reveal class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04]">
                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-500/10 border border-blue-500/20">
                        <svg aria-hidden="true" class="h-7 w-7 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-white">Global Reach</h3>
                    <p class="text-gray-500 dark:text-gray-400">Accept payments from customers worldwide. Available in 46+ countries.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Stripe Link (external)                                   -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <a href="https://stripe.com" target="_blank" rel="noopener noreferrer" data-reveal class="group block">
                <div class="rounded-3xl border border-gray-200 bg-gradient-to-br from-blue-100 to-sky-100 p-8 transition-all hover:border-gray-300 dark:border-white/10 dark:from-blue-900/30 dark:to-sky-900/30 dark:hover:border-white/20">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Official Site
                    </div>
                    <div class="mb-4 flex justify-center">
                        <div class="rounded-xl bg-white px-6 py-3 dark:bg-gray-800">
                            <span class="text-2xl font-bold" style="color: #635BFF;">stripe</span>
                        </div>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-200">Learn more about Stripe</h3>
                    <p class="mb-4 text-gray-500 dark:text-gray-400">Explore Stripe's documentation, features, and developer resources for payment processing.</p>
                    <span class="inline-flex items-center gap-2 font-medium text-blue-700 transition-all group-hover:gap-3 dark:text-blue-300">
                        Visit stripe.com
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Explore More Integrations                                -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/integrations') }}" data-reveal class="group block">
                <div class="rounded-3xl border border-gray-200 bg-gradient-to-br from-gray-100 to-gray-200 p-8 transition-all hover:border-gray-300 dark:border-white/10 dark:from-gray-800/50 dark:to-gray-900 dark:hover:border-white/20">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-gray-200 px-3 py-1 text-sm font-medium text-gray-600 dark:bg-white/15 dark:text-gray-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        Payments
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-200">Explore more integrations</h3>
                    <p class="mb-4 text-gray-500 dark:text-gray-400">Discover all the ways Event Schedule connects with your favorite tools.</p>
                    <span class="inline-flex items-center gap-2 font-medium text-gray-600 transition-all group-hover:gap-3 dark:text-gray-300">
                        View all integrations
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Related Features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-8 pb-16 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ marketing_url('/features/ticketing') }}" class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    Ticketing Features
                </a>
                <a href="{{ marketing_url('/features/embed-tickets') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    Embed Tickets
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-black/30">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-stripe">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common questions about Stripe payments.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['Are there platform fees on Stripe payments?', 'No, Event Schedule charges zero platform fees on ticket sales. You only pay Stripe\'s standard processing fees (typically 2.9% + $0.30 per transaction). 100% of the ticket price goes to you.'],
                        ['Can selfhosted users accept Stripe payments?', 'Yes, selfhosted users can connect their own Stripe account directly using their Stripe API keys. Full setup instructions are available in the selfhosted documentation.'],
                        ['Does Stripe support Apple Pay and Google Pay?', 'Yes, Stripe Checkout automatically supports credit cards, Apple Pay, and Google Pay. Customers can pay using whichever method they prefer.'],
                        ['How do payouts work?', 'Payments go directly to your Stripe account via Stripe Connect. Standard Stripe payout schedules apply, typically depositing funds to your bank account within 2 business days.'],
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
    <!-- 9. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-currencies absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-6 px-8 opacity-45" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @php $symbols2 = ['$', '€', '£', '¥', '₹', '₩', '₪', '฿', '₴']; @endphp
                        @foreach ($symbols2 as $i => $sym)
                            @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                            <span class="es-currency" style="font-size: {{ $sz }}px; --cu-dur: {{ $dur }}s; --cu-delay: {{ $delay }}s;">{{ $sym }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Start accepting <span class="text-gradient-stripe">payments</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Connect your Stripe account and sell tickets today. No setup fees.
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

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
