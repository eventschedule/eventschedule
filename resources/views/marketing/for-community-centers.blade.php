<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Community Centers | Manage Programs</x-slot>
    <x-slot name="description">Keep your community connected. Manage programs, classes, room bookings, and events. Email members directly - no algorithm. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Community Centers</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Community Centers",
        "description": "Keep your community connected. Manage programs, classes, room bookings, and events. Email members directly. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Community Centers & Recreation Facilities"
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
                "name": "Is Event Schedule free for community centers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your program calendar, building a community following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan."
                }
            },
            {
                "@type": "Question",
                "name": "Can I organize classes, meetings, and events by category?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize programs by type - fitness classes, youth programs, senior activities, community meetings, and special events. Each program can have its own schedule, description, and registration options."
                }
            },
            {
                "@type": "Question",
                "name": "How do community members stay informed about programs?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Members can follow your center's schedule and receive email notifications for new programs. Embed your calendar on your website, share on social media, or send newsletters to keep the community informed."
                }
            },
            {
                "@type": "Question",
                "name": "Can we handle event registration and payments?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Enable registration on any program to manage attendance. For paid classes or events, connect Stripe to handle payments with zero platform fees. Attendees receive QR codes for easy check-in."
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
        "name": "Event Schedule for Community Centers",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Community Center Event Management Software",
        "operatingSystem": "Web",
        "description": "Keep your community connected. Manage programs, classes, room bookings, and events. Email members directly. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Program and class announcement newsletters",
            "Facility rental and room booking inbox",
            "Class registration with payments",
            "Public community calendar with embedding",
            "Multi-room scheduling",
            "QR code check-in for attendance",
            "Virtual and livestreamed events",
            "Auto-generated social media graphics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "community center calendar, recreation program schedule, facility booking software, community events, free community center scheduling",
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
        /* For-community-centers "The Gathering Place" styles. The shared es-*
           motion system lives in marketing.css; this holds the teal gradient
           text, the drifting program-board card, and the gathering ripple. */
        .text-gradient-teal {
            background: linear-gradient(135deg, #14b8a6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-teal {
            background: linear-gradient(135deg, #2dd4bf, #22d3ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* Gathering ripple - concentric rings spreading from a center point */
        .es-ripple span {
            position: absolute;
            left: 50%;
            top: 50%;
            height: 44px;
            width: 44px;
            margin: -22px 0 0 -22px;
            border-radius: 9999px;
            border: 1px solid rgba(45, 212, 191, 0.35);
            transform: scale(0);
            opacity: 0;
            animation: es-ripple var(--rip-dur, 7s) ease-out infinite;
            animation-delay: var(--rip-delay, 0s);
        }
        @keyframes es-ripple {
            0% { transform: scale(0.2); opacity: 0.55; }
            100% { transform: scale(9); opacity: 0; }
        }

        /* --- The Gathering Place: corkboard + terracotta warmth --- */
        /* Warm terracotta secondary (#c2410c family) paired with the teal accent */
        .cc-terra { color: #c2410c; }
        .dark .cc-terra { color: #fb923c; }
        .cc-teal { color: #0d9488; }
        .dark .cc-teal { color: #2dd4bf; }
        .cc-bg-terra { background-color: #c2410c; }
        .dark .cc-bg-terra { background-color: #fb923c; }
        .cc-bg-teal { background-color: #0d9488; }
        .dark .cc-bg-teal { background-color: #2dd4bf; }
        .cc-chip-terra { border-color: rgba(194, 65, 12, 0.30); background-color: rgba(194, 65, 12, 0.10); }
        .dark .cc-chip-terra { border-color: rgba(251, 146, 60, 0.32); background-color: rgba(251, 146, 60, 0.13); }
        .cc-chip-teal { border-color: rgba(13, 148, 136, 0.30); background-color: rgba(13, 148, 136, 0.10); }
        .dark .cc-chip-teal { border-color: rgba(45, 212, 191, 0.32); background-color: rgba(45, 212, 191, 0.13); }

        /* Hand-drawn wobbly underline tucked beneath gradient words */
        .cc-wobble { position: relative; display: inline-block; }
        .cc-wobble-line {
            position: absolute;
            left: -3%;
            bottom: -0.26em;
            width: 106%;
            height: 0.34em;
            color: #c2410c;
            overflow: visible;
        }
        .dark .cc-wobble-line { color: #fb923c; }

        /* Stamped "Approved" chip for the facility-rental mock */
        .cc-stamp {
            display: inline-flex;
            align-items: center;
            color: #c2410c;
            border: 2px solid rgba(194, 65, 12, 0.55);
            background: rgba(194, 65, 12, 0.07);
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            transform: rotate(-11deg);
            box-shadow: 0 1px 2px rgba(60, 30, 10, 0.15);
        }
        .dark .cc-stamp {
            color: #fb923c;
            border-color: rgba(251, 146, 60, 0.50);
            background: rgba(251, 146, 60, 0.10);
        }

        /* Kraft corkboard that holds the community week */
        .cc-corkboard {
            background-color: #caa877;
            background-image:
                radial-gradient(circle at 15% 25%, rgba(99, 61, 26, 0.30) 0, rgba(99, 61, 26, 0.30) 1.5px, transparent 2px),
                radial-gradient(circle at 55% 12%, rgba(99, 61, 26, 0.18) 0, rgba(99, 61, 26, 0.18) 1px, transparent 2px),
                radial-gradient(circle at 82% 55%, rgba(99, 61, 26, 0.24) 0, rgba(99, 61, 26, 0.24) 1.5px, transparent 2px),
                radial-gradient(circle at 35% 78%, rgba(99, 61, 26, 0.16) 0, rgba(99, 61, 26, 0.16) 1px, transparent 2px),
                radial-gradient(circle at 68% 88%, rgba(99, 61, 26, 0.22) 0, rgba(99, 61, 26, 0.22) 1.5px, transparent 2px);
            background-size: 90px 90px, 70px 70px, 110px 110px, 80px 80px, 100px 100px;
            box-shadow:
                inset 0 0 0 4px rgba(124, 74, 33, 0.55),
                inset 0 2px 12px rgba(60, 33, 12, 0.45),
                0 12px 30px rgba(0, 0, 0, 0.30);
        }
        .dark .cc-corkboard {
            background-color: #5a4022;
            background-image:
                radial-gradient(circle at 15% 25%, rgba(28, 16, 6, 0.45) 0, rgba(28, 16, 6, 0.45) 1.5px, transparent 2px),
                radial-gradient(circle at 55% 12%, rgba(28, 16, 6, 0.30) 0, rgba(28, 16, 6, 0.30) 1px, transparent 2px),
                radial-gradient(circle at 82% 55%, rgba(28, 16, 6, 0.38) 0, rgba(28, 16, 6, 0.38) 1.5px, transparent 2px),
                radial-gradient(circle at 35% 78%, rgba(28, 16, 6, 0.26) 0, rgba(28, 16, 6, 0.26) 1px, transparent 2px),
                radial-gradient(circle at 68% 88%, rgba(28, 16, 6, 0.34) 0, rgba(28, 16, 6, 0.34) 1.5px, transparent 2px);
            box-shadow:
                inset 0 0 0 4px rgba(58, 36, 16, 0.70),
                inset 0 2px 12px rgba(0, 0, 0, 0.55),
                0 12px 30px rgba(0, 0, 0, 0.45);
        }

        /* Pinned flyer cards (cream paper, slight rotation applied inline) */
        .cc-flyer { background: #fdfaf1; box-shadow: 0 5px 12px rgba(45, 26, 8, 0.35); }
        .cc-flyer-day { color: #b45309; }
        .cc-flyer-title { color: #3f3a34; }
        .cc-flyer-sub { color: #6b6157; }
        .dark .cc-flyer { background: #f2ead9; box-shadow: 0 6px 14px rgba(0, 0, 0, 0.50); }

        /* Pushpins holding the flyers (alternating terracotta / teal) */
        .cc-pin {
            height: 12px;
            width: 12px;
            border-radius: 9999px;
            background: radial-gradient(circle at 35% 30%, #fca5a5, #c2410c 70%);
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.40);
        }
        .cc-pin-teal { background: radial-gradient(circle at 35% 30%, #99f6e4, #0d9488 70%); }

        /* Accent text links + related-card hovers (teal, warming to terracotta) */
        .cc-textlink { color: #0d9488; }
        .cc-textlink:hover { color: #c2410c; }
        .dark .cc-textlink { color: #2dd4bf; }
        .dark .cc-textlink:hover { color: #fb923c; }
        .cc-relcard:hover { border-color: #5eead4; background-color: #f0fdfa; }
        .dark .cc-relcard:hover { border-color: rgba(45, 212, 191, 0.35); background-color: rgba(20, 184, 166, 0.07); }
        .cc-relcard:hover .cc-relcard-title,
        .cc-relcard:hover .cc-relcard-arrow { color: #0d9488; }
        .dark .cc-relcard:hover .cc-relcard-title,
        .dark .cc-relcard:hover .cc-relcard-arrow { color: #2dd4bf; }

        @media (prefers-reduced-motion: reduce) {
            .es-ripple span { animation: none !important; }
            .es-ripple span { opacity: 0; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: where community comes together                      -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(20, 184, 166, 0.32), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(6, 182, 212, 0.3), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 55% 75%, rgba(16, 185, 129, 0.16), rgba(16, 185, 129, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Gathering ripple, centered behind the headline -->
            <div class="es-ripple absolute left-1/2 top-1/2 h-0 w-0 -translate-x-1/2 -translate-y-1/2">
                <span style="--rip-dur: 8s; --rip-delay: 0s;"></span>
                <span style="--rip-dur: 8s; --rip-delay: 2.6s;"></span>
                <span style="--rip-dur: 8s; --rip-delay: 5.2s;"></span>
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Community Centers & Recreation Facilities</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Where your community</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-teal es-gradient-anim">comes together.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Stop hoping members check your Facebook page. Email your community directly - no algorithm decides who sees your programs.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the week
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-teal-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
                    Create your center's calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Center-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Recreation Center', 'Senior Center', 'Youth Center', 'Cultural Center', 'Neighborhood Center', 'Faith-Based Center'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-100/80 px-4 py-1.5 text-xs font-semibold text-teal-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-teal-400 to-cyan-400"></span>
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
    <!-- 2. The community week (dark band)                            -->
    <!-- ============================================================ -->
    @php
        $week = [
            ['Mon', 'text-teal-300', [['bg-teal-400', 'Senior Fitness', true], ['bg-cyan-400', 'Yoga Class', false]]],
            ['Tue', 'text-emerald-300', [['bg-emerald-400', 'Youth Basketball', true], ['bg-green-400', 'Computer Lab', false]]],
            ['Wed', 'text-blue-300', [['bg-blue-400', 'Art Classes', true], ['bg-sky-400', 'Book Club', false]]],
            ['Thu', 'text-amber-300', [['bg-amber-400', 'Town Hall', true], ['bg-orange-400', 'Dance Class', false]]],
            ['Fri', 'text-rose-300', [['bg-rose-400', 'Movie Night', true], ['bg-cyan-400', 'Teen Program', false]]],
            ['Sat', 'text-sky-300', [['bg-sky-400', 'Kids Camp', true], ['bg-blue-400', 'Family Event', false]]],
            ['Sun', 'text-sky-300', [['bg-sky-400', 'Open Gym', true], ['bg-blue-400', 'Craft Fair', false]]],
        ];
        // Slight per-flyer rotations for the corkboard, plus a shared hand-drawn underline
        $ccRots = [-3.5, 2.5, -2, 3, -1.5, 2, -3];
        $ccWobble = '<svg class="cc-wobble-line" viewBox="0 0 200 9" preserveAspectRatio="none" fill="none" aria-hidden="true"><path d="M2 6 C 20 3, 35 3, 52 5.5 S 88 8, 108 5 S 150 2.5, 170 5.5 S 194 6.5, 198 4.5" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    @endphp
    <section class="bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(20, 184, 166, 0.26), rgba(20, 184, 166, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(6, 182, 212, 0.22), rgba(6, 182, 212, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-ripple absolute left-1/2 top-1/3 h-0 w-0 -translate-x-1/2">
                    <span style="--rip-dur: 9s; --rip-delay: 0s;"></span>
                    <span style="--rip-dur: 9s; --rip-delay: 3s;"></span>
                    <span style="--rip-dur: 9s; --rip-delay: 6s;"></span>
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        The community week, <span class="cc-wobble"><span class="text-gradient-teal">organized</span>{!! $ccWobble !!}</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Senior fitness Monday morning. Youth basketball Tuesday evening. Art classes Wednesday. Keep every program visible and every room booked.
                    </p>
                </div>

                <div class="cc-corkboard rounded-2xl p-5 md:p-7" data-reveal="panel">
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7" data-reveal-group="60">
                        @foreach ($week as $wi => [$day, $text, $items])
                            <div data-reveal>
                                <div class="cc-flyer relative rounded-sm px-3 pb-3 pt-6 text-center" style="transform: rotate({{ $ccRots[$wi] }}deg);">
                                    <span class="cc-pin {{ $wi % 2 === 0 ? '' : 'cc-pin-teal' }} absolute left-1/2 top-2 -translate-x-1/2"></span>
                                    <div class="cc-flyer-day mb-2 text-[11px] font-bold uppercase tracking-wider">{{ $day }}</div>
                                    <div class="space-y-1.5 text-left">
                                        @foreach ($items as [$dot, $name, $bold])
                                            <div class="flex items-center gap-1.5">
                                                <div class="h-1.5 w-1.5 shrink-0 rounded-full {{ $dot }}"></div>
                                                <span class="text-[11px] {{ $bold ? 'cc-flyer-title font-semibold' : 'cc-flyer-sub' }}">{{ $name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-10 text-center" data-reveal>
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.06] px-4 py-2 backdrop-blur-sm">
                        <svg aria-hidden="true" class="h-4 w-4 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="text-sm text-gray-300">All recurring programs in one place - members see what's happening every day</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Bento features                                            -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Everything to run your <span class="cc-wobble"><span class="text-gradient-teal">center</span>{!! $ccWobble !!}</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Program announcements (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Newsletter
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">New program? Your members are first to know.</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Summer camp registration, new fitness classes, special events - one click emails everyone who signed up. No algorithm decides who sees your announcements.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Your community, direct reach</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No middleman</span>
                                </div>
                            </div>
                            <div class="relative w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="es-ripple pointer-events-none absolute left-1/2 top-1/2 h-0 w-0 -translate-x-1/2 -translate-y-1/2">
                                    <span style="--rip-dur: 8s; --rip-delay: 0s;"></span>
                                    <span style="--rip-dur: 8s; --rip-delay: 2.6s;"></span>
                                    <span style="--rip-dur: 8s; --rip-delay: 5.2s;"></span>
                                </div>
                                <div class="animate-float relative">
                                    <div class="max-w-xs rounded-2xl border border-teal-300 bg-gradient-to-br from-teal-50 to-cyan-50 p-4 dark:border-teal-400/30 dark:from-teal-950 dark:to-cyan-950">
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-600"><svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div>
                                            <div><div class="text-sm font-medium text-gray-900 dark:text-white">Summer Camp Registration</div><div class="text-xs text-teal-600 dark:text-teal-300">Sending to 2,341 members...</div></div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-emerald-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Sports Camp (Ages 8-12)</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-cyan-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Art Camp (Ages 6-10)</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-teal-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Science Camp (Ages 10-14)</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Room booking -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Booking Inbox
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Facility rental requests come to you</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Groups submit requests online. Review, approve, or decline from your dashboard.</p>
                        <div class="relative mt-auto space-y-2" aria-hidden="true">
                            <div class="cc-stamp absolute -top-3 right-1 z-10">Approved</div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-sky-200 bg-sky-100 p-3 dark:border-sky-400/30 dark:bg-sky-500/20" style="--i: 0;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-blue-500 text-xs font-semibold text-white">PTA</div>
                                <div class="flex-1"><div class="text-sm font-medium text-gray-900 dark:text-white">Lincoln PTA</div><div class="text-xs text-sky-600 dark:text-sky-300">Meeting Room &bull; Oct 15</div></div>
                                <div class="flex gap-1">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20"><svg aria-hidden="true" class="h-3 w-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500/20"><svg aria-hidden="true" class="h-3 w-3 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></div>
                                </div>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl bg-gray-100 p-3 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-500 text-xs font-semibold text-white">SC</div>
                                <div class="flex-1"><div class="text-sm font-medium text-gray-600 dark:text-gray-300">Scout Troop 42</div><div class="text-xs text-gray-500 dark:text-gray-400">Gym &bull; Oct 22</div></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Class registration -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Registration
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Fill your classes and workshops</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Sell registrations, manage capacity, scan tickets at the door. Works for camps, classes, and special events.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="w-44 -rotate-2 rounded-xl border border-emerald-300/50 bg-gradient-to-br from-emerald-100 to-green-50 p-4 text-center shadow-lg transition-transform group-hover:rotate-0">
                                <div class="text-[10px] uppercase tracking-widest text-emerald-800">Registration</div>
                                <div class="mt-1 font-serif text-sm font-semibold text-emerald-900">Pottery Class</div>
                                <div class="mt-2 text-xl font-bold text-emerald-700">$45<span class="text-xs font-normal">/session</span></div>
                                <div class="mt-1 text-[10px] text-emerald-600">Wednesdays &bull; 6 PM</div>
                                <div class="mt-1 text-[9px] text-emerald-500">8 spots remaining</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Community calendar (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Public Calendar
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Everything happening at your center</h3>
                                <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Public events, recurring programs, meetings, and classes - all in one professional calendar. Embed on your website or share the link.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Mobile-friendly</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Embeddable</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Custom branding</span>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 text-center">
                                    <div class="font-semibold text-gray-900 dark:text-white">Riverside Community Center</div>
                                    <div class="text-sm text-cyan-600 dark:text-cyan-300">October 2024</div>
                                </div>
                                <div class="space-y-2">
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg border border-cyan-200 bg-cyan-100 p-2 dark:border-cyan-400/30 dark:bg-cyan-500/20" style="--i: 0;"><div class="w-10 font-mono text-xs text-cyan-700 dark:text-cyan-300">Oct 5</div><span class="text-sm text-gray-900 dark:text-white">Fall Festival</span></div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><div class="w-10 font-mono text-xs text-gray-500 dark:text-gray-400">Oct 8</div><span class="text-sm text-gray-600 dark:text-gray-300">Senior Lunch</span></div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><div class="w-10 font-mono text-xs text-gray-500 dark:text-gray-400">Oct 12</div><span class="text-sm text-gray-600 dark:text-gray-300">Town Hall Meeting</span></div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 3;"><div class="w-10 font-mono text-xs text-gray-500 dark:text-gray-400">Oct 15</div><span class="text-sm text-gray-600 dark:text-gray-300">Youth Basketball</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multi-room scheduling -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            Spaces
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Every room at a glance</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Gym, meeting rooms, activity rooms, outdoor spaces. Filter by room and avoid scheduling conflicts.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border cc-chip-terra p-2" style="--i: 0;"><div class="h-2 w-2 rounded-full cc-bg-terra"></div><span class="text-sm text-gray-900 dark:text-white">Gymnasium</span><span class="cc-terra ml-auto text-xs">12 events</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border cc-chip-teal p-2" style="--i: 1;"><div class="h-2 w-2 rounded-full cc-bg-teal"></div><span class="text-sm text-gray-900 dark:text-white">Meeting Room A</span><span class="cc-teal ml-auto text-xs">8 events</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border cc-chip-terra p-2" style="--i: 2;"><div class="h-2 w-2 rounded-full cc-bg-terra"></div><span class="text-sm text-gray-900 dark:text-white">Activity Room</span><span class="cc-terra ml-auto text-xs">15 events</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border cc-chip-teal p-2" style="--i: 3;"><div class="h-2 w-2 rounded-full cc-bg-teal"></div><span class="text-sm text-gray-900 dark:text-white">Outdoor Pavilion</span><span class="cc-teal ml-auto text-xs">4 events</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- QR check-in -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                            Check-in
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Track attendance easily</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Scan tickets at the door for classes and events. Know exactly who showed up.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="text-center">
                                <div class="mx-auto mb-2 h-20 w-20 rounded-xl bg-white p-2 shadow-sm"><div class="h-full w-full bg-contain bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%230f766e%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')]"></div></div>
                                <div class="text-xs font-medium text-blue-600 dark:text-blue-300">Scan with any smartphone</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Event graphics (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Ready for social media</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate promotional images for your programs. Download and share in seconds.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-32 w-32 rounded-xl border border-rose-200 bg-rose-100 p-2 dark:border-rose-400/30 dark:bg-rose-500/25">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br from-teal-600/50 to-cyan-600/50">
                                    <div class="mb-1 text-[10px] font-semibold text-white">THIS SATURDAY</div>
                                    <div class="text-xs font-bold text-white">Fall Festival</div>
                                    <div class="mt-1 text-[8px] text-white/80">Free admission</div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-rose-500">
                                    <svg aria-hidden="true" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
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
    <!-- 4. Virtual events                                            -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" data-reveal="panel" class="es-bento group block">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                Online Events
                            </div>
                            <h2 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400 lg:text-3xl">Reach members who can't come in person</h2>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Virtual town halls, online fitness classes, livestreamed community events. Members can participate from anywhere.</p>
                            <div class="mb-4 flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Virtual meetings</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Online classes</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Livestreams</span>
                            </div>
                            <span class="inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                Learn more
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="w-52 rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="mb-4 flex items-center justify-between"><span class="text-xs text-gray-600 dark:text-gray-300">Virtual Town Hall</span><div class="flex items-center gap-1"><div class="h-2 w-2 animate-pulse rounded-full bg-red-500"></div><span class="text-[10px] text-red-500 dark:text-red-400">LIVE</span></div></div>
                                <div class="mb-3 rounded-lg bg-gradient-to-br from-sky-600/30 to-blue-600/30 p-4 text-center"><div class="mb-1 text-2xl">&#128172;</div><div class="text-sm font-medium text-gray-900 dark:text-white">Community Q&A</div><div class="text-xs text-gray-500 dark:text-gray-400">with the Center Team</div></div>
                                <div class="flex items-center justify-center gap-2 text-xs text-gray-500 dark:text-gray-400"><svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg><span>96 attending</span></div>
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
    <!-- 5. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all types of <span class="cc-wobble"><span class="text-gradient-teal">community centers</span>{!! $ccWobble !!}</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From recreation facilities to neighborhood gathering spaces.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Recreation Centers"
                    description="Sports leagues, fitness classes, camps, and recreational programs. Keep your community active and engaged."
                    icon-color="teal"
                    blog-slug="for-recreation-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Senior Centers"
                    description="Programs, social events, wellness activities, and meals. Keep seniors connected and supported."
                    icon-color="cyan"
                    blog-slug="for-senior-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Youth Centers"
                    description="After-school programs, summer camps, teen activities. Give young people a safe place to grow."
                    icon-color="emerald"
                    blog-slug="for-youth-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Cultural Centers"
                    description="Heritage events, language classes, cultural celebrations. Preserve and share traditions with your community."
                    icon-color="violet"
                    blog-slug="for-cultural-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Neighborhood Centers"
                    description="Local meetings, block parties, civic events, and community gatherings. Strengthen local bonds."
                    icon-color="amber"
                    blog-slug="for-neighborhood-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Faith-Based Centers"
                    description="Congregation events, community outreach, classes, and fellowship gatherings. Bring people together."
                    icon-color="indigo"
                    blog-slug="for-faith-based-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. How it works                                              -->
    <!-- ============================================================ -->
    @php
        $steps = [
            ['1', 'Add your center', 'Sign up and add your center details. Set up rooms and spaces if you have multiple areas.'],
            ['2', 'Post your programs', 'Add classes, events, meetings, and rentals. Set up recurring programs once.'],
            ['3', 'Reach your members', 'Members follow your calendar. When you post a new program, it goes straight to their inbox.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    How it <span class="text-gradient-teal">works</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Get your community calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-500 text-2xl font-bold text-white shadow-lg shadow-teal-500/25">
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
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key <span class="text-gradient-teal">features</span></h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Add your schedule to any website with one snippet" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set events to repeat weekly on chosen days" :url="marketing_url('/features/recurring-events')" icon-color="lime">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send event updates directly to followers' inboxes" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="cc-textlink inline-flex items-center font-medium hover:underline">
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
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related <span class="text-gradient-teal">pages</span></h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-libraries', 'Libraries'], ['/for-theaters', 'Theaters'], ['/for-workshop-instructors', 'Workshop Instructors'], ['/for-fitness-and-yoga', 'Fitness & Yoga']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group cc-relcard flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="cc-relcard-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="cc-relcard-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="cc-textlink inline-flex items-center font-medium hover:underline">
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
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-teal">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything community centers ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for community centers?', 'Yes. Event Schedule is free forever for sharing your program calendar, building a community following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan.'],
                    ['Can I organize classes, meetings, and events by category?', 'Yes. Use sub-schedules to organize programs by type - fitness classes, youth programs, senior activities, community meetings, and special events. Each program can have its own schedule, description, and registration options.'],
                    ['How do community members stay informed about programs?', 'Members can follow your center\'s schedule and receive email notifications for new programs. Embed your calendar on your website, share on social media, or send newsletters to keep the community informed.'],
                    ['Can we handle event registration and payments?', 'Yes. Enable registration on any program to manage attendance. For paid classes or events, connect Stripe to handle payments with zero platform fees. Attendees receive QR codes for easy check-in.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-teal-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(20, 184, 166, 0.32), rgba(20, 184, 166, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-ripple absolute left-1/2 top-1/3 h-0 w-0 -translate-x-1/2">
                        <span style="--rip-dur: 9s; --rip-delay: 0s;"></span>
                        <span style="--rip-dur: 9s; --rip-delay: 3s;"></span>
                        <span style="--rip-dur: 9s; --rip-delay: 6s;"></span>
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Stop hoping members <span class="cc-wobble"><span class="text-gradient-teal">check Facebook</span>{!! $ccWobble !!}</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Email your community directly. Fill your programs. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-center" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-teal-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
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
