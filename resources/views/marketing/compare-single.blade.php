<x-marketing-layout>
    <x-slot name="title">{{ $name }} Alternative | Event Schedule</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
    <x-slot name="keywords">{{ $keywords }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ $name }} Alternative</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule",
        "description": "Open-source event management platform for sharing events, selling tickets, and bringing communities together. Zero platform fees.",
        "url": "{{ config('app.url') }}",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "offers": [
            {
                "@type": "Offer",
                "name": "Free",
                "price": "0",
                "priceCurrency": "USD",
                "description": "Unlimited events, team collaboration, Google Calendar sync, newsletters, and fan engagement features.",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Pro",
                "price": "5.00",
                "priceCurrency": "USD",
                "description": "Everything in Free plus ticketing with QR check-ins and live dashboard, ticket waitlist, sale notifications, sales CSV export, Stripe payments, remove branding, event graphics, REST API, and webhooks.",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Enterprise",
                "price": "15.00",
                "priceCurrency": "USD",
                "description": "Everything in Pro plus AI style generation, AI content generation, AI flyer generation, WhatsApp event creation, custom CSS, white-label branding, and priority support.",
                "availability": "https://schema.org/InStock"
            }
        ],
        "featureList": [
            "Zero platform fees on ticket sales",
            "AI-powered event import",
            "AI flyer generation",
            "AI style generation",
            "Two-way Google Calendar sync",
            "CalDAV sync",
            "iCal download",
            "Newsletter builder with A/B testing",
            "QR code ticketing and check-in",
            "Check-in dashboard",
            "Ticket waitlist",
            "Promo and discount codes",
            "Sale notification emails",
            "Sales CSV export",
            "Open source with selfhosting option",
            "Embeddable calendar and ticket widgets",
            "WhatsApp event creation",
            "Custom CSS styling",
            "Fan videos and comments",
            "Team collaboration"
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            @foreach ($faq as $index => $item)
            {
                "@type": "Question",
                "name": "{{ str_replace('"', '\\"', $item['question']) }}",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "{{ str_replace('"', '\\"', $item['answer']) }}"
                }
            }@if (!$loop->last),@endif

            @endforeach
        ]
    }
    </script>
    @php
        $howToSteps = $switch_steps ?? [
            ['title' => 'Create your schedule', 'description' => 'Sign up free and create your first schedule in under a minute.'],
            ['title' => 'Add your events', 'description' => 'Paste event details for AI import or create events manually.'],
            ['title' => 'Share and sell', 'description' => 'Share your schedule URL and start selling tickets.'],
        ];
    @endphp
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to switch from {{ str_replace('"', '\\"', $name) }} to Event Schedule",
        "step": [
            @foreach ($howToSteps as $index => $step)
            {
                "@type": "HowToStep",
                "position": {{ $index + 1 }},
                "name": "{{ str_replace('"', '\\"', $step['title']) }}",
                "text": "{{ str_replace('"', '\\"', $step['description']) }}"
            }@if (!$loop->last),@endif

            @endforeach
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (blue to sky to cyan) */
        .text-gradient-compare {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-compare {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-compare {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Comparison table: horizontal scroll + sticky first column */
        .compare-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .compare-table-wrapper::-webkit-scrollbar {
            height: 6px;
        }
        .compare-table-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        .compare-table-wrapper::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
        .dark .compare-table-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
        }
        @media (max-width: 768px) {
            .compare-table th:first-child,
            .compare-table td:first-child {
                position: sticky;
                left: 0;
                z-index: 10;
            }
            .compare-table th:first-child {
                z-index: 20;
            }
        }

        /* Signature motif: a row of comparison verdict marks (check vs cross) */
        .es-verdict {
            flex: 0 0 auto;
            animation: es-verdict-pulse var(--vd-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--vd-delay, 0s);
        }
        @keyframes es-verdict-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-verdict, .animate-pulse-slow { animation: none !important; }
            .es-verdict { opacity: 0.55; transform: none; }
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
    <section class="es-hero relative flex min-h-[calc(72svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Verdict motif along the bottom edge (check vs cross) -->
            <div class="es-verdicts absolute bottom-6 left-0 right-0 mx-auto hidden h-14 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 14; $i++)
                    @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.28; $win = $i % 3 !== 2; $sz = [20, 26, 18, 24][$i % 4]; @endphp
                    <span class="es-verdict {{ $win ? 'text-emerald-500' : 'text-gray-400 dark:text-gray-600' }}" style="--vd-dur: {{ $dur }}s; --vd-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            @if ($win)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            @endif
                        </svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-6">
                <a href="{{ route('marketing.compare') }}" class="text-sm text-gray-500 transition-colors hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                    &larr; Compare all platforms
                </a>
            </div>

            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Platform comparison</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Event Schedule</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-compare">vs {{ $name }}</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                {{ $tagline }}
            </p>
        </div>
    </section>

    @if (!empty($auto_import))
    <!-- ============================================================ -->
    <!-- 2. Auto-Import                                              -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <span class="mb-6 inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import from {{ $name }}
                    </span>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">
                        {{ $auto_import['title'] }}
                    </h2>
                    <p class="mb-8 text-lg text-gray-600 dark:text-gray-400">
                        {{ $auto_import['description'] }}
                    </p>
                    <ul class="space-y-3">
                        @foreach ($auto_import['bullets'] as $bullet)
                        <li class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                            <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $bullet }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="space-y-4" data-reveal-group="90">
                    @foreach ($auto_import['steps'] as $index => $step)
                    <div data-reveal class="relative flex items-start gap-5 rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 text-sm font-bold text-white shadow-lg">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $step['title'] }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $step['description'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- ============================================================ -->
    <!-- 3. Comparison Table                                         -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Feature-by-feature <span class="text-gradient-compare">comparison</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">How Event Schedule stacks up against {{ $name }}.</p>
            </div>

            <div data-reveal class="compare-table-wrapper rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">
                <table class="compare-table w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="min-w-[180px] bg-white px-6 py-5 text-sm font-semibold text-gray-900 dark:bg-[#0f0f14] dark:text-white">Feature</th>
                            <th class="min-w-[180px] bg-blue-50/50 px-6 py-5 text-sm font-semibold text-blue-600 dark:bg-blue-500/5 dark:text-blue-400">
                                Event Schedule
                            </th>
                            <th class="min-w-[180px] px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white">{{ $name }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($sections as $sectionName => $rows)
                            <tr>
                                <td colspan="3" class="bg-gray-50 px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-white/[0.03] dark:text-gray-400">
                                    {{ $sectionName }}
                                </td>
                            </tr>
                            @foreach ($rows as $row)
                                @php
                                    $esWins = $row[3] ?? false;
                                @endphp
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="bg-white px-6 py-4 text-sm font-medium text-gray-900 dark:bg-[#0f0f14] dark:text-white">{{ $row[0] }}</td>
                                    <td class="px-6 py-4 text-sm {{ $esWins ? 'bg-emerald-50/50 dark:bg-emerald-500/5' : 'bg-blue-50/50 dark:bg-blue-500/5' }}">
                                        @if (str_starts_with($row[1], 'Yes'))
                                            <span class="inline-flex items-center gap-1.5 font-medium">
                                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @if (strlen($row[1]) > 3)
                                                    <span class="text-xs text-emerald-600/70 dark:text-emerald-400/70">{{ substr($row[1], 4) }}</span>
                                                @endif
                                            </span>
                                        @elseif (str_starts_with($row[1], 'No'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                @if (strlen($row[1]) > 2)
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ trim(substr($row[1], 2)) }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-gray-700 dark:text-gray-300">{{ $row[1] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if (str_starts_with($row[2], 'Yes'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @if (strlen($row[2]) > 3)
                                                    <span class="text-xs text-emerald-600/70 dark:text-emerald-400/70">{{ substr($row[2], 4) }}</span>
                                                @endif
                                            </span>
                                        @elseif (str_starts_with($row[2], 'No'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                @if (strlen($row[2]) > 2)
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ trim(substr($row[2], 2)) }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-gray-700 dark:text-gray-300">{{ $row[2] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Where Event Schedule Shines                              -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Where Event Schedule <span class="text-gradient-compare">shines</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Key advantages over {{ $name }} that make Event Schedule a strong alternative.</p>
            </div>

            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ($key_advantages as $advantage)
                    <div data-reveal class="rounded-2xl border bg-gradient-to-br p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg {{ $advantage['gradient'] }} {{ $advantage['border'] }}">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl {{ $advantage['icon_bg'] }}">
                            <x-marketing-icon :icon="$advantage['icon']" :class="'w-6 h-6 ' . $advantage['icon_color']" />
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ $advantage['title'] }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $advantage['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. About Competitor + Why Choose                            -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <h2 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">About {{ $name }}</h2>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">{{ $about }}</p>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">{{ $name }}'s strengths</h3>
                    <ul class="space-y-3">
                        @foreach ($competitor_strengths as $strength)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $strength }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div data-reveal style="--reveal-delay: 0.1s;">
                    <h2 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">Why choose Event Schedule?</h2>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">
                        {{ $why_choose['summary'] ?? 'Event Schedule offers a unique combination of features that no other platform matches: zero platform fees, open source transparency, and powerful AI tools.' }}
                    </p>
                    @php
                        $whyChoosePoints = $why_choose['points'] ?? [
                            'Zero platform fees on all ticket sales, at any plan level',
                            'Fully open source with selfhosting option for complete control',
                            'AI-powered event parsing, flyer generation, and automatic graphics',
                            'Two-way Google Calendar and CalDAV sync included free',
                        ];
                    @endphp
                    <ul class="space-y-3">
                        @foreach ($whyChoosePoints as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. How to Switch                                            -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>How to switch in <span class="text-gradient-compare">3 steps</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Get started in minutes. No migration or data import needed.</p>
            </div>

            @php
                $steps = $switch_steps ?? [
                    ['title' => 'Create your schedule', 'description' => 'Sign up free and create your first schedule in under a minute.'],
                    ['title' => 'Add your events', 'description' => 'Paste event details for AI import or create events manually.'],
                    ['title' => 'Share and sell', 'description' => 'Share your schedule URL and start selling tickets.'],
                ];
            @endphp

            <div class="mx-auto grid max-w-4xl grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as $index => $step)
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 text-xl font-bold text-white shadow-lg shadow-blue-500/25">
                            {{ $index + 1 }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $step['title'] }}</h3>
                        <p class="text-gray-500 dark:text-gray-400">{{ $step['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-compare">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common questions about switching from {{ $name }}.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faq as $index => $item)
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $item['question'] }}</h3>
                            <svg aria-hidden="true" class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $item['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Cross-links                                              -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Also compare with</h2>
            </div>

            <div class="mx-auto grid max-w-3xl grid-cols-1 gap-6 sm:grid-cols-3" data-reveal-group="80">
                @foreach ($cross_links as $link)
                    <a href="{{ route($link['route']) }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-6 transition-all hover:-translate-y-1 hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule vs</div>
                            <div class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">{{ $link['name'] }}</div>
                        </div>
                        <svg aria-hidden="true" class="h-5 w-5 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
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
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-compare">switch?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your free schedule today. No credit card required, no platform fees ever.
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
                </div>
            </div>
        </div>
    </section>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
