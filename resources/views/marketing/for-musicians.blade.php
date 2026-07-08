<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Musicians | Share Tour Dates</x-slot>
    <x-slot name="description">Share your tour dates, sell tickets, and reach fans directly with newsletters. No algorithm blocking your content. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Musicians</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Musicians",
        "description": "Share your tour dates, sell tickets, and reach fans directly with newsletters. No algorithm blocking your content. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Musicians"
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
                "name": "Is Event Schedule free for musicians?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your gig schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro and Enterprise plans, with no platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "How do fans find out about my upcoming shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your schedule and receive email notifications when you add new shows. You can also send newsletters directly to followers with your upcoming dates, and share your schedule link on Spotify, Bandcamp, your EPK, or any social profile."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to my own shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Every ticket includes a QR code for check-in at the door. Event Schedule charges zero platform fees - you only pay Stripe's standard processing fees."
                }
            },
            {
                "@type": "Question",
                "name": "What happens when a venue books me for a show?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When a venue adds you to their event on Event Schedule, it automatically appears on your schedule too. No need to manually add the same gig in two places. Both calendars stay in sync."
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
        "name": "Event Schedule for Musicians",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Musician Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your tour dates, sell tickets, and reach fans directly with newsletters. Built for musicians, bands, and solo artists.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Tour announcements to fans",
            "Custom schedule URL for Spotify, Bandcamp, EPK",
            "Zero-fee ticket sales with door check-in",
            "Google Calendar sync for gigs, rehearsals, sessions",
            "Venue auto-linking for clubs, theaters, festivals",
            "Band and manager collaboration",
            "Fan notifications for nearby shows",
            "Fan videos and comments on events",
            "Setlist image parsing"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "musician schedule, band tour dates, share gig schedule, musician event calendar, band booking platform, free musician scheduling",
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
           For-musicians "The Stage" styles. The shared es-* motion
           system (aurora, reveals, bento, marquee, odometer, finale)
           lives in marketing.css; this block holds only this page's
           own effects: stage-glow text, panning stage lights, the
           equalizer motif, and the tour-journey timeline.
           ============================================================== */

        .stage-glow-text {
            background: linear-gradient(135deg, #06b6d4, #14b8a6, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(6, 182, 212, 0.3);
        }
        .dark .stage-glow-text {
            background: linear-gradient(135deg, #22d3ee, #2dd4bf, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(34, 211, 238, 0.3);
        }

        /* Panning concert spotlights (dark surfaces only) */
        .es-stagelight {
            position: absolute;
            top: -12%;
            width: 46%;
            height: 135%;
            pointer-events: none;
            transform-origin: 50% 0;
            animation: es-stage-pan 10s ease-in-out infinite alternate;
        }
        .es-stagelight-1 {
            left: 2%;
            background: conic-gradient(from 197deg at 50% 0%, transparent 0deg, rgba(34, 211, 238, 0.13) 11deg, transparent 24deg);
        }
        .es-stagelight-2 {
            right: 2%;
            background: conic-gradient(from 149deg at 50% 0%, transparent 0deg, rgba(45, 212, 191, 0.12) 11deg, transparent 24deg);
            animation-delay: -4s;
            animation-duration: 12s;
        }
        .es-stagelight-3 {
            left: 32%;
            background: conic-gradient(from 178deg at 50% 0%, transparent 0deg, rgba(78, 129, 250, 0.10) 9deg, transparent 20deg);
            animation-delay: -7s;
            animation-duration: 14s;
        }
        @keyframes es-stage-pan {
            from { transform: rotate(-7deg); }
            to   { transform: rotate(7deg); }
        }

        /* Equalizer motif */
        .es-eq {
            display: inline-flex;
            align-items: flex-end;
            gap: 2px;
            height: 14px;
        }
        .es-eq i {
            width: 3px;
            height: 100%;
            border-radius: 2px;
            background: linear-gradient(180deg, #22d3ee, #14b8a6);
            transform-origin: bottom;
            animation: es-eq-bounce 1.15s ease-in-out infinite;
            animation-delay: calc(var(--i, 0) * 0.14s);
        }
        @keyframes es-eq-bounce {
            0%, 100% { transform: scaleY(0.3); }
            35% { transform: scaleY(1); }
            70% { transform: scaleY(0.55); }
        }
        .es-eq-edge {
            position: absolute;
            inset-inline: 0;
            bottom: 0;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 5px;
            height: 34px;
            opacity: 0.5;
            pointer-events: none;
        }
        .es-eq-edge i {
            width: 5px;
            border-radius: 3px 3px 0 0;
            height: 100%;
            background: linear-gradient(180deg, rgba(34, 211, 238, 0.7), rgba(20, 184, 166, 0.15));
            transform-origin: bottom;
            animation: es-eq-bounce 1.3s ease-in-out infinite;
            animation-delay: calc(var(--i, 0) * 0.11s);
        }

        /* Tour-journey timeline */
        .es-tour { position: relative; }
        .es-tour-line {
            position: absolute;
            top: 0.5rem;
            bottom: 0.5rem;
            width: 2px;
            border-radius: 9999px;
            background: linear-gradient(180deg, #22d3ee, #14b8a6 55%, #f59e0b);
            opacity: 0.55;
            left: 1.25rem;
        }
        @media (min-width: 1024px) {
            .es-tour-line { left: 50%; margin-left: -1px; }
        }
        .es-tour-stop {
            position: absolute;
            top: 1.75rem;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #22d3ee, #14b8a6);
            box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.18), 0 0 18px rgba(34, 211, 238, 0.45);
            left: 0.65rem;
        }
        @media (min-width: 1024px) {
            .es-tour-stop { left: 50%; margin-left: -0.625rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-stagelight,
            .es-eq i,
            .es-eq-edge i {
                animation: none !important;
            }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the stage lights up                                 -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(6, 182, 212, 0.5), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(20, 184, 166, 0.45), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Musicians, Bands & Solo Artists</span>
                <span class="es-eq" aria-hidden="true"><i style="--i: 0;"></i><i style="--i: 1;"></i><i style="--i: 2;"></i><i style="--i: 3;"></i><i style="--i: 4;"></i></span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your gigs. Your fans.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="stage-glow-text">No middleman.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From coffee shop open mics to sold-out tours. One link for all your shows. Reach fans directly - no algorithm burying your posts.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See what you get
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-cyan-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-cyan-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                    Create your schedule
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
                                @foreach (['Rock', 'Jazz', 'Folk', 'Blues', 'Classical', 'Country', 'Indie', 'Metal', 'Hip-Hop', 'Electronic', 'Acoustic', 'Punk', 'Soul', 'Reggae'] as $genre)
                                    <span @if ($genreCopy === 1) aria-hidden="true" @endif class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400" aria-hidden="true"></span>
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
    <!-- 2. The problem: a dark stage, empty seats                    -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-stagelight es-stagelight-1"></div>
                <div class="es-stagelight es-stagelight-2"></div>
                <div class="es-stagelight es-stagelight-3"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>
            <div class="es-eq-edge" aria-hidden="true">
                @for ($i = 0; $i < 24; $i++)
                    <i style="--i: {{ $i % 8 }};"></i>
                @endfor
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">The problem</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Playing to a room your fans <span class="stage-glow-text">never heard about</span>
                    </h2>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="120">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mb-2 text-4xl font-black text-cyan-400">Most</div>
                        <div class="text-sm text-gray-400">of your social media followers never see your posts about shows</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mb-2 text-4xl font-black text-amber-400">Too many</div>
                        <div class="text-sm text-gray-400">fans only hear about your shows after they've happened</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="es-od mb-2 justify-center text-4xl font-black text-sky-400" data-odometer="10-20%">10-20%</div>
                        <div class="text-sm text-gray-400">of ticket revenue lost to platform fees elsewhere. Event Schedule charges zero.</div>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-400" data-reveal>
                    Your fans deserve better.
                    <a href="#features" class="inline-flex items-center gap-1 font-semibold text-cyan-400 transition-all hover:gap-2">
                        Here is the fix
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Features bento: built for the road                        -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Built for the road</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything a working musician <span class="text-gradient">actually needs</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Newsletter - Tour Announcements (spans 2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Direct to Fans
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Announce tours directly to fans</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">New album dropping? Going on tour? Send custom show graphics directly to your fans' inbox. No algorithm gatekeeping. Your music, your audience.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Tour announcements</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Album releases</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Show reminders</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-cyan-300 bg-gradient-to-br from-cyan-50 to-teal-50 p-4 shadow-lg dark:border-cyan-400/30 dark:from-cyan-950 dark:to-teal-950">
                                        <div class="mb-3 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 text-sm font-semibold text-white">MH</div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white">The Midnight Hour</div>
                                                <div class="text-xs text-cyan-600 dark:text-cyan-300">Summer Tour 2025!</div>
                                            </div>
                                        </div>
                                        <div class="rounded-xl border border-cyan-400/20 bg-gradient-to-br from-cyan-600/20 to-teal-600/20 p-3">
                                            <div class="text-center">
                                                <div class="mb-1 text-xs font-semibold text-gray-900 dark:text-white">THIS SATURDAY</div>
                                                <div class="text-sm font-bold text-cyan-700 dark:text-cyan-300">Live at The Roxy</div>
                                                <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Doors 7 PM - $25</div>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex gap-4 text-xs">
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-emerald-500 dark:text-emerald-400"><span data-count-to="72">72</span>%</span> opened</div>
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-amber-500 dark:text-amber-400"><span data-count-to="31">31</span>%</span> clicked</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Zero Platform Fees -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Zero platform fees on tickets</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Pre-sales and door sales. 100% of Stripe payments go to you. QR check-in at the venue.</p>

                        <div class="mt-auto rounded-xl border border-emerald-300/50 bg-emerald-50 p-4 dark:border-emerald-400/30 dark:bg-emerald-500/10" aria-hidden="true">
                            <div class="mb-3 text-center">
                                <div class="text-xs text-emerald-600 dark:text-emerald-300">You keep</div>
                                <div class="es-od justify-center text-3xl font-black text-gray-900 dark:text-white" data-odometer="100%">100%</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">of ticket sales</div>
                            </div>
                            <div class="border-t border-emerald-300/40 pt-3 dark:border-emerald-400/20">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">$0</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- One Link for Everything -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            Share Link
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One link for Spotify, Bandcamp & EPK</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Put it in your Spotify bio, Bandcamp page, EPK, or website. All your gigs in one place.</p>

                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-sky-400/30 bg-sky-500/15 p-2" dir="ltr">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <span class="truncate font-mono text-xs text-gray-900 dark:text-white">yourband.eventschedule.com</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1 text-center">
                                <div class="rounded bg-gray-100 p-1.5 text-[10px] font-medium text-sky-600 dark:bg-white/5 dark:text-sky-300">Spotify</div>
                                <div class="rounded bg-gray-100 p-1.5 text-[10px] font-medium text-sky-600 dark:bg-white/5 dark:text-sky-300">Bandcamp</div>
                                <div class="rounded bg-gray-100 p-1.5 text-[10px] font-medium text-sky-600 dark:bg-white/5 dark:text-sky-300">EPK</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Venue Sync (spans 2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Venue Sync
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Venues book you, it auto-appears</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">When venues add your show to their calendar, it automatically appears on yours. One booking, both schedules updated.</p>
                            </div>
                            <div class="relative flex items-center justify-center py-4" aria-hidden="true">
                                <div class="relative flex items-center gap-10">
                                    <div class="w-32 rounded-xl border border-blue-300/50 bg-blue-50 p-4 dark:border-blue-400/30 dark:bg-blue-500/15">
                                        <div class="mb-2 text-center text-xs font-semibold text-blue-600 dark:text-blue-300">Venue</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                            <div class="h-2 w-3/4 rounded bg-blue-400/40"></div>
                                            <div class="h-2 w-1/2 rounded bg-gray-300/40 dark:bg-white/10"></div>
                                        </div>
                                        <div class="mt-3 rounded-lg border border-blue-400/30 bg-blue-400/20 p-2">
                                            <div class="text-center text-[10px] font-medium text-blue-800 dark:text-white">+ Your Band</div>
                                        </div>
                                    </div>
                                    <div class="absolute left-1/2 top-1/2 h-px w-16 -translate-x-1/2 -translate-y-1/2 border-t border-dashed border-blue-300 dark:border-blue-500/40"></div>
                                    <div class="es-sync-dot"></div>
                                    <div class="w-32 rounded-xl border border-gray-300 bg-gray-100 p-4 dark:border-white/20 dark:bg-white/10">
                                        <div class="mb-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">You</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                            <div class="h-2 w-3/4 rounded bg-cyan-400/40"></div>
                                            <div class="h-2 w-1/2 rounded bg-gray-300/40 dark:bg-white/10"></div>
                                        </div>
                                        <div class="mt-3 rounded-lg border border-cyan-400/30 bg-cyan-400/20 p-2">
                                            <div class="text-center text-[10px] font-medium text-gray-900 dark:text-white">New gig!</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Team Collaboration -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Team
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Band, manager & agent access</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Invite band members, your manager, or booking agent. Everyone can add gigs and see what's coming up.</p>

                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/10" style="--i: 0;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-xs font-semibold text-white">JM</div>
                                <div class="flex-1 text-sm text-gray-900 dark:text-white">Jamie</div>
                                <span class="inline-flex items-center rounded bg-amber-500/20 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:text-amber-300">Lead</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-red-500 text-xs font-semibold text-white">MK</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Mike</div>
                                <span class="inline-flex items-center rounded bg-orange-500/20 px-1.5 py-0.5 text-[10px] font-medium text-orange-700 dark:text-orange-300">Manager</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 2;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-yellow-500 to-amber-500 text-xs font-semibold text-white">SA</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Sarah</div>
                                <span class="inline-flex items-center rounded bg-yellow-500/20 px-1.5 py-0.5 text-[10px] font-medium text-yellow-700 dark:text-yellow-300">Agent</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Calendar Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Google Calendar for gigs & rehearsals</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync. Gigs, rehearsals, recording sessions, soundchecks - all in one place.</p>

                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="w-24 rounded-xl border border-blue-300/50 bg-blue-50 p-3 dark:border-blue-400/30 dark:bg-blue-500/15">
                                <div class="mb-1 text-center text-[10px] font-medium text-blue-600 dark:text-blue-300">Schedule</div>
                                <div class="space-y-1">
                                    <div class="h-2 rounded bg-cyan-400/50"></div>
                                    <div class="h-2 rounded bg-amber-400/50"></div>
                                </div>
                            </div>
                            <div class="flex flex-col items-center gap-0.5">
                                <svg aria-hidden="true" class="h-4 w-4 animate-pulse-slow text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                <svg aria-hidden="true" class="h-4 w-4 animate-pulse-slow text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                            </div>
                            <div class="w-24 rounded-xl border border-gray-300 bg-gray-100 p-3 dark:border-white/20 dark:bg-white/10">
                                <div class="mb-1 text-center text-[10px] font-medium text-gray-600 dark:text-gray-300">Google</div>
                                <div class="space-y-1">
                                    <div class="h-2 rounded bg-blue-400/50"></div>
                                    <div class="h-2 rounded bg-green-400/50"></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Fans Follow Your Tour -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            Followers
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Fans follow your tour</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Location-based notifications. When you add a show near them, they get notified automatically.</p>

                        <div class="mt-auto" aria-hidden="true">
                            <div class="flex items-center justify-center">
                                <div class="flex -space-x-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-cyan-500 to-teal-500 text-xs text-white dark:border-[#15151c]">A</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-teal-500 to-emerald-500 text-xs text-white dark:border-[#15151c]">B</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-emerald-500 to-green-500 text-xs text-white dark:border-[#15151c]">C</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gray-200 text-xs text-gray-700 dark:border-[#15151c] dark:bg-white/20 dark:text-white">+127</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center text-xs font-semibold text-cyan-600 dark:text-cyan-300"><span data-count-to="130">130</span> fans following your tour</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Career journey: the tour timeline                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-cyan-400 to-amber-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Your journey</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    From open mics to <span class="stage-glow-text">headlining tours</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.16s;">
                    Event Schedule grows with your music career
                </p>
            </div>

            @php
                $tourStops = [
                    ['title' => 'Open mic nights', 'desc' => 'Playing coffee shops and local bars. Track your spots and let friends know where to catch you.', 'chip' => 'bg-cyan-100 dark:bg-cyan-900/30', 'text' => 'text-cyan-600 dark:text-cyan-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />'],
                    ['title' => 'Local gigging', 'desc' => 'Regular slots at venues in your city. Build a local following and start selling your own tickets.', 'chip' => 'bg-teal-100 dark:bg-teal-900/30', 'text' => 'text-teal-600 dark:text-teal-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />'],
                    ['title' => 'Regional tours', 'desc' => 'Weekend runs and opening slots. Fans in neighboring cities follow to know when you\'re coming through.', 'chip' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-600 dark:text-blue-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />'],
                    ['title' => 'Headlining', 'desc' => 'Your name on the marquee. Email your fans directly and sell out your own shows.', 'chip' => 'bg-sky-100 dark:bg-sky-900/30', 'text' => 'text-sky-600 dark:text-sky-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />'],
                    ['title' => 'National tours', 'desc' => 'Multi-city runs across the country. One link shows fans everywhere when you\'re hitting their town.', 'chip' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-600 dark:text-blue-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                    ['title' => 'Festivals & special events', 'desc' => 'Festival slots, album release shows, and special performances all in one professional calendar.', 'chip' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-600 dark:text-amber-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />'],
                ];
            @endphp
            <div class="es-tour">
                <div class="es-tour-line" aria-hidden="true"></div>
                <div class="space-y-8 lg:space-y-0">
                    @foreach ($tourStops as $stopIndex => $stop)
                        <div class="relative lg:grid lg:grid-cols-2 lg:gap-16 {{ $stopIndex > 0 ? 'lg:-mt-6' : '' }}">
                            <span class="es-tour-stop" aria-hidden="true"></span>
                            <div @class([
                                'ltr:pl-12 rtl:pr-12 lg:p-0',
                                'lg:col-start-2 lg:ltr:pl-12 lg:rtl:pr-12' => $stopIndex % 2 === 1,
                                'lg:ltr:pr-12 lg:rtl:pl-12 lg:text-right lg:rtl:text-left' => $stopIndex % 2 === 0,
                            ]) data-reveal="{{ $stopIndex % 2 === 0 ? 'left' : 'right' }}">
                                <div class="rounded-2xl border border-gray-200 bg-white p-6 text-left shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04] rtl:text-right">
                                    <div class="mb-4 flex items-center gap-3">
                                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $stop['chip'] }}">
                                            <svg aria-hidden="true" class="h-6 w-6 {{ $stop['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $stop['icon'] !!}</svg>
                                        </span>
                                        <span class="text-xs font-bold uppercase tracking-[0.18em] text-gray-400 dark:text-gray-500">Stop {{ str_pad($stopIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $stop['title'] }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $stop['desc'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Perfect for                                               -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all types of <span class="text-gradient">musicians</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether you're a solo artist or a touring band, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                <div data-reveal>
                    <x-sub-audience-card
                        name="Solo Artists"
                        description="Share your acoustic nights, open mics, and solo performances with your growing fanbase."
                        icon-color="cyan"
                        blog-slug="for-solo-artists"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Rock & Pop Bands"
                        description="Coordinate your tour dates across the whole band and let fans follow along."
                        icon-color="teal"
                        blog-slug="for-rock-pop-bands"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Jazz Musicians"
                        description="List your residencies, jam sessions, and special performances at clubs and festivals."
                        icon-color="indigo"
                        blog-slug="for-jazz-musicians"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Cover Bands"
                        description="Show your weekly bar gigs and private events all in one professional calendar."
                        icon-color="violet"
                        blog-slug="for-cover-bands"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Tribute Acts"
                        description="Build a dedicated fanbase for your tribute shows and special themed events."
                        icon-color="purple"
                        blog-slug="for-tribute-acts"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>

                <div data-reveal>
                    <x-sub-audience-card
                        name="Session Musicians"
                        description="Show your availability and let bands know when you're free for gigs and recording sessions."
                        icon-color="amber"
                        blog-slug="for-session-musicians"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-sub-audience-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. How it works                                              -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Quick setup</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Three steps to <span class="text-gradient">packed shows</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                <div class="text-center" data-reveal>
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-600 to-teal-600 text-xl font-bold text-white shadow-lg shadow-cyan-600/25">
                        1
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Add your gigs</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Import from Google Calendar or add tour dates manually. Set up ticket sales if you want.
                    </p>
                </div>

                <div class="text-center" data-reveal>
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-600 to-teal-600 text-xl font-bold text-white shadow-lg shadow-cyan-600/25">
                        2
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Share your link</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Add it to your Spotify bio, Bandcamp, EPK, or anywhere fans find you.
                    </p>
                </div>

                <div class="text-center" data-reveal>
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-600 to-teal-600 text-xl font-bold text-white shadow-lg shadow-cyan-600/25">
                        3
                    </div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Grow your fanbase</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Fans follow your schedule, get notified about shows near them, and share videos and comments after your gigs (all approved by you before going live). Build your audience on your terms.
                    </p>
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
                <div data-reveal>
                    <x-feature-link-card
                        name="Boost"
                        description="Promote events with Facebook and Instagram ads"
                        :url="marketing_url('/features/boost')"
                        icon-color="orange"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
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
    <!-- 8. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                <a href="{{ marketing_url('/for-comedians') }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">Comedians</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-djs') }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">DJs</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-spoken-word') }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">Spoken Word Artists</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-dance-groups') }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">Dance Groups</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors group-hover:text-blue-600 dark:group-hover:text-blue-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
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
                    Everything musicians ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is Event Schedule free for musicians?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">
                        Yes. Event Schedule is free forever for sharing your gig schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro and Enterprise plans, with no platform fees on ticket sales.
                    </p>
                </details>

                <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How do fans find out about my upcoming shows?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">
                        Fans can follow your schedule and receive email notifications when you add new shows. You can also send newsletters directly to followers with your upcoming dates, and share your schedule link on Spotify, Bandcamp, your EPK, or any social profile.
                    </p>
                </details>

                <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I sell tickets to my own shows?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">
                        Yes. Connect your Stripe account and sell tickets directly from your schedule. Every ticket includes a QR code for check-in at the door. Event Schedule charges zero platform fees - you only pay Stripe's standard processing fees.
                    </p>
                </details>

                <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                    <summary class="flex cursor-pointer items-center justify-between p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What happens when a venue books me for a show?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">
                        When a venue adds you to their event on Event Schedule, it automatically appears on your schedule too. No need to manually add the same gig in two places. Both calendars stay in sync.
                    </p>
                </details>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale: your name on the marquee                         -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-cyan-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-stagelight es-stagelight-1"></div>
                    <div class="es-stagelight es-stagelight-2"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your music. Your fans. <span class="stage-glow-text">No gatekeepers.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stop posting into the void. Fill your shows. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-band" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-cyan-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
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

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
