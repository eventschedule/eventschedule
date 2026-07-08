<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Farmers Markets | Vendor Calendar</x-slot>
    <x-slot name="description">Grow your market. Share market days, vendor lineups, and seasonal events. Email your community directly - no algorithm. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Farmers Markets</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Farmers Markets",
        "description": "Grow your market. Share market days, vendor lineups, and seasonal events. Email your community directly. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Farmers Markets & Outdoor Markets"
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
                "name": "Is Event Schedule free for farmers markets?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your market schedule, building a shopper following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan."
                }
            },
            {
                "@type": "Question",
                "name": "Can I list vendors and special events for each market day?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Create recurring events for your regular market days and add special events like cooking demos, live music, and seasonal festivals. Use sub-schedules to organize by vendor type, entertainment, or special programming."
                }
            },
            {
                "@type": "Question",
                "name": "How do shoppers find out about market days and events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Shoppers can follow your market's schedule and receive email notifications for upcoming events. Embed your calendar on your website, share on social media, or send newsletters with weekly highlights and featured vendors."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage vendor applications and booth fees?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use ticketing to manage vendor booth reservations and collect fees through Stripe with zero platform fees. Vendors receive confirmation with QR codes for check-in on market day."
                }
            }
        ]
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Farmers Markets",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Farmers Market Event Management Software",
        "operatingSystem": "Web",
        "description": "Grow your market. Share market days, vendor lineups, and seasonal events. Email your community directly. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Weekly market day newsletters",
            "Recurring market scheduling",
            "Vendor lineup listings",
            "Full seasonal market calendar",
            "Special event programming",
            "Google Calendar two-way sync",
            "Vendor booth ticketing"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "farmers market calendar, market vendor schedule, farmers market events, outdoor market management, free farmers market scheduling",
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
        /* For-farmers-markets "The Market" styles. The shared es-* motion system
           lives in marketing.css; this holds the lime gradient text, the rustic
           FRESH stamp, the wood-grain badge, and the drifting-seed motif. */
        .text-gradient-lime {
            background: linear-gradient(135deg, #84cc16, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-lime {
            background: linear-gradient(135deg, #a3e635, #4ade80);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .rustic-stamp {
            border: 3px solid currentColor;
            border-radius: 4px;
            color: rgb(101, 163, 13);
            transform: rotate(-3deg);
            background: rgba(101, 163, 13, 0.05);
            box-shadow: 0 4px 12px rgba(101, 163, 13, 0.15);
        }
        .dark .rustic-stamp {
            color: rgb(163, 230, 53);
            background: rgba(163, 230, 53, 0.05);
            box-shadow: 0 4px 12px rgba(163, 230, 53, 0.1);
        }
        .wood-grain-badge {
            background: linear-gradient(135deg, rgba(180, 140, 80, 0.1), rgba(160, 120, 60, 0.15), rgba(180, 140, 80, 0.1));
            position: relative;
        }
        .dark .wood-grain-badge {
            background: linear-gradient(135deg, rgba(120, 80, 30, 0.3), rgba(100, 70, 20, 0.4), rgba(120, 80, 30, 0.3));
        }
        @keyframes es-produce-float {
            0%, 100% { transform: rotate(-3deg) translateY(0px); }
            50% { transform: rotate(-3deg) translateY(-10px); }
        }
        .es-produce-float { animation: es-produce-float 6s ease-in-out infinite; }

        /* Drifting seeds */
        .es-seed { pointer-events: none; overflow: hidden; }
        .es-seed span {
            position: absolute;
            top: -12px;
            border-radius: 9999px;
            opacity: 0;
            animation: es-seed var(--seed-dur, 12s) linear infinite;
            animation-delay: var(--seed-delay, 0s);
        }
        @keyframes es-seed {
            0% { transform: translateY(0) translateX(0) rotate(0deg); opacity: 0; }
            15% { opacity: var(--seed-op, 0.5); }
            85% { opacity: var(--seed-op, 0.5); }
            100% { transform: translateY(220px) translateX(22px) rotate(200deg); opacity: 0; }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-produce-float, .es-seed span { animation: none !important; }
            .es-produce-float { transform: rotate(-3deg); }
            .es-seed span { opacity: 0.3; transform: none; }
        }
    </style>

    @php
        // Drifting seeds: [left, size(px), duration, delay, opacity, color]
        $seeds = [
            ['8%', 5, '11s', '0s', '0.5', 'rgba(132, 204, 22, 0.9)'],
            ['20%', 7, '13s', '2s', '0.4', 'rgba(245, 158, 11, 0.9)'],
            ['33%', 4, '10s', '3.5s', '0.35', 'rgba(249, 115, 22, 0.9)'],
            ['45%', 6, '12s', '1s', '0.45', 'rgba(132, 204, 22, 0.9)'],
            ['57%', 5, '11.5s', '2.6s', '0.4', 'rgba(74, 222, 128, 0.9)'],
            ['69%', 7, '13s', '4.2s', '0.4', 'rgba(245, 158, 11, 0.9)'],
            ['81%', 4, '10.5s', '1.3s', '0.45', 'rgba(132, 204, 22, 0.9)'],
            ['92%', 6, '12s', '3s', '0.35', 'rgba(249, 115, 22, 0.9)'],
        ];
        // Bunting pennant fill classes, cycled
        $pennants = ['fill-lime-500/25', 'fill-amber-500/25', 'fill-orange-500/25', 'fill-red-500/20'];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: your market, your community                         -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 35%, rgba(132, 204, 22, 0.3), rgba(132, 204, 22, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 42%, rgba(22, 163, 74, 0.3), rgba(22, 163, 74, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 55% 75%, rgba(245, 158, 11, 0.14), rgba(245, 158, 11, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-seed absolute inset-x-0 top-0 h-2/3">
                @foreach ($seeds as [$l, $s, $d, $dl, $op, $col])
                    <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; background: {{ $col }}; --seed-dur: {{ $d }}; --seed-delay: {{ $dl }}; --seed-op: {{ $op }};"></span>
                @endforeach
            </div>
        </div>

        <!-- Bunting across the top -->
        <div class="pointer-events-none absolute inset-x-0 top-16 hidden h-16 overflow-hidden md:block" aria-hidden="true">
            <svg class="w-full" height="60" viewBox="0 0 1200 60" preserveAspectRatio="none" fill="none">
                <path d="M0,10 Q300,25 600,10 Q900,25 1200,10" stroke="currentColor" stroke-width="1.5" class="text-gray-300 dark:text-gray-600" fill="none" />
                @for ($p = 0; $p < 14; $p++)
                    @php $x = 80 + $p * 80; $fill = $pennants[$p % 4]; @endphp
                    <polygon points="{{ $x }},13 {{ $x + 20 }},51 {{ $x + 40 }},13" class="{{ $fill }}" />
                @endfor
            </svg>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 wood-grain-badge mb-8 inline-flex items-center gap-3 rounded-lg border border-amber-300/50 px-5 py-2.5 backdrop-blur-sm dark:border-amber-600/30">
                <svg aria-hidden="true" class="h-5 w-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Farmers Markets & Outdoor Markets</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your market. Your community.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-lime es-gradient-anim">Fresh every week.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Stop losing shoppers to outdated flyers. Email your community directly, share what's fresh, and grow your market.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#seasons" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the season
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-lime-600 to-green-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-lime-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-lime-500/40">
                    Create your market's calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Market-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Organic Produce', 'Artisan Crafts', 'Baked Goods', 'Local Honey', 'Cut Flowers', 'Farm Fresh', 'Preserves', 'Seedlings'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-lime-200 bg-lime-100/80 px-4 py-1.5 text-xs font-semibold text-lime-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-lime-400 to-green-400"></span>
                                        {{ $tag }}
                                    </span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento features                                            -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Everything to grow your <span class="text-gradient-lime">market</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Market updates newsletter (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-lime-200 bg-lime-100 px-3 py-1.5 text-sm font-medium text-lime-700 dark:border-lime-800/30 dark:bg-lime-900/40 dark:text-lime-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Newsletter
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Tell shoppers what's fresh this week</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">No algorithm. No pay-to-play. New vendor this Saturday? Strawberries are in season? One click sends the market update straight to everyone who wants to know.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Skip the algorithm</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Your shoppers, your reach</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-lime-300 bg-gradient-to-br from-lime-100 to-green-100 p-4 dark:border-lime-400/30 dark:from-lime-950 dark:to-green-950">
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-lime-500 to-green-500"><svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div>
                                            <div><div class="text-sm font-medium text-gray-900 dark:text-white">This Week at the Market</div><div class="text-xs text-lime-600 dark:text-lime-300">Sending to 1,243 followers...</div></div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-lime-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Green Acres Farm - Fresh Berries</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-amber-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Baker's Dozen - Sourdough Bread</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-green-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Honey Bee Apiary - NEW VENDOR</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Recurring markets -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-green-200 bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700 dark:border-green-800/30 dark:bg-green-900/40 dark:text-green-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Recurring Events
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Every Saturday. Rain or shine.</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Markets run on weekly rhythms. Set up recurring market days once, they show automatically.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="grid grid-cols-7 gap-1">
                                @foreach (['M' => false, 'T' => false, 'W' => true, 'T2' => false, 'F' => false, 'S' => true, 'S2' => 'sun'] as $key => $active)
                                    @php $label = rtrim($key, '2'); @endphp
                                    <div class="text-center">
                                        <div class="mb-1 text-[10px] {{ $active ? 'font-medium text-green-600 dark:text-green-300' : 'text-gray-400' }}">{{ $label }}</div>
                                        @if ($active === true)
                                            <div class="mx-auto h-6 w-6 rounded-full bg-green-500"></div>
                                        @elseif ($active === 'sun')
                                            <div class="mx-auto h-6 w-6 rounded-full border border-lime-400/50 bg-lime-500/30"></div>
                                        @else
                                            <div class="mx-auto h-6 w-6 rounded-full bg-gray-100 dark:bg-white/5"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">Wed & Sat markets auto-repeat</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Vendor lineup -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Vendors
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Show your vendor lineup</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Shoppers want to know who's there before they come. Show them the full lineup.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-amber-400/30 bg-amber-500/15 p-2" style="--i: 0;">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-lime-500 to-green-500 text-[9px] font-semibold text-white">GA</div>
                                <div class="flex-1"><div class="text-xs font-medium text-gray-900 dark:text-white">Green Acres Farm</div><div class="text-[10px] text-amber-600 dark:text-amber-300">Produce</div></div>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-amber-400/30 bg-amber-500/15 p-2" style="--i: 1;">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-[9px] font-semibold text-white">BD</div>
                                <div class="flex-1"><div class="text-xs font-medium text-gray-900 dark:text-white">Baker's Dozen</div><div class="text-[10px] text-amber-600 dark:text-amber-300">Baked Goods</div></div>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-amber-400/30 bg-amber-500/15 p-2" style="--i: 2;">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-yellow-500 to-amber-500 text-[9px] font-semibold text-white">HB</div>
                                <div class="flex-1"><div class="text-xs font-medium text-gray-900 dark:text-white">Honey Bee Apiary</div><div class="text-[10px] text-amber-600 dark:text-amber-300">Honey & Preserves</div></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Seasonal calendar (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Seasonal Calendar
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Plan your whole season</h3>
                                <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Spring opening to winter holiday markets. Lay out the full season so shoppers know what to expect all year long.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Full season view</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Special event dates</span>
                                </div>
                            </div>
                            <div class="flex justify-center" aria-hidden="true">
                                <div class="grid w-full max-w-xs grid-cols-2 gap-2">
                                    <div class="es-ai-field rounded-xl border border-green-400/30 bg-green-500/15 p-3 text-center" style="--i: 0;"><div class="mb-1 text-lg">&#127793;</div><div class="text-xs font-bold text-green-700 dark:text-green-300">Spring</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Apr - May</div></div>
                                    <div class="es-ai-field rounded-xl border border-yellow-400/30 bg-yellow-500/15 p-3 text-center" style="--i: 1;"><div class="mb-1 text-lg">&#9728;&#65039;</div><div class="text-xs font-bold text-yellow-700 dark:text-yellow-300">Summer</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Jun - Aug</div></div>
                                    <div class="es-ai-field rounded-xl border border-orange-400/30 bg-orange-500/15 p-3 text-center" style="--i: 2;"><div class="mb-1 text-lg">&#127810;</div><div class="text-xs font-bold text-orange-700 dark:text-orange-300">Fall</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Sep - Nov</div></div>
                                    <div class="es-ai-field rounded-xl border border-blue-400/30 bg-blue-500/15 p-3 text-center" style="--i: 3;"><div class="mb-1 text-lg">&#10052;&#65039;</div><div class="text-xs font-bold text-blue-700 dark:text-blue-300">Winter</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Dec - Mar</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Special events -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            Special Events
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">More than just shopping</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Cooking demos, live music, kids activities. Make your market a destination.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-orange-400/30 bg-orange-500/15 p-2" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-orange-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Chef Demo: Farm to Table</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-orange-400/30 bg-orange-500/15 p-2" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-amber-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Live Bluegrass Band</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-orange-400/30 bg-orange-500/15 p-2" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-yellow-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Kids Pumpkin Painting</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Google Calendar sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Google Calendar
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sync with Google Calendar</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Shoppers add your market to their calendar with one click. Never miss a market day.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="w-full rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10">
                                <div class="mb-2 flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-sm bg-blue-500"></div>
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Farmers Market</span>
                                </div>
                                <div class="space-y-1">
                                    <div class="es-ai-field flex items-center gap-2 text-[10px]" style="--i: 0;"><span class="text-gray-500 dark:text-gray-400">Sat 8am</span><span class="font-medium text-blue-600 dark:text-blue-300">Saturday Market</span></div>
                                    <div class="es-ai-field flex items-center gap-2 text-[10px]" style="--i: 1;"><span class="text-gray-500 dark:text-gray-400">Wed 3pm</span><span class="font-medium text-blue-600 dark:text-blue-300">Midweek Market</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Analytics (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Analytics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">See what draws crowds</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Which events get shares? What markets are most popular? Know what's working.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center justify-between" style="--i: 0;"><span class="text-xs text-gray-500 dark:text-gray-400">Sat Market</span><div class="h-2 w-20 overflow-hidden rounded-full bg-cyan-500/20"><div class="h-full w-[92%] rounded-full bg-cyan-400"></div></div></div>
                            <div class="es-ai-field flex items-center justify-between" style="--i: 1;"><span class="text-xs text-gray-500 dark:text-gray-400">Chef Demo</span><div class="h-2 w-20 overflow-hidden rounded-full bg-cyan-500/20"><div class="h-full w-[70%] rounded-full bg-cyan-400"></div></div></div>
                            <div class="es-ai-field flex items-center justify-between" style="--i: 2;"><span class="text-xs text-gray-500 dark:text-gray-400">Wed Market</span><div class="h-2 w-20 overflow-hidden rounded-full bg-cyan-500/20"><div class="h-full w-[50%] rounded-full bg-cyan-400"></div></div></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Through the seasons (dark band)                           -->
    <!-- ============================================================ -->
    <section id="seasons" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 30%, rgba(132, 204, 22, 0.22), rgba(132, 204, 22, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 60%, rgba(245, 158, 11, 0.18), rgba(245, 158, 11, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-seed absolute inset-0">
                    @foreach ($seeds as [$l, $s, $d, $dl, $op, $col])
                        <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; background: {{ $col }}; --seed-dur: {{ $d }}; --seed-delay: {{ $dl }}; --seed-op: {{ $op }};"></span>
                    @endforeach
                </div>
                <!-- Bunting across the band top -->
                <div class="absolute inset-x-0 top-6 hidden h-12 overflow-hidden md:block">
                    <svg class="w-full" height="52" viewBox="0 0 1200 52" preserveAspectRatio="none" fill="none">
                        <path d="M0,8 Q300,22 600,8 Q900,22 1200,8" stroke="currentColor" stroke-width="1.5" class="text-white/20" fill="none" />
                        @for ($p = 0; $p < 14; $p++)
                            @php $x = 80 + $p * 80; $fill = $pennants[$p % 4]; @endphp
                            <polygon points="{{ $x }},11 {{ $x + 20 }},46 {{ $x + 40 }},11" class="{{ $fill }}" />
                        @endfor
                    </svg>
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Your market through the <span class="text-gradient-lime">seasons</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        From the first spring asparagus to holiday gift markets, your calendar tells the story of the whole year. Shoppers always know what's next.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4" data-reveal-group="90">
                    <!-- Spring -->
                    <div data-reveal class="relative flex min-h-[172px] flex-col items-center justify-center rounded-2xl border border-green-400/30 bg-green-500/[0.12] p-4 text-center">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 rounded bg-green-500 px-2 py-0.5 text-[9px] font-bold text-white">SPRING</div>
                        <svg aria-hidden="true" class="mb-2 h-8 w-8" viewBox="0 0 32 32" fill="none"><path d="M16,8 L14,28 Q16,30 18,28 Z" fill="#f97316" /><path d="M14,10 L10,6 M16,8 L16,3 M18,10 L22,6" stroke="#22c55e" stroke-width="2" stroke-linecap="round" /></svg>
                        <div class="mb-1 text-sm font-bold text-green-300">Spring Opening</div>
                        <div class="text-xs text-gray-400">April kick-off</div>
                        <div class="mt-2 text-[10px] text-gray-400">Asparagus, greens, seedlings</div>
                    </div>
                    <!-- Summer -->
                    <div data-reveal class="relative flex min-h-[172px] flex-col items-center justify-center rounded-2xl border border-yellow-400/30 bg-yellow-500/[0.12] p-4 text-center">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 rounded bg-yellow-500 px-2 py-0.5 text-[9px] font-bold text-white">SUMMER</div>
                        <svg aria-hidden="true" class="mb-2 h-8 w-8" viewBox="0 0 32 32" fill="none"><ellipse cx="16" cy="18" rx="11" ry="10" fill="#dc2626" /><path d="M12,8 Q14,5 16,7 Q18,5 20,8" stroke="#22c55e" stroke-width="2" fill="none" stroke-linecap="round" /><path d="M16,7 L16,4" stroke="#22c55e" stroke-width="1.5" stroke-linecap="round" /></svg>
                        <div class="mb-1 text-sm font-bold text-yellow-300">Peak Season</div>
                        <div class="text-xs text-gray-400">June - August</div>
                        <div class="mt-2 text-[10px] text-gray-400">Berries, tomatoes, corn</div>
                    </div>
                    <!-- Fall -->
                    <div data-reveal class="relative flex min-h-[172px] flex-col items-center justify-center rounded-2xl border border-orange-400/30 bg-orange-500/[0.12] p-4 text-center">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 rounded bg-orange-500 px-2 py-0.5 text-[9px] font-bold text-white">FALL</div>
                        <svg aria-hidden="true" class="mb-2 h-8 w-8" viewBox="0 0 32 32" fill="none"><ellipse cx="16" cy="20" rx="12" ry="10" fill="#f97316" /><ellipse cx="16" cy="20" rx="5" ry="10" fill="#ea580c" opacity="0.5" /><path d="M16,10 L16,6" stroke="#22c55e" stroke-width="2" stroke-linecap="round" /><path d="M14,8 Q16,5 18,8" stroke="#22c55e" stroke-width="1.5" fill="none" stroke-linecap="round" /></svg>
                        <div class="mb-1 text-sm font-bold text-orange-300">Harvest Festival</div>
                        <div class="text-xs text-gray-400">September - November</div>
                        <div class="mt-2 text-[10px] text-gray-400">Pumpkins, apples, cider</div>
                    </div>
                    <!-- Winter -->
                    <div data-reveal class="relative flex min-h-[172px] flex-col items-center justify-center rounded-2xl border border-blue-400/30 bg-blue-500/[0.12] p-4 text-center">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 rounded bg-blue-500 px-2 py-0.5 text-[9px] font-bold text-white">WINTER</div>
                        <svg aria-hidden="true" class="mb-2 h-8 w-8" viewBox="0 0 32 32" fill="none" stroke="#60a5fa" stroke-width="1.5" stroke-linecap="round"><line x1="16" y1="4" x2="16" y2="28" /><line x1="6" y1="10" x2="26" y2="22" /><line x1="6" y1="22" x2="26" y2="10" /><line x1="16" y1="4" x2="13" y2="7" /><line x1="16" y1="4" x2="19" y2="7" /><line x1="16" y1="28" x2="13" y2="25" /><line x1="16" y1="28" x2="19" y2="25" /></svg>
                        <div class="mb-1 text-sm font-bold text-blue-300">Holiday Market</div>
                        <div class="text-xs text-gray-400">December - March</div>
                        <div class="mt-2 text-[10px] text-gray-400">Gifts, preserves, wreaths</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all types of <span class="text-gradient-lime">markets</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From weekly farmers markets to holiday pop-ups, Event Schedule fits your market.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Weekly Farmers Markets"
                    description="Recurring market days, vendor lineups, and seasonal produce updates. Build a loyal community of local shoppers."
                    icon-color="lime"
                    blog-slug="for-weekly-farmers-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-lime-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Artisan & Craft Markets"
                    description="Handmade goods, pottery, jewelry, and art. Showcase your makers and attract craft-loving shoppers."
                    icon-color="amber"
                    blog-slug="for-artisan-craft-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Flea Markets & Swap Meets"
                    description="Vintage finds, collectibles, and one-of-a-kind treasures. Let bargain hunters know when you're open."
                    icon-color="orange"
                    blog-slug="for-flea-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Holiday & Seasonal Markets"
                    description="Christmas markets, harvest festivals, and spring pop-ups. Build anticipation for your seasonal events."
                    icon-color="red"
                    blog-slug="for-holiday-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Night Markets"
                    description="Street food, live entertainment, and late-night shopping. Create buzz for your after-dark events."
                    icon-color="violet"
                    blog-slug="for-night-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Specialty Food Markets"
                    description="Organic co-ops, cheese markets, and gourmet food halls. Connect food lovers with local producers."
                    icon-color="emerald"
                    blog-slug="for-specialty-food-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A2.704 2.704 0 003 15.546M9 6v2m3-2v2m3-2v2M9 3h6m-7 8h8a1 1 0 011 1v4a1 1 0 01-1 1H8a1 1 0 01-1-1v-4a1 1 0 011-1z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. How it works                                              -->
    <!-- ============================================================ -->
    @php
        $steps = [
            ['1', 'Set up your market', 'Add your market name, location, and upload your logo. Takes two minutes.'],
            ['2', 'Add your schedule', 'Saturday morning market, Wednesday evening market, special events. Set recurring dates once, or add seasonal events as they come.'],
            ['3', 'Build your community', 'Share your link. Shoppers follow. They get market updates in their inbox - no checking social media required.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    How it <span class="text-gradient-lime">works</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Get your market online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-lime-500 to-green-500 text-2xl font-bold text-white shadow-lg shadow-lime-500/25">
                            {{ $num }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Sub-Schedules" description="Organize events into categories and groups" :url="marketing_url('/features/sub-schedules')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set events to repeat weekly on chosen days" :url="marketing_url('/features/recurring-events')" icon-color="lime">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium text-blue-600 hover:underline dark:text-blue-400">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 7. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-food-trucks-and-vendors', 'Food Trucks & Vendors'], ['/for-breweries-and-wineries', 'Breweries & Wineries'], ['/for-community-centers', 'Community Centers'], ['/for-curators', 'Curators']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium text-blue-600 hover:underline dark:text-blue-400">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. FAQ                                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-lime">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything farmers markets ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for farmers markets?', 'Yes. Event Schedule is free forever for sharing your market schedule, building a shopper following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan.'],
                    ['Can I list vendors and special events for each market day?', 'Yes. Create recurring events for your regular market days and add special events like cooking demos, live music, and seasonal festivals. Use sub-schedules to organize by vendor type, entertainment, or special programming.'],
                    ['How do shoppers find out about market days and events?', 'Shoppers can follow your market\'s schedule and receive email notifications for upcoming events. Embed your calendar on your website, share on social media, or send newsletters with weekly highlights and featured vendors.'],
                    ['Can I manage vendor applications and booth fees?', 'Yes. Use ticketing to manage vendor booth reservations and collect fees through Stripe with zero platform fees. Vendors receive confirmation with QR codes for check-in on market day.'],
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
    <!-- 9. Finale                                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-lime-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(132, 204, 22, 0.3), rgba(132, 204, 22, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-seed absolute inset-0">
                        @foreach ($seeds as [$l, $s, $d, $dl, $op, $col])
                            <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; background: {{ $col }}; --seed-dur: {{ $d }}; --seed-delay: {{ $dl }}; --seed-op: {{ $op }};"></span>
                        @endforeach
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Stop losing shoppers to <span class="text-gradient-lime">outdated flyers</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Email your community directly. Grow your market. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-market" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-lime-600 to-green-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-lime-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-lime-500/40">
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
