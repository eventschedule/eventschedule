<x-marketing-layout>
    <x-slot name="title">Event Categories & Sub-Schedules | Multi-Room Venues - Event Schedule</x-slot>
    <x-slot name="description">Organize events into categories with unlimited sub-schedules. Perfect for venues with multiple rooms, stages, or event series.</x-slot>
    <x-slot name="breadcrumbTitle">Sub-Schedules</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Sub-Schedules",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Organize events into categories with unlimited sub-schedules. Perfect for venues with multiple rooms, stages, or event series.",
        "featureList": [
            "Unlimited sub-schedules",
            "Event categorization",
            "Multiple room support",
            "Stage management",
            "Event series organization",
            "Color-coded categories"
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
                "name": "What are sub-schedules used for?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Sub-schedules let you organize events into categories within your schedule. For example, a venue might have sub-schedules for 'Live Music,' 'Comedy Nights,' and 'Open Mic.' Visitors can filter by sub-schedule to find what interests them."
                }
            },
            {
                "@type": "Question",
                "name": "Can I nest sub-schedules?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Sub-schedules work as a single level of categorization within your schedule. Each event can belong to one or more sub-schedules, making it easy to organize without overcomplicating things."
                }
            },
            {
                "@type": "Question",
                "name": "How does filtering work for visitors?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Visitors see filter buttons at the top of your schedule, one for each sub-schedule. They can click to show only events in that category, making it easy to find what they are looking for."
                }
            }
        ]
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
        /* For sub-schedules "The Sort" styles. The shared es-* motion system lives in
           marketing.css; this holds the sky/blue glow gradient, the drifting category
           card, and the color-coded lane motif (events sorting into category lanes). */
        .text-gradient-subs {
            background: linear-gradient(135deg, #0284c7, #0ea5e9, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(14, 165, 233, 0.3);
        }
        .dark .text-gradient-subs {
            background: linear-gradient(135deg, #38bdf8, #22d3ee, #67e8f9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(56, 189, 248, 0.3);
        }
        @keyframes es-subs-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-subs-float { animation: es-subs-float 6s ease-in-out infinite; }

        /* Color-coded lane motif: vertical bars in distinct category colors rise and
           fall in a wave, like parallel rooms/stages filling their own schedules. */
        .es-lanes { display: flex; align-items: flex-end; }
        .es-lane {
            flex: 0 0 auto; border-radius: 9999px; transform-origin: bottom;
            animation: es-lane-rise var(--ln-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--ln-delay, 0s);
        }
        @keyframes es-lane-rise {
            0%, 100% { transform: scaleY(0.4); opacity: 0.35; }
            50% { transform: scaleY(1); opacity: 1; }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-subs-float, .es-lane, .animate-pulse-slow { animation: none !important; }
            .es-lane { opacity: 0.6; transform: scaleY(0.7); }
        }
    </style>

    @php
        // Brand-safe category palette (no rose/pink) for the lane motif + color-coded dots.
        $laneColors = [
            'rgba(56, 189, 248, 0.85)',  // sky-400
            'rgba(34, 211, 238, 0.85)',  // cyan-400
            'rgba(96, 165, 250, 0.85)',  // blue-400
            'rgba(45, 212, 191, 0.85)',  // teal-400
            'rgba(52, 211, 153, 0.85)',  // emerald-400
            'rgba(251, 191, 36, 0.85)',  // amber-400
        ];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: sub-schedules                                       -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(6, 182, 212, 0.28), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.14), rgba(59, 130, 246, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Color-coded category lanes along the bottom edge -->
            <div class="es-lanes absolute bottom-0 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-end justify-center gap-2.5 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 30; $i++)
                    @php $h = [44, 30, 52, 36, 24, 40][$i % 6]; $dur = 2.2 + ($i % 6) * 0.26; $delay = ($i % 13) * 0.16; $c = $laneColors[$i % 6]; @endphp
                    <span class="es-lane" style="width: 6px; height: {{ $h }}px; background: {{ $c }}; --ln-dur: {{ $dur }}s; --ln-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Sub-schedules</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Organize your</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-subs">events</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Create sub-schedules to categorize events by room, stage, series, or any way you like. Visitors can filter to find exactly what they're looking for.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
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
    <!-- 2. Bento features                                           -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>One schedule, <span class="text-gradient-subs">many categories</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">

                <!-- Unlimited sub-schedules (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Unlimited
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Create as many as you need</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Add unlimited sub-schedules to organize your events. Perfect for venues with multiple rooms, stages, or different event series running simultaneously.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Main Stage</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Lounge</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Rooftop</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">VIP Room</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-64" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">Sub-schedules</div>
                                    <div class="space-y-2">
                                        <div class="es-ai-field flex items-center gap-2 rounded-lg border border-sky-500/30 bg-sky-500/20 p-2" style="--i: 0;">
                                            <span class="h-2 w-2 rounded-full bg-sky-400"></span>
                                            <span class="text-sm text-gray-900 dark:text-white">Main Stage</span>
                                        </div>
                                        <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/5" style="--i: 1;">
                                            <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">Acoustic Room</span>
                                        </div>
                                        <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/5" style="--i: 2;">
                                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">Outdoor Patio</span>
                                        </div>
                                        <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/5" style="--i: 3;">
                                            <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">Jazz Lounge</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Easy filtering -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Filtering
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Easy filtering for visitors</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">When you have multiple sub-schedules, visitors see a dropdown filter on your calendar. They can quickly find events in the room or series they care about.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="flex items-center justify-between rounded-lg bg-white p-2 dark:bg-white/5">
                                <span class="text-sm text-gray-600 dark:text-gray-300">Filter by schedule</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Shareable URLs -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            Direct Links
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Shareable URLs</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Each sub-schedule gets its own URL. Share links directly to specific rooms or event series. Visitors land on exactly what they want to see.</p>
                        <div class="mt-auto space-y-2 rounded-xl border border-gray-200 bg-gray-100 p-3 font-mono dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-1 text-xs" style="--i: 0;">
                                <span class="text-gray-400 dark:text-gray-500">venue.eventschedule.com/</span>
                                <span class="text-sky-600 dark:text-sky-300">main-stage</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-1 text-xs" style="--i: 1;">
                                <span class="text-gray-400 dark:text-gray-500">venue.eventschedule.com/</span>
                                <span class="text-sky-600 dark:text-sky-300">jazz-lounge</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-1 text-xs" style="--i: 2;">
                                <span class="text-gray-400 dark:text-gray-500">venue.eventschedule.com/</span>
                                <span class="text-sky-600 dark:text-sky-300">comedy-nights</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Automate with the API (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                    API Support
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Automate with the API</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">Create events programmatically and assign them to sub-schedules using the API. Pass the <code class="rounded bg-sky-500/15 px-1 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">schedule</code> parameter when creating events.</p>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gray-100 p-4 font-mono text-sm dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-2 text-gray-400 dark:text-gray-500">// Create event via API</div>
                                <div class="text-sky-600 dark:text-sky-300">POST /api/events</div>
                                <div class="mt-2 text-gray-500 dark:text-gray-400">{</div>
                                <div class="pl-4 text-gray-500 dark:text-gray-400">"name": "Jazz Night",</div>
                                <div class="pl-4 text-gray-500 dark:text-gray-400">"schedule": "<span class="text-sky-600 dark:text-sky-300">jazz-lounge</span>",</div>
                                <div class="pl-4 text-gray-500 dark:text-gray-400">...</div>
                                <div class="text-gray-500 dark:text-gray-400">}</div>
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
    <!-- 3. Perfect for (dark band)                                  -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(14, 165, 233, 0.24), rgba(14, 165, 233, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(6, 182, 212, 0.2), rgba(6, 182, 212, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-lanes absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-4xl items-end justify-center gap-2.5 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 28; $i++)
                        @php $h = [40, 28, 48, 34, 22, 38][$i % 6]; $dur = 2.2 + ($i % 6) * 0.26; $delay = ($i % 13) * 0.16; $c = $laneColors[$i % 6]; @endphp
                        <span class="es-lane" style="width: 6px; height: {{ $h }}px; background: {{ $c }}; --ln-dur: {{ $dur }}s; --ln-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Perfect <span class="text-gradient-subs">for</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Sub-schedules work great for any venue or organizer with multiple event categories.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @php
                        $subUseCases = [
                            ['Concert Venues', 'Organize by main stage, side stage, VIP area, and outdoor spaces.', 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
                            ['Conference Centers', 'Separate tracks for different topics, workshops, and keynotes.', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                            ['Theaters', 'Main theater, black box, rehearsal space, and special screenings.', 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
                            ['Event Series', 'Group recurring events like weekly trivia, monthly open mics, or seasonal markets.', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                            ['Multi-Purpose Venues', 'Bars, restaurants, and community centers with different event types.', 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                            ['Festival Stages', 'Multiple stages, food areas, workshop tents, and activity zones.', 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
                        ];
                    @endphp
                    @foreach ($subUseCases as $uc)
                        <div data-reveal class="flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-sky-500/30 hover:bg-white/[0.06]">
                            <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                                <svg aria-hidden="true" class="h-8 w-8 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $uc[2] }}" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-white">{{ $uc[0] }}</h3>
                            <p class="text-sm text-gray-300">{{ $uc[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Next feature                                             -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute left-1/4 top-10 h-[300px] w-[300px] rounded-full bg-orange-600/20 blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 h-[200px] w-[200px] rounded-full bg-amber-600/20 blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="90">

                <a href="{{ marketing_url('/features/fan-videos') }}" data-reveal class="group block">
                    <div class="flex h-full flex-col rounded-3xl border border-orange-200 bg-gradient-to-br from-orange-100 to-amber-100 p-8 transition-all duration-300 hover:scale-[1.02] dark:border-white/10 dark:from-orange-900 dark:to-amber-900 lg:p-10">
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-orange-600 dark:text-white dark:group-hover:text-orange-300">Fan Videos &amp; Comments</h3>
                        <p class="mb-4 text-lg text-gray-600 dark:text-white/80">Fans add YouTube videos and comments to your events. All submissions need your approval.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-orange-500 transition-all group-hover:gap-3 dark:text-orange-400">
                            Learn more
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Popular with -->
                <div data-reveal class="flex h-full flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Venues</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-curators') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Curators</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-farmers-markets') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Farmers Markets</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-subs">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about sub-schedules.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What are sub-schedules used for?', 'Sub-schedules let you organize events into categories within your schedule. For example, a venue might have sub-schedules for "Live Music," "Comedy Nights," and "Open Mic." Visitors can filter by sub-schedule to find what interests them.'],
                    ['Can I nest sub-schedules?', 'Sub-schedules work as a single level of categorization within your schedule. Each event can belong to one or more sub-schedules, making it easy to organize without overcomplicating things.'],
                    ['How does filtering work for visitors?', 'Visitors see filter buttons at the top of your schedule, one for each sub-schedule. They can click to show only events in that category, making it easy to find what they are looking for.'],
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
    <!-- 6. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Team Scheduling"
                        description="Invite team members to manage your schedule together"
                        :url="marketing_url('/features/team-scheduling')"
                        icon-color="amber"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom Fields"
                        description="Collect additional info from attendees with custom form fields"
                        :url="marketing_url('/features/custom-fields')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Embed your event schedule on any website"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-sky-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-lanes absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-3xl items-end justify-center gap-2.5 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 24; $i++)
                            @php $h = [40, 28, 48, 34, 22, 38][$i % 6]; $dur = 2.2 + ($i % 6) * 0.26; $delay = ($i % 13) * 0.16; $c = $laneColors[$i % 6]; @endphp
                            <span class="es-lane" style="width: 6px; height: {{ $h }}px; background: {{ $c }}; --ln-dur: {{ $dur }}s; --ln-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Organize your <span class="text-gradient-subs">events</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Start using sub-schedules to categorize your events. Free on all plans.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30"
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

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
