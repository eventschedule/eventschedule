<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.ticketing_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.ticketing_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Ticketing</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Ticketing",
        "description": "Sell tickets directly through your event schedule with QR codes, multiple ticket types, and secure payment processing. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Ticketing"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What are the fees for selling tickets?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event Schedule charges zero platform fees on ticket sales. You only pay the standard Stripe processing fees (typically 2.9% + $0.30 per transaction). 100% of the ticket price goes to you."
                }
            },
            {
                "@type": "Question",
                "name": "How does QR code check-in work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Every ticket includes a unique QR code sent to the buyer via email. At the door, use any smartphone to scan the QR code and check in attendees instantly. The system prevents duplicate scans and tracks attendance in real time."
                }
            },
            {
                "@type": "Question",
                "name": "What payment methods are supported?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event Schedule supports Stripe for credit cards, Apple Pay, and Google Pay, as well as Invoice Ninja for professional B2B invoicing. You can also add a custom payment URL to link to any payment system."
                }
            },
            {
                "@type": "Question",
                "name": "How are refunds handled?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Refunds are managed directly through your Stripe dashboard. You have full control over your refund policy and can issue full or partial refunds to individual ticket buyers at any time."
                }
            },
            {
                "@type": "Question",
                "name": "Can I offer promo codes or discounts?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes! You can create promo codes with either percentage or fixed amount discounts. Promo codes can be targeted to specific ticket types, giving you full control over your discount strategy."
                }
            },
            {
                "@type": "Question",
                "name": "What happens when tickets sell out?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When all tickets sell out, a waitlist button appears automatically. Fans can join the waitlist and are notified one at a time when spots open up, with 24 hours to complete their purchase."
                }
            },
            {
                "@type": "Question",
                "name": "Can I get notified when tickets sell?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Enable sale notification emails in your schedule settings to receive an email whenever a ticket sells. Each notification includes buyer details, ticket type, and payment status."
                }
            },
            {
                "@type": "Question",
                "name": "Can I export my sales data?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Export all your sales as a CSV file with buyer info, ticket types, amounts, promo codes, payment status, and custom field responses. The file opens in Excel, Google Sheets, or any spreadsheet app."
                }
            }
        ]
    }
    </script>
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
            "Automatic confirmation emails",
            "Ticket waitlist",
            "Check-in dashboard",
            "Sale notification emails",
            "Sales CSV export"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* For ticketing "The Turnstile" styles. The shared es-* motion system lives
           in marketing.css; this holds the ticket glow gradient, the drifting
           ticket-stub card, the QR scan line, and the barcode-scan motif. */
        .text-gradient-ticket {
            background: linear-gradient(135deg, #0284c7, #0ea5e9, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(2, 132, 199, 0.3);
        }
        .dark .text-gradient-ticket {
            background: linear-gradient(135deg, #38bdf8, #22d3ee, #7dd3fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(56, 189, 248, 0.3);
        }
        @keyframes es-ticket-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-ticket-float { animation: es-ticket-float 6s ease-in-out infinite; }

        /* QR scan line (kept from the original for the QR mock) */
        @keyframes scan-line {
            0%, 100% { transform: translateY(0); opacity: 1; }
            50% { transform: translateY(100px); opacity: 0.5; }
        }
        .animate-scan { animation: scan-line 2s ease-in-out infinite; }

        /* Barcode-scan motif: bars brighten under a sweeping scanner light. */
        .es-barcode { display: flex; align-items: center; gap: 2px; }
        .es-bar {
            flex: 0 0 auto; height: 100%; border-radius: 1px;
            background: linear-gradient(to bottom, rgba(14, 165, 233, 0.6), rgba(6, 182, 212, 0.6));
            animation: es-bar-scan var(--br-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--br-delay, 0s);
        }
        @keyframes es-bar-scan {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 0.9; box-shadow: 0 0 8px rgba(14, 165, 233, 0.5); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-ticket-float, .animate-scan, .es-bar { animation: none !important; }
            .es-bar { opacity: 0.5; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: integrated ticketing                                -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(2, 132, 199, 0.3), rgba(2, 132, 199, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(37, 99, 235, 0.28), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Barcode scanner along the bottom edge -->
            <div class="es-barcode absolute bottom-0 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center px-8 opacity-40 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 80; $i++)
                    @php $w = [2, 4, 3, 6, 2, 5, 3, 2][$i % 8]; $dur = 2.2 + ($i % 6) * 0.25; $delay = ($i % 14) * 0.1; @endphp
                    <span class="es-bar" style="width: {{ $w }}px; --br-dur: {{ $dur }}s; --br-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Zero platform fees</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Integrated</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-ticket">ticketing</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Sell tickets directly through your schedule. No plugins, no complicated setup.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                    Start selling tickets
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.tickets') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Ticketing guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento features                                            -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="100">

                <!-- QR Tickets (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                    QR Tickets
                                </div>
                                <h2 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Contactless check-in</h2>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Every ticket comes with a unique QR code. Scan with any smartphone to check in attendees instantly. No special hardware required.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Instant validation</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Prevent duplicates</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Real-time tracking</span>
                                </div>
                            </div>
                            <div class="shrink-0" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="h-64 w-48 rounded-2xl border border-gray-300 bg-gradient-to-br from-gray-200 to-gray-100 p-4 shadow-2xl dark:border-white/20 dark:from-white/10 dark:to-white/5">
                                        <div class="mb-3 text-center">
                                            <div class="font-semibold text-gray-900 dark:text-white">Jazz Night</div>
                                            <div class="text-sm text-sky-600 dark:text-sky-300">VIP Access</div>
                                        </div>
                                        <div class="relative mx-auto h-32 w-32 overflow-hidden rounded-xl bg-white p-2">
                                            <div class="h-full w-full bg-contain bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%231f2937%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')]"></div>
                                            <div class="absolute inset-x-0 top-0 h-1 animate-scan bg-gradient-to-r from-sky-500 to-blue-500"></div>
                                        </div>
                                        <div class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">Scan to check in</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multiple ticket types -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            Ticket Types
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Multiple tiers</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">GA, VIP, early bird, group rates. Create as many ticket types as you need.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="es-ai-field flex items-center justify-between rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 0;">
                                <div><div class="font-medium text-gray-900 dark:text-white">Early Bird</div><div class="text-xs text-emerald-500 dark:text-emerald-400">50 remaining</div></div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">$15</div>
                            </div>
                            <div class="es-ai-field flex items-center justify-between rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 1;">
                                <div><div class="font-medium text-gray-900 dark:text-white">General</div><div class="text-xs text-gray-500 dark:text-gray-400">200 remaining</div></div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">$25</div>
                            </div>
                            <div class="es-ai-field flex items-center justify-between rounded-xl border border-blue-400/30 bg-blue-500/20 p-3" style="--i: 2;">
                                <div><div class="font-medium text-gray-900 dark:text-white">VIP</div><div class="text-xs text-blue-600 dark:text-blue-300">20 remaining</div></div>
                                <div class="text-xl font-bold text-gray-900 dark:text-white">$75</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Promo codes -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            Promo Codes
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Discount codes</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Offer percentage or fixed amount discounts. Target specific ticket types.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="es-ai-field flex items-center justify-between rounded-xl border border-amber-400/20 bg-amber-500/10 p-3" style="--i: 0;">
                                <div class="font-mono text-sm text-gray-900 dark:text-white">EARLY20</div>
                                <div class="text-sm font-semibold text-amber-600 dark:text-amber-300">20% off</div>
                            </div>
                            <div class="es-ai-field flex items-center justify-between rounded-xl border border-amber-400/20 bg-amber-500/10 p-3" style="--i: 1;">
                                <div class="font-mono text-sm text-gray-900 dark:text-white">VIP10</div>
                                <div class="text-sm font-semibold text-amber-600 dark:text-amber-300">$10 off</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Reservations (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Reservations
                                </div>
                                <h2 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Hold now, pay later</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Let customers reserve tickets without paying upfront. Set expiration times and automatic reminders.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-100 p-6 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-4 flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/20">
                                        <svg aria-hidden="true" class="h-5 w-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">2 tickets reserved</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Jazz Night - VIP</div>
                                    </div>
                                </div>
                                <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-emerald-600 dark:text-emerald-300">Expires in</span>
                                        <span class="font-mono font-bold text-gray-900 dark:text-white">23:45:12</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Waitlist (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    Waitlist
                                </div>
                                <h2 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Never miss a sale</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">When tickets sell out, fans can join the waitlist. They're automatically notified when spots open up, one at a time, with 24 hours to buy.</p>
                            </div>
                            <div class="w-full shrink-0 lg:w-64" aria-hidden="true">
                                <div class="space-y-3">
                                    <div class="es-ai-field flex items-center gap-3 rounded-xl border border-teal-400/20 bg-teal-500/10 p-3" style="--i: 0;">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-500/20">
                                            <svg aria-hidden="true" class="h-4 w-4 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div class="min-w-0"><div class="truncate text-sm font-medium text-gray-900 dark:text-white">Sarah M.</div><div class="text-xs text-teal-600 dark:text-teal-300">Notified</div></div>
                                    </div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 1;">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-300 dark:bg-white/10">
                                            <svg aria-hidden="true" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <div class="min-w-0"><div class="truncate text-sm font-medium text-gray-900 dark:text-white">James K.</div><div class="text-xs text-gray-500 dark:text-gray-400">Waiting</div></div>
                                    </div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 2;">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-300 dark:bg-white/10">
                                            <svg aria-hidden="true" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <div class="min-w-0"><div class="truncate text-sm font-medium text-gray-900 dark:text-white">Alex R.</div><div class="text-xs text-gray-500 dark:text-gray-400">Waiting</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Sale notifications -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-lime-200 bg-lime-100 px-3 py-1.5 text-sm font-medium text-lime-700 dark:border-lime-800/30 dark:bg-lime-900/40 dark:text-lime-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            Sale Alerts
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Know when you sell</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Get an email whenever a ticket sells. Buyer details, ticket types, and payment status, right in your inbox.</p>
                        <div class="mt-auto rounded-xl border border-lime-400/20 bg-lime-500/10 p-4" aria-hidden="true">
                            <div class="mb-2 flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-500/20">
                                    <svg aria-hidden="true" class="h-4 w-4 text-lime-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">New sale!</div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">2x VIP - Jazz Night</div>
                            <div class="mt-1 text-xs text-lime-600 dark:text-lime-300">$150.00 - Paid</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Free registration (3 cols) -->
                <div class="es-bento group relative md:col-span-2 lg:col-span-3" data-tilt="2.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-3">
                            <div class="md:col-span-2">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700 dark:border-green-800/30 dark:bg-green-900/40 dark:text-green-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                                    Free
                                </div>
                                <h2 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Free event registration</h2>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Not selling tickets? Enable registration and let attendees sign up with just a name and email. No payment setup needed, no ticketing complexity. Perfect for meetups, community events, and open gatherings.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Optional capacity limits</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">QR code check-in</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Confirmation emails</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Free tier</span>
                                </div>
                            </div>
                            <div class="flex justify-center" aria-hidden="true">
                                <div class="w-full max-w-[220px] rounded-2xl border border-gray-200 bg-gray-100 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-4 text-center">
                                        <div class="font-semibold text-gray-900 dark:text-white">Community Meetup</div>
                                        <div class="text-sm text-green-600 dark:text-green-300">Free</div>
                                    </div>
                                    <div class="mb-4 space-y-3">
                                        <div class="flex h-8 items-center rounded-lg border border-gray-200 bg-gray-200 px-3 dark:border-white/10 dark:bg-white/10"><span class="text-xs text-gray-500 dark:text-gray-400">Your name</span></div>
                                        <div class="flex h-8 items-center rounded-lg border border-gray-200 bg-gray-200 px-3 dark:border-white/10 dark:bg-white/10"><span class="text-xs text-gray-500 dark:text-gray-400">Your email</span></div>
                                    </div>
                                    <div class="w-full rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 py-2 text-center text-sm font-semibold text-white">Register</div>
                                    <div class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">12 of 50 spots remaining</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Check-in dashboard -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Live Dashboard
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Track every check-in</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Real-time progress bars, per-ticket breakdowns, and a live activity feed. Auto-refreshes on any device.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="mb-1 flex justify-between text-sm">
                                <span class="font-medium text-gray-900 dark:text-white">Checked in</span>
                                <span class="font-semibold text-rose-600 dark:text-rose-300">67%</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                                <div class="h-full rounded-full bg-gradient-to-r from-rose-500 to-orange-500" style="width: 67%"></div>
                            </div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">67 / 100 attendees</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Sales export (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    Export
                                </div>
                                <h2 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Download your sales data</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Export all sales as a CSV with buyer info, ticket types, amounts, promo codes, payment status, and custom field responses. Opens in Excel, Google Sheets, or any spreadsheet app.</p>
                            </div>
                            <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="flex gap-3 whitespace-nowrap font-mono text-xs">
                                    <div class="space-y-2"><div class="font-semibold text-gray-500 dark:text-gray-400">Name</div><div class="text-gray-900 dark:text-white">Sarah M.</div><div class="text-gray-900 dark:text-white">James K.</div></div>
                                    <div class="space-y-2"><div class="font-semibold text-gray-500 dark:text-gray-400">Ticket</div><div class="text-gray-900 dark:text-white">VIP</div><div class="text-gray-900 dark:text-white">GA</div></div>
                                    <div class="space-y-2"><div class="font-semibold text-gray-500 dark:text-gray-400">Amount</div><div class="text-gray-900 dark:text-white">$75.00</div><div class="text-gray-900 dark:text-white">$25.00</div></div>
                                    <div class="space-y-2"><div class="font-semibold text-gray-500 dark:text-gray-400">Promo</div><div class="text-gray-900 dark:text-white">EARLY20</div><div class="text-gray-500 dark:text-gray-400">-</div></div>
                                    <div class="space-y-2"><div class="font-semibold text-gray-500 dark:text-gray-400">Status</div><div class="text-emerald-600 dark:text-emerald-400">Paid</div><div class="text-emerald-600 dark:text-emerald-400">Paid</div></div>
                                </div>
                            </div>
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
    @php
        $steps = [
            ['1', 'Connect Payments', 'Link Stripe or Invoice Ninja to receive payments directly.'],
            ['2', 'Add Tickets', 'Create ticket types with prices and quantities for your event.'],
            ['3', 'Share Your Event', 'Visitors can purchase tickets directly from your schedule.'],
            ['4', 'Check In', 'Scan QR codes at the door and track attendance with a live dashboard.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Start selling in <span class="text-gradient-ticket">minutes</span>
                </h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Four simple steps to your first ticket sale.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-4" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-blue-500 text-2xl font-bold text-white shadow-lg shadow-sky-500/25">
                            {{ $num }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Zero fees (dark band)                                    -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(16, 185, 129, 0.24), rgba(16, 185, 129, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(2, 132, 199, 0.22), rgba(2, 132, 199, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-barcode absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-4xl items-center justify-center px-8 opacity-25" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 72; $i++)
                        @php $w = [2, 4, 3, 6, 2, 5, 3, 2][$i % 8]; $dur = 2.2 + ($i % 6) * 0.25; $delay = ($i % 14) * 0.1; @endphp
                        <span class="es-bar" style="width: {{ $w }}px; --br-dur: {{ $dur }}s; --br-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl text-center">
                <div class="mb-8 inline-flex h-20 w-20 items-center justify-center rounded-3xl border border-emerald-500/30 bg-gradient-to-br from-emerald-500/20 to-teal-500/20" data-reveal>
                    <span class="text-4xl font-black text-emerald-400">$0</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                    No platform fees. <span class="text-gradient-ticket">Ever.</span>
                </h2>
                <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    We don't take a cut of your ticket sales. You only pay Stripe's standard processing fees (typically 2.9% + $0.30 per transaction).
                </p>

                <div class="mx-auto grid max-w-3xl grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="80">
                    <a href="{{ marketing_url('/stripe') }}" data-reveal class="group flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-6 transition-all hover:-translate-y-1 hover:border-blue-400/30 hover:bg-white/[0.07]">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/20">
                            <svg aria-hidden="true" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                        </div>
                        <h3 class="mb-2 font-semibold text-white transition-colors group-hover:text-blue-300">Stripe</h3>
                        <p class="mb-3 text-sm text-gray-400">Credit cards, Apple Pay, Google Pay</p>
                        <span class="mt-auto inline-flex items-center text-xs font-medium text-blue-400">
                            Learn more
                            <svg aria-hidden="true" class="ml-1 h-3 w-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </a>

                    <a href="{{ marketing_url('/invoiceninja') }}" data-reveal class="group flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-6 transition-all hover:-translate-y-1 hover:border-emerald-400/30 hover:bg-white/[0.07]">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/20">
                            <svg aria-hidden="true" class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <h3 class="mb-2 font-semibold text-white transition-colors group-hover:text-emerald-300">Invoice Ninja</h3>
                        <p class="mb-3 text-sm text-gray-400">Professional invoicing for B2B</p>
                        <span class="mt-auto inline-flex items-center text-xs font-medium text-emerald-400">
                            Learn more
                            <svg aria-hidden="true" class="ml-1 h-3 w-3 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </a>

                    <div data-reveal class="flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-6">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-sky-500/20">
                            <svg aria-hidden="true" class="h-6 w-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        </div>
                        <h3 class="mb-2 font-semibold text-white">Custom URL</h3>
                        <p class="text-sm text-gray-400">Link to any payment system</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Guide & next feature                                     -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <a href="{{ route('marketing.docs.tickets') }}" data-reveal class="group block">
                    <div class="ap-card flex h-full flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5 lg:p-10">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400">Read the guide</h3>
                        <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Learn how to get the most out of ticketing.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-sky-500 transition-all group-hover:gap-3 dark:text-sky-400">
                            Read guide
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </div>
                </a>

                <a href="{{ marketing_url('/features/ai') }}" data-reveal class="group block">
                    <div class="flex h-full flex-col rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-100 to-sky-100 p-8 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-blue-900 dark:to-sky-900 lg:p-10">
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300">AI-Powered Import</h3>
                        <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Paste text or drop an image. AI extracts event details automatically.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-blue-500 transition-all group-hover:gap-3 dark:text-blue-400">
                            Learn more
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </div>
                </a>

                <div data-reveal class="ap-card flex h-full flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        @foreach ([['/for-musicians', 'Musicians'], ['/for-venues', 'Venues'], ['/for-comedy-clubs', 'Comedy Clubs']] as [$href, $label])
                            <a href="{{ marketing_url($href) }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $label }}</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-ticket">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything you need to know about ticketing.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What are the fees for selling tickets?', 'Event Schedule charges zero platform fees on ticket sales. You only pay the standard Stripe processing fees (typically 2.9% + $0.30 per transaction). 100% of the ticket price goes to you.'],
                    ['How does QR code check-in work?', 'Every ticket includes a unique QR code sent to the buyer via email. At the door, use any smartphone to scan the QR code and check in attendees instantly. The system prevents duplicate scans and tracks attendance in real time.'],
                    ['What payment methods are supported?', 'Event Schedule supports Stripe for credit cards, Apple Pay, and Google Pay, as well as Invoice Ninja for professional B2B invoicing. You can also add a custom payment URL to link to any payment system.'],
                    ['How are refunds handled?', 'Refunds are managed directly through your Stripe dashboard. You have full control over your refund policy and can issue full or partial refunds to individual ticket buyers at any time.'],
                    ['Can I offer promo codes or discounts?', 'Yes! You can create promo codes with either percentage or fixed amount discounts. Promo codes can be targeted to specific ticket types, giving you full control over your discount strategy.'],
                    ['What happens when tickets sell out?', 'When all tickets sell out, a waitlist button appears automatically. Fans can join the waitlist and are notified one at a time when spots open up, with 24 hours to complete their purchase.'],
                    ['Can I get notified when tickets sell?', 'Yes. Enable sale notification emails in your schedule settings to receive an email whenever a ticket sells. Each notification includes buyer details, ticket type, and payment status.'],
                    ['Can I export my sales data?', 'Yes. Export all your sales as a CSV file with buyer info, ticket types, amounts, promo codes, payment status, and custom field responses. The file opens in Excel, Google Sheets, or any spreadsheet app.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <!-- 7. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Stripe Payments" description="Accept credit cards, Apple Pay, and Google Pay with zero platform fees" :url="marketing_url('/stripe')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom Fields" description="Collect additional info from ticket buyers with custom form fields" :url="marketing_url('/features/custom-fields')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send branded newsletters to followers and ticket buyers" :url="marketing_url('/features/newsletters')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Embed your full event calendar on any website" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Tickets" description="Embed a ticket purchase or RSVP form on any website with one line of code" :url="marketing_url('/features/embed-tickets')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-sky-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(2, 132, 199, 0.3), rgba(2, 132, 199, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-barcode absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-3xl items-center justify-center px-8 opacity-25" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 60; $i++)
                            @php $w = [2, 4, 3, 6, 2, 5, 3, 2][$i % 8]; $dur = 2.2 + ($i % 6) * 0.25; $delay = ($i % 14) * 0.1; @endphp
                            <span class="es-bar" style="width: {{ $w }}px; --br-dur: {{ $dur }}s; --br-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Start selling tickets <span class="text-gradient-ticket">today</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Set up ticketing for your events in minutes. No credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-sky-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
