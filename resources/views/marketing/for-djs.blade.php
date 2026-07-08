<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for DJs | Share Your Sets & Bookings</x-slot>
    <x-slot name="description">Share your DJ sets, residencies, and guest spots. Reach fans directly with no promoter middleman. Sell tickets with zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For DJs</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for DJs",
        "description": "Share your DJ sets, residencies, and guest spots. Reach fans directly with no promoter middleman. Zero platform fees on tickets.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "DJs"
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
                "name": "Can I track both residencies and one-off bookings?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up recurring events for your weekly or monthly residencies and they auto-repeat on your schedule. Add guest spots and festival bookings as one-off events. Everything shows up in one clean calendar that fans can follow."
                }
            },
            {
                "@type": "Question",
                "name": "Does it handle late-night sets that cross midnight?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule handles overnight events correctly. A set that starts at 11 PM Saturday and ends at 4 AM Sunday displays properly on the Saturday listing, so fans know when to show up."
                }
            },
            {
                "@type": "Question",
                "name": "What happens when a club adds me to their lineup?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When a club or promoter adds you to their event on Event Schedule, it automatically appears on your schedule. No double-entry needed. Both calendars stay in sync so your fans always see your latest bookings."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell advance tickets to my sets?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect Stripe and sell tickets directly from your schedule with zero platform fees. Each ticket includes a unique QR code for check-in at the door. You keep 100% of the sale minus Stripe's standard processing fees."
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
        "name": "Event Schedule for DJs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "DJ Event Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your DJ sets, residencies, and guest spots. Built for club DJs, festival DJs, and electronic producers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Residency tracking",
            "Late-night event support",
            "Club calendar sync",
            "Promo graphic generator",
            "Zero-fee ticketing",
            "Direct fan newsletters"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "DJ schedule, DJ set times, DJ booking platform, DJ event calendar, DJ gig management, free DJ scheduling",
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
        /* ==============================================================
           For-DJs "The Booth" styles. The shared es-* motion system
           (aurora, reveals, bento, marquee, odometer, finale) lives in
           marketing.css; this block holds only this page's own effects:
           the neon gradient, the spinning vinyl badge, the pulsing
           waveform, and panning club lights.
           ============================================================== */

        .text-gradient-neon {
            background: linear-gradient(135deg, #4E81FA, #0EA5E9, #22D3EE);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .neon-glow {
            box-shadow: 0 0 20px rgba(78, 129, 250, 0.45), 0 0 40px rgba(14, 165, 233, 0.28);
        }

        /* Spinning vinyl badge */
        .es-vinyl {
            position: relative;
            height: 1.5rem;
            width: 1.5rem;
            border-radius: 9999px;
            background: radial-gradient(circle at 50% 50%, #1f2937 0 42%, #0b1220 43% 100%);
            animation: es-spin 6s linear infinite;
        }
        .es-vinyl::before {
            content: "";
            position: absolute;
            inset: 32%;
            border-radius: 9999px;
            background: linear-gradient(135deg, #0EA5E9, #22D3EE);
        }
        .es-vinyl::after {
            content: "";
            position: absolute;
            inset: 46%;
            border-radius: 9999px;
            background: #0b1220;
        }
        @keyframes es-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        /* Pulsing waveform */
        .es-wave {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            height: 100%;
        }
        .es-wave i {
            width: 4px;
            border-radius: 9999px;
            background: linear-gradient(180deg, #22D3EE, #4E81FA);
            transform-origin: center;
            animation: es-wave-bounce 1.1s ease-in-out infinite;
            animation-delay: calc(var(--i, 0) * 0.06s);
        }
        @keyframes es-wave-bounce {
            0%, 100% { transform: scaleY(0.25); }
            45% { transform: scaleY(1); }
            75% { transform: scaleY(0.5); }
        }

        /* Panning club lights (dark surfaces) */
        .es-clublight {
            position: absolute;
            top: -12%;
            width: 46%;
            height: 135%;
            pointer-events: none;
            transform-origin: 50% 0;
            animation: es-club-pan 9s ease-in-out infinite alternate;
        }
        .es-clublight-1 { left: 3%; background: conic-gradient(from 197deg at 50% 0%, transparent 0deg, rgba(14, 165, 233, 0.15) 11deg, transparent 24deg); }
        .es-clublight-2 { right: 3%; background: conic-gradient(from 149deg at 50% 0%, transparent 0deg, rgba(78, 129, 250, 0.14) 11deg, transparent 24deg); animation-delay: -3.5s; animation-duration: 11s; }
        .es-clublight-3 { left: 33%; background: conic-gradient(from 178deg at 50% 0%, transparent 0deg, rgba(34, 211, 238, 0.11) 9deg, transparent 20deg); animation-delay: -6s; animation-duration: 13s; }
        @keyframes es-club-pan { from { transform: rotate(-7deg); } to { transform: rotate(7deg); } }

        @media (prefers-reduced-motion: reduce) {
            .es-vinyl,
            .es-wave i,
            .es-clublight {
                animation: none !important;
            }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: step up to the booth                                -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(14, 165, 233, 0.55), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(78, 129, 250, 0.5), rgba(78, 129, 250, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <!-- Waveform along the base -->
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 opacity-30 [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" aria-hidden="true">
            <div class="es-wave">
                @for ($i = 0; $i < 80; $i++)
                    <i style="--i: {{ $i % 12 }}; height: {{ 20 + (($i * 37) % 60) }}%;"></i>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="es-vinyl" aria-hidden="true"></span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For DJs & Electronic Producers</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Fill the dancefloor.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-neon es-gradient-anim">Skip the algorithm.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Your residencies. Your guest spots. One link. Direct to fans - no promoter middleman, no pay-to-play socials.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the setup
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group neon-glow pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                    Create your DJ schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Genre marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($genreCopy = 0; $genreCopy < 2; $genreCopy++)
                                @foreach (['House', 'Techno', 'DnB', 'Trance', 'Dubstep', 'Garage', 'Minimal', 'Electro', 'Breakbeat', 'Disco', 'Acid', 'Hardgroove'] as $genre)
                                    <span @if ($genreCopy === 1) aria-hidden="true" @endif class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-sky-400 to-blue-400" aria-hidden="true"></span>
                                        {{ $genre }}
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
    <!-- 2. Bento features: the whole rig                             -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-sky-400 to-blue-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">The whole rig</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything that keeps you <span class="text-gradient-neon">on the bill</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Residency Tracker (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Residency Tracker
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Track your residencies & guest spots</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Weekly residency at Output? Monthly at Basement? One schedule shows everything - your regular slots AND one-off bookings.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Weekly residencies</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Guest spots</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">One-off bookings</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-sky-300 bg-gradient-to-br from-sky-50 to-blue-50 p-4 shadow-lg dark:border-sky-400/30 dark:from-sky-950 dark:to-blue-950">
                                        <div class="mb-3 text-xs font-semibold text-sky-600 dark:text-sky-300">DECEMBER</div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-sky-400/20 bg-sky-500/15 p-2" style="--i: 0;">
                                                <div class="w-12 text-xs font-bold text-sky-600 dark:text-sky-300">FRI 6</div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Fabric</div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">Every Friday</div>
                                                </div>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-blue-400/20 bg-blue-500/15 p-2" style="--i: 1;">
                                                <div class="w-12 text-xs font-bold text-blue-600 dark:text-blue-300">SAT 14</div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Berghain</div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">Guest spot</div>
                                                </div>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-sky-400/20 bg-sky-500/15 p-2" style="--i: 2;">
                                                <div class="w-12 text-xs font-bold text-sky-600 dark:text-sky-300">FRI 20</div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">Fabric</div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">Every Friday</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Late Night Ready (1 col) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            Late Night
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Built for late nights</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Sets that start at 11 PM and end at 6 AM? No problem. We handle overnight events correctly.</p>

                        <div class="mt-auto rounded-xl border border-blue-300/50 bg-blue-50 p-4 text-center dark:border-blue-400/30 dark:bg-blue-500/10" aria-hidden="true">
                            <svg aria-hidden="true" class="mx-auto mb-2 h-5 w-5 text-blue-500 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                            <div class="text-2xl font-black text-gray-900 dark:text-white">11 PM - 4 AM</div>
                            <div class="mt-1 text-xs text-blue-600 dark:text-blue-300">Saturday into Sunday</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Club Sync (1 col) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Club Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Clubs book you, fans know instantly</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">When promoters add you to their lineup, it auto-appears on your schedule. No double-entry.</p>

                        <div class="relative mt-auto flex items-center justify-center gap-8 py-2" aria-hidden="true">
                            <div class="w-20 rounded-lg border border-sky-400/30 bg-sky-500/15 p-2">
                                <div class="mb-1 text-center text-[10px] font-medium text-sky-600 dark:text-sky-300">Club</div>
                                <div class="mb-1 h-1.5 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                <div class="h-1.5 w-3/4 rounded bg-sky-400/40"></div>
                            </div>
                            <div class="absolute left-1/2 top-1/2 h-px w-10 -translate-x-1/2 -translate-y-1/2 border-t border-dashed border-sky-300 dark:border-sky-500/40"></div>
                            <div class="es-sync-dot" style="left: calc(50% - 24px);"></div>
                            <div class="w-20 rounded-lg border border-gray-300 bg-gray-100 p-2 dark:border-white/20 dark:bg-white/10">
                                <div class="mb-1 text-center text-[10px] font-medium text-gray-600 dark:text-gray-300">You</div>
                                <div class="mb-1 h-1.5 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                <div class="h-1.5 w-3/4 rounded bg-sky-400/40"></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- One Link Everywhere (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    Share Link
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">One link for Linktree, RA, SoundCloud</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Drop your schedule link anywhere. Fans see all upcoming sets - residencies, festivals, guest spots.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Your schedule link</div>
                                <div class="flex items-center gap-2 rounded-xl border border-blue-400/30 bg-blue-500/15 p-3" dir="ltr">
                                    <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    <span class="truncate font-mono text-sm text-gray-900 dark:text-white">djnova.eventschedule.com</span>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <div class="flex-1 rounded-lg bg-gray-100 p-2 text-center text-xs font-medium text-blue-600 dark:bg-white/5 dark:text-blue-300">Resident Advisor</div>
                                    <div class="flex-1 rounded-lg bg-gray-100 p-2 text-center text-xs font-medium text-blue-600 dark:bg-white/5 dark:text-blue-300">SoundCloud</div>
                                    <div class="flex-1 rounded-lg bg-gray-100 p-2 text-center text-xs font-medium text-blue-600 dark:bg-white/5 dark:text-blue-300">Mixcloud</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Newsletter to Dancefloor (1 col) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Newsletter
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Email the dancefloor directly</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">No algorithm throttling. One click sends set graphics to everyone who wants to come dance.</p>

                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="relative w-24 rounded-lg border border-cyan-400/30 bg-cyan-500/15 p-3">
                                <svg aria-hidden="true" class="mx-auto mb-1 h-6 w-6 text-cyan-500 dark:text-cyan-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <div class="mb-1 h-1 w-full rounded bg-cyan-400/40"></div>
                                <div class="h-1 w-3/4 rounded bg-cyan-400/30"></div>
                            </div>
                            <svg aria-hidden="true" class="h-4 w-4 animate-pulse-slow text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                            <div class="flex -space-x-1">
                                <div class="h-6 w-6 rounded-full border border-white bg-gradient-to-br from-cyan-500 to-blue-500 dark:border-[#15151c]"></div>
                                <div class="h-6 w-6 rounded-full border border-white bg-gradient-to-br from-blue-500 to-sky-500 dark:border-[#15151c]"></div>
                                <div class="h-6 w-6 rounded-full border border-white bg-gradient-to-br from-sky-500 to-cyan-500 dark:border-[#15151c]"></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Set Graphics (1 col) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Promo flyers, auto-generated</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Download graphics sized for Instagram stories, RA event pages, WhatsApp groups.</p>

                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-36 w-28 -rotate-3 rounded-xl border border-amber-400/30 bg-gradient-to-br from-amber-500/25 to-orange-500/25 p-2 transition-transform duration-300 group-hover:rotate-0">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br from-sky-600/60 to-blue-600/60">
                                    <div class="mb-1 text-[8px] font-semibold text-white">THIS SATURDAY</div>
                                    <div class="text-sm font-bold text-amber-300">DJ NOVA</div>
                                    <div class="mt-1 text-[10px] text-white">@ FABRIC</div>
                                    <div class="mt-1 text-[8px] text-white/80">11 PM - 4 AM</div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500">
                                    <svg aria-hidden="true" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Zero Fees (1 col) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Your door, your money</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Sell advance tickets with zero platform cut. Stripe pays you direct.</p>

                        <div class="mx-auto mt-auto w-full max-w-[200px] rounded-xl border border-emerald-300/50 bg-emerald-50 p-4 dark:border-emerald-400/30 dark:bg-emerald-500/10" aria-hidden="true">
                            <div class="mb-3 text-center">
                                <div class="text-xs text-emerald-600 dark:text-emerald-300">Platform fee</div>
                                <div class="es-od justify-center text-3xl font-black text-gray-900 dark:text-white" data-odometer="$0">$0</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">You keep 100%</div>
                            </div>
                            <div class="border-t border-emerald-300/40 pt-3 dark:border-emerald-400/20">
                                <div class="flex items-center justify-center gap-1">
                                    <svg aria-hidden="true" class="h-4 w-4 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-xs text-emerald-600 dark:text-emerald-300">Direct to your Stripe</span>
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
    <!-- 3. Perfect for                                               -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Whether you spin vinyl or <span class="text-gradient-neon">push buttons</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Event Schedule works for every type of DJ.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                <div data-reveal>
                    <x-sub-audience-card
                        name="Resident DJs"
                        description="Track your weekly slots and build loyal locals who know exactly where to find you."
                        icon-color="indigo"
                        blog-slug="for-resident-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Touring DJs"
                        description="Share your international dates with fans worldwide. They'll know when you're in their city."
                        icon-color="purple"
                        blog-slug="for-touring-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="B2B Partners"
                        description="Show joint sets and collaborations. Both schedules stay synced automatically."
                        icon-color="fuchsia"
                        blog-slug="for-b2b-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Underground DJs"
                        description="Warehouse parties, afters, secret locations. Share with your inner circle only."
                        icon-color="violet"
                        blog-slug="for-underground-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Open Format DJs"
                        description="Weddings, corporate gigs, private events. Keep your public and private bookings organized."
                        icon-color="pink"
                        blog-slug="for-open-format-djs"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Producers"
                        description="Live sets, album launches, listening parties. Show fans where to hear your music live."
                        icon-color="amber"
                        blog-slug="for-dj-producers"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. How it works: in the booth (dark)                         -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-clublight es-clublight-1"></div>
                <div class="es-clublight es-clublight-2"></div>
                <div class="es-clublight es-clublight-3"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-9 opacity-40 [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" aria-hidden="true">
                <div class="es-wave">
                    @for ($i = 0; $i < 60; $i++)
                        <i style="--i: {{ $i % 12 }}; height: {{ 25 + (($i * 43) % 55) }}%;"></i>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="es-vinyl h-4 w-4" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">In the booth</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Get your set schedule online in <span class="text-gradient-neon">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-blue-500 text-xl font-bold text-white shadow-lg shadow-sky-500/30">1</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Add your sets</h3>
                        <p class="text-sm text-gray-400">Import from Google Cal or add manually. Residencies auto-repeat weekly or monthly.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-blue-500 text-xl font-bold text-white shadow-lg shadow-sky-500/30">2</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Drop your link</h3>
                        <p class="text-sm text-gray-400">Add to your RA profile, Linktree, SoundCloud bio. Anywhere fans find you.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-blue-500 text-xl font-bold text-white shadow-lg shadow-sky-500/30">3</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Pack the dancefloor</h3>
                        <p class="text-sm text-gray-400">Fans follow you, get notified when you're spinning, and show up ready to dance.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Sell tickets with QR check-in and zero platform fees"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Newsletters"
                        description="Send event updates directly to followers' inboxes"
                        :url="marketing_url('/features/newsletters')"
                        icon-color="green"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
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
    <!-- 6. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-musicians', 'Musicians'], ['/for-nightclubs', 'Nightclubs'], ['/for-bars', 'Bars'], ['/for-live-concerts', 'Live Concerts']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
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
    <!-- 7. FAQ                                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-neon">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything DJs ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Can I track both residencies and one-off bookings?', 'Yes. Set up recurring events for your weekly or monthly residencies and they auto-repeat on your schedule. Add guest spots and festival bookings as one-off events. Everything shows up in one clean calendar that fans can follow.'],
                    ['Does it handle late-night sets that cross midnight?', 'Yes. Event Schedule handles overnight events correctly. A set that starts at 11 PM Saturday and ends at 4 AM Sunday displays properly on the Saturday listing, so fans know when to show up.'],
                    ['What happens when a club adds me to their lineup?', 'When a club or promoter adds you to their event on Event Schedule, it automatically appears on your schedule. No double-entry needed. Both calendars stay in sync so your fans always see your latest bookings.'],
                    ['Can I sell advance tickets to my sets?', 'Yes. Connect Stripe and sell tickets directly from your schedule with zero platform fees. Each ticket includes a unique QR code for check-in at the door. You keep 100% of the sale minus Stripe\'s standard processing fees.'],
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
    <!-- 8. Finale: stop posting into the void                        -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-gray-50 px-2 py-16 dark:bg-[#0f0f14] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-sky-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-clublight es-clublight-1"></div>
                    <div class="es-clublight es-clublight-2"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 opacity-40 [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" aria-hidden="true">
                    <div class="es-wave">
                        @for ($i = 0; $i < 60; $i++)
                            <i style="--i: {{ $i % 12 }}; height: {{ 25 + (($i * 51) % 55) }}%;"></i>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Stop posting <span class="text-gradient-neon">into the void.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Your fans want to dance. Tell them where. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="dj-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-sky-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
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
