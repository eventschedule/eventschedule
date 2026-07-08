<x-marketing-layout>
    <x-slot name="title">Replace {{ $short_name ?? $name }} for Events | Event Schedule</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
    <x-slot name="keywords">{{ $keywords }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ $short_name ?? $name }} Replacement</x-slot>

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
        "isSimilarTo": {
            "@type": "SoftwareApplication",
            "name": "{{ str_replace('"', '\\"', $name) }}",
            "applicationCategory": "BusinessApplication"
        },
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
        .text-gradient-replace {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-replace {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-replace {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature motif: a row of verdict marks (check vs cross) */
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
    <section class="es-hero relative flex min-h-[calc(70svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

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
                <a href="{{ route('marketing.replace') }}" class="text-sm text-gray-500 transition-colors hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                    &larr; All tools
                </a>
            </div>

            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Replace your workaround</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Replace <span class="text-gradient-replace">{{ $name }}</span></span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line">for Events</span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                {{ $tagline }}
            </p>

            @if (!empty($audience_hint))
                <p class="es-fade-up es-d-3 mx-auto mt-4 max-w-3xl text-base text-gray-400 dark:text-gray-500">
                    {{ $audience_hint }}
                </p>
            @endif

            <div class="es-fade-up es-d-3 mt-8">
                @include('marketing.partials.github-star-badge')
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Pain Points                                              -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Where {{ $name }} <span class="text-gradient-replace">limits you</span> for events</h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">{{ $name }} was not built for event management. Here is where it leaves gaps.</p>
            </div>

            <div class="mx-auto grid max-w-3xl grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="70">
                @foreach ($pain_points as $pain)
                    <div data-reveal class="flex items-start gap-4 rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                        <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">{{ $pain }}</span>
                    </div>
                @endforeach
            </div>

            @if (!empty($competitor_price) && !empty($es_price))
            <div class="mx-auto mt-12 grid max-w-4xl grid-cols-1 items-stretch gap-8 md:grid-cols-2" data-reveal-group="80">
                <div data-reveal class="rounded-2xl border border-gray-200 bg-white p-8 text-center dark:border-white/10 dark:bg-white/5">
                    <div class="mb-6 text-sm font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $name }}</div>
                    <div class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $competitor_price }}</div>
                    @if (!empty($pricing_note))
                        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $pricing_note }}</p>
                    @endif
                </div>
                <div data-reveal class="rounded-2xl border border-blue-200 bg-blue-50/50 p-8 text-center dark:border-blue-500/30 dark:bg-blue-500/5">
                    <div class="mb-6 text-sm font-medium uppercase tracking-wider text-blue-500 dark:text-blue-400">Event Schedule</div>
                    <div class="mb-2 text-3xl font-bold text-gradient-replace">{{ $es_price }}</div>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Zero platform fees on ticket sales. You only pay Stripe's processing fee.</p>
                </div>
            </div>
            @endif
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Solutions                                                -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>What Event Schedule gives you over <span class="text-gradient-replace">{{ $name }}</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Purpose-built features that replace the workarounds.</p>
            </div>

            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ($es_solutions as $solution)
                    <div data-reveal class="rounded-2xl border bg-gradient-to-br p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg {{ $solution['gradient'] }} {{ $solution['border'] }}">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl {{ $solution['icon_bg'] }}">
                            <x-marketing-icon :icon="$solution['icon']" :class="'w-6 h-6 ' . $solution['icon_color']" />
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ $solution['title'] }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $solution['description'] }}</p>
                    </div>
                @endforeach
            </div>

            @if (!empty($comparison_rows))
            <div class="mx-auto mt-16 max-w-3xl">
                <div class="mb-8 text-center">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white md:text-3xl">{{ $name }} vs. Event Schedule</h3>
                </div>
                <div data-reveal class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Feature</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900 dark:text-white">{{ $short_name ?? $name }}</th>
                                <th class="px-6 py-4 text-center text-sm font-semibold text-blue-600 dark:text-blue-400">Event Schedule</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($comparison_rows as $row)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $row['feature'] }}</td>
                                    <td class="px-6 py-4 text-center text-sm">
                                        @if ($row['competitor'] === true)
                                            <svg aria-hidden="true" class="mx-auto h-5 w-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @elseif ($row['competitor'] === false)
                                            <svg aria-hidden="true" class="mx-auto h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        @else
                                            <span class="text-gray-500 dark:text-gray-400">{{ $row['competitor'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm">
                                        @if ($row['es'] === true)
                                            <svg aria-hidden="true" class="mx-auto h-5 w-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @elseif ($row['es'] === false)
                                            <svg aria-hidden="true" class="mx-auto h-5 w-5 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        @else
                                            <span class="font-medium text-blue-600 dark:text-blue-400">{{ $row['es'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </section>

    <!-- Pricing Nudge -->
    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 4. How to Switch                                            -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>How to switch from {{ $name }} in <span class="text-gradient-replace">3 steps</span></h2>
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

            <div class="mt-12 text-center" data-reveal>
                <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Create Your Free Schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <p class="mt-3 text-sm text-gray-400 dark:text-gray-500">No credit card required.</p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. About Tool + Why Switch                                  -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <h2 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">About {{ $name }}</h2>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">{{ $about }}</p>
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">{{ $name }}'s strengths</h3>
                    <ul class="space-y-3">
                        @foreach ($tool_strengths as $strength)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $strength }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div data-reveal style="--reveal-delay: 0.1s;" class="rounded-2xl border border-blue-200 bg-blue-50/50 p-8 dark:border-blue-500/20 dark:bg-blue-500/5">
                    <h2 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">Why switch to Event Schedule?</h2>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">
                        {{ $why_switch['intro'] ?? 'Event Schedule offers a unique combination of features that no other platform matches: zero platform fees, open source transparency, and powerful AI tools.' }}
                    </p>
                    <ul class="space-y-3">
                        @foreach (($why_switch['points'] ?? ['Zero platform fees on all ticket sales, at any plan level', 'Fully open source with selfhosting option for complete control', 'AI-powered event parsing, flyer generation, and automatic graphics', 'Two-way Google Calendar and CalDAV sync included free']) as $point)
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
    <!-- 6. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>{{ $name }} to Event Schedule <span class="text-gradient-replace">FAQ</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common questions about switching from {{ $name }}.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faq as $item)
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
    <!-- 7. Cross-links + Related + Compare                          -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Also replace</h2>
            </div>

            <div class="mx-auto grid max-w-3xl grid-cols-1 gap-6 sm:grid-cols-3" data-reveal-group="80">
                @foreach ($cross_links as $link)
                    <a href="{{ route($link['route']) }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-6 transition-all hover:-translate-y-1 hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Replace</div>
                            <div class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">{{ $link['name'] }}</div>
                            @if (!empty($link['description']))
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $link['description'] }}</div>
                            @endif
                        </div>
                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>

            @if (!empty($related_features))
            <div class="mx-auto mt-20 max-w-3xl">
                <div class="mb-12 text-center">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Explore related features</h2>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3" data-reveal-group="80">
                    @foreach ($related_features as $feature)
                        <a href="{{ route($feature['route']) }}" data-reveal class="group flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 transition-all hover:-translate-y-1 hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                            <div class="mb-2 text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">{{ $feature['name'] }}</div>
                            <p class="mt-auto text-sm text-gray-500 dark:text-gray-400">{{ $feature['description'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="mx-auto mt-20 max-w-3xl">
                <a href="{{ route('marketing.compare') }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-8 transition-all hover:-translate-y-1 hover:border-blue-300 hover:bg-blue-50 dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                    <div>
                        <h2 class="mb-2 text-xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400 md:text-2xl">
                            Looking for direct platform comparisons?
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400">
                            See how Event Schedule compares to Eventbrite, Luma, and Ticket Tailor.
                        </p>
                    </div>
                    <svg aria-hidden="true" class="ml-6 h-6 w-6 shrink-0 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Finale                                                   -->
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
                        Ready to replace <span class="text-gradient-replace">{{ $name }}?</span>
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
