<x-marketing-layout>
    <x-slot name="title">Recurring Events | Flexible Repeat Scheduling - Event Schedule</x-slot>
    <x-slot name="description">Set events to repeat daily, weekly, biweekly, monthly, or yearly with flexible end conditions, per-occurrence tickets, and automatic Google Calendar sync.</x-slot>
    <x-slot name="breadcrumbTitle">Recurring Events</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Recurring Events",
        "description": "Set events to repeat daily, weekly, biweekly, monthly, or yearly with flexible end conditions, per-occurrence tickets, and automatic Google Calendar sync.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Recurring Event Scheduling"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What recurrence patterns are available?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You can set events to repeat daily, weekly on specific days, every N weeks, monthly on the same date, monthly on the same weekday, or yearly. Each pattern supports flexible end conditions."
                }
            },
            {
                "@type": "Question",
                "name": "Can I edit a single occurrence without changing the whole series?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Each occurrence of a recurring event is an independent event with its own details, tickets, and attendance tracking. You can edit any single occurrence without affecting the rest of the series."
                }
            },
            {
                "@type": "Question",
                "name": "Can I add or skip specific dates in a recurring series?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use Include Dates to add extra dates outside your recurring pattern, and Exclude Dates to skip dates that match the pattern. Exclude dates take priority if a date appears in both lists."
                }
            },
            {
                "@type": "Question",
                "name": "How do recurring events sync with Google Calendar?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Recurring events sync to Google Calendar as individual occurrences. Each date appears separately in your Google Calendar, and changes sync both ways automatically."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Recurring Events",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Scheduling Software",
        "operatingSystem": "Web",
        "description": "Set events to repeat daily, weekly, biweekly, monthly, or yearly with flexible end conditions, per-occurrence tickets, and automatic Google Calendar sync.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "Daily recurrence",
            "Weekly day-of-week recurrence",
            "Every N weeks recurrence",
            "Monthly same-date recurrence",
            "Monthly same-weekday recurrence",
            "Yearly recurrence",
            "Three end conditions",
            "Include and exclude specific dates",
            "Per-occurrence tickets",
            "Google Calendar sync",
            "Individual ticket sales"
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
        /* For recurring-events "The Loop" styles. The shared es-* motion system lives in
           marketing.css; this holds the emerald glow gradient, the drifting recurrence
           card, and the calendar-cell rhythm motif (recurring dates lighting up in turn). */
        .text-gradient-recur {
            background: linear-gradient(135deg, #059669, #22c55e, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(5, 150, 105, 0.3);
        }
        .dark .text-gradient-recur {
            background: linear-gradient(135deg, #34d399, #4ade80, #6ee7b7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(52, 211, 153, 0.3);
        }
        @keyframes es-recur-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-recur-float { animation: es-recur-float 6s ease-in-out infinite; }

        /* Calendar-cell rhythm motif: cells brighten in a repeating traveling wave,
           like recurring dates lighting up on a schedule. */
        .es-cycle { display: flex; align-items: center; }
        .es-cell {
            flex: 0 0 auto; width: 14px; height: 14px; border-radius: 4px;
            background: rgba(16, 185, 129, 0.14);
            animation: es-cell-pulse var(--cy-dur, 3s) ease-in-out infinite;
            animation-delay: var(--cy-delay, 0s);
        }
        @keyframes es-cell-pulse {
            0%, 100% { background: rgba(16, 185, 129, 0.12); box-shadow: none; }
            50% { background: rgba(16, 185, 129, 0.85); box-shadow: 0 0 10px rgba(16, 185, 129, 0.5); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-recur-float, .es-cell, .animate-pulse-slow { animation: none !important; }
            .es-cell { background: rgba(16, 185, 129, 0.35); }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: recurring events                                    -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(5, 150, 105, 0.3), rgba(5, 150, 105, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(34, 197, 94, 0.28), rgba(34, 197, 94, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(20, 184, 166, 0.14), rgba(20, 184, 166, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Recurring calendar-cell rhythm along the bottom edge -->
            <div class="es-cycle absolute bottom-0 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-3 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 30; $i++)
                    @php $dur = 2.6 + ($i % 4) * 0.3; $delay = ($i % 15) * 0.2; @endphp
                    <span class="es-cell" style="--cy-dur: {{ $dur }}s; --cy-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Recurring Events</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Set it and</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-recur">forget it</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Schedule events to repeat daily, weekly, monthly, or yearly. Set end conditions and let Google Calendar sync handle the rest.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-green-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                    Start for free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_events') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Events guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento features                                           -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Schedule once, <span class="text-gradient-recur">repeat forever</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Frequency options (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Flexible Recurrence
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Choose your frequency</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Pick from six recurrence options to match any schedule. Daily, weekly, biweekly, monthly, or yearly.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Daily</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Weekly</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Monthly</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Yearly</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-64" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">Frequency</div>
                                    <div class="mb-4 space-y-1.5">
                                        @foreach (['Daily' => 0, 'Weekly' => 1, 'Every N Weeks' => 0, 'Monthly (same date)' => 0, 'Monthly (same day)' => 0, 'Yearly' => 0] as $opt => $active)
                                            <div class="rounded-lg px-3 py-1.5 text-xs {{ $active ? 'bg-emerald-500 font-semibold text-white shadow-lg shadow-emerald-500/25' : 'bg-gray-200 text-gray-500 dark:bg-white/10 dark:text-gray-400' }}">{{ $opt }}</div>
                                        @endforeach
                                    </div>
                                    <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Repeat on</div>
                                    <div class="flex gap-1.5">
                                        @foreach ([['S', 0], ['M', 1], ['T', 0], ['W', 1], ['T', 0], ['F', 1], ['S', 0]] as [$dl, $on])
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full text-[10px] font-semibold {{ $on ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/25' : 'bg-gray-200 text-gray-500 dark:bg-white/10 dark:text-gray-400' }}">{{ $dl }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- End conditions -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            End Conditions
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Control the run</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Choose when your recurring series stops.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-100 p-3 dark:border-emerald-500/30 dark:bg-emerald-500/20" style="--i: 0;">
                                <div class="h-4 w-4 rounded-full border-4 border-emerald-500"></div>
                                <span class="text-sm text-gray-900 dark:text-white">Never (ongoing)</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 1;">
                                <div class="h-4 w-4 rounded-full border-2 border-gray-400 dark:border-gray-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">On a specific date</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 2;">
                                <div class="h-4 w-4 rounded-full border-2 border-gray-400 dark:border-gray-500"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">After N occurrences</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Per-event tickets -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Per-Event Tickets
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sell per event</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Each occurrence has its own ticket count and sales.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3" style="--i: 0;">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Mon, Feb 3</span>
                                    <span class="text-xs text-emerald-600 dark:text-emerald-300">12 sold</span>
                                </div>
                            </div>
                            <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 1;">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Wed, Feb 5</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">8 sold</span>
                                </div>
                            </div>
                            <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 2;">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Fri, Feb 7</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">5 sold</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Calendar preview (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Calendar View
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">See the series</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">All recurring dates appear on your schedule automatically, regardless of frequency. Guests see each occurrence individually with its own ticket availability.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 text-xs font-medium text-gray-500 dark:text-gray-400">February 2026</div>
                                <div class="grid grid-cols-7 gap-1 text-center text-[10px]">
                                    @foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $dh)
                                        <div class="py-1 text-gray-400 dark:text-gray-500">{{ $dh }}</div>
                                    @endforeach
                                    @php $recurDays = [2, 4, 6, 9, 11, 13, 16, 18, 20, 23, 25, 27]; @endphp
                                    @for ($d = 1; $d <= 28; $d++)
                                        @if (in_array($d, $recurDays))
                                            <div class="rounded bg-emerald-500 py-1 font-semibold text-white">{{ $d }}</div>
                                        @else
                                            <div class="py-1 text-gray-600 dark:text-gray-300">{{ $d }}</div>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Google Calendar sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Auto Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Syncs automatically</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Recurring events sync to Google Calendar as individual occurrences. Each date appears separately.</p>
                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="flex-1 rounded-xl border border-emerald-200 bg-emerald-100 p-3 dark:border-emerald-400/30 dark:bg-emerald-500/20">
                                <div class="mb-1.5 text-center text-xs text-emerald-600 dark:text-emerald-300">Event Schedule</div>
                                <div class="space-y-1">
                                    <div class="h-1.5 rounded bg-emerald-400/40"></div>
                                    <div class="h-1.5 w-3/4 rounded bg-emerald-400/40"></div>
                                </div>
                            </div>
                            <div class="flex flex-col items-center gap-0.5">
                                <svg aria-hidden="true" class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                <svg aria-hidden="true" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            </div>
                            <div class="flex-1 rounded-xl border border-gray-300 bg-gray-100 p-3 dark:border-white/20 dark:bg-white/10">
                                <div class="mb-1.5 text-center text-xs text-gray-600 dark:text-gray-300">Google Calendar</div>
                                <div class="space-y-1">
                                    <div class="h-1.5 rounded bg-blue-400/40"></div>
                                    <div class="h-1.5 w-3/4 rounded bg-green-400/40"></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Date exceptions -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Date Exceptions
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Add or skip dates</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Include extra dates outside your pattern, or exclude dates that match it.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div>
                                <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Include Dates</div>
                                <div class="flex items-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-2.5">
                                    <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <span class="text-sm text-gray-900 dark:text-white">Thu, Dec 25 (bonus session)</span>
                                </div>
                            </div>
                            <div>
                                <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Exclude Dates</div>
                                <div class="flex items-center gap-2 rounded-xl border border-red-500/20 bg-red-500/10 p-2.5">
                                    <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                    <span class="text-sm text-gray-900 dark:text-white">Tue, Dec 31 (holiday)</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Use cases -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Use Cases
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Built for regulars</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Perfect for events that repeat on any schedule.</p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            @foreach (['Yoga classes', 'Trivia nights', 'Daily standups', 'Board meetings', 'Open mic', 'Annual galas'] as $tag)
                                <span class="inline-flex items-center rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-sm text-emerald-700 dark:text-emerald-300">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Keep exploring (dark band)                               -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(5, 150, 105, 0.24), rgba(5, 150, 105, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-cycle absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-3 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 28; $i++)
                        @php $dur = 2.6 + ($i % 4) * 0.3; $delay = ($i % 15) * 0.2; @endphp
                        <span class="es-cell" style="--cy-dur: {{ $dur }}s; --cy-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Keep <span class="text-gradient-recur">exploring</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Learn the ropes and see what pairs well with recurring events.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                    <!-- Read the guide -->
                    <a href="{{ route('marketing.docs.creating_events') }}" data-reveal class="group flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-8 transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/30 hover:bg-white/[0.06]">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-white transition-colors group-hover:text-emerald-300">Read the guide</h3>
                        <p class="mb-4 text-lg text-gray-300">Learn how to get the most out of recurring events.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-emerald-400 transition-all group-hover:gap-3">
                            Read guide
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>

                    <!-- Next feature: Custom Fields -->
                    <a href="{{ marketing_url('/features/custom-fields') }}" data-reveal class="group flex flex-col rounded-2xl border border-amber-500/20 bg-gradient-to-br from-amber-500/10 to-yellow-500/10 p-8 transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/40">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-white transition-colors group-hover:text-amber-300">Custom Fields</h3>
                        <p class="mb-4 text-lg text-gray-300">Collect additional info from ticket buyers with text, dropdown, date, and yes/no fields.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-amber-400 transition-all group-hover:gap-3">
                            Learn more
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>

                    <!-- Popular with -->
                    <div data-reveal class="flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-8">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="mb-4 text-xl font-bold text-white">Popular with</h3>
                        <div class="space-y-3">
                            <a href="{{ marketing_url('/for-bars') }}" class="group/link flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition-all hover:border-emerald-500/30 hover:bg-white/10">
                                <span class="font-medium text-white">Bars</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-emerald-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="{{ marketing_url('/for-fitness-and-yoga') }}" class="group/link flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition-all hover:border-emerald-500/30 hover:bg-white/10">
                                <span class="font-medium text-white">Fitness &amp; Yoga</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-emerald-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="{{ marketing_url('/for-libraries') }}" class="group/link flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition-all hover:border-emerald-500/30 hover:bg-white/10">
                                <span class="font-medium text-white">Libraries</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-emerald-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-recur">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about recurring events.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What recurrence patterns are available?', 'You can set events to repeat daily, weekly on specific days, every N weeks, monthly on the same date, monthly on the same weekday, or yearly. Each pattern supports flexible end conditions.'],
                    ['Can I edit a single occurrence without changing the whole series?', 'Yes. Each occurrence of a recurring event is an independent event with its own details, tickets, and attendance tracking. You can edit any single occurrence without affecting the rest of the series.'],
                    ['Can I add or skip specific dates in a recurring series?', 'Yes. Use "Include Dates" to add extra dates outside your recurring pattern (like a bonus session), and "Exclude Dates" to skip dates that match the pattern (like holidays). Exclude dates take priority if a date appears in both lists.'],
                    ['How do recurring events sync with Google Calendar?', 'Recurring events sync to Google Calendar as individual occurrences. Each date appears separately in your Google Calendar, and changes sync both ways automatically.'],
                ] as [$q, $a])
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
    <!-- 5. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Calendar Sync"
                        description="Two-way sync with Google Calendar"
                        :url="marketing_url('/features/calendar-sync')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Online Events"
                        description="Host virtual events with Zoom, Google Meet, and more"
                        :url="marketing_url('/features/online-events')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Sell tickets with QR check-in and zero platform fees"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="emerald"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-emerald-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(5, 150, 105, 0.3), rgba(5, 150, 105, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-cycle absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-3 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 24; $i++)
                            @php $dur = 2.6 + ($i % 4) * 0.3; $delay = ($i % 15) * 0.2; @endphp
                            <span class="es-cell" style="--cy-dur: {{ $dur }}s; --cy-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Automate your <span class="text-gradient-recur">schedule</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Set up recurring events today. Free to use, no credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-green-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
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
