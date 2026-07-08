<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.calendar_sync_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.calendar_sync_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Calendar Sync</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Calendar Sync",
        "description": "Two-way sync with Google Calendar and CalDAV. Real-time webhook updates. Let attendees add events to Apple, Google, or Outlook calendars instantly.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Calendar Synchronization"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How do I connect Google Calendar?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Go to your schedule settings and click 'Connect Google Calendar.' Sign in with your Google account and select which calendar to sync. Events start syncing immediately."
                }
            },
            {
                "@type": "Question",
                "name": "How often does the calendar sync?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Google Calendar sync is near real-time. Changes made in either direction are synced within minutes through webhook notifications."
                }
            },
            {
                "@type": "Question",
                "name": "Is the sync two-way?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Events created or edited in Google Calendar automatically appear on your Event Schedule, and events added to Event Schedule sync back to Google Calendar."
                }
            },
            {
                "@type": "Question",
                "name": "Can attendees add events to their personal calendars?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Every event has 'Add to Calendar' buttons for Google Calendar, Apple Calendar, and Outlook. Attendees can add individual events with one click."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Calendar Sync",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Calendar Synchronization Software",
        "operatingSystem": "Web",
        "description": "Two-way sync with Google Calendar and CalDAV. Real-time webhook updates. Let attendees add events to Apple, Google, or Outlook calendars instantly.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "Google Calendar two-way sync",
            "CalDAV server support",
            "Real-time webhook updates",
            "Add-to-calendar buttons",
            "Apple Calendar support",
            "Outlook integration"
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
        /* For calendar-sync "The Loop" styles. The shared es-* motion system lives in
           marketing.css; this holds the sync glow gradient, the drifting synced-
           calendars card, and the two-way sync motif. */
        .text-gradient-cal {
            background: linear-gradient(135deg, #2563eb, #0ea5e9, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(37, 99, 235, 0.3);
        }
        .dark .text-gradient-cal {
            background: linear-gradient(135deg, #60a5fa, #38bdf8, #2dd4bf);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(96, 165, 250, 0.3);
        }
        @keyframes es-cal-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-cal-float { animation: es-cal-float 6s ease-in-out infinite; }

        /* Two-way sync motif: a bright band sweeps out and back, like data flowing
           in both directions between two calendars. */
        .es-twoway { display: flex; align-items: center; }
        .es-tw-dot {
            flex: 0 0 auto;
            width: 7px; height: 7px; border-radius: 9999px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.7), rgba(20, 184, 166, 0.7));
            animation: es-tw var(--tw-dur, 2.8s) ease-in-out infinite alternate;
            animation-delay: var(--tw-delay, 0s);
        }
        @keyframes es-tw {
            0% { opacity: 0.2; transform: scale(0.7); }
            100% { opacity: 1; transform: scale(1.25); box-shadow: 0 0 8px rgba(37, 99, 235, 0.5); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-cal-float, .es-tw-dot { animation: none !important; }
            .es-tw-dot { opacity: 0.5; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: two-way calendar sync                               -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(6, 182, 212, 0.28), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(20, 184, 166, 0.14), rgba(20, 184, 166, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Two-way sync line along the bottom edge -->
            <div class="es-twoway absolute bottom-0 left-0 right-0 hidden h-20 items-center justify-center gap-3 px-8 pb-6 opacity-50 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 34; $i++)
                    @php $dur = 2 + ($i % 6) * 0.22; $delay = ($i % 17) * 0.14; @endphp
                    <span class="es-tw-dot" style="--tw-dur: {{ $dur }}s; --tw-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Calendar Integration</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Two-way</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-cal">calendar sync</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Sync with Google Calendar or any CalDAV server. Let attendees add events to their favorite calendar app.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-google-calendar" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Calendar Sync guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Choose your integration                                   -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Choose your <span class="text-gradient-cal">integration</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Two powerful options for syncing your events.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="90">
                <!-- Google Calendar -->
                <a href="{{ marketing_url('/google-calendar') }}" data-reveal class="es-bento group relative block" data-tilt="4">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm dark:bg-gray-800">
                                <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-300">Google Calendar</h3>
                                <p class="text-sm text-gray-500 dark:text-white/60">Real-time sync with webhooks</p>
                            </div>
                        </div>
                        <p class="mb-6 text-gray-600 dark:text-white/80">Connect with OAuth for instant, real-time synchronization. Changes in Google Calendar appear immediately via webhook notifications.</p>
                        <div class="mb-6 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">OAuth 2.0</span>
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Webhooks</span>
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Instant sync</span>
                        </div>
                        <div class="mt-auto flex items-center gap-2 font-medium text-blue-500 transition-all group-hover:gap-3 dark:text-blue-400">
                            Learn more
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- CalDAV -->
                <a href="{{ marketing_url('/caldav') }}" data-reveal class="es-bento group relative block" data-tilt="4">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-500">
                                <svg aria-hidden="true" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 transition-colors group-hover:text-teal-600 dark:text-white dark:group-hover:text-teal-300">CalDAV</h3>
                                <p class="text-sm text-gray-500 dark:text-white/60">Open standard, any server</p>
                            </div>
                        </div>
                        <p class="mb-6 text-gray-600 dark:text-white/80">Sync with any CalDAV-compatible server--Nextcloud, Radicale, Fastmail, iCloud, and more. Perfect for selfhosted setups.</p>
                        <div class="mb-6 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1 text-sm text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">Open standard</span>
                            <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1 text-sm text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">Selfhosted</span>
                            <span class="inline-flex items-center rounded-full bg-teal-100 px-3 py-1 text-sm text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">Any server</span>
                        </div>
                        <div class="mt-auto flex items-center gap-2 font-medium text-teal-500 transition-all group-hover:gap-3 dark:text-teal-400">
                            Learn more
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Powerful sync features (bento)                            -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Powerful <span class="text-gradient-cal">sync features</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Available with both Google Calendar and CalDAV.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="100">

                <!-- True two-way sync (full width) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    Bidirectional
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">True two-way sync</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Push events to your calendar, pull events from your calendar, or sync both ways. Choose the direction that works for your workflow.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Push only</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Pull only</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Both directions</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="flex items-center gap-4">
                                        <div class="w-32 rounded-2xl border border-blue-400/30 bg-gradient-to-br from-blue-500/20 to-sky-500/20 p-4">
                                            <div class="mb-2 text-center text-xs text-blue-700 dark:text-blue-300">Event Schedule</div>
                                            <div class="space-y-2"><div class="h-2 rounded bg-gray-300 dark:bg-white/20"></div><div class="h-2 w-3/4 rounded bg-gray-300 dark:bg-white/20"></div><div class="h-2 w-1/2 rounded bg-gray-300 dark:bg-white/20"></div></div>
                                        </div>
                                        <div class="flex flex-col items-center gap-1">
                                            <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                            <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-teal-400" style="--i: 1;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                                        </div>
                                        <div class="w-32 rounded-2xl border border-gray-300 bg-gray-100 p-4 dark:border-white/20 dark:bg-white/10">
                                            <div class="mb-2 text-center text-xs text-gray-600 dark:text-gray-300">Your Calendar</div>
                                            <div class="space-y-2"><div class="h-2 rounded bg-blue-400/40"></div><div class="h-2 w-3/4 rounded bg-teal-400/40"></div><div class="h-2 w-1/2 rounded bg-blue-400/40"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- One-click add -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            For Attendees
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One-click add</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Visitors can add any event to their personal calendar with a single click. No account required.</p>
                        <div class="mt-auto flex flex-wrap justify-center gap-2" aria-hidden="true">
                            <span class="flex items-center gap-2 rounded-xl bg-gray-200 px-4 py-2 text-sm font-medium text-gray-900 dark:bg-white/10 dark:text-white">
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm6.369 17.308c-.281.281-.663.439-1.061.439H6.692c-.398 0-.78-.158-1.061-.439-.281-.281-.439-.663-.439-1.061V7.753c0-.398.158-.78.439-1.061.281-.281.663-.439 1.061-.439h10.616c.398 0 .78.158 1.061.439.281.281.439.663.439 1.061v8.494c0 .398-.158.78-.439 1.061z"/></svg>
                                Google
                            </span>
                            <span class="flex items-center gap-2 rounded-xl bg-gray-200 px-4 py-2 text-sm font-medium text-gray-900 dark:bg-white/10 dark:text-white">
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                                Apple
                            </span>
                            <span class="flex items-center gap-2 rounded-xl bg-gray-200 px-4 py-2 text-sm font-medium text-gray-900 dark:bg-white/10 dark:text-white">
                                <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7.88 12.04q0 .45-.11.87-.1.41-.33.74-.22.33-.58.52-.37.2-.87.2t-.85-.2q-.35-.21-.57-.55-.22-.33-.33-.75-.1-.42-.1-.86t.1-.87q.1-.43.34-.76.22-.34.59-.54.36-.2.87-.2t.86.2q.35.21.57.55.22.34.31.77.1.43.1.88zM24 12v9.38q0 .46-.33.8-.33.32-.8.32H7.13q-.46 0-.8-.33-.32-.33-.32-.8V18H1q-.41 0-.7-.3-.3-.29-.3-.7V7q0-.41.3-.7Q.58 6 1 6h6.63V2.55q0-.44.3-.75.3-.3.75-.3h14.7q.44 0 .75.3.3.3.3.75V12zm-6.5 0q0-.12-.1-.28-.1-.16-.1-.35v-.7q0-.18.1-.37.1-.18.1-.26 0-.15-.08-.27-.08-.12-.27-.12h-4.76l-.06.34q-.07.31-.13.72-.07.42-.08.88v.8q0 .18.08.18h.61q.5 0 .88-.07.38-.08.58-.4.09-.17.09-.3v-.07q.24.02.43.02.68 0 1.18-.23.5-.23.84-.62.33-.4.5-.92.18-.53.18-1.13zm-3.28-4.03q.14 0 .29-.1.15-.1.29-.1h2.32q.14 0 .28.1.14.1.28.1.27 0 .45-.18.18-.19.18-.46v-4.7q0-.27-.18-.45-.18-.19-.45-.19h-7.26q-.27 0-.45.19-.19.18-.19.45v4.7q0 .27.19.46.18.18.45.18h.91q.14 0 .28-.1.14-.1.29-.1z"/></svg>
                                Outlook
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Choose your calendar -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            Flexible
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Choose your calendar</h3>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Select which calendar to sync with for each schedule. Different schedules can use different calendars.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="flex items-center gap-2 rounded-lg border border-cyan-400/30 bg-cyan-500/20 p-2">
                                <div class="h-2 w-2 rounded-full bg-cyan-400"></div>
                                <span class="text-sm text-cyan-700 dark:text-cyan-200">Work Events</span>
                                <svg aria-hidden="true" class="ml-auto h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-100 p-2 dark:border-white/10 dark:bg-white/5">
                                <div class="h-2 w-2 rounded-full bg-blue-400"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Personal</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Set it and forget it (full width) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 md:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    Automatic
                                </div>
                                <h3 class="mb-3 text-2xl font-black tracking-tight text-gray-900 dark:text-white lg:text-3xl">Set it and forget it</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Events sync automatically when created, updated, or deleted. No manual work required.</p>
                            </div>
                            <div class="shrink-0" aria-hidden="true">
                                <div class="space-y-2 rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    @foreach (['Event created', 'Event updated', 'Event deleted'] as $si => $ev)
                                        <div class="es-ai-field flex items-center gap-3" style="--i: {{ $si }};">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20"><svg aria-hidden="true" class="h-3 w-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                            <div class="text-sm text-emerald-700 dark:text-emerald-300">{{ $ev }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">synced</div>
                                        </div>
                                    @endforeach
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
    <!-- 4. How sync works (dark band)                               -->
    <!-- ============================================================ -->
    @php
        $steps = [
            ['1', 'Connect your calendar', 'Choose Google Calendar (OAuth) or any CalDAV server (username/password).'],
            ['2', 'Choose direction', 'Push events out, pull events in, or sync both ways. You decide.'],
            ['3', 'Select calendar', 'Pick which calendar to use. Different schedules can use different calendars.'],
            ['4', 'Auto-sync', 'Events sync automatically when created, updated, or deleted. No manual work.'],
        ];
    @endphp
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(37, 99, 235, 0.24), rgba(37, 99, 235, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(20, 184, 166, 0.2), rgba(20, 184, 166, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-twoway absolute bottom-0 left-0 right-0 flex h-16 items-center justify-center gap-3 px-8 pb-3 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 32; $i++)
                        @php $dur = 2 + ($i % 6) * 0.22; $delay = ($i % 17) * 0.14; @endphp
                        <span class="es-tw-dot" style="--tw-dur: {{ $dur }}s; --tw-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>How sync <span class="text-gradient-cal">works</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Connect once, stay in sync forever.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="80">
                    @foreach ($steps as [$num, $title, $desc])
                        <div data-reveal class="rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-center transition-all hover:-translate-y-1 hover:bg-white/[0.07]">
                            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 text-2xl font-bold text-white shadow-lg shadow-blue-500/25">{{ $num }}</div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Which should I choose? (comparison)                      -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>Which should I <span class="text-gradient-cal">choose?</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Both options offer two-way sync. Here's how they differ.</p>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-white/10" data-reveal>
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="px-4 py-4 text-left font-medium text-gray-500 dark:text-gray-400">Feature</th>
                            <th class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 font-semibold text-blue-700 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                    Google Calendar
                                </div>
                            </th>
                            <th class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 font-semibold text-teal-700 dark:text-teal-300">
                                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    CalDAV
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">Authentication</td>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">OAuth 2.0 (one-click)</td>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Username/password</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">Sync speed</td>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Real-time (webhooks)</td>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Every 15 minutes</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">Server options</td>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Google only</td>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Any CalDAV server</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">Selfhosted friendly</td>
                            <td class="px-4 py-4 text-center"><span class="text-gray-500 dark:text-gray-400">Requires Google API setup</span></td>
                            <td class="px-4 py-4 text-center"><svg aria-hidden="true" class="mx-auto h-5 w-5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-white/5">
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">Two-way sync</td>
                            <td class="px-4 py-4 text-center"><svg aria-hidden="true" class="mx-auto h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></td>
                            <td class="px-4 py-4 text-center"><svg aria-hidden="true" class="mx-auto h-5 w-5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">Best for</td>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Google users who want instant sync</td>
                            <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">Selfhosted or privacy-focused users</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Next feature                                             -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="80">

                <a href="{{ route('marketing.analytics') }}" data-reveal class="group block">
                    <div class="flex h-full flex-col rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-100 to-teal-100 p-8 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-emerald-900 dark:to-teal-900 lg:p-10">
                        <div class="flex flex-1 flex-col items-center gap-8 lg:flex-row">
                            <div class="flex flex-1 flex-col text-center lg:text-left">
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-emerald-600 dark:text-white dark:group-hover:text-emerald-300 lg:text-3xl">Built-in Analytics</h3>
                                <p class="mb-4 text-lg text-gray-500 dark:text-white/80">Track page views, device breakdown, and traffic sources. Privacy-first with no external services.</p>
                                <span class="mt-auto inline-flex items-center gap-2 font-medium text-emerald-500 transition-all group-hover:gap-3 dark:text-emerald-400">
                                    Learn more
                                    <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </span>
                            </div>
                            <div class="shrink-0" aria-hidden="true">
                                <div class="w-48 rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 text-[10px] text-gray-500 dark:text-gray-400">Views this week</div>
                                    <div class="flex h-20 items-end justify-between gap-1.5">
                                        @foreach ([40, 55, 45, 70, 60, 85, 100] as $bh)
                                            <div class="w-4 rounded-t bg-emerald-500/70" style="height: {{ $bh }}%"></div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 flex justify-between text-[9px] text-gray-600 dark:text-gray-400"><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <div data-reveal class="ap-card flex h-full flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        @foreach ([['/for-musicians', 'Musicians'], ['/for-venues', 'Venues'], ['/for-theaters', 'Theaters']] as [$href, $label])
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
    <!-- 7. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-cal">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about calendar sync.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['How do I connect Google Calendar?', 'Go to your schedule settings and click "Connect Google Calendar." Sign in with your Google account and select which calendar to sync. Events start syncing immediately.'],
                    ['How often does the calendar sync?', 'Google Calendar sync is near real-time. Changes made in either direction are synced within minutes through webhook notifications.'],
                    ['Is the sync two-way?', 'Yes. Events created or edited in Google Calendar automatically appear on your Event Schedule, and events added to Event Schedule sync back to Google Calendar.'],
                    ['Can attendees add events to their personal calendars?', 'Yes. Every event has "Add to Calendar" buttons for Google Calendar, Apple Calendar, and Outlook. Attendees can add individual events with one click.'],
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
    <!-- 8. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set events to repeat on any schedule automatically" :url="marketing_url('/features/recurring-events')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="AI Features" description="Import, generate, and create with AI-powered tools" :url="marketing_url('/features/ai')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Embed your event schedule on any website" :url="marketing_url('/features/embed-calendar')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
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
                    <div class="es-twoway absolute bottom-0 left-0 right-0 flex h-14 items-center justify-center gap-3 px-8 pb-3 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 28; $i++)
                            @php $dur = 2 + ($i % 6) * 0.22; $delay = ($i % 17) * 0.14; @endphp
                            <span class="es-tw-dot" style="--tw-dur: {{ $dur }}s; --tw-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Keep your calendars <span class="text-gradient-cal">in sync</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Connect Google Calendar or any CalDAV server. Your events stay synchronized automatically.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
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
