<x-marketing-layout>
    <x-slot name="title">Invoice Ninja Integration - Event Schedule</x-slot>
    <x-slot name="description">Generate professional invoices for ticket purchases. Automatic client management, QR code tickets, and automatic payment tracking with Invoice Ninja.</x-slot>
    <x-slot name="breadcrumbTitle">Invoice Ninja</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Invoice Ninja Integration",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Generate professional invoices for ticket purchases. Automatic client management, QR code tickets, and automatic payment tracking with Invoice Ninja.",
        "featureList": [
            "Professional invoice generation",
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
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Custom emerald gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #10B981 0%, #14B8A6 50%, #06B6D4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient {
            background: linear-gradient(135deg, #34d399 0%, #2dd4bf 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-pulse-slow,
            .animate-pulse {
                animation: none;
            }
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Professional Invoicing</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Professional invoices with<br>
                <span class="text-gradient">Invoice Ninja</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Automatic invoice generation for ticket purchases. Perfect for B2B sales and corporate events.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-emerald-500/25">
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

                <!-- Professional Invoices -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Invoicing
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Professional invoices</h2>
                    <p class="text-gray-500 dark:text-gray-300 mb-6">Every ticket purchase automatically generates a professional invoice in Invoice Ninja with all the details.</p>

                    <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-emerald-600 dark:text-emerald-400 text-xs font-medium">INVOICE #1042</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-xs">Sent</span>
                        </div>
                        <div class="text-gray-900 dark:text-white font-semibold mb-1">Jazz Night VIP</div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">2 tickets</span>
                            <span class="text-gray-900 dark:text-white font-bold">$150.00</span>
                        </div>
                    </div>
                </div>

                <!-- Client Management -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-teal-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Clients
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Client management</h2>
                    <p class="text-gray-500 dark:text-gray-300 mb-4">Automatically creates new clients or links to existing ones. Build your customer database with every sale.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                            <div class="w-8 h-8 rounded-full bg-teal-500/20 flex items-center justify-center">
                                <span class="text-teal-600 dark:text-teal-300 text-xs font-bold">JD</span>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">john@company.com</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-teal-500/20 border border-teal-400/30">
                            <div class="w-8 h-8 rounded-full bg-teal-500/30 flex items-center justify-center">
                                <span class="text-teal-700 dark:text-teal-200 text-xs font-bold">AS</span>
                            </div>
                            <span class="text-teal-700 dark:text-teal-200 text-sm">alice@startup.io</span>
                            <span class="ml-auto text-teal-600 dark:text-teal-300 text-xs">New</span>
                        </div>
                    </div>
                </div>

                <!-- QR Code Tickets -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900 dark:to-blue-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        Tickets
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">QR code tickets</h2>
                    <p class="text-gray-500 dark:text-gray-300 mb-4">The invoice includes a unique QR code for event check-in. Customers can use the invoice as their ticket.</p>

                    <div class="flex justify-center">
                        <div class="w-24 h-24 bg-white rounded-xl p-2">
                            <svg aria-hidden="true" viewBox="0 0 25 25" class="w-full h-full">
                                <!-- Top-left position square -->
                                <rect x="0" y="0" width="7" height="7" fill="black"/>
                                <rect x="1" y="1" width="5" height="5" fill="white"/>
                                <rect x="2" y="2" width="3" height="3" fill="black"/>
                                <!-- Top-right position square -->
                                <rect x="18" y="0" width="7" height="7" fill="black"/>
                                <rect x="19" y="1" width="5" height="5" fill="white"/>
                                <rect x="20" y="2" width="3" height="3" fill="black"/>
                                <!-- Bottom-left position square -->
                                <rect x="0" y="18" width="7" height="7" fill="black"/>
                                <rect x="1" y="19" width="5" height="5" fill="white"/>
                                <rect x="2" y="20" width="3" height="3" fill="black"/>
                                <!-- Data pattern -->
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
                </div>

                <!-- Client Portal (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-cyan-100 dark:from-emerald-900 dark:to-cyan-900 border border-emerald-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                Self-service
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Client portal</h2>
                            <p class="text-gray-500 dark:text-gray-300 text-lg">Customers access their invoices through Invoice Ninja's client portal. View history, download PDFs, and pay online.</p>
                        </div>
                        <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                    <svg aria-hidden="true" class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white font-medium">Client Portal</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-sm">john@company.com</div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">Invoice #1042</span>
                                    <span class="text-emerald-400 text-sm">Paid</span>
                                </div>
                                <div class="flex justify-between p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">Invoice #1038</span>
                                    <span class="text-emerald-400 text-sm">Paid</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Webhook Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Real-time
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Webhook sync</h2>
                    <p class="text-gray-500 dark:text-gray-300 mb-4">Payment notifications flow back automatically. When customers pay, tickets are marked as confirmed instantly.</p>

                    <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl p-3 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></div>
                            <span class="text-blue-600 dark:text-blue-300 text-xs font-mono">invoice.paid</span>
                        </div>
                    </div>
                </div>

                <!-- Multiple Currencies -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Global
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">100+ currencies</h2>
                    <p class="text-gray-500 dark:text-gray-300 mb-4">Invoice in any currency supported by Invoice Ninja. Perfect for international events and clients.</p>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">USD</span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">EUR</span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">GBP</span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-amber-500/20 text-amber-700 dark:text-amber-300 text-sm">+100 more</span>
                    </div>
                </div>

                <!-- Selfhosted Friendly -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 border border-teal-200 dark:border-white/10 p-8">
                    <div class="grid md:grid-cols-2 gap-6 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                                Selfhosted
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Selfhosted friendly</h2>
                            <p class="text-gray-500 dark:text-gray-300 mb-4">Works with your own Invoice Ninja installation. Full control over your invoicing data and branding.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-[#0f0f14] border border-gray-100 dark:border-white/5">
                                <svg aria-hidden="true" class="w-5 h-5 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300 text-sm">Your server, your data</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-[#0f0f14] border border-gray-100 dark:border-white/5">
                                <svg aria-hidden="true" class="w-5 h-5 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300 text-sm">Custom branding and templates</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-[#0f0f14] border border-gray-100 dark:border-white/5">
                                <svg aria-hidden="true" class="w-5 h-5 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300 text-sm">Works with Invoice Ninja hosted too</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Automatic invoicing in four simple steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Connect account</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Add your Invoice Ninja API URL and token in settings. Works with hosted or selfhosted.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create tickets</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Add ticket types to your events. Invoice Ninja handles the billing side.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Customer purchases</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        When someone buys tickets, an invoice is created automatically in Invoice Ninja.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/25">
                        4
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Invoice sent</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Customer receives the invoice with QR ticket. Payment confirmation syncs back automatically.
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
                    Perfect for business events
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Professional invoicing for corporate clients and B2B sales.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-2xl p-8 border border-emerald-200 dark:border-emerald-500/20">
                    <div class="w-14 h-14 bg-emerald-500/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg aria-hidden="true" class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">B2B Ready</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Corporate clients get proper invoices for expense reports and accounting.
                    </p>
                </div>

                <div class="bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/30 dark:to-cyan-900/30 rounded-2xl p-8 border border-teal-200 dark:border-teal-500/20">
                    <div class="w-14 h-14 bg-teal-500/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg aria-hidden="true" class="w-7 h-7 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Tax Tracking</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Invoice Ninja handles tax calculations and reporting for your records.
                    </p>
                </div>

                <div class="bg-gradient-to-br from-cyan-50 to-blue-50 dark:from-cyan-900/30 dark:to-blue-900/30 rounded-2xl p-8 border border-cyan-200 dark:border-cyan-500/20">
                    <div class="w-14 h-14 bg-cyan-500/20 rounded-2xl flex items-center justify-center mb-6">
                        <svg aria-hidden="true" class="w-7 h-7 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Flexible Payments</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Customers pay through Invoice Ninja using their preferred payment method.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Invoice Ninja Link -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <a href="https://invoiceninja.com" target="_blank" rel="noopener noreferrer" class="group block">
                <div class="bg-gradient-to-br from-emerald-900/30 to-teal-900/30 rounded-3xl border border-gray-200 dark:border-white/10 p-8 hover:border-gray-300 dark:hover:border-white/20 transition-all">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Official Site
                    </div>
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 bg-emerald-500/20 rounded-2xl flex items-center justify-center">
                            <svg aria-hidden="true" class="w-9 h-9 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors">Learn more about Invoice Ninja</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Explore Invoice Ninja's open-source invoicing platform, features, and selfhosted options.</p>
                    <span class="inline-flex items-center text-emerald-700 dark:text-emerald-300 font-medium group-hover:gap-3 gap-2 transition-all">
                        Visit invoiceninja.com
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Invoicing
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors">Explore more integrations</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">Discover all the ways Event Schedule connects with your favorite tools.</p>
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
    <section class="relative bg-gradient-to-br from-emerald-600 to-teal-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Professional invoicing made easy
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Connect Invoice Ninja and automate your event billing today.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-emerald-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
