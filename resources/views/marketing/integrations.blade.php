<x-marketing-layout>
    <x-slot name="title">Integrations | Google Calendar & Stripe - Event Schedule</x-slot>
    <x-slot name="description">Connect Event Schedule with Google Calendar, CalDAV, Stripe, and Invoice Ninja so your calendar, payments, and invoices stay in sync automatically.</x-slot>
    <x-slot name="breadcrumbTitle">Integrations</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Integrations",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Connect Event Schedule with Google Calendar, CalDAV, Stripe, and Invoice Ninja for a streamlined event management experience.",
        "featureList": [
            "Google Calendar two-way sync",
            "CalDAV calendar support",
            "Stripe payment processing",
            "Invoice Ninja invoicing"
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

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* For integrations "The Wire" styles. The shared es-* motion system lives in
           marketing.css; this holds the integrations glow gradient, the drifting
           connections card, and the flowing-connection motif. */
        .text-gradient-integrations {
            background: linear-gradient(135deg, #2563eb, #0ea5e9, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(37, 99, 235, 0.3);
        }
        .dark .text-gradient-integrations {
            background: linear-gradient(135deg, #60a5fa, #38bdf8, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(96, 165, 250, 0.3);
        }
        @keyframes es-conn-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-conn-float { animation: es-conn-float 6s ease-in-out infinite; }

        /* Connection motif: pulses flow through wires between nodes, like data
           passing between connected services. */
        .es-connect { display: flex; align-items: center; }
        .es-node2 {
            flex: 0 0 auto; width: 9px; height: 9px; border-radius: 9999px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.9), rgba(37, 99, 235, 0.25) 70%);
            box-shadow: 0 0 6px rgba(37, 99, 235, 0.4);
        }
        .es-wire {
            flex: 1 1 auto; min-width: 16px; height: 2px; border-radius: 2px;
            background: rgba(37, 99, 235, 0.18); position: relative; overflow: hidden;
        }
        .es-wire::after {
            content: ""; position: absolute; top: 0; bottom: 0; left: 0; width: 35%; border-radius: 2px;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.95), transparent);
            animation: es-wire-flow var(--wr-dur, 2.2s) linear infinite;
            animation-delay: var(--wr-delay, 0s);
        }
        @keyframes es-wire-flow {
            0% { transform: translateX(-120%); }
            100% { transform: translateX(360%); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-conn-float { animation: none !important; }
            .es-wire::after { animation: none !important; left: 30%; opacity: 0.35; transform: none; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: powerful integrations                              -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.28), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.14), rgba(16, 185, 129, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Flowing connection line along the bottom edge -->
            <div class="es-connect absolute bottom-0 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center px-8 opacity-50 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 16; $i++)
                    @php $dur = 1.8 + ($i % 5) * 0.3; $delay = ($i % 8) * 0.24; @endphp
                    <span class="es-node2"></span>
                    @if ($i < 15)
                        <span class="es-wire" style="--wr-dur: {{ $dur }}s; --wr-delay: {{ $delay }}s;"></span>
                    @endif
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Connect your tools</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Powerful</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-integrations">integrations</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Connect with the tools you already use. Sync calendars and accept payments with ease.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_schedules') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Scheduling guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Calendar integrations                                    -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-2" data-reveal>
                    <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Calendar Integrations</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Keep your events <span class="text-gradient-integrations">in sync</span></h2>
                <p class="mx-auto mb-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.05s;">Sync across all your calendars automatically</p>
                <a href="{{ marketing_url('/features/calendar-sync') }}" data-reveal style="--reveal-delay: 0.1s;" class="inline-flex items-center text-sm font-medium text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                    Learn about all calendar options
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="90">
                <!-- Google Calendar -->
                <a href="{{ marketing_url('/google-calendar') }}" data-reveal class="es-bento group relative block" data-tilt="4" aria-label="Learn more about Google Calendar integration">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex w-fit items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Calendar Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300">Google Calendar</h3>
                        <div class="flex-grow">
                            <p class="mb-6 text-gray-600 dark:text-white/80">Two-way sync with Google Calendar. Import events automatically or push your schedule to Google. Changes sync in real-time.</p>
                            <div class="mb-6 animate-float" aria-hidden="true">
                                <div class="w-full rounded-2xl border border-gray-300 bg-gradient-to-br from-gray-100 to-gray-50 p-4 shadow-xl dark:border-white/20 dark:from-white/10 dark:to-white/5">
                                    <div class="mb-3 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white"><svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg></div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">Google Calendar</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 rounded-full bg-emerald-100 px-2 py-0.5 dark:bg-emerald-500/20">
                                            <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span></span>
                                            <span class="text-[10px] font-medium text-emerald-700 dark:text-emerald-300">Synced</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center rounded-lg border border-blue-200 bg-blue-50 p-2 dark:border-blue-400/30 dark:bg-blue-500/10"><div class="mr-2.5 h-8 w-1 shrink-0 rounded-full bg-blue-500"></div><div class="min-w-0 flex-1"><span class="block text-xs font-medium text-gray-900 dark:text-white">Jazz Night</span><span class="text-[10px] text-gray-500 dark:text-gray-400">Fri, 8:00 PM - 11:00 PM</span></div><svg class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></div>
                                        <div class="flex items-center rounded-lg border border-emerald-200 bg-emerald-50 p-2 dark:border-emerald-400/30 dark:bg-emerald-500/10"><div class="mr-2.5 h-8 w-1 shrink-0 rounded-full bg-emerald-500"></div><div class="min-w-0 flex-1"><span class="block text-xs font-medium text-gray-900 dark:text-white">Open Mic</span><span class="text-[10px] text-gray-500 dark:text-gray-400">Sat, 7:00 PM - 9:30 PM</span></div><svg class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></div>
                                        <div class="flex items-center rounded-lg border border-amber-200 bg-amber-50 p-2 dark:border-amber-400/30 dark:bg-amber-500/10"><div class="mr-2.5 h-8 w-1 shrink-0 rounded-full bg-amber-500"></div><div class="min-w-0 flex-1"><span class="block text-xs font-medium text-gray-900 dark:text-white">Comedy Show</span><span class="text-[10px] text-gray-500 dark:text-gray-400">Sun, 6:00 PM - 8:00 PM</span></div><svg class="es-sync-dot h-3.5 w-3.5 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <div class="mb-4 flex min-h-[40px] flex-wrap gap-3">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Two-way sync</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Auto import</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Real-time updates</span>
                            </div>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 transition-all group-hover:gap-2 dark:text-blue-300">
                                Learn more
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- CalDAV -->
                <a href="{{ marketing_url('/caldav') }}" data-reveal class="es-bento group relative block" data-tilt="4" aria-label="Learn more about CalDAV integration">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex w-fit items-center gap-2 rounded-full border border-teal-200 bg-teal-100 px-3 py-1 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Calendar Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-teal-600 dark:text-white dark:group-hover:text-teal-300">CalDAV</h3>
                        <div class="flex-grow">
                            <p class="mb-6 text-gray-600 dark:text-white/80">Sync with any CalDAV server: Nextcloud, Radicale, Fastmail, and more. Push, pull, or sync both ways to keep events in harmony.</p>
                            <div class="mb-6 animate-float" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-300 bg-gradient-to-br from-gray-100 to-gray-50 p-4 shadow-xl dark:border-white/20 dark:from-white/10 dark:to-white/5">
                                    <div class="mb-3 flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-white/15 dark:bg-white/10">
                                        <svg class="h-3.5 w-3.5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span class="truncate font-mono text-[10px] text-gray-500 dark:text-gray-400">https://cloud.example.com/dav</span>
                                    </div>
                                    <div class="mb-2 text-[10px] font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Calendars found</div>
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-2 rounded-lg border border-teal-200 bg-teal-50 p-1.5 dark:border-teal-400/30 dark:bg-teal-500/15"><div class="h-2.5 w-2.5 shrink-0 rounded-full bg-teal-500"></div><span class="flex-1 text-xs font-medium text-gray-900 dark:text-white">My Events</span><div class="flex items-center gap-0.5 text-teal-600 dark:text-teal-300"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18M17 8l4 4m0 0l-4 4"/></svg><span class="text-[9px] font-medium">Both</span></div></div>
                                        <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-1.5 dark:border-white/10 dark:bg-white/5"><div class="h-2.5 w-2.5 shrink-0 rounded-full bg-blue-400"></div><span class="flex-1 text-xs text-gray-600 dark:text-gray-300">Work</span><div class="flex items-center gap-0.5 text-gray-400 dark:text-gray-500"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg><span class="text-[9px] font-medium">Push</span></div></div>
                                        <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-1.5 dark:border-white/10 dark:bg-white/5"><div class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400"></div><span class="flex-1 text-xs text-gray-600 dark:text-gray-300">Shared</span><div class="flex items-center gap-0.5 text-gray-400 dark:text-gray-500"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg><span class="text-[9px] font-medium">Pull</span></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <div class="mb-4 flex min-h-[40px] flex-wrap gap-2">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Two-way sync</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Selfhosted friendly</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Auto-discovery</span>
                            </div>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-teal-600 transition-all group-hover:gap-2 dark:text-teal-300">
                                Learn more
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Payment integrations                                     -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-2" data-reveal>
                    <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Payment Integrations</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Accept payments and <span class="text-gradient-integrations">manage invoices</span></h2>
                <p class="mx-auto max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.05s;">Get paid directly with secure payment processing</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="90">
                <!-- Stripe -->
                <a href="{{ marketing_url('/stripe') }}" data-reveal class="es-bento group relative block" data-tilt="4" aria-label="Learn more about Stripe payment integration">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex w-fit items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            Payments
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300">Stripe</h3>
                        <div class="flex-grow">
                            <p class="mb-6 text-gray-600 dark:text-white/80">Accept credit cards, Apple Pay, and Google Pay. Payments go directly to your Stripe account with no platform fees.</p>
                            <div class="mb-6 animate-float" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-300 bg-gradient-to-br from-gray-100 to-gray-50 p-4 shadow-xl dark:border-white/20 dark:from-white/10 dark:to-white/5">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-xs font-semibold text-gray-900 dark:text-white">Checkout</span>
                                        <div class="flex items-center gap-1 rounded bg-gray-100 px-2 py-0.5 dark:bg-white/10">
                                            <svg class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            <span class="text-[10px] font-bold" style="color: #635BFF;">stripe</span>
                                        </div>
                                    </div>
                                    <div class="mb-3 rounded-lg border border-gray-200 bg-white p-2.5 dark:border-white/10 dark:bg-white/10">
                                        <div class="mb-1.5 flex items-start justify-between"><div><span class="block text-xs font-medium text-gray-900 dark:text-white">Jazz Night</span><span class="text-[10px] text-gray-400 dark:text-gray-500">2x VIP Ticket</span></div><span class="text-xs font-semibold text-gray-900 dark:text-white">$150.00</span></div>
                                        <div class="flex justify-between border-t border-gray-100 pt-1.5 dark:border-white/10"><span class="text-[10px] text-gray-500 dark:text-gray-400">Total</span><span class="text-xs font-bold text-gray-900 dark:text-white">$150.00</span></div>
                                    </div>
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 py-1.5 dark:border-blue-400/30 dark:bg-blue-500/10"><svg class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg><span class="text-[10px] font-medium text-blue-700 dark:text-blue-300">Card</span></div>
                                        <div class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 py-1.5 dark:border-white/10 dark:bg-white/5"><svg class="h-3.5 w-3.5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg><span class="text-[10px] font-medium text-gray-600 dark:text-gray-400">Apple</span></div>
                                        <div class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 py-1.5 dark:border-white/10 dark:bg-white/5"><svg class="h-3.5 w-3.5 text-gray-600 dark:text-gray-400" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/></svg><span class="text-[10px] font-medium text-gray-600 dark:text-gray-400">Google</span></div>
                                    </div>
                                    <div class="w-full rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 py-2 text-center"><span class="text-xs font-semibold text-white">Pay $150.00</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <div class="mb-4 flex min-h-[40px] flex-wrap gap-3">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Credit cards</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Apple Pay</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Google Pay</span>
                            </div>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 transition-all group-hover:gap-2 dark:text-blue-300">
                                Learn more
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- Invoice Ninja -->
                <a href="{{ marketing_url('/invoiceninja') }}" data-reveal class="es-bento group relative block" data-tilt="4" aria-label="Learn more about Invoice Ninja integration">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Invoicing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-emerald-600 dark:text-white dark:group-hover:text-emerald-300">Invoice Ninja</h3>
                        <div class="flex-grow">
                            <p class="mb-6 text-gray-600 dark:text-white/80">Generate professional invoices for ticket purchases. Perfect for corporate events and B2B sales.</p>
                            <div class="mb-6 rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 flex items-center justify-between"><span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">INVOICE #1042</span><span class="inline-flex items-center rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Paid</span></div>
                                <div class="mb-2 font-semibold text-gray-900 dark:text-white">Corporate Event Package</div>
                                <div class="mb-1.5 flex items-center justify-between"><span class="text-sm text-gray-500 dark:text-gray-400">10 x VIP Ticket</span><span class="text-sm text-gray-700 dark:text-gray-300">$500.00</span></div>
                                <div class="mb-2.5 flex items-center justify-between"><span class="text-sm text-gray-500 dark:text-gray-400">5 x General Admission</span><span class="text-sm text-gray-700 dark:text-gray-300">$250.00</span></div>
                                <div class="flex items-center justify-between border-t border-gray-300 pt-2 dark:border-white/10"><span class="text-sm font-medium text-gray-900 dark:text-white">Total</span><span class="font-bold text-gray-900 dark:text-white">$750.00</span></div>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <div class="mb-4 flex min-h-[40px] flex-wrap gap-3">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Professional invoices</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Corporate events</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Selfhosted option</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Payment links</span>
                            </div>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 transition-all group-hover:gap-2 dark:text-emerald-300">
                                Learn more
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Why integrate? (dark band)                               -->
    <!-- ============================================================ -->
    @php
        $whys = [
            ['Automatic Sync', 'Changes in one place reflect everywhere. No manual updates needed.', 'from-blue-500 to-sky-500', 'text-blue-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />'],
            ['Secure & Private', 'Your data stays yours. We use OAuth and encrypted connections.', 'from-emerald-500 to-teal-500', 'text-emerald-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />'],
            ['Quick Setup', 'Connect in minutes. Most integrations are just a few clicks.', 'from-blue-500 to-sky-500', 'text-sky-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />'],
        ];
    @endphp
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(37, 99, 235, 0.24), rgba(37, 99, 235, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-connect absolute bottom-0 left-0 right-0 mx-auto flex h-16 max-w-4xl items-center px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 16; $i++)
                        @php $dur = 1.8 + ($i % 5) * 0.3; $delay = ($i % 8) * 0.24; @endphp
                        <span class="es-node2"></span>
                        @if ($i < 15)
                            <span class="es-wire" style="--wr-dur: {{ $dur }}s; --wr-delay: {{ $delay }}s;"></span>
                        @endif
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2" data-reveal>
                        <svg aria-hidden="true" class="h-4 w-4 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        <span class="text-sm font-medium text-gray-300">Seamless workflow</span>
                    </div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Why <span class="text-gradient-integrations">integrate?</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Keep your workflow smooth with tools that work together.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="80">
                    @foreach ($whys as [$title, $desc, $grad, $iconText, $icon])
                        <div data-reveal class="rounded-2xl border border-white/10 bg-white/[0.04] p-8 transition-all hover:-translate-y-1 hover:bg-white/[0.07]">
                            <div class="mb-6 h-1 w-12 rounded-full bg-gradient-to-r {{ $grad }}"></div>
                            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} shadow-lg">
                                <svg aria-hidden="true" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $icon !!}</svg>
                            </div>
                            <h3 class="mb-3 text-xl font-semibold text-white">{{ $title }}</h3>
                            <p class="text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-connect absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-3xl items-center px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 13; $i++)
                            @php $dur = 1.8 + ($i % 5) * 0.3; $delay = ($i % 8) * 0.24; @endphp
                            <span class="es-node2"></span>
                            @if ($i < 12)
                                <span class="es-wire" style="--wr-dur: {{ $dur }}s; --wr-delay: {{ $delay }}s;"></span>
                            @endif
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Connect your tools <span class="text-gradient-integrations">today</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Get started for free and integrate with your favorite services.
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

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
