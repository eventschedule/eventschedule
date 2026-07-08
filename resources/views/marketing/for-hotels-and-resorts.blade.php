<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Hotels & Resorts | Guest Activities</x-slot>
    <x-slot name="description">Elevate the guest experience. Share your activity calendar, sell tickets to special events, and keep guests engaged. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Hotels & Resorts</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Hotels & Resorts",
        "description": "Elevate the guest experience. Share your activity calendar, sell tickets to special events, and keep guests engaged. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Hotels & Resorts"
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
                "name": "Is Event Schedule free for hotels and resorts?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your activity calendar, building a guest following, and syncing with Google Calendar. Advanced features and newsletters are available on the Pro plan."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage guest activities, entertainment, and conferences together?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by category - pool activities, spa sessions, live entertainment, kids clubs, dining events, and conference schedules. Guests see a unified calendar of everything happening at your property."
                }
            },
            {
                "@type": "Question",
                "name": "How do guests discover activities during their stay?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Share your activity calendar via QR codes at check-in, embed it on your hotel website, or include the link in pre-arrival emails. Guests can also follow your schedule and receive notifications for new activities."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to special events and experiences?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account to sell tickets for special dinners, spa packages, excursions, and entertainment shows. Create different pricing for hotel guests and external visitors. Zero platform fees on all sales."
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
        "name": "Event Schedule for Hotels & Resorts",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Hotel and Resort Activity Management Software",
        "operatingSystem": "Web",
        "description": "Elevate the guest experience. Share your activity calendar, sell tickets to special events, and keep guests engaged. No platform fees. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Guest activity newsletters",
            "Multi-space management (pool, ballroom, spa, restaurant)",
            "Zero-fee ticketing for special experiences",
            "Full weekly activity planner",
            "Google Calendar two-way sync",
            "Team access for concierge and event staff",
            "Activity engagement analytics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "hotel activity calendar, resort event schedule, guest activity management, hotel entertainment calendar, free hotel scheduling",
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
        /* For-hotels-and-resorts "The Concierge" styles. The shared es-* motion
           system lives in marketing.css; this holds the slate-gold gradient,
           the brass badge, the gold foil shimmer line, the drifting activity
           card, and the twinkling gold-dust motif. */
        .text-gradient-slate-gold {
            background: linear-gradient(135deg, #64748b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-slate-gold {
            background: linear-gradient(135deg, #94a3b8, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hotel-brass-badge {
            border: 1.5px solid rgba(217, 119, 6, 0.35);
            box-shadow: inset 0 0 6px rgba(217, 119, 6, 0.1), 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .dark .hotel-brass-badge {
            border-color: rgba(245, 158, 11, 0.3);
            box-shadow: inset 0 0 8px rgba(245, 158, 11, 0.08), 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        .hotel-shimmer-line {
            background: linear-gradient(90deg, transparent, #d97706, transparent);
            position: relative;
            overflow: hidden;
        }
        .hotel-shimmer-line::after {
            content: '';
            position: absolute;
            top: -1px;
            left: -100%;
            width: 60%;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            animation: es-shimmer 5s ease-in-out infinite;
        }
        .dark .hotel-shimmer-line::after {
            background: linear-gradient(90deg, transparent, rgba(251, 191, 36, 0.4), transparent);
        }
        @keyframes es-shimmer {
            0% { left: -100%; }
            100% { left: 200%; }
        }
        @keyframes es-activity-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-activity-float { animation: es-activity-float 6s ease-in-out infinite; }

        /* Twinkling gold dust */
        .es-gold-dust span {
            position: absolute;
            border-radius: 9999px;
            background: radial-gradient(circle, rgba(251, 191, 36, 0.95), rgba(217, 119, 6, 0.3));
            opacity: 0;
            animation: es-twinkle var(--tw-dur, 4s) ease-in-out infinite;
            animation-delay: var(--tw-delay, 0s);
        }
        @keyframes es-twinkle {
            0%, 100% { opacity: 0; transform: scale(0.4); }
            50% { opacity: var(--tw-op, 0.7); transform: scale(1); }
        }
        @media (prefers-reduced-motion: reduce) {
            .hotel-shimmer-line::after, .es-activity-float, .es-gold-dust span { animation: none !important; }
            .es-gold-dust span { opacity: 0.35; transform: scale(0.7); }
        }
    </style>

    @php
        // Twinkling gold dust: [left, top, size(px), duration, delay, opacity]
        $gold = [
            ['12%', '22%', 4, '4s', '0s', '0.8'],
            ['28%', '14%', 3, '3.5s', '1.2s', '0.6'],
            ['44%', '30%', 5, '5s', '2s', '0.7'],
            ['60%', '17%', 3, '4s', '0.6s', '0.65'],
            ['74%', '26%', 4, '4.5s', '2.6s', '0.75'],
            ['88%', '19%', 3, '3.5s', '1.5s', '0.6'],
            ['20%', '42%', 4, '4s', '3s', '0.6'],
            ['82%', '44%', 5, '5s', '0.9s', '0.7'],
        ];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: elevate the guest experience                        -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 30%, rgba(100, 116, 139, 0.28), rgba(100, 116, 139, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 42%, rgba(217, 119, 6, 0.28), rgba(217, 119, 6, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 55% 72%, rgba(234, 179, 8, 0.14), rgba(234, 179, 8, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Marble speckle texture -->
            <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.05]">
                <svg aria-hidden="true" width="100%" height="100%">
                    <defs>
                        <pattern id="marble-speckle" x="0" y="0" width="120" height="120" patternUnits="userSpaceOnUse">
                            <circle cx="15" cy="20" r="0.8" fill="#94a3b8" /><circle cx="45" cy="10" r="0.5" fill="#d97706" /><circle cx="80" cy="35" r="0.7" fill="#94a3b8" /><circle cx="25" cy="60" r="0.4" fill="#d97706" /><circle cx="65" cy="75" r="0.9" fill="#94a3b8" /><circle cx="100" cy="50" r="0.6" fill="#d97706" /><circle cx="35" cy="95" r="0.5" fill="#94a3b8" /><circle cx="75" cy="105" r="0.7" fill="#d97706" /><circle cx="110" cy="85" r="0.4" fill="#94a3b8" /><circle cx="10" cy="110" r="0.6" fill="#d97706" /><circle cx="55" cy="45" r="0.3" fill="#94a3b8" /><circle cx="90" cy="15" r="0.5" fill="#94a3b8" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#marble-speckle)" />
                </svg>
            </div>
            <div class="es-gold-dust absolute inset-0">
                @foreach ($gold as [$l, $t, $s, $d, $dl, $op])
                    <span style="left: {{ $l }}; top: {{ $t }}; width: {{ $s }}px; height: {{ $s }}px; --tw-dur: {{ $d }}; --tw-delay: {{ $dl }}; --tw-op: {{ $op }};"></span>
                @endforeach
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 hotel-brass-badge mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Hotels & Resorts</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Elevate the</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-slate-gold">guest experience.</span></span></span>
            </h1>

            <div class="es-fade-up es-d-2 mb-8 flex justify-center">
                <div class="hotel-shimmer-line h-px w-48"></div>
            </div>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From pool parties to wine tastings. One calendar for every guest activity. Keep guests engaged and delighted.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#planner" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the week
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-slate-700 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Create your activity calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Property-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Pool Parties', 'Live Entertainment', 'Conferences', 'Spa Events', 'Wine Tastings', 'Wedding Receptions', 'Excursions', 'Cooking Classes'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100/80 px-4 py-1.5 text-xs font-semibold text-amber-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-slate-400 to-amber-400"></span>
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
                    Everything to delight your <span class="text-gradient-slate-gold">guests</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Guest updates newsletter (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 dark:border-slate-700/40 dark:bg-slate-800/60 dark:text-slate-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Newsletter
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Guests know what's happening before they ask.</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Pool party tonight, wine tasting tomorrow, live jazz this weekend - one click emails every subscribed guest. Keep your property buzzing with activity.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Direct to guests</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No app download needed</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-amber-300 bg-gradient-to-br from-slate-100 to-amber-100 p-4 dark:border-amber-400/30 dark:from-slate-950 dark:to-amber-950">
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-500 to-amber-600"><svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div>
                                            <div><div class="text-sm font-medium text-gray-900 dark:text-white">This Week's Activities</div><div class="text-xs text-amber-600 dark:text-amber-300">Sending to 1,287 guests...</div></div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-amber-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Sunset Pool Party - Fri 6PM</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Wine Tasting - Sat 7PM</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-teal-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Live Jazz Brunch - Sun 11AM</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multi-space management -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            Spaces
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Every space, one dashboard</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Pool, ballroom, spa, restaurant. Filter by space and avoid double-bookings across your property.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-sky-400/30 bg-sky-500/15 p-2" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-sky-400"></div><span class="text-sm text-gray-900 dark:text-white">Pool Deck</span><span class="ml-auto text-xs text-sky-600 dark:text-sky-300">8 events</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Grand Ballroom</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">5 events</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-teal-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Spa & Wellness</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">12 events</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 3;"><div class="h-2 w-2 rounded-full bg-amber-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Restaurant Terrace</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">6 events</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Ticketed events -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Tickets
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sell tickets, keep 100%</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Ticketed wine dinners, cooking classes, spa packages. Sell directly to guests with no platform fees.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="w-44 -rotate-2 rounded-xl border border-emerald-300/50 bg-gradient-to-br from-emerald-100 to-green-50 p-4 text-center shadow-lg transition-transform group-hover:rotate-0">
                                <div class="text-[10px] uppercase tracking-widest text-emerald-800">VIP Experience</div>
                                <div class="mt-1 font-serif text-sm font-semibold text-emerald-900">Wine Dinner</div>
                                <div class="mt-2 text-xl font-bold text-emerald-700">$120<span class="text-xs font-normal">/guest</span></div>
                                <div class="mt-1 text-[10px] text-emerald-600">Saturday &bull; 7 PM</div>
                                <div class="mt-1 text-[9px] text-emerald-500">12 seats remaining</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Weekly activity planner (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Activity Calendar
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">A full week of guest activities</h3>
                                <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Plan every day of your guests' stay. From morning yoga to evening entertainment, keep the schedule fresh and engaging.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Recurring events</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Mobile-friendly</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Embeddable</span>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 text-center">
                                    <div class="font-semibold text-gray-900 dark:text-white">The Grand Resort</div>
                                    <div class="text-sm text-amber-600 dark:text-amber-300">This Week's Activities</div>
                                </div>
                                <div class="space-y-2">
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg border border-amber-400/30 bg-amber-500/15 p-2" style="--i: 0;"><div class="w-10 font-mono text-xs text-amber-600 dark:text-amber-300">Mon</div><span class="text-sm text-gray-900 dark:text-white">Pool Yoga &bull; 9 AM</span></div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><div class="w-10 font-mono text-xs text-gray-500 dark:text-gray-400">Tue</div><span class="text-sm text-gray-600 dark:text-gray-300">Guided Hike &bull; 8 AM</span></div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><div class="w-10 font-mono text-xs text-gray-500 dark:text-gray-400">Wed</div><span class="text-sm text-gray-600 dark:text-gray-300">Spa Special &bull; All Day</span></div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 3;"><div class="w-10 font-mono text-xs text-gray-500 dark:text-gray-400">Thu</div><span class="text-sm text-gray-600 dark:text-gray-300">Cooking Class &bull; 4 PM</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Google Calendar -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Google Calendar
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Syncs with Google Calendar</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync keeps your property calendar and Google Calendar in perfect harmony. Update once, reflected everywhere.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-lg dark:bg-gray-800">
                                    <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24"><path fill="#4285F4" d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12s4.48 10 10 10 10-4.48 10-10z" opacity="0.1" /><path fill="#4285F4" d="M12 7v5l4.28 2.54.72-1.21-3.5-2.08V7H12z" /><path fill="#EA4335" d="M12 2C6.48 2 2 6.48 2 12h2c0-4.42 3.58-8 8-8V2z" /><path fill="#FBBC05" d="M2 12c0 5.52 4.48 10 10 10v-2c-4.42 0-8-3.58-8-8H2z" /><path fill="#34A853" d="M12 22c5.52 0 10-4.48 10-10h-2c0 4.42-3.58 8-8 8v2z" /></svg>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <svg aria-hidden="true" class="es-sync-dot h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    <span class="text-[10px] text-blue-600 dark:text-blue-300">Two-way sync</span>
                                </div>
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-slate-600 to-amber-500 shadow-lg">
                                    <svg aria-hidden="true" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Team management -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Team
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Your whole team, connected</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Events manager, concierge, spa director, F&B coordinator. Everyone updates the same calendar.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-teal-400/30 bg-teal-500/15 p-2" style="--i: 0;"><div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-[10px] font-semibold text-white">EM</div><div class="flex-1"><div class="text-xs font-medium text-gray-900 dark:text-white">Events Manager</div><div class="text-[10px] text-teal-600 dark:text-teal-300">Full access</div></div></div>
                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-yellow-500 text-[10px] font-semibold text-white">CD</div><div class="flex-1"><div class="text-xs font-medium text-gray-600 dark:text-gray-300">Concierge Desk</div><div class="text-[10px] text-gray-500 dark:text-gray-400">View & share</div></div></div>
                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-500 text-[10px] font-semibold text-white">SD</div><div class="flex-1"><div class="text-xs font-medium text-gray-600 dark:text-gray-300">Spa Director</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Spa events only</div></div></div>
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
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Know what guests love</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">See which activities get the most views, RSVPs, and ticket sales. Double down on what works.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="es-ai-field" style="--i: 0;">
                                <div class="mb-1 flex justify-between text-xs"><span class="text-gray-600 dark:text-gray-300">Pool Party</span><span class="text-cyan-600 dark:text-cyan-300">847 views</span></div>
                                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10"><div class="h-2 rounded-full bg-gradient-to-r from-cyan-500 to-sky-500" style="width: 92%"></div></div>
                            </div>
                            <div class="es-ai-field" style="--i: 1;">
                                <div class="mb-1 flex justify-between text-xs"><span class="text-gray-600 dark:text-gray-300">Wine Tasting</span><span class="text-cyan-600 dark:text-cyan-300">623 views</span></div>
                                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10"><div class="h-2 rounded-full bg-gradient-to-r from-cyan-500 to-sky-500" style="width: 68%"></div></div>
                            </div>
                            <div class="es-ai-field" style="--i: 2;">
                                <div class="mb-1 flex justify-between text-xs"><span class="text-gray-600 dark:text-gray-300">Spa Morning</span><span class="text-cyan-600 dark:text-cyan-300">412 views</span></div>
                                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10"><div class="h-2 rounded-full bg-gradient-to-r from-cyan-500 to-sky-500" style="width: 45%"></div></div>
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
    <!-- 3. Your guests' week (dark band)                             -->
    <!-- ============================================================ -->
    @php
        $days = [
            ['Mon', 'Pool Yoga', '9:00 AM', 'border-amber-400/30 bg-amber-500/[0.12]', 'text-amber-300', 'bg-amber-400', false],
            ['Tue', 'Guided Hike', '8:00 AM', 'border-emerald-400/30 bg-emerald-500/[0.12]', 'text-emerald-300', 'bg-emerald-400', false],
            ['Wed', 'Spa Special', 'All Day', 'border-teal-400/30 bg-teal-500/[0.12]', 'text-teal-300', 'bg-teal-400', false],
            ['Thu', 'Cooking Class', '4:00 PM', 'border-orange-400/30 bg-orange-500/[0.12]', 'text-orange-300', 'bg-orange-400', false],
            ['Fri', 'Wine Tasting', '7:00 PM', 'border-blue-400/40 bg-blue-500/[0.16]', 'text-blue-300', 'bg-blue-400', true],
            ['Sat', 'Beach Party', '3:00 PM', 'border-rose-400/30 bg-rose-500/[0.12]', 'text-rose-300', 'bg-rose-400', false],
            ['Sun', 'Sunday Brunch', '10:00 AM', 'border-sky-400/30 bg-sky-500/[0.12]', 'text-sky-300', 'bg-sky-400', false],
        ];
    @endphp
    <section id="planner" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 30%, rgba(100, 116, 139, 0.24), rgba(100, 116, 139, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 60%, rgba(217, 119, 6, 0.2), rgba(217, 119, 6, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-gold-dust absolute inset-0">
                    @foreach ($gold as [$l, $t, $s, $d, $dl, $op])
                        <span style="left: {{ $l }}; top: {{ $t }}; width: {{ $s }}px; height: {{ $s }}px; --tw-dur: {{ $d }}; --tw-delay: {{ $dl }}; --tw-op: {{ $op }};"></span>
                    @endforeach
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Your guests' week <span class="text-gradient-slate-gold">at a glance</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Morning yoga by the pool. Sunset cocktails on the terrace. Every day offers something special for your guests.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7" data-reveal-group="70">
                    @foreach ($days as [$day, $activity, $time, $card, $accent, $dot, $concierge])
                        <div data-reveal class="relative overflow-hidden rounded-xl border p-4 {{ $card }}">
                            @if ($concierge)
                                <div class="absolute -right-1 -top-1 h-8 w-8">
                                    <svg aria-hidden="true" viewBox="0 0 32 32" fill="none" class="h-full w-full"><path d="M16,2 L19,11 L28,11 L21,17 L23,26 L16,21 L9,26 L11,17 L4,11 L13,11 Z" fill="#d97706" opacity="0.85" /></svg>
                                </div>
                            @endif
                            <div class="mb-3 text-xs font-semibold uppercase tracking-wider {{ $accent }}">{{ $day }}</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-1.5"><div class="h-1.5 w-1.5 rounded-full {{ $dot }}"></div><span class="text-xs font-medium text-white">{{ $activity }}</span></div>
                                <div class="flex items-center gap-1.5"><div class="h-1.5 w-1.5 rounded-full {{ $dot }} opacity-50"></div><span class="text-xs text-gray-400">{{ $time }}</span></div>
                                @if ($concierge)
                                    <div class="mt-1 text-[8px] font-medium text-amber-400">Concierge Pick</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 text-center" data-reveal>
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.06] px-4 py-2 backdrop-blur-sm">
                        <svg aria-hidden="true" class="h-4 w-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="text-sm text-gray-300">Set it once - recurring activities appear automatically every week</span>
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
                    Perfect for every type of <span class="text-gradient-slate-gold">property</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From boutique hotels to sprawling resorts.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Boutique Hotels"
                    description="Curated experiences, intimate tastings, and local excursions. Give guests a reason to stay in and explore."
                    icon-color="slate"
                    blog-slug="for-boutique-hotels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Beach Resorts"
                    description="Pool parties, water sports, sunset yoga, beach bonfires. Keep the vacation vibes going all week long."
                    icon-color="amber"
                    blog-slug="for-beach-resorts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Conference Hotels"
                    description="Manage conference sessions, breakout rooms, networking events, and corporate dinners in one place."
                    icon-color="indigo"
                    blog-slug="for-conference-hotels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Spa & Wellness Resorts"
                    description="Meditation sessions, wellness workshops, spa treatments, and mindfulness classes. Nurture your guests' wellbeing."
                    icon-color="teal"
                    blog-slug="for-spa-resorts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Mountain Lodges"
                    description="Guided hikes, ski lessons, fireside gatherings, and nature excursions. Adventure awaits your guests every day."
                    icon-color="emerald"
                    blog-slug="for-mountain-lodges"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21l6-6m0 0l4-8 4 8m-4-8l6 6M3 21h18" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Casino Hotels"
                    description="Shows, tournaments, dining events, and nightlife. Keep the entertainment calendar packed and visible."
                    icon-color="violet"
                    blog-slug="for-casino-hotels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
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
            ['1', 'Set up your property', 'Sign up and add your hotel or resort details. Set up spaces like pool, ballroom, spa, and restaurant.'],
            ['2', 'Build your activity calendar', 'Add activities, entertainment, classes, and events. Set up recurring activities once and they appear every week.'],
            ['3', 'Delight your guests', 'Share the link at check-in, print QR codes for rooms, embed on your website. Guests always know what\'s happening.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    How it <span class="text-gradient-slate-gold">works</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Get your property's activity calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-600 to-amber-500 text-2xl font-bold text-white shadow-lg shadow-amber-500/25">
                            {{ $num }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $desc }}</p>
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
    <!-- 7. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-restaurants', 'Restaurants'], ['/for-venues', 'Venues'], ['/for-community-centers', 'Community Centers'], ['/for-bars', 'Bars']] as [$relHref, $relName])
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
                    Frequently asked <span class="text-gradient-slate-gold">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything hotels and resorts ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for hotels and resorts?', 'Yes. Event Schedule is free forever for sharing your activity calendar, building a guest following, and syncing with Google Calendar. Advanced features and newsletters are available on the Pro plan.'],
                    ['Can I manage guest activities, entertainment, and conferences together?', 'Yes. Use sub-schedules to organize by category - pool activities, spa sessions, live entertainment, kids clubs, dining events, and conference schedules. Guests see a unified calendar of everything happening at your property.'],
                    ['How do guests discover activities during their stay?', 'Share your activity calendar via QR codes at check-in, embed it on your hotel website, or include the link in pre-arrival emails. Guests can also follow your schedule and receive notifications for new activities.'],
                    ['Can I sell tickets to special events and experiences?', 'Yes. Connect your Stripe account to sell tickets for special dinners, spa packages, excursions, and entertainment shows. Create different pricing for hotel guests and external visitors. Zero platform fees on all sales.'],
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
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(217, 119, 6, 0.3), rgba(217, 119, 6, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-gold-dust absolute inset-0">
                        @foreach ($gold as [$l, $t, $s, $d, $dl, $op])
                            <span style="left: {{ $l }}; top: {{ $t }}; width: {{ $s }}px; height: {{ $s }}px; --tw-dur: {{ $d }}; --tw-delay: {{ $dl }}; --tw-op: {{ $op }};"></span>
                        @endforeach
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Give guests a reason to <span class="text-gradient-slate-gold">stay in</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        One calendar for every activity at your property. Keep guests engaged, informed, and delighted. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-resort" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-slate-700 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
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
