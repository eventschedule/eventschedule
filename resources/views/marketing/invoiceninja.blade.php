<x-marketing-layout>
    <x-slot name="title">Invoice Ninja Invoicing - Event Schedule</x-slot>
    <x-slot name="description">Generate invoices and payment links for ticket purchases. Automatic client management, QR code tickets, and payment tracking with Invoice Ninja.</x-slot>
    <x-slot name="breadcrumbTitle">Invoice Ninja</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Invoice Ninja Integration",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Generate invoices and payment links for ticket purchases. Automatic client management, QR code tickets, and payment tracking with Invoice Ninja.",
        "featureList": [
            "Professional invoice generation",
            "Payment links",
            "Automatic client management",
            "QR code tickets",
            "Payment tracking",
            "B2B ticketing support",
            "Selfhosted invoicing"
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
                "name": "Can I use Invoice Ninja with selfhosted Event Schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, both Event Schedule and Invoice Ninja can be selfhosted, giving you full control over your event management and invoicing infrastructure."
                }
            },
            {
                "@type": "Question",
                "name": "Are QR code tickets generated automatically?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, when a payment is recorded in Invoice Ninja, tickets with unique QR codes are automatically generated and sent to buyers via email."
                }
            },
            {
                "@type": "Question",
                "name": "Does Invoice Ninja create client records automatically?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, new clients are created automatically in Invoice Ninja when tickets are purchased. Existing clients are matched by email address to avoid duplicates."
                }
            },
            {
                "@type": "Question",
                "name": "Can I use both Stripe and Invoice Ninja?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You can configure either Stripe or Invoice Ninja for each schedule, depending on whether you need instant card payments or professional B2B invoicing."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (emerald to teal to cyan) */
        .text-gradient-invoiceninja {
            background: linear-gradient(135deg, #10B981 0%, #14B8A6 50%, #06B6D4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-invoiceninja {
            background: linear-gradient(135deg, #34d399 0%, #2dd4bf 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* On always-dark surfaces, keep the accent bright regardless of page mode */
        .es-finale-panel .text-gradient-invoiceninja {
            background: linear-gradient(135deg, #34d399 0%, #2dd4bf 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live invoice card */
        .es-invoice-float { animation: es-invoice-bob 5.5s ease-in-out infinite; }
        @keyframes es-invoice-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a row of invoice documents pulsing */
        .es-invoice-icon {
            flex: 0 0 auto;
            color: rgba(16, 185, 129, 0.8);
            animation: es-invoice-pulse var(--in-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--in-delay, 0s);
        }
        @keyframes es-invoice-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-invoice-float, .es-invoice-icon, .animate-pulse-slow, .animate-pulse { animation: none !important; }
            .es-invoice-icon { opacity: 0.55; transform: none; }
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
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(20, 184, 166, 0.26), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Invoice-document motif along the bottom edge -->
            <div class="es-invoices absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 12; $i++)
                    @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-invoice-icon" style="--in-dur: {{ $dur }}s; --in-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Professional Invoicing</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Professional invoices with</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-invoiceninja">Invoice Ninja</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Invoices and payment links for ticket purchases. Perfect for B2B sales and corporate events.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
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

                <!-- Professional Invoices -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Invoicing
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Professional invoices</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Every ticket purchase automatically generates a professional invoice in Invoice Ninja with all the details.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">INVOICE #1042</span>
                                <span class="inline-flex items-center rounded bg-emerald-500/20 px-2 py-0.5 text-xs text-emerald-700 dark:text-emerald-300">Sent</span>
                            </div>
                            <div class="mb-1 font-semibold text-gray-900 dark:text-white">Jazz Night VIP</div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">2 tickets</span>
                                <span class="font-bold text-gray-900 dark:text-white">$150.00</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Client Management -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Clients
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Client management</h2>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Automatically creates new clients or links to existing ones. Build your customer database with every sale.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-500/20">
                                    <span class="text-xs font-bold text-teal-600 dark:text-teal-300">JD</span>
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">john@company.com</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg border border-teal-400/30 bg-teal-500/20 p-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-500/30">
                                    <span class="text-xs font-bold text-teal-700 dark:text-teal-200">AS</span>
                                </div>
                                <span class="text-sm text-teal-700 dark:text-teal-200">alice@startup.io</span>
                                <span class="ml-auto text-xs text-teal-600 dark:text-teal-300">New</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- QR Code Tickets -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            Tickets
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">QR code tickets</h2>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">The invoice includes a unique QR code for event check-in. Customers can use the invoice as their ticket.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="h-24 w-24 rounded-xl bg-white p-2">
                                <svg aria-hidden="true" viewBox="0 0 25 25" class="h-full w-full">
                                    <rect x="0" y="0" width="7" height="7" fill="black"/>
                                    <rect x="1" y="1" width="5" height="5" fill="white"/>
                                    <rect x="2" y="2" width="3" height="3" fill="black"/>
                                    <rect x="18" y="0" width="7" height="7" fill="black"/>
                                    <rect x="19" y="1" width="5" height="5" fill="white"/>
                                    <rect x="20" y="2" width="3" height="3" fill="black"/>
                                    <rect x="0" y="18" width="7" height="7" fill="black"/>
                                    <rect x="1" y="19" width="5" height="5" fill="white"/>
                                    <rect x="2" y="20" width="3" height="3" fill="black"/>
                                    <rect x="8" y="0" width="1" height="1" fill="black"/>
                                    <rect x="10" y="0" width="1" height="1" fill="black"/>
                                    <rect x="12" y="0" width="1" height="1" fill="black"/>
                                    <rect x="8" y="2" width="1" height="1" fill="black"/>
                                    <rect x="10" y="2" width="1" height="1" fill="black"/>
                                    <rect x="14" y="2" width="1" height="1" fill="black"/>
                                    <rect x="9" y="4" width="1" height="1" fill="black"/>
                                    <rect x="11" y="4" width="1" height="1" fill="black"/>
                                    <rect x="13" y="4" width="1" height="1" fill="black"/>
                                    <rect x="8" y="6" width="1" height="1" fill="black"/>
                                    <rect x="12" y="6" width="1" height="1" fill="black"/>
                                    <rect x="0" y="8" width="1" height="1" fill="black"/>
                                    <rect x="2" y="8" width="1" height="1" fill="black"/>
                                    <rect x="4" y="8" width="1" height="1" fill="black"/>
                                    <rect x="6" y="8" width="1" height="1" fill="black"/>
                                    <rect x="8" y="8" width="1" height="1" fill="black"/>
                                    <rect x="10" y="8" width="1" height="1" fill="black"/>
                                    <rect x="14" y="8" width="1" height="1" fill="black"/>
                                    <rect x="18" y="8" width="1" height="1" fill="black"/>
                                    <rect x="20" y="8" width="1" height="1" fill="black"/>
                                    <rect x="22" y="8" width="1" height="1" fill="black"/>
                                    <rect x="24" y="8" width="1" height="1" fill="black"/>
                                    <rect x="9" y="9" width="1" height="1" fill="black"/>
                                    <rect x="11" y="9" width="1" height="1" fill="black"/>
                                    <rect x="13" y="9" width="1" height="1" fill="black"/>
                                    <rect x="15" y="9" width="1" height="1" fill="black"/>
                                    <rect x="19" y="9" width="1" height="1" fill="black"/>
                                    <rect x="23" y="9" width="1" height="1" fill="black"/>
                                    <rect x="8" y="10" width="1" height="1" fill="black"/>
                                    <rect x="12" y="10" width="1" height="1" fill="black"/>
                                    <rect x="16" y="10" width="1" height="1" fill="black"/>
                                    <rect x="20" y="10" width="1" height="1" fill="black"/>
                                    <rect x="24" y="10" width="1" height="1" fill="black"/>
                                    <rect x="9" y="11" width="1" height="1" fill="black"/>
                                    <rect x="11" y="11" width="1" height="1" fill="black"/>
                                    <rect x="15" y="11" width="1" height="1" fill="black"/>
                                    <rect x="17" y="11" width="1" height="1" fill="black"/>
                                    <rect x="21" y="11" width="1" height="1" fill="black"/>
                                    <rect x="8" y="12" width="1" height="1" fill="black"/>
                                    <rect x="10" y="12" width="1" height="1" fill="black"/>
                                    <rect x="14" y="12" width="1" height="1" fill="black"/>
                                    <rect x="18" y="12" width="1" height="1" fill="black"/>
                                    <rect x="22" y="12" width="1" height="1" fill="black"/>
                                    <rect x="9" y="13" width="1" height="1" fill="black"/>
                                    <rect x="13" y="13" width="1" height="1" fill="black"/>
                                    <rect x="15" y="13" width="1" height="1" fill="black"/>
                                    <rect x="19" y="13" width="1" height="1" fill="black"/>
                                    <rect x="23" y="13" width="1" height="1" fill="black"/>
                                    <rect x="8" y="14" width="1" height="1" fill="black"/>
                                    <rect x="12" y="14" width="1" height="1" fill="black"/>
                                    <rect x="16" y="14" width="1" height="1" fill="black"/>
                                    <rect x="20" y="14" width="1" height="1" fill="black"/>
                                    <rect x="24" y="14" width="1" height="1" fill="black"/>
                                    <rect x="11" y="15" width="1" height="1" fill="black"/>
                                    <rect x="13" y="15" width="1" height="1" fill="black"/>
                                    <rect x="17" y="15" width="1" height="1" fill="black"/>
                                    <rect x="21" y="15" width="1" height="1" fill="black"/>
                                    <rect x="0" y="16" width="1" height="1" fill="black"/>
                                    <rect x="2" y="16" width="1" height="1" fill="black"/>
                                    <rect x="4" y="16" width="1" height="1" fill="black"/>
                                    <rect x="6" y="16" width="1" height="1" fill="black"/>
                                    <rect x="8" y="16" width="1" height="1" fill="black"/>
                                    <rect x="10" y="16" width="1" height="1" fill="black"/>
                                    <rect x="14" y="16" width="1" height="1" fill="black"/>
                                    <rect x="18" y="16" width="1" height="1" fill="black"/>
                                    <rect x="22" y="16" width="1" height="1" fill="black"/>
                                    <rect x="18" y="18" width="1" height="1" fill="black"/>
                                    <rect x="20" y="18" width="1" height="1" fill="black"/>
                                    <rect x="24" y="18" width="1" height="1" fill="black"/>
                                    <rect x="19" y="19" width="1" height="1" fill="black"/>
                                    <rect x="21" y="19" width="1" height="1" fill="black"/>
                                    <rect x="23" y="19" width="1" height="1" fill="black"/>
                                    <rect x="18" y="20" width="1" height="1" fill="black"/>
                                    <rect x="22" y="20" width="1" height="1" fill="black"/>
                                    <rect x="19" y="21" width="1" height="1" fill="black"/>
                                    <rect x="21" y="21" width="1" height="1" fill="black"/>
                                    <rect x="23" y="21" width="1" height="1" fill="black"/>
                                    <rect x="18" y="22" width="1" height="1" fill="black"/>
                                    <rect x="20" y="22" width="1" height="1" fill="black"/>
                                    <rect x="24" y="22" width="1" height="1" fill="black"/>
                                    <rect x="19" y="23" width="1" height="1" fill="black"/>
                                    <rect x="21" y="23" width="1" height="1" fill="black"/>
                                    <rect x="18" y="24" width="1" height="1" fill="black"/>
                                    <rect x="22" y="24" width="1" height="1" fill="black"/>
                                    <rect x="24" y="24" width="1" height="1" fill="black"/>
                                </svg>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Client Portal (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    Self-service
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">Client portal</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Customers access their invoices through Invoice Ninja's client portal. View history, download PDFs, and pay online.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-100 p-6 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-4 flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/20">
                                        <svg aria-hidden="true" class="h-5 w-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">Client Portal</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">john@company.com</div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Invoice #1042</span>
                                        <span class="text-sm text-emerald-500 dark:text-emerald-400">Paid</span>
                                    </div>
                                    <div class="flex justify-between rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Invoice #1038</span>
                                        <span class="text-sm text-emerald-500 dark:text-emerald-400">Paid</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Webhook Sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Real-time
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Webhook sync</h2>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Payment notifications flow back automatically. When customers pay, tickets are marked as confirmed instantly.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 animate-pulse rounded-full bg-blue-400"></div>
                                <span class="font-mono text-xs text-blue-600 dark:text-blue-300">invoice.paid</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multiple Currencies -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Global
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">100+ currencies</h2>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Invoice in any currency supported by Invoice Ninja. Perfect for international events and clients.</p>
                        <div class="mt-auto flex flex-wrap gap-2" aria-hidden="true">
                            <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">USD</span>
                            <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">EUR</span>
                            <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">GBP</span>
                            <span class="inline-flex items-center rounded bg-amber-500/20 px-2 py-1 text-sm text-amber-700 dark:text-amber-300">+100 more</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Selfhosted Friendly (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-6 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                    </svg>
                                    Selfhosted
                                </div>
                                <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Selfhosted friendly</h2>
                                <p class="text-gray-500 dark:text-gray-400">Works with your own Invoice Ninja installation. Full control over your invoicing data and branding.</p>
                            </div>
                            <div class="space-y-3" aria-hidden="true">
                                @foreach (['Your server, your data', 'Custom branding and templates', 'Works with Invoice Ninja hosted too'] as $item)
                                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/5 dark:bg-[#0f0f14]">
                                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                    </div>
                                @endforeach
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
    <section id="how-it-works" class="scroll-mt-24 bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>How it <span class="text-gradient-invoiceninja">works</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Automatic invoicing in four simple steps.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-4" data-reveal-group="90">
                @php
                    $steps = [
                        ['Connect account', 'Add your Invoice Ninja API URL and token in settings. Works with hosted or selfhosted.'],
                        ['Create tickets', 'Add ticket types to your events. Invoice Ninja handles the billing side.'],
                        ['Customer purchases', 'When someone buys tickets, an invoice is created automatically in Invoice Ninja.'],
                        ['Invoice sent', 'Customer receives the invoice with QR ticket. Payment confirmation syncs back automatically.'],
                    ];
                @endphp
                @foreach ($steps as $si => $step)
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-2xl font-bold text-white shadow-lg shadow-emerald-500/25">
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
    <!-- 4. Two checkout modes                                       -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Two checkout <span class="text-gradient-invoiceninja">modes</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Choose the mode that fits your workflow.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2" data-reveal-group="90">
                <!-- Invoice Mode -->
                <div data-reveal class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-8 dark:border-emerald-800/50 dark:from-emerald-900/20 dark:to-teal-900/20">
                    <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">Invoice mode</h3>
                    <p class="mb-6 text-gray-600 dark:text-gray-300">Customers select tickets and enter promo codes in Event Schedule. Each purchase creates a separate invoice in Invoice Ninja.</p>
                    <ul class="space-y-3">
                        @foreach (['Multiple promo codes per event', 'Per-ticket promo targeting', 'Full checkout control in Event Schedule'] as $item)
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Payment Link Mode -->
                <div data-reveal class="rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 to-cyan-50 p-8 dark:border-teal-800/50 dark:from-teal-900/20 dark:to-cyan-900/20">
                    <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">Payment link mode</h3>
                    <p class="mb-6 text-gray-600 dark:text-gray-300">Customers select tickets and enter promo codes on Invoice Ninja's purchase page. Invoices are grouped together for easier management.</p>
                    <ul class="space-y-3">
                        @foreach (['Invoices grouped in Invoice Ninja', 'Invoice Ninja purchase page', 'One promo code per event'] as $item)
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="mt-8 text-center" data-reveal>
                <a href="{{ route('marketing.docs.tickets') }}#invoiceninja-modes" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                    Compare modes in detail &rarr;
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Perfect for business events                              -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Perfect for <span class="text-gradient-invoiceninja">business events</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Professional invoicing for corporate clients and B2B sales.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                @php
                    // Full class strings (interpolated Tailwind classes are not generated by JIT)
                    $benefits = [
                        ['B2B Ready', 'Corporate clients get proper invoices for expense reports and accounting.', 'border-emerald-500/20 bg-emerald-500/10', 'text-emerald-500 dark:text-emerald-400', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ['Tax Tracking', 'Invoice Ninja handles tax calculations and reporting for your records.', 'border-teal-500/20 bg-teal-500/10', 'text-teal-500 dark:text-teal-400', 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                        ['Flexible Payments', 'Customers pay through Invoice Ninja using their preferred payment method.', 'border-cyan-500/20 bg-cyan-500/10', 'text-cyan-500 dark:text-cyan-400', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ];
                @endphp
                @foreach ($benefits as $b)
                    <div data-reveal class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl border {{ $b[2] }}">
                            <svg aria-hidden="true" class="h-7 w-7 {{ $b[3] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $b[4] }}" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-gray-900 dark:text-white">{{ $b[0] }}</h3>
                        <p class="text-gray-500 dark:text-gray-400">{{ $b[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Invoice Ninja Link (external)                            -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <a href="https://invoiceninja.com" target="_blank" rel="noopener noreferrer" data-reveal class="group block">
                <div class="rounded-3xl border border-gray-200 bg-gradient-to-br from-emerald-100 to-teal-100 p-8 transition-all hover:border-gray-300 dark:border-white/10 dark:from-emerald-900/30 dark:to-teal-900/30 dark:hover:border-white/20">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-1 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Official Site
                    </div>
                    <div class="mb-4 flex justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/20">
                            <svg aria-hidden="true" class="h-9 w-9 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-200">Learn more about Invoice Ninja</h3>
                    <p class="mb-4 text-gray-500 dark:text-gray-400">Explore Invoice Ninja's open-source invoicing platform, features, and selfhosted options.</p>
                    <span class="inline-flex items-center gap-2 font-medium text-emerald-700 transition-all group-hover:gap-3 dark:text-emerald-300">
                        Visit invoiceninja.com
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Explore More Integrations                                -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/integrations') }}" data-reveal class="group block">
                <div class="rounded-3xl border border-gray-200 bg-gradient-to-br from-gray-100 to-gray-200 p-8 transition-all hover:border-gray-300 dark:border-white/10 dark:from-gray-800/50 dark:to-gray-900 dark:hover:border-white/20">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-gray-200 px-3 py-1 text-sm font-medium text-gray-600 dark:bg-white/15 dark:text-gray-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Invoicing
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
    <!-- 8. Related Features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-8 pb-16 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ marketing_url('/features/ticketing') }}" class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-100 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-500/20">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    Ticketing Features
                </a>
                <a href="{{ marketing_url('/open-source') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    Open Source
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-black/30">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-invoiceninja">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common questions about Invoice Ninja integration.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['Can I use Invoice Ninja with selfhosted Event Schedule?', 'Yes, both Event Schedule and Invoice Ninja can be selfhosted, giving you full control over your event management and invoicing infrastructure.'],
                        ['Are QR code tickets generated automatically?', 'Yes, when a payment is recorded in Invoice Ninja, tickets with unique QR codes are automatically generated and sent to buyers via email.'],
                        ['Does Invoice Ninja create client records automatically?', 'Yes, new clients are created automatically in Invoice Ninja when tickets are purchased. Existing clients are matched by email address to avoid duplicates.'],
                        ['Can I use both Stripe and Invoice Ninja?', 'You can configure either Stripe or Invoice Ninja for each schedule, depending on whether you need instant card payments or professional B2B invoicing.'],
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
    <!-- 10. Finale                                                  -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-emerald-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-invoices absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-6 px-8 opacity-45" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 9; $i++)
                            @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                            <span class="es-invoice-icon" style="--in-dur: {{ $dur }}s; --in-delay: {{ $delay }}s;">
                                <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Professional invoicing <span class="text-gradient-invoiceninja">made easy</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Connect Invoice Ninja and automate your event billing today.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
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
