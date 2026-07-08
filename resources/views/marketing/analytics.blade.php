<x-marketing-layout>
    <x-slot name="title">Privacy-First Event Analytics - Event Schedule</x-slot>
    <x-slot name="description">Track page views, device breakdown, traffic sources, and conversion rates. Privacy-first analytics with no external services required.</x-slot>
    <x-slot name="breadcrumbTitle">Analytics</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Analytics",
        "description": "Track page views, device breakdown, traffic sources, and conversion rates. Privacy-first analytics with no external services required.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Analytics"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Analytics",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Analytics Software",
        "operatingSystem": "Web",
        "description": "Privacy-first event analytics. Track page views, device breakdown, traffic sources, and conversion rates with no external services required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "Page view tracking",
            "Device breakdown",
            "Traffic source analysis",
            "Conversion tracking",
            "Privacy-first approach",
            "No external services"
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
        /* For analytics "The Dashboard" styles. The shared es-* motion system lives in
           marketing.css; this holds the emerald glow gradient, the drifting live-stats
           card, and the rising bar-chart motif. */
        .text-gradient-analytics {
            background: linear-gradient(135deg, #059669, #10b981, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(5, 150, 105, 0.3);
        }
        .dark .text-gradient-analytics {
            background: linear-gradient(135deg, #34d399, #10b981, #6ee7b7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(52, 211, 153, 0.3);
        }
        @keyframes es-an-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-an-float { animation: es-an-float 6s ease-in-out infinite; }

        /* Bar-chart motif: columns rise and fall in a wave, like a live stats feed. */
        .es-chart { display: flex; align-items: flex-end; }
        .es-col {
            flex: 0 0 auto; width: 8px; border-radius: 2px 2px 0 0;
            background: linear-gradient(to top, rgba(16, 185, 129, 0.35), rgba(52, 211, 153, 0.85));
            animation: es-col-grow var(--co-dur, 2.4s) ease-in-out infinite alternate;
            animation-delay: var(--co-delay, 0s);
        }
        @keyframes es-col-grow {
            0% { height: 22%; opacity: 0.4; }
            100% { height: 100%; opacity: 1; box-shadow: 0 0 8px rgba(16, 185, 129, 0.4); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-an-float, .es-col { animation: none !important; }
            .es-col { height: 60%; opacity: 0.6; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: event analytics                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(5, 150, 105, 0.3), rgba(5, 150, 105, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(13, 148, 136, 0.28), rgba(13, 148, 136, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(52, 211, 153, 0.14), rgba(52, 211, 153, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Rising bar chart along the bottom edge -->
            <div class="es-chart absolute bottom-0 left-0 right-0 mx-auto hidden h-20 max-w-4xl items-end justify-center gap-2 px-8 pb-2 opacity-40 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 48; $i++)
                    @php $dur = 2 + ($i % 6) * 0.24; $delay = ($i % 16) * 0.12; @endphp
                    <span class="es-col" style="--co-dur: {{ $dur }}s; --co-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Built-in Analytics</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Event</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-analytics">analytics</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Track page views, device breakdown, traffic sources, and conversion rates. No external services required.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.analytics') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Analytics guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Analytics at a glance (bento)                            -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Analytics at a <span class="text-gradient-analytics">glance</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="100">

                <!-- Views over time (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                                    Views Over Time
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Track your growth</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">See daily, weekly, or monthly view counts. Choose from 7 time ranges: last 7/30/90 days, this month, last month, this year, or all time.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Last 7 days</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Last 30 days</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">This year</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="w-64 rounded-2xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                        <div class="flex h-32 items-end justify-between gap-2">
                                            @foreach ([40, 55, 45, 70, 60, 85, 100] as $ci => $ch)
                                                <div class="es-ai-field w-6 rounded-t bg-emerald-500/70" style="height: {{ $ch }}%; --i: {{ $ci }};"></div>
                                            @endforeach
                                        </div>
                                        <div class="mt-2 flex justify-between text-xs text-gray-400"><span>Mon</span><span>Wed</span><span>Fri</span><span>Sun</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Device breakdown -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            Device Breakdown
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Know your devices</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">See how visitors access your schedule: desktop, mobile, or tablet. Bot traffic is automatically filtered.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3" style="--i: 0;"><div class="h-3 w-3 rounded-full bg-cyan-400"></div><span class="flex-1 text-sm text-gray-600 dark:text-white/80">Mobile</span><span class="font-medium text-gray-900 dark:text-white">62%</span></div>
                            <div class="es-ai-field flex items-center gap-3" style="--i: 1;"><div class="h-3 w-3 rounded-full bg-blue-400"></div><span class="flex-1 text-sm text-gray-600 dark:text-white/80">Desktop</span><span class="font-medium text-gray-900 dark:text-white">35%</span></div>
                            <div class="es-ai-field flex items-center gap-3" style="--i: 2;"><div class="h-3 w-3 rounded-full bg-sky-400"></div><span class="flex-1 text-sm text-gray-600 dark:text-white/80">Tablet</span><span class="font-medium text-gray-900 dark:text-white">3%</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Traffic sources -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            Traffic Sources
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Where they come from</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Track direct visits, search engines, social media, email campaigns, and referrer domains.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            @foreach ([['Direct', 45, 'bg-blue-400'], ['Social', 30, 'bg-blue-400'], ['Search', 15, 'bg-sky-400'], ['Email', 10, 'bg-cyan-400']] as $ti => [$src, $pct, $bc])
                                <div class="es-ai-field flex items-center gap-2" style="--i: {{ $ti }};">
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full {{ $bc }}" style="width: {{ $pct }}%"></div></div>
                                    <span class="w-16 text-xs text-gray-500 dark:text-gray-400">{{ $src }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Conversion tracking (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Conversion Tracking
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Measure what matters</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Track revenue per event, conversion rates, and revenue per view. See which events perform best.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4" aria-hidden="true">
                                @foreach ([['4.2%', 'Conversion Rate'], ['$2.40', 'Revenue/View'], ['$1,248', 'Total Revenue'], ['52', 'Tickets Sold']] as $mi => [$val, $lbl])
                                    <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-4 text-center dark:border-white/10 dark:bg-[#0f0f14]" style="--i: {{ $mi }};">
                                        <div class="mb-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $val }}</div>
                                        <div class="text-sm text-amber-500 dark:text-amber-400">{{ $lbl }}</div>
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
    <!-- 3. More insights (dark band)                                -->
    <!-- ============================================================ -->
    @php
        $insights = [
            ['Top Events', 'See which events get the most views and drive the most ticket sales.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />'],
            ['Multi-Schedule', 'Filter analytics by schedule or view combined data across all your schedules.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />'],
            ['Privacy First', 'No external tracking services. All data stored locally. No cookies required.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />'],
        ];
    @endphp
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(5, 150, 105, 0.24), rgba(5, 150, 105, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(13, 148, 136, 0.2), rgba(13, 148, 136, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-chart absolute bottom-0 left-0 right-0 mx-auto flex h-16 max-w-4xl items-end justify-center gap-2 px-8 pb-2 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 44; $i++)
                        @php $dur = 2 + ($i % 6) * 0.24; $delay = ($i % 16) * 0.12; @endphp
                        <span class="es-col" style="--co-dur: {{ $dur }}s; --co-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>More <span class="text-gradient-analytics">insights</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to understand your audience.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="80">
                    @foreach ($insights as [$title, $desc, $icon])
                        <div data-reveal class="rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-center transition-all hover:-translate-y-1 hover:bg-white/[0.07]">
                            <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10">
                                <svg aria-hidden="true" class="h-8 w-8 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $icon !!}</svg>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Accurate data only (bot filtering)                       -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>Accurate data <span class="text-gradient-analytics">only</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">We filter out bot traffic so you see real visitors.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="80">
                <div data-reveal class="ap-card rounded-2xl border border-gray-200 bg-white p-6 text-center dark:border-white/10 dark:bg-white/5">
                    <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-red-500/20">
                        <svg aria-hidden="true" class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Bot Detection</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">60+ bot patterns filtered automatically. Googlebot, Bingbot, crawlers, and scrapers are excluded from your stats.</p>
                </div>
                <div data-reveal class="rounded-2xl border border-emerald-400/30 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 p-6 text-center">
                    <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500/20">
                        <svg aria-hidden="true" class="h-8 w-8 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Real Visitors</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Only human visitors count. Get accurate data you can trust for making decisions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Next feature                                             -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="80">

                <a href="{{ marketing_url('/features/recurring-events') }}" data-reveal class="group block">
                    <div class="flex h-full flex-col rounded-3xl border border-lime-200 bg-gradient-to-br from-lime-100 to-green-100 p-8 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-lime-900 dark:to-green-900 lg:p-10">
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-lime-600 dark:text-white dark:group-hover:text-lime-300">Recurring Events</h3>
                        <p class="mb-4 text-lg text-gray-500 dark:text-white/80">Set events to repeat weekly on chosen days with flexible end conditions and per-occurrence tickets.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-lime-500 transition-all group-hover:gap-3 dark:text-lime-400">
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
                        @foreach ([['/for-venues', 'Venues'], ['/for-curators', 'Curators'], ['/for-bars', 'Bars']] as [$href, $label])
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
    <!-- 6. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-gray-50 px-2 py-16 dark:bg-[#0f0f14] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-emerald-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(5, 150, 105, 0.3), rgba(5, 150, 105, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-chart absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-3xl items-end justify-center gap-2 px-8 pb-2 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 38; $i++)
                            @php $dur = 2 + ($i % 6) * 0.24; $delay = ($i % 16) * 0.12; @endphp
                            <span class="es-col" style="--co-dur: {{ $dur }}s; --co-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Understand your <span class="text-gradient-analytics">audience</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Start tracking your event analytics today. No setup required.
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

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
