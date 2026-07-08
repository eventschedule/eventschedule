<x-marketing-layout>
    <x-slot name="title">CalDAV Calendar Sync - Event Schedule</x-slot>
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
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Which CalDAV servers are supported?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any CalDAV-compatible server works with Event Schedule, including Nextcloud, Radicale, Fastmail, iCloud, and any other server that implements the CalDAV protocol (RFC 4791)."
                }
            },
            {
                "@type": "Question",
                "name": "Does CalDAV sync work with selfhosted Event Schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, CalDAV is ideal for selfhosted setups since both Event Schedule and your calendar server can run on your own infrastructure, keeping all data under your control."
                }
            },
            {
                "@type": "Question",
                "name": "How often does CalDAV sync run?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "CalDAV syncs automatically every 15 minutes via scheduled tasks. The sync uses change tokens for efficient detection, only transferring events that have actually changed."
                }
            },
            {
                "@type": "Question",
                "name": "Is my CalDAV password stored securely?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, all CalDAV credentials are stored encrypted in the database. Even if the database is compromised, your username and password remain secure."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (teal to cyan to sky) */
        .text-gradient-caldav {
            background: linear-gradient(135deg, #14b8a6 0%, #06b6d4 50%, #0ea5e9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-caldav {
            background: linear-gradient(135deg, #2dd4bf 0%, #22d3ee 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* On always-dark surfaces, keep the accent bright regardless of page mode */
        .es-finale-panel .text-gradient-caldav {
            background: linear-gradient(135deg, #2dd4bf 0%, #22d3ee 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live CalDAV connection card */
        .es-caldav-float { animation: es-caldav-bob 5.5s ease-in-out infinite; }
        @keyframes es-caldav-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a row of calendar glyphs pulsing (the CalDAV standard) */
        .es-cal-icon {
            flex: 0 0 auto;
            color: rgba(20, 184, 166, 0.8);
            animation: es-cal-pulse var(--ca-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--ca-delay, 0s);
        }
        @keyframes es-cal-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(20, 184, 166, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-caldav-float, .es-cal-icon, .animate-pulse-slow, .animate-float, .animate-pulse { animation: none !important; }
            .es-cal-icon { opacity: 0.55; transform: none; }
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
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(20, 184, 166, 0.3), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(6, 182, 212, 0.26), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.14), rgba(14, 165, 233, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Calendar-standard motif along the bottom edge -->
            <div class="es-cals absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 12; $i++)
                    @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-cal-icon" style="--ca-dur: {{ $dur }}s; --ca-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">CalDAV Protocol</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Open standard</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-caldav">CalDAV sync</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Sync with any CalDAV-compatible server. Nextcloud, Radicale, Fastmail, iCloud, and more.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-teal-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
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
    <!-- 2. Compatible servers                                       -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Works with <span class="text-gradient-caldav">your server</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Any CalDAV-compatible server. Your data, your infrastructure.</p>
            </div>

            <div class="grid grid-cols-2 gap-6 md:grid-cols-4" data-reveal-group="80">
                <!-- Nextcloud -->
                <div data-reveal class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-sky-50 p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-teal-400/30 dark:border-blue-500/20 dark:from-blue-900/30 dark:to-sky-900/30">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600">
                        <svg aria-hidden="true" class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                        </svg>
                    </div>
                    <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">Nextcloud</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Selfhosted cloud</p>
                </div>

                <!-- Radicale -->
                <div data-reveal class="rounded-2xl border border-orange-200 bg-gradient-to-br from-orange-50 to-red-50 p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-teal-400/30 dark:border-orange-500/20 dark:from-orange-900/30 dark:to-red-900/30">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-red-500">
                        <svg aria-hidden="true" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">Radicale</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Lightweight server</p>
                </div>

                <!-- Fastmail -->
                <div data-reveal class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-sky-50 p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-teal-400/30 dark:border-blue-500/20 dark:from-blue-900/30 dark:to-sky-900/30">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500">
                        <svg aria-hidden="true" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">Fastmail</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Privacy-focused email</p>
                </div>

                <!-- iCloud -->
                <div data-reveal class="rounded-2xl border border-gray-300 bg-gradient-to-br from-gray-50 to-slate-50 p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-teal-400/30 dark:border-gray-500/20 dark:from-gray-900/30 dark:to-slate-900/30">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-gray-400 to-gray-600">
                        <svg aria-hidden="true" class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                        </svg>
                    </div>
                    <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">iCloud</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Apple ecosystem</p>
                </div>
            </div>

            <p class="mt-8 text-center text-gray-500 dark:text-gray-400" data-reveal>And any other CalDAV-compatible server</p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Bento grid                                               -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-24 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Open Standard -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Open Standard
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Universal protocol</h3>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">CalDAV is an open internet standard. No vendor lock-in. Works with any compliant server.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]">
                            <code class="font-mono text-xs text-teal-700 dark:text-teal-300">RFC 4791 - CalDAV</code>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- HTTPS Security -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Secure
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">HTTPS enforced</h3>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">All connections require HTTPS. Your credentials and calendar data are encrypted in transit.</p>
                        <div class="mt-auto flex items-center gap-2 text-sm text-emerald-500 dark:text-emerald-400">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Encrypted credentials
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Three Sync Modes -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Flexible
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Three sync modes</h3>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Push events to CalDAV, pull events from CalDAV, or sync both ways.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                                <svg aria-hidden="true" class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Push only</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                                <svg aria-hidden="true" class="h-4 w-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Pull only</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg border border-cyan-400/30 bg-cyan-500/20 p-2">
                                <svg aria-hidden="true" class="h-4 w-4 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span class="text-sm text-cyan-700 dark:text-cyan-300">Both directions</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Auto Discovery (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Auto-Discovery
                                </div>
                                <h3 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">Automatic calendar discovery</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Enter your server URL and we'll find all your calendars automatically. No manual configuration needed.</p>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 font-mono text-xs text-gray-500 dark:text-gray-400">PROPFIND Response</div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 rounded-lg border border-blue-400/30 bg-blue-500/20 p-2">
                                        <div class="h-3 w-3 rounded-full bg-blue-400"></div>
                                        <span class="text-sm text-blue-700 dark:text-blue-200">Personal Calendar</span>
                                        <svg aria-hidden="true" class="ml-auto h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                                        <div class="h-3 w-3 rounded-full bg-cyan-400"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Work Calendar</span>
                                    </div>
                                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                                        <div class="h-3 w-3 rounded-full bg-emerald-400"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Shared Events</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Scheduled Sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Scheduled
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Syncs every 15 minutes</h3>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Automatic scheduled sync keeps your calendars in harmony. Uses sync tokens for efficient change detection.</p>
                        <div class="mt-auto flex items-center gap-2" aria-hidden="true">
                            <div class="flex gap-1">
                                <div class="h-2 w-2 animate-pulse rounded-full bg-amber-400"></div>
                                <div class="h-2 w-2 animate-pulse rounded-full bg-amber-400/60" style="animation-delay: 0.2s;"></div>
                                <div class="h-2 w-2 animate-pulse rounded-full bg-amber-400/30" style="animation-delay: 0.4s;"></div>
                            </div>
                            <span class="text-sm text-amber-600 dark:text-amber-300">Next sync in 12 min</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Selfhosted Friendly (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                    </svg>
                                    Selfhosted Friendly
                                </div>
                                <h3 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">Your server, your data</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Perfect for selfhosted setups. Connect Event Schedule to your own CalDAV server and keep full control of your data.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No cloud dependency</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Full data ownership</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Privacy-first</span>
                                </div>
                            </div>
                            <div class="shrink-0" aria-hidden="true">
                                <div class="w-48 rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-500/20">
                                            <svg aria-hidden="true" class="h-4 w-4 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">Your Server</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="h-2 w-full rounded bg-teal-500/40"></div>
                                        <div class="h-2 w-3/4 rounded bg-teal-500/30"></div>
                                        <div class="h-2 w-1/2 rounded bg-teal-500/20"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Encrypted Storage -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 dark:border-slate-700/40 dark:bg-slate-500/20 dark:text-slate-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            </svg>
                            Protected
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Encrypted credentials</h3>
                        <p class="text-gray-500 dark:text-gray-400">Your CalDAV username and password are stored encrypted. Even if the database is compromised, credentials remain secure.</p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. How it works                                             -->
    <!-- ============================================================ -->
    <section id="how-it-works" class="scroll-mt-24 bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>How it <span class="text-gradient-caldav">works</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Connect to your CalDAV server in five simple steps.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-5" data-reveal-group="80">
                @php
                    $steps = [
                        ['Enter server URL', 'Provide your CalDAV server URL (HTTPS required).'],
                        ['Authenticate', 'Enter your username and password securely.'],
                        ['Select calendar', 'Choose from auto-discovered calendars.'],
                        ['Choose direction', 'Push, pull, or sync both ways.'],
                        ['Auto-sync', 'Sync runs every 15 minutes automatically.'],
                    ];
                @endphp
                @foreach ($steps as $si => $step)
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-500 text-xl font-bold text-white shadow-lg shadow-teal-500/25">
                            {{ $si + 1 }}
                        </div>
                        <h3 class="mb-2 text-base font-semibold text-gray-900 dark:text-white">{{ $step[0] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $step[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. What gets synced                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>What gets <span class="text-gradient-caldav">synced</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Full iCalendar (ICS) format support.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3" data-reveal-group="70">
                @php
                    $syncs = [
                        ['SUMMARY', 'Event name', 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z'],
                        ['DESCRIPTION', 'Full details', 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                        ['DTSTART / DTEND', 'Date and time', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['LOCATION', 'Venue address', 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['URL', 'Event link', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                        ['UID', 'Unique identifier', 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2'],
                    ];
                @endphp
                @foreach ($syncs as $sync)
                    <div data-reveal class="rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 to-cyan-50 p-6 text-center transition-all duration-300 hover:-translate-y-1 dark:border-teal-500/20 dark:from-teal-900/30 dark:to-cyan-900/30">
                        <svg aria-hidden="true" class="mx-auto mb-3 h-8 w-8 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @foreach (explode(' M', $sync[2]) as $pi => $seg)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pi === 0 ? $seg : 'M'.$seg }}" />
                            @endforeach
                        </svg>
                        <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">{{ $sync[0] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $sync[1] }}</p>
                    </div>
                @endforeach
            </div>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendars
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
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-medium text-teal-700 transition-colors hover:bg-teal-100 dark:border-teal-500/20 dark:bg-teal-500/10 dark:text-teal-300 dark:hover:bg-teal-500/20">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Calendar Sync Features
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
    <!-- 8. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-black/30">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-caldav">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common questions about CalDAV sync.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['Which CalDAV servers are supported?', 'Any CalDAV-compatible server works with Event Schedule, including Nextcloud, Radicale, Fastmail, iCloud, and any other server that implements the CalDAV protocol (RFC 4791).'],
                        ['Does CalDAV sync work with selfhosted Event Schedule?', 'Yes, CalDAV is ideal for selfhosted setups since both Event Schedule and your calendar server can run on your own infrastructure, keeping all data under your control.'],
                        ['How often does CalDAV sync run?', 'CalDAV syncs automatically every 15 minutes via scheduled tasks. The sync uses change tokens for efficient detection, only transferring events that have actually changed.'],
                        ['Is my CalDAV password stored securely?', 'Yes, all CalDAV credentials are stored encrypted in the database. Even if the database is compromised, your username and password remain secure.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-teal-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(20, 184, 166, 0.3), rgba(20, 184, 166, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-cals absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-6 px-8 opacity-45" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 9; $i++)
                            @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                            <span class="es-cal-icon" style="--ca-dur: {{ $dur }}s; --ca-delay: {{ $delay }}s;">
                                <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Connect your <span class="text-gradient-caldav">CalDAV server</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Open standards, your infrastructure. Start syncing in minutes.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-teal-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
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
