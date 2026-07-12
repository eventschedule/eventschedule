<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Breweries & Wineries</x-slot>
    <x-slot name="description">Fill your tasting room with fans. Announce releases, host tastings, and sell tickets to brewery events. Email your fans directly. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Breweries & Wineries</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Breweries & Wineries",
        "description": "Fill your tasting room with fans. Announce releases, host tastings, and sell tickets to brewery events. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Breweries & Wineries"
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
                "name": "Is Event Schedule free for breweries and wineries?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your tasting events, live music nights, and seasonal happenings. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage tastings, live music, and seasonal events together?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize events by type - tastings, live music, seasonal releases, food pairings, and private events. Each event can include descriptions, images, pricing, and ticket options all in one place."
                }
            },
            {
                "@type": "Question",
                "name": "How do customers discover our events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Customers can follow your schedule and receive email notifications when you add new events. Embed your calendar on your website, share the link on social media, or send newsletters to followers with your upcoming calendar."
                }
            },
            {
                "@type": "Question",
                "name": "Does it sync with Google Calendar?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Two-way Google Calendar sync keeps your events updated across platforms. Add an event in either place and it appears in both. Your staff and customers always see the latest schedule."
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
        "name": "Event Schedule for Breweries & Wineries",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Brewery and Winery Event Management Software",
        "operatingSystem": "Web",
        "description": "Fill your tasting room with fans. Announce new releases, host tastings, and sell tickets to brewery events. Email your fans directly. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Limited release announcements with email newsletters",
            "Club member first access and allocation management",
            "Ticketed tastings and barrel room tours",
            "Private event and tour booking inbox",
            "Multiple space calendars (taproom, patio, barrel room)",
            "Live tap list with QR code scanning",
            "Virtual tasting support",
            "Auto-generated social media graphics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "brewery event calendar, winery tasting schedule, taproom event management, brewery live music, free brewery scheduling",
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
        /* For-breweries-and-wineries "From Grain to Glass" styles. The shared
           es-* motion system lives in marketing.css; this holds the copper
           gradient text, rising carbonation, and the matte craft materials:
           foam-cream caps, wood-grain eyebrows, tap-handle silhouettes, a wax
           seal, and the copper link recolors. */
        .text-gradient-copper {
            background: linear-gradient(135deg, #d97706, #92400e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-copper {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Rising carbonation bubbles */
        .es-fizz { pointer-events: none; overflow: hidden; }
        .es-fizz span {
            position: absolute;
            bottom: -14px;
            border-radius: 9999px;
            background: radial-gradient(circle at 35% 30%, rgba(251, 191, 36, 0.9), rgba(217, 119, 6, 0.35));
            opacity: 0;
            animation: es-fizz var(--fizz-dur, 8s) ease-in infinite;
            animation-delay: var(--fizz-delay, 0s);
        }
        @keyframes es-fizz {
            0% { transform: translateY(0) scale(0.9); opacity: 0; }
            12% { opacity: var(--fizz-op, 0.5); }
            88% { opacity: var(--fizz-op, 0.5); }
            100% { transform: translateY(-150px) scale(1.35); opacity: 0; }
        }

        /* Foam-cream head: a soft off-white cap for the release band + finale */
        .es-foam-cap {
            border-top-left-radius: 2.5rem;
            border-top-right-radius: 2.5rem;
            background: linear-gradient(to bottom, rgba(255, 251, 235, 0.9), rgba(253, 230, 138, 0.28) 45%, rgba(253, 230, 138, 0));
        }
        .dark .es-foam-cap {
            background: linear-gradient(to bottom, rgba(255, 251, 235, 0.72), rgba(251, 191, 36, 0.14) 45%, rgba(251, 191, 36, 0));
        }

        /* Matte wood-grain texture layered over eyebrow badges. Color-safe: it
           sets only background-image, so the element keeps its own bg color. */
        .es-woodgrain {
            background-image:
                repeating-linear-gradient(90deg, rgba(120, 53, 15, 0.12) 0px, rgba(120, 53, 15, 0.12) 1px, transparent 1px, transparent 3px),
                repeating-linear-gradient(90deg, rgba(146, 64, 14, 0.07) 0px, rgba(146, 64, 14, 0.07) 2px, transparent 2px, transparent 8px);
        }
        .dark .es-woodgrain {
            background-image:
                repeating-linear-gradient(90deg, rgba(251, 191, 36, 0.12) 0px, rgba(251, 191, 36, 0.12) 1px, transparent 1px, transparent 3px),
                repeating-linear-gradient(90deg, rgba(217, 119, 6, 0.08) 0px, rgba(217, 119, 6, 0.08) 2px, transparent 2px, transparent 8px);
        }

        /* Stubby tap-handle silhouettes for the tap-list bento strip */
        .es-tap-handle {
            position: relative;
            display: inline-block;
            width: 10px;
            height: var(--tap-h, 26px);
            border-radius: 5px 5px 2px 2px;
            background: linear-gradient(to bottom, #a16207, #78350f);
            box-shadow: inset -1.5px 0 1.5px rgba(0, 0, 0, 0.28), 0 1px 1px rgba(0, 0, 0, 0.2);
        }
        .es-tap-handle::before {
            content: "";
            position: absolute;
            top: -4px;
            left: 50%;
            width: 13px;
            height: 8px;
            transform: translateX(-50%);
            border-radius: 9999px;
            background: linear-gradient(to bottom, #ca8a04, #92400e);
        }
        .dark .es-tap-handle { background: linear-gradient(to bottom, #d97706, #78350f); }
        .dark .es-tap-handle::before { background: linear-gradient(to bottom, #f59e0b, #b45309); }

        /* Wax seal stamped on the club allocation mock */
        .es-wax-seal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 9999px;
            border: 1px solid rgba(120, 53, 15, 0.6);
            background: radial-gradient(circle at 38% 32%, #b45309, #7c2d12 68%, #5b1a0a);
            color: rgba(255, 237, 213, 0.85);
            font-family: Georgia, Cambria, "Times New Roman", Times, serif;
            font-size: 9px;
            font-variant: small-caps;
            letter-spacing: 0.04em;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.25), inset 0 -2px 3px rgba(0, 0, 0, 0.4), 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        .dark .es-wax-seal {
            background: radial-gradient(circle at 38% 32%, #d97706, #92400e 68%, #7c2d12);
        }

        /* Copper recolor for the hard-coded blue links */
        .es-link-accent { color: #b45309; }
        .es-link-accent:hover { color: #92400e; }
        .dark .es-link-accent { color: #fbbf24; }
        .dark .es-link-accent:hover { color: #fcd34d; }

        /* Related-page cards: copper hover */
        .es-rel-card:hover { border-color: #fed7aa; background-color: #fffbeb; }
        .dark .es-rel-card:hover { border-color: rgba(217, 119, 6, 0.4); background-color: rgba(217, 119, 6, 0.08); }
        .group:hover .es-rel-accent { color: #b45309; }
        .dark .group:hover .es-rel-accent { color: #fbbf24; }

        @media (prefers-reduced-motion: reduce) {
            .es-fizz span { animation: none !important; opacity: 0.35; transform: none; }
        }
    </style>

    @php
        // Rising bubbles: [left, size(px), duration, delay, opacity]
        $fizz = [
            ['8%', 6, '8s', '0s', '0.4'],
            ['18%', 10, '10.5s', '1.6s', '0.5'],
            ['29%', 5, '7s', '3s', '0.35'],
            ['41%', 8, '9s', '0.8s', '0.45'],
            ['53%', 12, '11.5s', '2.4s', '0.5'],
            ['64%', 6, '8s', '4.2s', '0.4'],
            ['74%', 9, '10s', '1.1s', '0.45'],
            ['85%', 7, '9s', '2.9s', '0.4'],
            ['93%', 5, '7.5s', '3.7s', '0.35'],
        ];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: every pour                                          -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(217, 119, 6, 0.34), rgba(217, 119, 6, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(234, 88, 12, 0.3), rgba(234, 88, 12, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-fizz absolute inset-x-0 bottom-0 top-1/3">
                @foreach ($fizz as [$l, $s, $d, $dl, $op])
                    <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; --fizz-dur: {{ $d }}; --fizz-delay: {{ $dl }}; --fizz-op: {{ $op }};"></span>
                @endforeach
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-woodgrain es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Breweries, Wineries & Tasting Rooms</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Every pour</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-copper es-gradient-anim">deserves an audience.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                New release hitting taps? Hosting a tasting this weekend? Email your fans directly - no paying Facebook to reach people who already love your craft.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Tour the taproom
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-800 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Create your tasting room calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Producer-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Craft Brewery', 'Winery', 'Cidery', 'Meadery', 'Distillery', 'Taproom', 'Vineyard', 'Brewpub'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100/80 px-4 py-1.5 text-xs font-semibold text-amber-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
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
    <!-- 2. The release cycle (dark band)                             -->
    <!-- ============================================================ -->
    @php
        $phases = [
            ['1', '&#127806;', 'The Craft', 'Share behind-the-scenes: harvest updates, brew day photos, barrel selection. Build anticipation from day one.', 'from-amber-500 to-amber-700', 'text-amber-300'],
            ['2', '&#127866;', 'The Wait', 'Aging in barrels, fermenting in tanks. Tease your members with early samples and first-access previews.', 'from-blue-500 to-blue-700', 'text-blue-300'],
            ['3', '&#127881;', 'The Release', 'The moment arrives. Email blast, release party, allocations ship. Your fans showed up because you told them first.', 'from-rose-500 to-rose-700', 'text-rose-300'],
            ['4', '&#129346;', 'The Community', 'Taproom visits, club events, share nights. The relationship continues until the next release.', 'from-emerald-500 to-emerald-700', 'text-emerald-300'],
        ];
    @endphp
    <section class="bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(217, 119, 6, 0.28), rgba(217, 119, 6, 0) 60%); opacity: 0.65;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(234, 88, 12, 0.24), rgba(234, 88, 12, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-fizz absolute inset-0">
                    @foreach ($fizz as [$l, $s, $d, $dl, $op])
                        <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; --fizz-dur: {{ $d }}; --fizz-delay: {{ $dl }}; --fizz-op: {{ $op }};"></span>
                    @endforeach
                </div>
            </div>
            <div class="es-foam-cap pointer-events-none absolute inset-x-0 top-0 h-16" aria-hidden="true"></div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        From grain to glass, <span class="text-gradient-copper">vine to bottle</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        You don't just serve drinks - you create them. Your fans want to follow the journey, from first brew day to release party.
                    </p>
                </div>

                <div class="relative">
                    <div class="absolute left-0 right-0 top-8 hidden h-0.5 bg-gradient-to-r from-amber-500/50 via-rose-500/50 to-emerald-500/50 lg:block" aria-hidden="true"></div>
                    <div class="relative grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                        @foreach ($phases as [$num, $emoji, $title, $desc, $grad, $text])
                            <div data-reveal class="text-center">
                                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br text-2xl {{ $grad }}">
                                    {!! $emoji !!}
                                </div>
                                <div class="mb-2 text-xs font-semibold uppercase tracking-wider {{ $text }}">{{ $title }}</div>
                                <p class="text-sm text-gray-400">{{ $desc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-12 text-center" data-reveal>
                    <div class="es-woodgrain inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.06] px-4 py-2 backdrop-blur-sm">
                        <svg aria-hidden="true" class="h-4 w-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-sm text-gray-300">You make what you sell. That story is worth sharing.</span>
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
                    Everything to fill the <span class="text-gradient-copper">tasting room</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Release announcements (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Release Announcements
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Limited release? Your fans know first.</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">That bourbon barrel stout you've been aging for 18 months? The reserve Pinot from the best vintage in a decade? One click sends it straight to everyone who wants to know - before it's gone.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Skip the algorithm</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Straight from the source</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-amber-400/30 bg-gradient-to-br from-amber-950 to-orange-950 p-4">
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-600"><svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div>
                                            <div><div class="text-sm font-medium text-white">Barrel Room Release!</div><div class="text-xs text-amber-300">Sending to 2,341 fans...</div></div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white/10 p-2" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-amber-400"></div><span class="text-xs text-gray-300">Bourbon Barrel Stout</span><span class="ml-auto text-[10px] text-amber-300">47 left</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white/10 p-2" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-xs text-gray-300">Reserve Pinot Noir</span><span class="ml-auto text-[10px] text-blue-300">12 cases</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white/10 p-2" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-orange-400"></div><span class="text-xs text-gray-300">Maple Porter</span><span class="ml-auto text-[10px] text-orange-300">SOLD OUT</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Club members first / allocation -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                            First Access
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Club members get first dibs</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Limited releases sell out fast. Your loyal fans - mug club, wine club, followers - get the heads up before anyone else.</p>
                        <div class="mt-auto relative rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="es-wax-seal absolute -right-3 -top-3 rotate-12">Club</div>
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">2024 Reserve Allocation</span>
                                <span class="text-xs font-medium text-rose-700 dark:text-rose-300">47 spots left</span>
                            </div>
                            <div class="space-y-2">
                                <div class="es-ai-field flex items-center gap-2" style="--i: 0;">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-[10px] font-bold text-white">M</div>
                                    <span class="flex-1 text-xs text-gray-900 dark:text-white">Wine Club Member</span>
                                    <span class="text-[10px] text-emerald-500 dark:text-emerald-400">Confirmed</span>
                                </div>
                                <div class="es-ai-field flex items-center gap-2" style="--i: 1;">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-[10px] font-bold text-white">J</div>
                                    <span class="flex-1 text-xs text-gray-600 dark:text-gray-300">Email Subscriber</span>
                                    <span class="text-[10px] text-amber-500 dark:text-amber-400">Waitlist #3</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Ticketed tastings -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Tastings worth paying for</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Barrel room tours, vertical tastings, winemaker dinners. Sell tickets, limit capacity, scan at the door.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="w-44 rotate-2 rounded-xl border border-sky-300/50 bg-gradient-to-br from-sky-100 to-blue-50 p-4 text-center shadow-lg transition-transform group-hover:rotate-0">
                                <div class="text-[10px] uppercase tracking-widest text-sky-800">Exclusive Tasting</div>
                                <div class="mt-1 font-serif text-sm font-semibold text-sky-900">Barrel Room Tour</div>
                                <div class="mt-2 text-xl font-bold text-sky-700">$45<span class="text-xs font-normal">pp</span></div>
                                <div class="mt-1 text-[10px] text-sky-600">Sat, Oct 14 &bull; 2:00 PM</div>
                                <div class="mt-1 text-[9px] text-sky-500">Only 8 spots left</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Private events & tours (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Booking Inbox
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Tours and private events come to you</h3>
                                <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Corporate team outings, bachelorette parties, private tastings. They submit the request, you approve. No email ping-pong.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Group tours</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Private parties</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Buyouts</span>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">Booking Requests</div>
                                <div class="space-y-2">
                                    <div class="es-ai-field flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-100 p-3 dark:border-blue-400/30 dark:bg-blue-500/20" style="--i: 0;">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 text-xs font-semibold text-white">AT</div>
                                        <div class="flex-1"><div class="text-sm font-medium text-gray-900 dark:text-white">Acme Tech - Team Outing</div><div class="text-xs text-blue-600 dark:text-blue-300">Nov 3 &bull; 25 people &bull; Private tour</div></div>
                                        <div class="flex gap-1">
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20"><svg aria-hidden="true" class="h-3 w-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500/20"><svg aria-hidden="true" class="h-3 w-3 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></div>
                                        </div>
                                    </div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-xl bg-gray-100 p-3 dark:bg-white/5" style="--i: 1;">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-rose-500 text-xs font-semibold text-white">JM</div>
                                        <div class="flex-1"><div class="text-sm font-medium text-gray-600 dark:text-gray-300">Jamie's Bachelorette</div><div class="text-xs text-gray-500 dark:text-gray-400">Oct 28 &bull; 12 people</div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multiple spaces -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            Spaces
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Taproom. Patio. Barrel room.</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Separate calendars for each space. Visitors see what's happening where.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-100 p-2 dark:border-emerald-500/30 dark:bg-emerald-500/20" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-emerald-400"></div><span class="text-sm text-gray-900 dark:text-white">Taproom</span><span class="ml-auto text-xs text-emerald-600 dark:text-emerald-300">6 events</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-amber-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Patio & Beer Garden</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">4 events</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Barrel Room</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">2 events</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- What's pouring / QR -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                            Live Menu
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">What's pouring today</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">QR code for your current tap list. Visitors scan, you update. No printed menus to change.</p>
                        <div class="mt-auto flex flex-col items-center gap-4" aria-hidden="true">
                            <div class="flex items-end justify-center gap-2">
                                <span class="es-tap-handle" style="--tap-h: 22px;"></span>
                                <span class="es-tap-handle" style="--tap-h: 30px;"></span>
                                <span class="es-tap-handle" style="--tap-h: 18px;"></span>
                                <span class="es-tap-handle" style="--tap-h: 26px;"></span>
                            </div>
                            <div class="text-center">
                                <div class="mb-2 h-24 w-24 rounded-lg bg-white p-2 shadow-sm"><div class="h-full w-full bg-contain bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%23c2410c%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')]"></div></div>
                                <div class="text-[10px] font-semibold text-orange-600 dark:text-orange-300">SCAN FOR TAP LIST</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Event graphics (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Ready for social</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate promo graphics for release parties. Download and post in seconds.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-32 w-32 rounded-xl border border-amber-200 bg-amber-100 p-2 dark:border-amber-400/30 dark:bg-amber-500/25">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br from-amber-600/50 to-orange-700/50">
                                    <div class="mb-1 text-[10px] font-semibold text-white">THIS SATURDAY</div>
                                    <div class="text-xs font-bold text-white">IPA Release</div>
                                    <div class="mt-1 text-[8px] text-white/80">Taproom 4pm</div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-cyan-500">
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
    <!-- 4. Virtual tastings                                          -->
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
                            <h2 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400 lg:text-3xl">Virtual tastings go global</h2>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Ship your product, host a live tasting. Fans anywhere can join, pay, and taste along. Turn your taproom into a worldwide experience.</p>
                            <div class="mb-4 flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Live tastings</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Sell tickets worldwide</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Any platform</span>
                            </div>
                            <span class="inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                Learn more
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="w-52 rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="mb-4 flex items-center justify-between"><span class="text-xs text-gray-600 dark:text-gray-300">Virtual Tasting</span><div class="flex items-center gap-1"><div class="h-2 w-2 animate-pulse rounded-full bg-red-500"></div><span class="text-[10px] text-red-500">LIVE</span></div></div>
                                <div class="mb-3 rounded-lg bg-gradient-to-br from-amber-600/30 to-orange-600/30 p-4 text-center"><div class="mb-1 text-2xl">&#127866;</div><div class="text-sm font-medium text-gray-900 dark:text-white">IPA Flight</div><div class="text-xs text-gray-500 dark:text-gray-400">with Brewmaster Mike</div></div>
                                <div class="flex items-center justify-center gap-2 text-xs text-gray-500 dark:text-gray-400"><svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg><span>89 viewers</span></div>
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
                    Perfect for all types of <span class="text-gradient-copper">craft beverage makers</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From small-batch breweries to estate wineries, Event Schedule fits your operation.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Craft Breweries"
                    description="Release parties, tap takeovers, and brewery tours. Your fans followed you for your IPAs - make sure they know when the new batch drops."
                    icon-color="amber"
                    blog-slug="for-craft-breweries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Brewpubs & Taprooms"
                    description="Live music, trivia nights, and food pop-ups. Turn your taproom into a destination every night of the week."
                    icon-color="orange"
                    blog-slug="for-brewpubs-taprooms"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Wineries & Vineyards"
                    description="Wine releases, harvest dinners, and vineyard tours. From first crush to final pour, keep your wine club in the loop."
                    icon-color="purple"
                    blog-slug="for-wineries-vineyards"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Cideries & Orchards"
                    description="Apple picking events, seasonal releases, and cider tastings. The orchard-to-glass story your fans want to be part of."
                    icon-color="red"
                    blog-slug="for-cideries-orchards"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Meaderies & Distilleries"
                    description="Mead tastings, cocktail classes, and spirit releases. Educate visitors about your ancient craft or your small-batch spirits."
                    icon-color="yellow"
                    blog-slug="for-meaderies-distilleries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Taproom-Only Breweries"
                    description="No distribution? No problem. Your taproom is your stage. Fill it with fans who came specifically to try your latest creation."
                    icon-color="emerald"
                    blog-slug="for-taproom-only-breweries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
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
            ['1', 'Add your brewery or winery', 'Upload your logo, add your spaces (taproom, patio, barrel room), and customize your branding.'],
            ['2', 'Post your events', 'Release parties, tastings, live music. Add tickets if needed. Set recurring events once and forget about them.'],
            ['3', 'Grow your following', 'Visitors follow your calendar. When you post a new release, it goes straight to their inbox. No middleman.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    How it <span class="text-gradient-copper">works</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Get your tasting room calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="es-woodgrain mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-600 to-amber-800 text-2xl font-bold text-white shadow-lg shadow-amber-500/25">
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
    <!-- 7. Key features                                              -->
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
                <a href="{{ marketing_url('/features') }}" class="es-link-accent inline-flex items-center font-medium hover:underline">
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
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-bars', 'Bars'], ['/for-restaurants', 'Restaurants'], ['/for-farmers-markets', 'Farmers Markets'], ['/for-food-trucks-and-vendors', 'Food Trucks & Vendors']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-rel-card group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="es-rel-accent text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-rel-accent w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-link-accent inline-flex items-center font-medium hover:underline">
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
                    Frequently asked <span class="text-gradient-copper">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything breweries and wineries ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for breweries and wineries?', 'Yes. Event Schedule is free forever for sharing your tasting events, live music nights, and seasonal happenings. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I manage tastings, live music, and seasonal events together?', 'Yes. Use sub-schedules to organize events by type - tastings, live music, seasonal releases, food pairings, and private events. Each event can include descriptions, images, pricing, and ticket options all in one place.'],
                    ['How do customers discover our events?', 'Customers can follow your schedule and receive email notifications when you add new events. Embed your calendar on your website, share the link on social media, or send newsletters to followers with your upcoming calendar.'],
                    ['Does it sync with Google Calendar?', 'Yes. Two-way Google Calendar sync keeps your events updated across platforms. Add an event in either place and it appears in both. Your staff and customers always see the latest schedule.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-colors hover:border-[#fed7aa] dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-amber-600/40">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-all duration-300 group-open/faq:rotate-180 group-open/faq:text-[#b45309] dark:text-gray-400 dark:group-open/faq:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-amber-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(217, 119, 6, 0.32), rgba(217, 119, 6, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-fizz absolute inset-0">
                        @foreach ($fizz as [$l, $s, $d, $dl, $op])
                            <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; --fizz-dur: {{ $d }}; --fizz-delay: {{ $dl }}; --fizz-op: {{ $op }};"></span>
                        @endforeach
                    </div>
                </div>
                <div class="es-foam-cap pointer-events-none absolute inset-x-0 top-0 h-16" aria-hidden="true"></div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your fans. Direct reach. <span class="text-gradient-copper">No middleman.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        You make the product. You own the relationship. Email your fans directly - no algorithm in the way.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-brewery" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 to-amber-800 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
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
