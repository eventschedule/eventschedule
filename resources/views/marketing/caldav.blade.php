<x-marketing-layout>
    <x-slot name="title">CalDAV Sync - Event Schedule</x-slot>
    <x-slot name="description">Sync with any CalDAV-compatible calendar server. Works with Nextcloud, Radicale, Fastmail, iCloud, and more. Open standard, selfhosted friendly.</x-slot>
    <x-slot name="breadcrumbTitle">CalDAV</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - CalDAV Sync",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Sync with any CalDAV-compatible calendar server. Works with Nextcloud, Radicale, Fastmail, iCloud, and more. Open standard, selfhosted friendly.",
        "featureList": [
            "CalDAV protocol support",
            "Nextcloud compatibility",
            "Radicale support",
            "Fastmail integration",
            "iCloud sync",
            "Selfhosted calendar support"
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
        /* Custom teal gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #14b8a6 0%, #06b6d4 50%, #0ea5e9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .text-gradient {
            background: linear-gradient(135deg, #2dd4bf 0%, #22d3ee 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">CalDAV Protocol</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Open standard<br>
                <span class="text-gradient">CalDAV sync</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Sync with any CalDAV-compatible server. Nextcloud, Radicale, Fastmail, iCloud, and more.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-teal-500/25">
                    Get started free
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Compatible Servers -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Works with your server
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Any CalDAV-compatible server. Your data, your infrastructure.
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Nextcloud -->
                <div class="bento-card bg-gradient-to-br from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30 rounded-2xl p-6 border border-blue-200 dark:border-blue-500/20 text-center hover:border-teal-400/30">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg aria-hidden="true" class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">Nextcloud</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Selfhosted cloud</p>
                </div>

                <!-- Radicale -->
                <div class="bento-card bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/30 dark:to-red-900/30 rounded-2xl p-6 border border-orange-200 dark:border-orange-500/20 text-center hover:border-teal-400/30">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg aria-hidden="true" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">Radicale</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Lightweight server</p>
                </div>

                <!-- Fastmail -->
                <div class="bento-card bg-gradient-to-br from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30 rounded-2xl p-6 border border-blue-200 dark:border-blue-500/20 text-center hover:border-teal-400/30">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg aria-hidden="true" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">Fastmail</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Privacy-focused email</p>
                </div>

                <!-- iCloud -->
                <div class="bento-card bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-900/30 dark:to-slate-900/30 rounded-2xl p-6 border border-gray-300 dark:border-gray-500/20 text-center hover:border-teal-400/30">
                    <div class="w-16 h-16 bg-gradient-to-br from-gray-400 to-gray-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg aria-hidden="true" class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">iCloud</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Apple ecosystem</p>
                </div>
            </div>

            <p class="text-center text-gray-500 dark:text-gray-400 mt-8">
                And any other CalDAV-compatible server
            </p>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Open Standard -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Open Standard
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Universal protocol</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-4">CalDAV is an open internet standard. No vendor lock-in. Works with any compliant server.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-3 border border-gray-200 dark:border-white/10">
                        <code class="text-teal-700 dark:text-teal-300 text-xs font-mono">RFC 4791 - CalDAV</code>
                    </div>
                </div>

                <!-- HTTPS Security -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Secure
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">HTTPS enforced</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-4">All connections require HTTPS. Your credentials and calendar data are encrypted in transit.</p>

                    <div class="flex items-center gap-2 text-emerald-400 text-sm">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Encrypted credentials
                    </div>
                </div>

                <!-- Three Sync Modes -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        Flexible
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Three sync modes</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-4">Push events to CalDAV, pull events from CalDAV, or sync both ways.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                            <svg aria-hidden="true" class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Push only</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                            <svg aria-hidden="true" class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Pull only</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-cyan-500/20 border border-cyan-400/30">
                            <svg aria-hidden="true" class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span class="text-cyan-700 dark:text-cyan-300 text-sm">Both directions</span>
                        </div>
                    </div>
                </div>

                <!-- Auto Discovery (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Auto-Discovery
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Automatic calendar discovery</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Enter your server URL and we'll find all your calendars automatically. No manual configuration needed.</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3 font-mono">PROPFIND Response</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-blue-500/20 border border-blue-400/30">
                                    <div class="w-3 h-3 rounded-full bg-blue-400"></div>
                                    <span class="text-blue-700 dark:text-blue-200 text-sm">Personal Calendar</span>
                                    <svg aria-hidden="true" class="w-4 h-4 text-blue-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                                    <div class="w-3 h-3 rounded-full bg-cyan-400"></div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Work Calendar</span>
                                </div>
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                                    <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Shared Events</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Scheduled
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Syncs every 15 minutes</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-4">Automatic scheduled sync keeps your calendars in harmony. Uses sync tokens for efficient change detection.</p>

                    <div class="flex items-center gap-2">
                        <div class="flex gap-1">
                            <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
                            <div class="w-2 h-2 rounded-full bg-amber-400/60 animate-pulse" style="animation-delay: 0.2s;"></div>
                            <div class="w-2 h-2 rounded-full bg-amber-400/30 animate-pulse" style="animation-delay: 0.4s;"></div>
                        </div>
                        <span class="text-amber-700 dark:text-amber-300 text-sm">Next sync in 12 min</span>
                    </div>
                </div>

                <!-- Selfhosted Friendly -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-cyan-100 dark:from-rose-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                </svg>
                                Selfhosted Friendly
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Your server, your data</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Perfect for selfhosted setups. Connect Event Schedule to your own CalDAV server and keep full control of your data.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">No cloud dependency</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Full data ownership</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Privacy-first</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 animate-float">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4 w-48">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-8 h-8 rounded-lg bg-rose-500/20 flex items-center justify-center">
                                        <svg aria-hidden="true" class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                                        </svg>
                                    </div>
                                    <span class="text-gray-900 dark:text-white text-sm font-medium">Your Server</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="h-2 bg-rose-500/40 rounded w-full"></div>
                                    <div class="h-2 bg-rose-500/30 rounded w-3/4"></div>
                                    <div class="h-2 bg-rose-500/20 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Encrypted Storage -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-100 to-zinc-100 dark:from-slate-800/50 dark:to-zinc-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                        Protected
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Encrypted credentials</h3>
                    <p class="text-gray-600 dark:text-white/80">Your CalDAV username and password are stored encrypted. Even if the database is compromised, credentials remain secure.</p>
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
                    Connect to your CalDAV server in five simple steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-teal-500/25">
                        1
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Enter server URL</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Provide your CalDAV server URL (HTTPS required).
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-teal-500/25">
                        2
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Authenticate</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Enter your username and password securely.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-teal-500/25">
                        3
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Select calendar</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Choose from auto-discovered calendars.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-teal-500/25">
                        4
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Choose direction</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Push, pull, or sync both ways.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-teal-500/25">
                        5
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2">Auto-sync</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Sync runs every 15 minutes automatically.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- What Syncs Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    What gets synced
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Full iCalendar (ICS) format support.
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-teal-200 dark:border-teal-500/20 text-center">
                    <svg aria-hidden="true" class="w-8 h-8 text-teal-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">SUMMARY</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Event name</p>
                </div>

                <div class="bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-teal-200 dark:border-teal-500/20 text-center">
                    <svg aria-hidden="true" class="w-8 h-8 text-teal-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">DESCRIPTION</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Full details</p>
                </div>

                <div class="bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-teal-200 dark:border-teal-500/20 text-center">
                    <svg aria-hidden="true" class="w-8 h-8 text-teal-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">DTSTART / DTEND</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Date and time</p>
                </div>

                <div class="bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-teal-200 dark:border-teal-500/20 text-center">
                    <svg aria-hidden="true" class="w-8 h-8 text-teal-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">LOCATION</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Venue address</p>
                </div>

                <div class="bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-teal-200 dark:border-teal-500/20 text-center">
                    <svg aria-hidden="true" class="w-8 h-8 text-teal-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">URL</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Event link</p>
                </div>

                <div class="bg-gradient-to-br from-teal-50 to-cyan-50 dark:from-teal-900/30 dark:to-cyan-900/30 rounded-2xl p-6 border border-teal-200 dark:border-teal-500/20 text-center">
                    <svg aria-hidden="true" class="w-8 h-8 text-teal-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    <h3 class="text-gray-900 dark:text-white font-semibold mb-1">UID</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Unique identifier</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Explore More Integrations -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <a href="{{ marketing_url('/features/integrations') }}" class="group block">
                <div class="bg-gradient-to-br from-gray-100 dark:from-gray-800/50 to-gray-200 dark:to-gray-900 rounded-3xl border border-gray-200 dark:border-white/10 p-8 hover:border-gray-300 dark:hover:border-white/20 transition-all">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-200 dark:bg-white/15 text-gray-600 dark:text-gray-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendars
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
    <section class="relative bg-gradient-to-br from-teal-600 to-cyan-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Connect your CalDAV server
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Open standards, your infrastructure. Start syncing in minutes.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-teal-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
