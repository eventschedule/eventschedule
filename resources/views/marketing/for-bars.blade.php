<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Bars & Pubs | Fill Your Event Calendar</x-slot>
    <x-slot name="description">Fill your bar's calendar with great events. Email your regulars directly - no algorithm. Sell tickets, accept booking requests. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Bars</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Bars",
        "description": "Fill your bar's calendar with great events. Email your regulars directly. Sell tickets, accept booking requests. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Bars & Pubs"
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
                "name": "How do I let my regulars know what's on this week?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Customers follow your bar's schedule and get your weekly lineup sent straight to their inbox. One click sends the whole week's events - trivia, live music, specials - to everyone who wants to know. No algorithm deciding who sees it."
                }
            },
            {
                "@type": "Question",
                "name": "Can I set up recurring weekly events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up trivia night, live music, karaoke, or any weekly event once and it automatically repeats on your calendar. You can still add one-off specials like tap takeovers or watch parties alongside your recurring lineup."
                }
            },
            {
                "@type": "Question",
                "name": "Do I need to pay to list my bar's events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Event Schedule is free forever for creating and sharing your bar's event calendar. You can upgrade to Pro or Enterprise for ticketing and newsletters, with no platform fees on ticket sales - you only pay Stripe's standard processing fees."
                }
            },
            {
                "@type": "Question",
                "name": "Can bands and musicians request to play at my bar?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Turn on the booking inbox and performers can submit requests directly. You review each one and approve or decline. Approved acts are added to your calendar automatically - no back-and-forth emails needed."
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
        "name": "Event Schedule for Bars & Pubs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Bar Event Management Software",
        "operatingSystem": "Web",
        "description": "Fill your bar's calendar with great events. Email your regulars directly - no paying for Facebook ads. Sell tickets with QR check-in, accept booking requests from performers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Direct newsletter to regulars - no algorithm middleman",
            "Accept booking requests from performers",
            "Recurring weekly events",
            "Watch parties and sports events",
            "Tap takeovers and specials announcements",
            "QR code ticketing with zero platform fees",
            "Event analytics and tracking"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "bar event calendar, bar trivia night schedule, bar live music, bar event management, pub event planning, free bar scheduling",
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
        /* For-bars "On Tap" styles. The shared es-* motion system lives in
           marketing.css; this holds only the amber gradient text and the
           flickering neon OPEN sign. */
        .text-gradient-amber {
            background: linear-gradient(135deg, #f59e0b, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-amber {
            background: linear-gradient(135deg, #fbbf24, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes es-neon-flicker {
            0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
                opacity: 1;
                text-shadow: 0 0 10px #f59e0b, 0 0 20px #f59e0b, 0 0 30px #f59e0b;
                box-shadow: 0 0 10px rgba(245, 158, 11, 0.3), 0 0 20px rgba(245, 158, 11, 0.2);
            }
            20%, 24%, 55% { opacity: 0.7; text-shadow: none; box-shadow: none; }
        }
        .es-neon { animation: es-neon-flicker 3s infinite; }
        .es-neon-text { text-shadow: 0 0 10px #f59e0b, 0 0 20px #f59e0b, 0 0 30px #f59e0b; }
        @media (prefers-reduced-motion: reduce) {
            .es-neon { animation: none; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: on tap                                              -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.42), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(234, 88, 12, 0.4), rgba(234, 88, 12, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <!-- Neon OPEN sign -->
        <div class="es-neon absolute right-8 top-24 z-20 hidden opacity-90 sm:block md:right-16" aria-hidden="true">
            <div class="rounded-lg border-2 border-amber-500 bg-amber-500/10 px-6 py-3 shadow-lg shadow-amber-500/20 dark:border-amber-400">
                <span class="es-neon-text text-2xl font-bold tracking-widest text-amber-600 dark:text-amber-400">OPEN</span>
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Bars, Pubs & Taprooms</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.75rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line"><span class="text-gradient-amber es-gradient-anim">Your bar.</span></span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line">Your crowd.</span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Stop paying to reach your own regulars. Email your regulars directly, announce what's on, and fill your bar - without begging the algorithm.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See what's on tap
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-600 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Create your bar's calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Bar-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Craft Beer', 'Wine Bar', 'Sports Bar', 'Cocktail Lounge', 'Irish Pub', 'Dive Bar', 'Taproom', 'Speakeasy'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-amber-400 to-orange-400"></span>
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
    <section id="features" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-amber-400 to-orange-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Last call for empty nights</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything to keep your bar <span class="text-gradient-amber">packed</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Weekly regulars email (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Newsletter
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Tell your regulars what's on</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">No algorithm. No pay-to-play. Wednesday trivia's back? New band this Friday? One click sends the week's lineup straight to everyone who wants to know.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Skip the algorithm</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Your crowd, your reach</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-amber-300 bg-gradient-to-br from-amber-50 to-orange-50 p-4 dark:border-amber-400/30 dark:from-amber-950 dark:to-orange-950">
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500">
                                                <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                            </div>
                                            <div><div class="text-sm font-medium text-gray-900 dark:text-white">This Week at The Tap Room</div><div class="text-xs text-amber-600 dark:text-amber-300">Sending to 847 followers...</div></div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/10" style="--i: 0;"><span class="h-2 w-2 rounded-full bg-amber-400"></span><span class="text-xs text-gray-600 dark:text-gray-300">Wed - Trivia Night</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/10" style="--i: 1;"><span class="h-2 w-2 rounded-full bg-orange-400"></span><span class="text-xs text-gray-600 dark:text-gray-300">Fri - Live Jazz</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/10" style="--i: 2;"><span class="h-2 w-2 rounded-full bg-yellow-400"></span><span class="text-xs text-gray-600 dark:text-gray-300">Sat - IPA Release Party</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Recurring rhythm -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Recurring Events
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Trivia Wednesdays. Jazz Fridays.</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Bars run on weekly rhythms. Set up recurring events once, they show automatically.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="grid grid-cols-7 gap-1">
                                @foreach ([['M', false], ['T', 'dim'], ['W', true], ['T', 'dim'], ['F', true], ['S', true], ['S', 'dim']] as [$dl, $state])
                                    <div class="text-center">
                                        <div class="mb-1 text-[10px] {{ $state === true ? 'font-medium text-orange-600 dark:text-orange-300' : 'text-gray-500 dark:text-gray-400' }}">{{ $dl }}</div>
                                        <div class="mx-auto h-6 w-6 rounded-full {{ $state === true ? 'bg-orange-500' : ($state === 'dim' ? 'border border-amber-400/50 bg-amber-500/30' : 'bg-gray-100 dark:bg-white/5') }}"></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">Weekly events auto-repeat</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Tap takeovers -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            Specials
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Announce what's pouring</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">New keg? Brewery collab? Whiskey tasting? Let your crowd know what's special this week.</p>
                        <div class="mt-auto flex justify-center gap-3" aria-hidden="true">
                            <div class="relative h-16 w-8 rounded-b-lg rounded-t-full border border-amber-500/50 bg-gradient-to-b from-amber-600 to-amber-800"><div class="absolute -top-2 left-1/2 h-4 w-4 -translate-x-1/2 rounded-full bg-amber-400"></div></div>
                            <div class="relative h-16 w-8 rounded-b-lg rounded-t-full border border-orange-500/50 bg-gradient-to-b from-orange-600 to-orange-800"><div class="absolute -top-2 left-1/2 h-4 w-4 -translate-x-1/2 rounded-full bg-orange-400"></div><div class="absolute -top-4 left-1/2 -translate-x-1/2 rounded bg-emerald-500 px-1.5 py-0.5 text-[8px] font-bold text-white">NEW</div></div>
                            <div class="relative h-16 w-8 rounded-b-lg rounded-t-full border border-yellow-500/50 bg-gradient-to-b from-yellow-600 to-yellow-800"><div class="absolute -top-2 left-1/2 h-4 w-4 -translate-x-1/2 rounded-full bg-yellow-400"></div></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Watch parties (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700 dark:border-green-800/30 dark:bg-green-900/40 dark:text-green-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Watch Parties
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Game on the big screen</h3>
                                <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">NFL Sundays, UFC fight nights, World Cup matches. Your sports fans follow to know what's showing and when.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Live sports</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Watch parties</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Big screen events</span>
                                </div>
                            </div>
                            <div class="flex justify-center" aria-hidden="true">
                                <div class="w-48 rounded-xl border-4 border-gray-300 bg-gray-200 p-4 dark:border-gray-700 dark:bg-[#0f0f14]">
                                    <div class="rounded-lg bg-gradient-to-br from-green-600/30 to-emerald-600/30 p-3 text-center">
                                        <div class="mb-1 text-3xl">&#9917;</div>
                                        <div class="text-sm font-bold text-green-600 dark:text-green-300">LIVE</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Sunday 4pm</div>
                                    </div>
                                    <div class="mt-2 flex justify-center gap-1">
                                        <div class="h-1.5 w-1.5 rounded-full bg-green-500"></div>
                                        <div class="h-1.5 w-1.5 rounded-full bg-gray-400 dark:bg-gray-600"></div>
                                        <div class="h-1.5 w-1.5 rounded-full bg-gray-400 dark:bg-gray-600"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Ticketed events -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Charge at the door</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Comedy night? Special tasting? Sell tickets, scan QR codes at entry. Zero platform fees.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-20 w-20 rounded-xl bg-white p-2 shadow-sm">
                                <div class="h-full w-full bg-contain bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%231f2937%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')]"></div>
                                <div class="absolute inset-x-0 top-2 h-0.5 animate-pulse bg-gradient-to-r from-rose-500 to-cyan-500"></div>
                            </div>
                        </div>
                        <div class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">Scan to check in</div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Booking inbox -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Booking Inbox
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Bands come to you</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Musicians and DJs request to play. Review, approve, book - without the back-and-forth.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="flex items-center gap-3 rounded-xl border border-sky-400/30 bg-sky-500/15 p-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-blue-500 text-xs font-semibold text-white">BT</div>
                                <div class="flex-1"><div class="text-sm font-medium text-gray-900 dark:text-white">Blues Trio</div><div class="text-xs text-sky-600 dark:text-sky-300">Requesting Sat night</div></div>
                                <div class="flex gap-1">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20"><svg aria-hidden="true" class="h-3 w-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500/20"><svg aria-hidden="true" class="h-3 w-3 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Analytics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">See what fills seats</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Which nights draw crowds? What events get shares? Know what's working.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            @foreach ([['Trivia', '90'], ['Live Music', '65'], ['Karaoke', '45']] as [$label, $pct])
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
                                    <div class="h-2 w-20 overflow-hidden rounded-full bg-cyan-500/30">
                                        <div class="h-full rounded-full bg-cyan-400" style="width: {{ $pct }}%;"></div>
                                    </div>
                                </div>
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
    <!-- 3. Your week at a glance                                     -->
    <!-- ============================================================ -->
    @php
        $barWeek = [
            ['Mon', 'Industry Night', '50% off for service workers', 'text-amber-500 dark:text-amber-400', false],
            ['Tue', 'Taco Tuesday', '$2 tacos all night', 'text-orange-500 dark:text-orange-400', false],
            ['Wed', 'Trivia Night', '7pm start', 'text-amber-600 dark:text-amber-300', 'pop'],
            ['Thu', 'Open Mic', 'Sign up 6pm', 'text-blue-500 dark:text-blue-400', false],
            ['Fri', 'Live Music', '9pm-12am', 'text-orange-600 dark:text-orange-300', 'accent'],
            ['Sat', 'DJ Night', '10pm-2am', 'text-rose-600 dark:text-rose-300', 'accent'],
            ['Sun', 'Brunch', 'Bloody Mary bar', 'text-yellow-600 dark:text-yellow-400', false],
        ];
    @endphp
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Your week at a <span class="text-gradient-amber">glance</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Bars run on rhythm. Weekly trivia, Friday bands, Sunday brunch - your regulars know the schedule by heart. Show them what's on, every week.
                </p>
            </div>

            <div class="rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-100 to-orange-100 p-6 dark:border-white/10 dark:from-amber-900/30 dark:to-orange-900/30 md:p-8" data-reveal="panel">
                <div class="grid grid-cols-7 gap-2 md:gap-4" data-reveal-group="60">
                    @foreach ($barWeek as [$day, $name, $detail, $textCls, $state])
                        <div data-reveal class="text-center">
                            <div class="mb-2 text-xs font-medium md:mb-3 md:text-sm {{ $state === 'pop' ? 'font-bold text-amber-600 dark:text-amber-400' : ($state === 'accent' ? 'font-bold ' . $textCls : 'text-gray-600 dark:text-gray-400') }}">{{ $day }}</div>
                            <div class="relative min-h-[80px] rounded-xl p-2 md:min-h-[100px] md:p-3 {{ $state === 'pop' ? 'border border-amber-500/50 bg-gradient-to-br from-amber-500/30 to-orange-500/30' : ($state === 'accent' ? 'border border-orange-300 bg-white/70 dark:border-orange-500/30 dark:bg-white/5' : 'bg-white/60 dark:bg-white/5') }}">
                                @if ($state === 'pop')
                                    <div class="absolute -top-2 right-1 rounded bg-amber-500 px-1.5 py-0.5 text-[8px] font-bold text-white md:right-2 md:text-[9px]">POPULAR</div>
                                @endif
                                <div class="text-[10px] font-semibold md:text-xs {{ $textCls }}">{{ $name }}</div>
                                <div class="mt-1 text-[9px] text-gray-600 dark:text-gray-400 md:text-[10px]">{{ $detail }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all types of <span class="text-gradient-amber">bars</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From craft beer taprooms to cocktail lounges, Event Schedule fits your vibe.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Craft Beer Bars -->
                <x-sub-audience-card
                    name="Craft Beer Bars"
                    description="Tap takeovers, brewery events, and beer release parties. Build a following of craft beer enthusiasts."
                    icon-color="amber"
                    blog-slug="for-craft-beer-bars"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Wine Bars -->
                <x-sub-audience-card
                    name="Wine Bars"
                    description="Wine tastings, vineyard dinners, and sommelier events. Educate and delight your wine-loving guests."
                    icon-color="rose"
                    blog-slug="for-wine-bars"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Sports Bars -->
                <x-sub-audience-card
                    name="Sports Bars"
                    description="Game day watch parties, trivia nights, and UFC events. Let fans know what's on the big screen."
                    icon-color="green"
                    blog-slug="for-sports-bars"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Cocktail Lounges -->
                <x-sub-audience-card
                    name="Cocktail Lounges"
                    description="Mixology classes, speakeasy nights, and cocktail competitions. Attract the craft cocktail crowd."
                    icon-color="violet"
                    blog-slug="for-cocktail-lounges"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Irish & British Pubs -->
                <x-sub-audience-card
                    name="Irish & British Pubs"
                    description="Pub quizzes, live traditional music, and St. Patrick's Day celebrations. Keep the craic alive."
                    icon-color="emerald"
                    blog-slug="for-irish-british-pubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Dive Bars & Neighborhood Bars -->
                <x-sub-audience-card
                    name="Dive Bars & Neighborhood Bars"
                    description="Open mics, karaoke nights, and local band showcases. Your neighborhood's living room."
                    icon-color="slate"
                    blog-slug="for-dive-bars"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. How it works (dark band)                                  -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.28), rgba(245, 158, 11, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 60%, rgba(234, 88, 12, 0.26), rgba(234, 88, 12, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Get your bar's calendar online in <span class="text-gradient-amber">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    @foreach ([['1', 'Set up your bar', 'Add your name, spaces (main bar, patio, back room), and upload your logo. Takes two minutes.'], ['2', 'Add your weekly lineup', 'Trivia night, live music, tap takeovers, watch parties. Set recurring events once, or add one-offs as they come.'], ['3', 'Let regulars follow', 'Share your link. Locals follow. They get the week\'s events in their inbox - no checking Facebook required.']] as [$n, $title, $desc])
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 text-xl font-bold text-white shadow-lg shadow-amber-500/30">{{ $n }}</div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="60">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send event updates directly to followers' inboxes" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-Schedules" description="Organize events into categories and groups" :url="marketing_url('/features/sub-schedules')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Boost" description="Promote events with Facebook and Instagram ads" :url="marketing_url('/features/boost')" icon-color="orange">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg></x-slot:icon>
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
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-music-venues', 'Music Venues'], ['/for-comedy-clubs', 'Comedy Clubs'], ['/for-nightclubs', 'Nightclubs'], ['/for-restaurants', 'Restaurants']] as [$relHref, $relName])
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
    <!-- 8. FAQ                                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-amber">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything bar owners ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['How do I let my regulars know what\'s on this week?', 'Customers follow your bar\'s schedule and get your weekly lineup sent straight to their inbox. One click sends the whole week\'s events - trivia, live music, specials - to everyone who wants to know. No algorithm deciding who sees it.'],
                    ['Can I set up recurring weekly events?', 'Yes. Set up trivia night, live music, karaoke, or any weekly event once and it automatically repeats on your calendar. You can still add one-off specials like tap takeovers or watch parties alongside your recurring lineup.'],
                    ['Do I need to pay to list my bar\'s events?', 'No. Event Schedule is free forever for creating and sharing your bar\'s event calendar. You can upgrade to Pro or Enterprise for ticketing and newsletters, with no platform fees on ticket sales - you only pay Stripe\'s standard processing fees.'],
                    ['Can bands and musicians request to play at my bar?', 'Yes. Turn on the booking inbox and performers can submit requests directly. You review each one and approve or decline. Approved acts are added to your calendar automatically - no back-and-forth emails needed.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-amber-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(245, 158, 11, 0.32), rgba(245, 158, 11, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="es-neon absolute right-8 top-8 z-20 hidden opacity-90 sm:block" aria-hidden="true">
                    <div class="rounded-lg border-2 border-amber-400 bg-amber-500/10 px-4 py-2 shadow-lg shadow-amber-500/20">
                        <span class="es-neon-text text-lg font-bold tracking-widest text-amber-400">OPEN</span>
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Stop paying to reach your <span class="text-gradient-amber">own regulars</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Email your regulars directly. Fill your bar. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-bar" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
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

    <x-marketing.related-pages />
</x-marketing-layout>
