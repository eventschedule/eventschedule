<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Nightclubs | Fill Your Event Calendar</x-slot>
    <x-slot name="description">Fill your club with DJ lineups and themed nights. Email your crowd directly - no algorithm. Sell tickets, accept DJ bookings. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Nightclubs</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Nightclubs",
        "description": "Fill your club with DJ lineups and themed nights. Email your crowd directly. Sell tickets, accept DJ bookings. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Nightclubs"
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
                "name": "Is Event Schedule free for nightclubs?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your lineup, building a following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I promote DJ lineups and themed nights?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Create events with detailed descriptions, flyer images, and lineup information. Use sub-schedules to organize by night type - EDM Fridays, Latin Saturdays, or themed events. Each event can have its own ticket tiers for VIP, general admission, and bottle service."
                }
            },
            {
                "@type": "Question",
                "name": "How do partygoers find out about upcoming events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your club's schedule and get email notifications when new events are added. Share your schedule link on social media, embed it on your website, or send newsletters directly to followers with upcoming lineups."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets with different tiers like VIP and general admission?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Create multiple ticket types per event with different prices and quantities. Connect your Stripe account and sell directly - Event Schedule charges zero platform fees. Every ticket includes a QR code for door check-in."
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
        "name": "Event Schedule for Nightclubs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Nightclub Event Management Software",
        "operatingSystem": "Web",
        "description": "Fill your club's calendar with DJ lineups and themed nights. Email your crowd directly - no paying for ads. Sell tickets with QR check-in, accept booking requests from DJs.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Weekly lineup blast newsletter",
            "Guest list signups and management",
            "VIP table and bottle service requests",
            "DJ booking inbox with SoundCloud links",
            "Auto-generate lineup graphics for social",
            "Ticketed events with QR check-in",
            "Themed night scheduling"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "nightclub event calendar, club night schedule, DJ booking management, nightclub event software, free nightclub scheduling",
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

    <!-- ============================================================ -->
    <!-- 1. Hero: tonight's lineup                                    -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 32%, rgba(34, 211, 238, 0.4), rgba(34, 211, 238, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 42%, rgba(14, 165, 233, 0.42), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Nightclubs & Dance Venues</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line"><span class="text-gradient es-gradient-anim">Pack the dancefloor.</span></span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line">Own your crowd.</span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Stop paying to reach your own followers. Email your crowd directly, announce your lineups, and fill your club - without the algorithm getting in the way.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the lineup
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-cyan-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-cyan-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                    Create your club's calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Genre marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['EDM', 'Hip-Hop', 'Latin', 'Rooftop', 'Underground', 'Lounge', 'Techno', 'House'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-cyan-400 to-sky-400"></span>
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
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-cyan-400 to-sky-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Everything after dark</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything to fill the <span class="text-gradient">floor</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Weekly lineup blast (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Weekly Lineup Blast
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Drop your weekend lineup</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Thursday rolls around, you blast out the weekend's DJs to everyone who follows you. No algorithm. No pay-to-play. Just your crowd, hyped and ready.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">One-click send</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Your crowd, direct reach</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-cyan-300 bg-gradient-to-br from-cyan-50 to-sky-50 p-4 dark:border-cyan-400/30 dark:from-cyan-950 dark:to-sky-950">
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-sky-500">
                                                <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                            </div>
                                            <div><div class="text-sm font-medium text-gray-900 dark:text-white">This Weekend at Vortex</div><div class="text-xs text-cyan-600 dark:text-cyan-300">Sending to 2,341 followers...</div></div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-cyan-100 p-2 dark:bg-white/10" style="--i: 0;"><span class="h-2 w-2 rounded-full bg-sky-500 dark:bg-sky-400"></span><span class="text-xs text-gray-600 dark:text-gray-300">Thu - Industry Night (free entry)</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-cyan-100 p-2 dark:bg-white/10" style="--i: 1;"><span class="h-2 w-2 rounded-full bg-cyan-500 dark:bg-cyan-400"></span><span class="text-xs text-gray-600 dark:text-gray-300">Fri - DJ Nova (House)</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-cyan-100 p-2 dark:bg-white/10" style="--i: 2;"><span class="h-2 w-2 rounded-full bg-blue-500 dark:bg-blue-400"></span><span class="text-xs text-gray-600 dark:text-gray-300">Sat - Latin Nights with DJ Fuego</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Guest list -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            Guest List
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Get on the list</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Fans sign up for your guest list right from your calendar. Reduced cover before 11pm - the status thing that fills your early crowd.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-emerald-400/30 bg-emerald-500/15 p-2" style="--i: 0;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 text-[10px] font-semibold text-white">MR</div>
                                <div class="flex-1 text-sm text-gray-900 dark:text-white">Marco R. +3</div>
                                <span class="inline-flex items-center rounded bg-emerald-300 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-500/30 dark:text-emerald-300">Confirmed</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 text-[10px] font-semibold text-white">SK</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Sarah K. +1</div>
                                <span class="inline-flex items-center rounded bg-emerald-300 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-500/30 dark:text-emerald-300">Confirmed</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Themed nights -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Branded Nights
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Your signature nights</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Latin Fridays. 80s Saturdays. Techno Sundays. Set them up once - they show automatically every week. Build the brand.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="grid grid-cols-7 gap-1">
                                @foreach ([['M', ''], ['T', ''], ['W', ''], ['T', 'bg-sky-500'], ['F', 'bg-orange-500'], ['S', 'bg-cyan-500'], ['S', 'bg-blue-500']] as [$dl, $dot])
                                    <div class="text-center">
                                        <div class="mb-1 text-[10px] {{ $dot ? 'font-medium text-gray-700 dark:text-gray-300' : 'text-gray-500 dark:text-gray-400' }}">{{ $dl }}</div>
                                        <div class="mx-auto h-6 w-6 rounded-full {{ $dot ?: 'bg-gray-200 dark:bg-white/5' }}"></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">Color-coded. Repeating. Memorable.</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- VIP tables (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                    VIP & Bottle Service
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Table bookings, handled</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">VIP table requests, bottle service inquiries, birthday packages - all in one inbox. See party size, minimum spend, special requests. Your biggest revenue driver, organized.</p>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Table reservations</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Bottle service</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Birthday packages</span>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">VIP Requests - Saturday</div>
                                <div class="space-y-2">
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg border border-amber-400/30 bg-amber-500/15 p-3" style="--i: 0;">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-[10px] font-semibold text-white">MR</div>
                                        <div class="flex-1"><div class="text-sm font-medium text-gray-900 dark:text-white">Marco R.</div><div class="text-xs text-amber-600 dark:text-amber-300">Table for 8 &bull; $500 min</div></div>
                                        <span class="inline-flex items-center rounded bg-amber-500/30 px-2 py-0.5 text-[10px] font-medium text-amber-600 dark:text-amber-300">VIP</span>
                                    </div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-3 dark:bg-white/5" style="--i: 1;">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-red-500 text-[10px] font-semibold text-white">JT</div>
                                        <div class="flex-1"><div class="text-sm font-medium text-gray-600 dark:text-gray-300">James T.</div><div class="text-xs text-gray-500 dark:text-gray-400">Birthday party &bull; 12 guests</div></div>
                                        <span class="inline-flex items-center rounded bg-cyan-500/30 px-2 py-0.5 text-[10px] font-medium text-cyan-600 dark:text-cyan-300">BDAY</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- DJ inbox -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            DJ Inbox
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">DJs come to you</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Artists submit to play your club. See their SoundCloud, genre, and past gigs. Accept or pass - no endless email threads.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="flex items-center gap-3 rounded-xl border border-sky-400/30 bg-sky-500/15 p-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-blue-500 text-xs font-semibold text-white">KR</div>
                                <div class="flex-1"><div class="text-sm font-medium text-gray-900 dark:text-white">DJ Kira</div><div class="text-[10px] text-sky-600 dark:text-sky-300">Tech House &bull; SoundCloud linked</div></div>
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

                <!-- Lineup graphics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Social Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Insta-ready lineups</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate lineup graphics for Instagram stories and posts. Your DJs, your branding, ready to share.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative">
                                <div class="h-36 w-28 rounded-xl border border-blue-400/30 bg-gradient-to-br from-blue-600 to-blue-700 p-3 shadow-xl">
                                    <div class="mb-1 text-[8px] font-bold text-blue-200">SATURDAY</div>
                                    <div class="mb-2 text-[6px] text-blue-300/70">CLUB VORTEX</div>
                                    <div class="space-y-1">
                                        <div class="rounded bg-white/20 px-1 py-0.5 text-[6px] font-medium text-white">DJ NOVA</div>
                                        <div class="rounded bg-white/10 px-1 py-0.5 text-[6px] text-blue-200">MAX LUNA</div>
                                        <div class="rounded bg-white/10 px-1 py-0.5 text-[6px] text-blue-200">KIRA B2B ECHO</div>
                                    </div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-sky-500 shadow-lg">
                                    <svg aria-hidden="true" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Sold out Saturdays -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sold out Saturdays</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">NYE. Headliner shows. Theme party takeovers. Sell tickets, scan QR codes at the door. Zero platform fees.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-rose-400/30 bg-rose-500/15 p-3">
                                <div class="mb-1 text-[10px] font-semibold text-rose-600 dark:text-rose-300">NYE {{ date('Y') + 1 }}</div>
                                <div class="flex items-center gap-2"><span class="text-sm font-bold text-gray-900 dark:text-white">342</span><span class="text-xs text-gray-500 dark:text-gray-400">tickets sold</span></div>
                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-rose-500/30"><div class="h-full w-[85%] rounded-full bg-gradient-to-r from-rose-500 to-cyan-500"></div></div>
                                <div class="mt-1 text-[10px] text-rose-600 dark:text-rose-300">Almost sold out</div>
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
    <!-- 3. The club weekend                                          -->
    <!-- ============================================================ -->
    @php
        $clubWeekend = [
            ['THU', 'Industry Night', 'Service industry', 'sky', ['Lower cover / free entry', 'Late night crowd arrives late', 'Resident DJs on rotation']],
            ['FRI', 'Themed Night', 'Latin, Hip-Hop, 80s', 'orange', ['Branded recurring night', 'Loyal theme-specific crowd', 'Guest list before 11pm']],
            ['SAT', 'Headliner Night', 'Touring DJ, premium', 'cyan', ['Premium pricing / ticketed', 'VIP tables sell out', 'Guest headliner draw']],
        ];
    @endphp
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    The club <span class="text-gradient">weekend</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Clubs run on a rhythm. Thursday builds momentum, Friday brings the theme, Saturday's the headliner. Show your crowd what's coming.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ($clubWeekend as [$day, $title, $sub, $color, $points])
                    <div data-reveal class="rounded-2xl border p-6 transition-all hover:-translate-y-1 hover:shadow-lg
                        {{ $color === 'sky' ? 'border-sky-200 bg-gradient-to-br from-sky-100 to-blue-100 dark:border-sky-500/30 dark:from-sky-900/40 dark:to-blue-900/40' : ($color === 'orange' ? 'border-orange-200 bg-gradient-to-br from-orange-100 to-amber-100 dark:border-orange-500/30 dark:from-orange-900/40 dark:to-amber-900/40' : 'border-cyan-200 bg-gradient-to-br from-cyan-100 to-rose-100 dark:border-cyan-500/30 dark:from-cyan-900/40 dark:to-rose-900/40') }}">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $color === 'sky' ? 'bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-400' : ($color === 'orange' ? 'bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400' : 'bg-cyan-100 text-cyan-600 dark:bg-cyan-500/20 dark:text-cyan-400') }} text-lg font-bold">{{ $day }}</div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                                <p class="text-sm {{ $color === 'sky' ? 'text-sky-600 dark:text-sky-300' : ($color === 'orange' ? 'text-orange-600 dark:text-orange-300' : 'text-cyan-600 dark:text-cyan-300') }}">{{ $sub }}</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            @foreach ($points as $point)
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-1.5 rounded-full {{ $color === 'sky' ? 'bg-sky-400' : ($color === 'orange' ? 'bg-orange-400' : 'bg-cyan-400') }}"></div>
                                    <span>{{ $point }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
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
                    Perfect for all types of <span class="text-gradient">clubs</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From underground warehouses to rooftop lounges, Event Schedule fits your vibe.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Dance Clubs & EDM Venues -->
                <x-sub-audience-card
                    name="Dance Clubs & EDM Venues"
                    description="House, techno, trance crowds. Big rooms, bigger sound systems, and lineups that matter."
                    icon-color="pink"
                    blog-slug="for-dance-clubs-edm"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Hip-Hop & Urban Clubs -->
                <x-sub-audience-card
                    name="Hip-Hop & Urban Clubs"
                    description="Hip-hop nights, R&B showcases, urban music events. Build your scene's go-to spot."
                    icon-color="fuchsia"
                    blog-slug="for-hip-hop-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Latin Clubs -->
                <x-sub-audience-card
                    name="Latin Clubs"
                    description="Salsa, bachata, reggaeton communities. Themed nights that keep dancers coming back."
                    icon-color="orange"
                    blog-slug="for-latin-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Rooftop Clubs -->
                <x-sub-audience-card
                    name="Rooftop Clubs"
                    description="Sunset sessions, seasonal programming, skyline views. Weather-dependent vibes done right."
                    icon-color="violet"
                    blog-slug="for-rooftop-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Underground & Warehouse Venues -->
                <x-sub-audience-card
                    name="Underground & Warehouse"
                    description="Intimate sets, warehouse parties, curated crowds. Where the real heads gather."
                    icon-color="indigo"
                    blog-slug="for-underground-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- VIP Lounges -->
                <x-sub-audience-card
                    name="VIP Lounges"
                    description="Bottle service focused, upscale nightlife, exclusive atmosphere. Premium experiences only."
                    icon-color="amber"
                    blog-slug="for-vip-lounges"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Stream to the world                                       -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" data-reveal="panel" class="es-bento group block">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                Online Events
                            </div>
                            <h3 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-cyan-600 dark:text-white dark:group-hover:text-cyan-400 lg:text-3xl">Stream to the world</h3>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Broadcast your DJ sets worldwide. Sell tickets to viewers anywhere - no capacity limits.</p>
                            <div class="mb-4 flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Twitch</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">YouTube Live</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Zoom</span>
                            </div>
                            <span class="inline-flex items-center gap-2 font-medium text-cyan-600 transition-all group-hover:gap-3 dark:text-cyan-400">
                                Learn more
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="w-52 rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-300">Live Stream</span>
                                    <div class="flex items-center gap-1"><div class="h-2 w-2 animate-pulse rounded-full bg-red-500"></div><span class="text-xs font-medium text-red-500">LIVE</span></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-blue-500"></div><span class="text-xs text-gray-600 dark:text-gray-300">2,341 watching</span></div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-green-500"></div><span class="text-xs text-gray-600 dark:text-gray-300">Global audience</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="es-glare" aria-hidden="true"></div>
                    <div class="es-ring-glow" aria-hidden="true"></div>
                </div>
            </a>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. How it works (dark band)                                  -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="opacity: 0.28;"></div>
                <div class="es-aurora es-aurora-2" style="opacity: 0.22;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Get your club's calendar online in <span class="text-gradient">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    @foreach ([['1', 'Set up your club', 'Add your name, rooms (main floor, rooftop, VIP), and upload your logo. Takes two minutes.'], ['2', 'Add your lineup', 'Resident DJs, guest headliners, themed nights. Set recurring events or add one-offs as bookings come in.'], ['3', 'Let your crowd follow', 'Share your link. Clubbers follow. They get the week\'s lineup in their inbox - no checking Instagram required.']] as [$n, $title, $desc])
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-sky-500 text-xl font-bold text-white shadow-lg shadow-cyan-500/30">{{ $n }}</div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
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
    <!-- 8. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-music-venues', 'Music Venues'], ['/for-bars', 'Bars'], ['/for-djs', 'DJs'], ['/for-venues', 'Venues']] as [$relHref, $relName])
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
    <!-- 9. FAQ                                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything nightclub owners ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for nightclubs?', 'Yes. Event Schedule is free forever for sharing your lineup, building a following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I promote DJ lineups and themed nights?', 'Yes. Create events with detailed descriptions, flyer images, and lineup information. Use sub-schedules to organize by night type - EDM Fridays, Latin Saturdays, or themed events. Each event can have its own ticket tiers for VIP, general admission, and bottle service.'],
                    ['How do partygoers find out about upcoming events?', 'Fans can follow your club\'s schedule and get email notifications when new events are added. Share your schedule link on social media, embed it on your website, or send newsletters directly to followers with upcoming lineups.'],
                    ['Can I sell tickets with different tiers like VIP and general admission?', 'Yes. Create multiple ticket types per event with different prices and quantities. Connect your Stripe account and sell directly - Event Schedule charges zero platform fees. Every ticket includes a QR code for door check-in.'],
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
    <!-- 10. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-cyan-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="opacity: 0.3;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your crowd. Direct reach. <span class="text-gradient">Pack the floor.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Email your crowd directly. Fill your dancefloor. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-club" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-cyan-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
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
