<x-marketing-layout>
    <x-slot name="title">Boost Events with Ad Campaigns - Event Schedule</x-slot>
    <x-slot name="description">Turn your event details into live Facebook and Instagram ads. Automated targeting, budget control, and real-time analytics with no ad manager experience required.</x-slot>
    <x-slot name="breadcrumbTitle">Boost</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Boost",
        "description": "Turn your event details into live Facebook and Instagram ads. Automated targeting, budget control, and real-time analytics with no ad manager experience required.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Advertising Automation"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Does Boost guarantee ticket sales or attendance?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Boost increases your event's visibility by placing ads in front of relevant audiences on Facebook and Instagram. It does not guarantee ticket sales, RSVPs, or attendance. Results depend on your event, audience, creative, and budget."
                }
            },
            {
                "@type": "Question",
                "name": "How much does Boost cost?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You set your own ad budget from $10 to $1,000. Event Schedule charges a transparent service fee on top of your ad spend. There are no hidden costs, and you can see the full breakdown before you launch."
                }
            },
            {
                "@type": "Question",
                "name": "Which platforms do Boost ads run on?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Boost ads run on Facebook and Instagram, including feeds, Stories, and Reels placements. Your ad is automatically formatted for each placement."
                }
            },
            {
                "@type": "Question",
                "name": "How does targeting work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Boost uses your event's location, category, and details to build a relevant audience automatically. You can also customize age range, interests, and radius. Advanced mode lets you fine-tune every parameter."
                }
            },
            {
                "@type": "Question",
                "name": "Can I pause or cancel a Boost campaign?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You can pause or cancel a running campaign at any time from your dashboard. If you cancel before any budget is spent, you receive a full refund. If Meta rejects the ad, you also receive a full refund. When a campaign completes, any unspent budget and the proportional service fee are automatically refunded."
                }
            },
            {
                "@type": "Question",
                "name": "What happens to unspent budget?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "If Meta rejects your ad or you cancel before any budget is spent, you receive a full refund. When a campaign completes with unspent budget, the remaining ad spend and the proportional service fee are automatically refunded. You receive email notifications when your campaign is created, when 75% of your budget has been spent, and when it completes with final stats."
                }
            },
            {
                "@type": "Question",
                "name": "Do I need a Meta (Facebook) Ads account?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Event Schedule handles the ad account, creative, and delivery on your behalf. You do not need to create or manage a Meta Ads account."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Boost",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Advertising Software",
        "operatingSystem": "Web",
        "description": "Turn your event details into live Facebook and Instagram ads. Automated targeting, budget control, and real-time analytics with no ad manager experience required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Ad budget starts at $10"
        },
        "featureList": [
            "Automated Facebook and Instagram ads",
            "Smart audience targeting",
            "Budget control from $10 to $1,000",
            "Real-time campaign analytics",
            "Transparent pricing",
            "Advanced targeting mode"
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
        /* For boost "The Launch" styles. The shared es-* motion system lives in
           marketing.css; this holds the orange glow gradient, the drifting boosted-ad
           card, and the ember motif (boost energy rising off the page). */
        .text-gradient-boost {
            background: linear-gradient(135deg, #ea580c, #d97706, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(234, 88, 12, 0.3);
        }
        .dark .text-gradient-boost {
            background: linear-gradient(135deg, #fb923c, #fbbf24, #fcd34d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(251, 146, 60, 0.3);
        }
        @keyframes es-boost-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-boost-float { animation: es-boost-float 6s ease-in-out infinite; }

        /* Ember motif: embers rise and fade, like the energy lift of a boosted event. */
        .es-embers { display: flex; align-items: flex-end; }
        .es-ember {
            flex: 0 0 auto; width: 6px; height: 6px; border-radius: 9999px;
            background: rgba(249, 115, 22, 0.85);
            animation: es-ember-rise var(--em-dur, 3s) ease-in-out infinite;
            animation-delay: var(--em-delay, 0s);
        }
        @keyframes es-ember-rise {
            0% { transform: translateY(10px) scale(0.5); opacity: 0; }
            30% { opacity: 1; }
            100% { transform: translateY(-22px) scale(1); opacity: 0; }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-boost-float, .es-ember, .animate-pulse-slow { animation: none !important; }
            .es-ember { opacity: 0.6; transform: none; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: boost                                               -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(234, 88, 12, 0.3), rgba(234, 88, 12, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(217, 119, 6, 0.28), rgba(217, 119, 6, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(245, 158, 11, 0.14), rgba(245, 158, 11, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Ember motif along the bottom edge -->
            <div class="es-embers absolute bottom-10 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-end justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 22; $i++)
                    @php $dur = 2.6 + ($i % 5) * 0.34; $delay = ($i % 11) * 0.24; @endphp
                    <span class="es-ember" style="--em-dur: {{ $dur }}s; --em-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Event Boost</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your events,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-boost">amplified</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Turn your event details into live Facebook and Instagram ads. Set a budget, pick your audience, and launch in minutes. No ad manager required.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-orange-600 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-orange-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.boost') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Boost guide
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
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Built for <span class="text-gradient-boost">event promotion</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Smart targeting (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Smart Targeting
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Reach the right people</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Your event's location, category, and details are used to build a relevant audience automatically. Boost auto-detects whether your event is in-person, online, or hybrid and adjusts targeting and creative accordingly. Target by radius, age range, and interests without touching an ad manager.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Location radius</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Interest targeting</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Age range</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-56" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="space-y-2">
                                        @foreach ([['10 mi radius', 'bg-orange-500', true], ['Live music fans', 'bg-amber-500', false], ['Ages 21 to 45', 'bg-yellow-500', false], ['Nightlife', 'bg-orange-400', false]] as $ti => [$lbl, $dot, $active])
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg p-2 {{ $active ? 'border border-orange-200 bg-orange-50 dark:border-orange-400/30 dark:bg-orange-500/10' : 'bg-gray-50 dark:bg-white/5' }}" style="--i: {{ $ti }};">
                                                <div class="h-3 w-3 rounded-full {{ $dot }}"></div>
                                                <span class="text-sm {{ $active ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300' }}">{{ $lbl }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Instant ad creation -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            One Click
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Instant ad creation</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Your event title, image, date, and location become a polished ad in seconds. No copywriting or design work needed.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="w-40 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="mb-2 flex h-16 items-center justify-center rounded-lg bg-orange-200 dark:bg-orange-800">
                                    <svg aria-hidden="true" class="h-6 w-6 text-orange-500 dark:text-orange-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="text-[10px] font-bold text-gray-900 dark:text-white">Jazz Night at The Blue Note</div>
                                <div class="text-[9px] text-gray-500 dark:text-gray-400">Sat, Mar 15 - 8 PM</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Budget control -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Flexible
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Budget control</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Slide from $10 to $1,000. See estimated reach before you pay. No surprises, no overruns.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Budget</span>
                                <span class="font-bold text-gray-900 dark:text-white">$75</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                                <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-amber-500" style="width: 15%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-400">
                                <span>$10</span>
                                <span>$1,000</span>
                            </div>
                            <div class="text-center text-sm font-medium text-orange-600 dark:text-orange-400">Est. 3,200 to 8,500 reach</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Real-time analytics (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                    </svg>
                                    Real-time Stats
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">See what's working</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Track impressions, reach, clicks, and click-through rate as your campaign runs. All stats update in real time on your dashboard.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Impressions</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Reach</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Clicks</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Click-through rate</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-64" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 grid grid-cols-2 gap-2">
                                        <div class="text-center"><div class="text-lg font-bold text-gray-900 dark:text-white">12,480</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Impressions</div></div>
                                        <div class="text-center"><div class="text-lg font-bold text-orange-600 dark:text-orange-400">8,320</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Reach</div></div>
                                        <div class="text-center"><div class="text-lg font-bold text-amber-600 dark:text-amber-400">412</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Clicks</div></div>
                                        <div class="text-center"><div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">3.3%</div><div class="text-[10px] text-gray-500 dark:text-gray-400">CTR</div></div>
                                    </div>
                                    <div class="flex h-16 items-end justify-between gap-1">
                                        @foreach ([20, 40, 55, 80, 100, 90, 95, 85] as $bi => $bh)
                                            <div class="w-4 rounded-t bg-orange-500" style="height: {{ $bh }}%; opacity: {{ 0.3 + $bi * 0.07 }};"></div>
                                        @endforeach
                                    </div>
                                    <div class="mt-1 text-center text-[10px] text-gray-500 dark:text-gray-400">Impressions over time</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Transparent pricing (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    No Hidden Costs
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Transparent pricing</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">See exactly what you pay before you launch. Your ad budget and the service fee are shown upfront. No surprise charges, no minimum commitments.</p>
                            </div>
                            <div aria-hidden="true">
                                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="space-y-3">
                                        <div class="flex justify-between"><span class="text-gray-600 dark:text-gray-400">Ad budget</span><span class="font-semibold text-gray-900 dark:text-white">$75.00</span></div>
                                        <div class="flex justify-between"><span class="text-gray-600 dark:text-gray-400">Service fee</span><span class="font-semibold text-gray-900 dark:text-white">$15.00</span></div>
                                        <div class="flex justify-between border-t border-gray-200 pt-3 dark:border-white/10"><span class="font-bold text-gray-900 dark:text-white">Total</span><span class="font-bold text-orange-600 dark:text-orange-400">$90.00</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Advanced mode -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Pro Controls
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Advanced mode</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Want more control? Choose a campaign objective (awareness, traffic, or engagement), set daily or lifetime budgets, write custom ad copy, fine-tune targeting, and pick specific placements like Facebook Feed, Instagram Feed, Stories, or Reels.</p>
                        <div class="mt-auto flex items-center justify-center gap-2" aria-hidden="true">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-orange-500 shadow dark:border-white/20">
                                <svg aria-hidden="true" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-amber-500 shadow dark:border-white/20">
                                <svg aria-hidden="true" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-yellow-500 shadow dark:border-white/20">
                                <svg aria-hidden="true" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
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
    <!-- 3. How it works (dark band)                                 -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(234, 88, 12, 0.24), rgba(234, 88, 12, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-embers absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-end justify-center gap-6 px-8 opacity-45" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    @for ($i = 0; $i < 20; $i++)
                        @php $dur = 2.6 + ($i % 5) * 0.34; $delay = ($i % 11) * 0.24; @endphp
                        <span class="es-ember" style="--em-dur: {{ $dur }}s; --em-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>How it <span class="text-gradient-boost">works</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">From event to live ad in three steps.</p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                    @php
                        $steps = [
                            ['Pick your event', 'Choose any upcoming event from your schedule. Boost pulls in the title, date, location, and image automatically.'],
                            ['Set your budget', 'Slide from $10 to $1,000. See estimated reach before you commit. Pay with the card on file.'],
                            ['Launch and track', 'Your ad goes live on Facebook and Instagram. Watch impressions, reach, and clicks roll in from your dashboard.'],
                        ];
                    @endphp
                    @foreach ($steps as $si => $step)
                        <div data-reveal class="text-center">
                            <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 text-2xl font-bold text-white shadow-lg shadow-orange-500/25">{{ $si + 1 }}</div>
                            <h3 class="mb-3 text-xl font-bold text-white">{{ $step[0] }}</h3>
                            <p class="text-gray-300">{{ $step[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Keep exploring                                           -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute left-1/4 top-10 h-[300px] w-[300px] rounded-full bg-orange-600/20 blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 h-[200px] w-[200px] rounded-full bg-amber-600/20 blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                <!-- Newsletters -->
                <a href="{{ marketing_url('/features/newsletters') }}" data-reveal class="group flex flex-col rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-100 to-cyan-100 p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-sky-900 dark:to-cyan-900 lg:p-10">
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-300">Newsletters</h3>
                    <p class="mb-4 text-lg text-gray-600 dark:text-white/80">Send branded emails to your followers and ticket buyers with a drag-and-drop editor.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-sky-500 transition-all group-hover:gap-3 dark:text-sky-400">
                        Learn more
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Analytics -->
                <a href="{{ marketing_url('/features/analytics') }}" data-reveal class="group flex flex-col rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-100 to-teal-100 p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-emerald-900 dark:to-teal-900 lg:p-10">
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-emerald-600 dark:text-white dark:group-hover:text-emerald-300">Analytics</h3>
                    <p class="mb-4 text-lg text-gray-600 dark:text-white/80">Track views, followers, and engagement across all your events and schedules.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-emerald-500 transition-all group-hover:gap-3 dark:text-emerald-400">
                        Learn more
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Popular with -->
                <div data-reveal class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-orange-500/20 bg-orange-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-500 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-orange-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-orange-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Venues</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-orange-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-musicians') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-orange-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-orange-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Musicians</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-orange-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-bars') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-orange-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-orange-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Bars</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-orange-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-boost">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about Boost.</p>
            </div>

            <div class="space-y-4" data-reveal-group="70">
                @foreach ([
                    ['Does Boost guarantee ticket sales or attendance?', 'No. Boost increases your event\'s visibility by placing ads in front of relevant audiences on Facebook and Instagram. Results depend on your event, audience, creative, and budget. Think of it as distribution, not a guarantee.'],
                    ['How much does Boost cost?', 'You set your own ad budget from $10 to $1,000. Event Schedule charges a transparent service fee on top of your ad spend. The full breakdown is shown before you launch. No hidden costs, no minimum commitments.'],
                    ['Which platforms do Boost ads run on?', 'Boost ads run on Facebook and Instagram, including feeds, Stories, and Reels placements. Your ad is automatically formatted for each placement.'],
                    ['How does targeting work?', 'Boost uses your event\'s location, category, and details to build a relevant audience automatically. You can also customize age range, interests, and radius. Advanced mode lets you fine-tune every parameter.'],
                    ['Can I pause or cancel a campaign?', 'Yes. You can pause or cancel a running campaign at any time from your dashboard. If you cancel before any budget is spent, you receive a full refund. If Meta rejects the ad, you also receive a full refund. When a campaign completes, any unspent budget and the proportional service fee are automatically refunded.'],
                    ['What happens to unspent budget?', 'If Meta rejects your ad or you cancel before any budget is spent, you receive a full refund. When a campaign completes with unspent budget, the remaining ad spend and the proportional service fee are automatically refunded. You receive email notifications when your campaign is created, when 75% of your budget has been spent, and when it completes with final stats.'],
                    ['Do I need a Meta (Facebook) Ads account?', 'No. Event Schedule handles the ad account, creative, and delivery on your behalf. You do not need to create or manage a Meta Ads account.'],
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
    <!-- 6. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-orange-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(234, 88, 12, 0.3), rgba(234, 88, 12, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-embers absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-end justify-center gap-6 px-8 opacity-45" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 16; $i++)
                            @php $dur = 2.6 + ($i % 5) * 0.34; $delay = ($i % 11) * 0.24; @endphp
                            <span class="es-ember" style="--em-dur: {{ $dur }}s; --em-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Put your next event in front of <span class="text-gradient-boost">the right audience</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Launch a campaign in minutes. No ad experience needed.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-orange-600 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-orange-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No ad experience needed</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
