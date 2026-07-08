<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Magicians | Share Your Show Dates</x-slot>
    <x-slot name="description">Share your magic shows, sell tickets directly, and reach your audience with newsletters. No algorithm blocking your content. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Magicians</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Magicians",
        "description": "Share your magic shows, sell tickets directly, and reach your audience with newsletters. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Magicians"
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
                "name": "Is Event Schedule free for magicians?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your show schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I promote different types of shows in one schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by show type - stage shows, close-up performances, children's parties, corporate events, and workshops. Each event can have its own description, images, and ticket options."
                }
            },
            {
                "@type": "Question",
                "name": "How do audiences discover my performances?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your schedule and receive email notifications for new shows. Share your schedule link on your booking website, social media profiles, or embed it on any webpage. Send newsletters to followers with upcoming appearances."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to my shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Create different ticket types for VIP, general admission, or meet-and-greet packages. Zero platform fees - you only pay Stripe's processing fees."
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
        "name": "Event Schedule for Magicians",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Magician Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your magic shows, sell tickets directly, and reach your audience with newsletters. Built for magicians, mentalists, illusionists, and variety performers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Private event booking management for corporate and wedding bookings",
            "Zero-fee ticket sales with QR check-in",
            "Direct newsletter communication with fans",
            "Virtual show streaming support",
            "Two-way Google Calendar sync",
            "Team coordination for assistants and agents",
            "Auto-generated promotional graphics for social media"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "magician schedule, magic show calendar, magician booking platform, magic event management, free magician scheduling",
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
           For-magicians "The Reveal" styles. The shared es-* motion
           system (aurora, reveals, bento, finale) lives in
           marketing.css; this block holds only this page's own effects:
           the blue gradient text and the twinkling sparkles.
           ============================================================== */

        .text-gradient-blue {
            background: linear-gradient(135deg, #4E81FA, #0EA5E9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @keyframes es-sparkle {
            0%, 100% { opacity: 0.25; transform: scale(0.8) rotate(0deg); }
            50% { opacity: 1; transform: scale(1.2) rotate(20deg); }
        }
        .es-sparkle {
            position: absolute;
            color: #fcd34d;
            animation: es-sparkle 2.4s ease-in-out infinite;
            animation-delay: var(--d, 0s);
            pointer-events: none;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-sparkle { animation: none !important; }
        }
    </style>

    @php
        $sparkleSvg = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="h-full w-full"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" /></svg>';
        $heroSparkles = [
            ['top:16%;left:12%', '1.5rem', '0s'], ['top:26%;right:16%', '1rem', '0.5s'],
            ['top:62%;left:20%', '1.25rem', '1s'], ['top:20%;left:52%', '0.75rem', '1.5s'],
            ['bottom:20%;right:14%', '1.25rem', '0.8s'], ['bottom:30%;left:44%', '0.9rem', '2s'],
        ];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: the reveal                                          -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(78, 129, 250, 0.5), rgba(78, 129, 250, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(14, 165, 233, 0.45), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            @foreach ($heroSparkles as [$pos, $size, $delay])
                <span class="es-sparkle" style="{{ $pos }};width:{{ $size }};height:{{ $size }};--d:{{ $delay }};">{!! $sparkleSvg !!}</span>
            @endforeach
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Magicians, Mentalists & Variety Artists</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line"><span class="text-gradient-blue es-gradient-anim">Make your bookings appear.</span></span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line">Like magic.</span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                One link for all your shows. Event planners find your availability, fans follow your performances, no algorithm makes you invisible.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the trick
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Create your performance schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Performance-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Close-up', 'Stage', 'Mentalism', 'Illusion', 'Corporate', 'Kids Shows', 'Weddings', 'Variety', 'Escape', 'Hypnosis'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-blue-400 to-sky-400"></span>
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
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-blue-400 to-sky-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">The whole act</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything a working performer <span class="text-gradient-blue">needs</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Private Event Bookings (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Bookings
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">One link for event planners</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Wedding planners, corporate event coordinators, and party hosts find your availability, videos, and rates. Professional booking made simple.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Corporate events</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Weddings</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Private parties</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-blue-300 bg-gradient-to-br from-blue-50 to-sky-50 p-4 shadow-lg dark:border-blue-400/30 dark:from-blue-950 dark:to-sky-950">
                                        <div class="mb-3 text-[10px] font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-300">Booking Inquiry</div>
                                        <div class="space-y-3">
                                            <div class="es-ai-field rounded-lg border border-blue-300/30 bg-blue-500/10 p-3" style="--i: 0;">
                                                <div class="mb-1 flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-amber-400"></span><span class="text-sm font-medium text-gray-900 dark:text-white">Corporate Gala</span></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Dec 15 · Marriott Ballroom</div>
                                            </div>
                                            <div class="es-ai-field rounded-lg bg-gray-100 p-3 dark:bg-white/5" style="--i: 1;">
                                                <div class="mb-1 flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-cyan-400"></span><span class="text-sm font-medium text-gray-600 dark:text-gray-300">Wedding Reception</span></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Jan 8 · Private Venue</div>
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

                <!-- Newsletter -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Newsletter
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Reach fans directly</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">New show? Email your audience directly. No paying Facebook to reach your own followers.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative">
                                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-sky-500">
                                    <svg aria-hidden="true" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-cyan-400 text-[10px] font-bold text-white">!</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Virtual Shows (link) -->
                <a href="{{ marketing_url('/features/online-events') }}" class="es-bento group relative block" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            Virtual Magic
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400">Stream shows worldwide</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Perform for audiences anywhere. Sell tickets globally for your virtual magic shows.</p>
                        <span class="mt-auto inline-flex items-center gap-1 text-sm font-medium text-sky-600 transition-all group-hover:gap-2 dark:text-sky-400">
                            Learn more
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </span>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- Sell Tickets (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    Ticketing
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Keep 100% of ticket sales</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Your show, your revenue. Zero platform fees. Stripe goes directly to you. QR tickets for easy check-in.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="space-y-3">
                                    <div class="es-ai-field flex items-center justify-between rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 0;">
                                        <div><div class="font-medium text-gray-900 dark:text-white">General Admission</div><div class="text-xs text-emerald-600 dark:text-emerald-400">120 remaining</div></div>
                                        <div class="text-xl font-bold text-gray-900 dark:text-white">$25</div>
                                    </div>
                                    <div class="es-ai-field flex items-center justify-between rounded-xl border border-emerald-400/30 bg-emerald-500/15 p-3" style="--i: 1;">
                                        <div><div class="font-medium text-gray-900 dark:text-white">VIP Front Row</div><div class="text-xs text-emerald-600 dark:text-emerald-300">8 remaining</div></div>
                                        <div class="text-xl font-bold text-gray-900 dark:text-white">$75</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Calendar Sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Calendar
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sync with Google Calendar</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync keeps everything updated. Add a show anywhere, see it everywhere.</p>
                        <div class="relative mt-auto flex items-center justify-center gap-8 py-2" aria-hidden="true">
                            <div class="w-20 rounded-lg border border-blue-400/30 bg-blue-500/15 p-2">
                                <div class="mb-1 text-center text-[10px] font-medium text-blue-600 dark:text-blue-300">Schedule</div>
                                <div class="mb-1 h-1.5 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                <div class="h-1.5 w-3/4 rounded bg-blue-400/40"></div>
                            </div>
                            <div class="absolute left-1/2 top-1/2 h-px w-10 -translate-x-1/2 -translate-y-1/2 border-t border-dashed border-blue-300 dark:border-blue-500/40"></div>
                            <div class="es-sync-dot" style="left: calc(50% - 24px);"></div>
                            <div class="w-20 rounded-lg border border-gray-300 bg-gray-100 p-2 dark:border-white/20 dark:bg-white/10">
                                <div class="mb-1 text-center text-[10px] font-medium text-gray-600 dark:text-gray-300">Google</div>
                                <div class="mb-1 h-1.5 rounded bg-blue-400/50"></div>
                                <div class="h-1.5 w-3/4 rounded bg-green-400/50"></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Team Coordination -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Team
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Coordinate your crew</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Assistants, agents, technicians - everyone sees the schedule. No double-bookings.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/10" style="--i: 0;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-500 text-xs font-semibold text-white">DM</div>
                                <div class="flex-1 text-sm text-gray-900 dark:text-white">David</div>
                                <span class="inline-flex items-center rounded bg-blue-100 px-1.5 py-0.5 text-[10px] text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Magician</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-sky-500 text-xs font-semibold text-white">SK</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Sarah</div>
                                <span class="inline-flex items-center rounded bg-blue-100 px-1.5 py-0.5 text-[10px] text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Assistant</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Promo Graphics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Share-ready images</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate promotional graphics for social media. Perfect for Instagram and Facebook.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-32 w-32 -rotate-3 rounded-xl border border-amber-400/30 bg-gradient-to-br from-amber-500/25 to-orange-500/25 p-2 transition-transform duration-300 group-hover:rotate-0">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br from-blue-600/50 to-sky-600/50">
                                    <div class="mb-1 text-[10px] font-semibold text-white">MAGIC SHOW</div>
                                    <div class="text-xs font-bold text-amber-300">The Amazing David</div>
                                    <div class="mt-1 text-[8px] text-white/80">Sat 8PM · Grand Theater</div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500">
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
    <!-- 3. Where magic happens                                       -->
    <!-- ============================================================ -->
    @php
        $magicVenues = [
            ['Corporate', 'bg-blue-100 dark:bg-blue-900/30', 'text-blue-600 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />'],
            ['Weddings', 'bg-cyan-100 dark:bg-cyan-900/30', 'text-cyan-600 dark:text-cyan-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />'],
            ['Parties', 'bg-amber-100 dark:bg-amber-900/30', 'text-amber-600 dark:text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />'],
            ['Theaters', 'bg-blue-100 dark:bg-blue-900/30', 'text-blue-600 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />'],
            ['Restaurants', 'bg-teal-100 dark:bg-teal-900/30', 'text-teal-600 dark:text-teal-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />'],
            ['Virtual', 'bg-sky-100 dark:bg-sky-900/30', 'text-sky-600 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />'],
        ];
    @endphp
    <section class="bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <h2 class="es-balance mb-3 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Where magic happens
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">One schedule for every venue and event type</p>
            </div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6" data-reveal-group="60">
                @foreach ($magicVenues as [$mName, $mChip, $mText, $mIcon])
                    <div data-reveal class="group rounded-2xl border border-gray-200 bg-white p-6 text-center transition-all duration-200 hover:-translate-y-1 hover:border-blue-300 hover:shadow-md dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-500/30">
                        <div class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $mChip }} transition-transform group-hover:scale-110">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $mText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $mIcon !!}</svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $mName }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Perfect for                                               -->
    <!-- ============================================================ -->
    @php
        $magicPerformers = [
            ['Close-Up Magicians', 'Card tricks, coin magic, sleight of hand for intimate gatherings and table-hopping at events.', 'bg-blue-100 dark:bg-blue-500/20', 'text-blue-600 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />'],
            ['Stage Illusionists', 'Large-scale illusions and theatrical magic shows that fill theaters and wow audiences.', 'bg-blue-100 dark:bg-blue-500/20', 'text-blue-600 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />'],
            ['Mentalists', 'Mind reading, predictions, and psychological entertainment that leaves audiences amazed.', 'bg-sky-100 dark:bg-sky-500/20', 'text-sky-600 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />'],
            ["Children's Entertainers", 'Birthday parties, school shows, and family events with fun, interactive magic for kids.', 'bg-cyan-100 dark:bg-cyan-500/20', 'text-cyan-600 dark:text-cyan-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Corporate Magicians', 'Trade shows, conferences, and product launches with customized magic presentations.', 'bg-amber-100 dark:bg-amber-500/20', 'text-amber-600 dark:text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />'],
            ['Variety Artists', 'Ventriloquists, escape artists, hypnotists, and specialty acts that defy categorization.', 'bg-sky-100 dark:bg-sky-500/20', 'text-sky-600 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all types of <span class="text-gradient-blue">magic performers</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether you're doing close-up magic or grand illusions, Event Schedule works for you.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                @foreach ($magicPerformers as [$pName, $pDesc, $pChip, $pText, $pIcon])
                    <div data-reveal class="rounded-2xl border border-gray-200 bg-white p-6 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $pChip }}">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $pText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $pIcon !!}</svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $pName }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $pDesc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. How it works: the reveal (dark)                           -->
    <!-- ============================================================ -->
    <section class="relative bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="opacity: 0.28;"></div>
                <div class="es-aurora es-aurora-2" style="opacity: 0.22;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                @foreach ([['top:14%;left:10%','1.4rem','0s'],['top:22%;right:12%','1rem','0.7s'],['bottom:18%;left:16%','1.2rem','1.3s'],['bottom:24%;right:14%','0.9rem','1.9s']] as [$pos,$size,$delay])
                    <span class="es-sparkle" style="{{ $pos }};width:{{ $size }};height:{{ $size }};--d:{{ $delay }};">{!! $sparkleSvg !!}</span>
                @endforeach
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">Quick setup</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Get your performance schedule online in <span class="text-gradient-blue">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 text-xl font-bold text-white shadow-lg shadow-blue-500/30">1</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Add your shows</h3>
                        <p class="text-sm text-gray-400">Performances, private bookings, recurring gigs. Import from Google Calendar or add manually.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 text-xl font-bold text-white shadow-lg shadow-blue-500/30">2</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Share one link</h3>
                        <p class="text-sm text-gray-400">Add to your website, social bios, and EPK. Planners see your availability instantly.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 text-xl font-bold text-white shadow-lg shadow-blue-500/30">3</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Build your audience</h3>
                        <p class="text-sm text-gray-400">Fans follow your schedule and get notified about shows near them.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Online events                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" data-reveal="panel" class="es-bento group block">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                Virtual Magic Shows
                            </div>
                            <h3 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400 lg:text-3xl">Stream to audiences worldwide</h3>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Sell tickets to viewers anywhere. Virtual magic became huge and stays relevant - reach fans who can't make it to your live shows.</p>
                            <div class="mb-4 flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Zoom shows</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Global ticket sales</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Interactive magic</span>
                            </div>
                            <span class="inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                Learn more about online events
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="w-52 rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-300">Online Event</span>
                                    <div class="relative h-5 w-10 rounded-full bg-sky-500"><div class="absolute right-0.5 top-0.5 h-4 w-4 rounded-full bg-white"></div></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Zoom</span></div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-red-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">YouTube Live</span></div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Twitch</span></div>
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
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google Calendar" :url="marketing_url('/features/calendar-sync')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
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
                @foreach ([['/for-comedians', 'Comedians'], ['/for-circus-acrobatics', 'Circus & Acrobatics'], ['/for-theater-performers', 'Theater Performers'], ['/for-spoken-word', 'Spoken Word Artists']] as [$relHref, $relName])
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
                    Frequently asked <span class="text-gradient-blue">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything magicians ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for magicians?', 'Yes. Event Schedule is free forever for sharing your show schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I promote different types of shows in one schedule?', 'Yes. Use sub-schedules to organize by show type - stage shows, close-up performances, children\'s parties, corporate events, and workshops. Each event can have its own description, images, and ticket options.'],
                    ['How do audiences discover my performances?', 'Fans can follow your schedule and receive email notifications for new shows. Share your schedule link on your booking website, social media profiles, or embed it on any webpage. Send newsletters to followers with upcoming appearances.'],
                    ['Can I sell tickets to my shows?', 'Yes. Connect your Stripe account and sell tickets directly from your schedule. Create different ticket types for VIP, general admission, or meet-and-greet packages. Zero platform fees - you only pay Stripe\'s processing fees.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="opacity: 0.3;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    @foreach ([['top:16%;left:12%','1.4rem','0s'],['top:24%;right:14%','1rem','0.8s'],['bottom:20%;left:18%','1.2rem','1.5s']] as [$pos,$size,$delay])
                        <span class="es-sparkle" style="{{ $pos }};width:{{ $size }};height:{{ $size }};--d:{{ $delay }};">{!! $sparkleSvg !!}</span>
                    @endforeach
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        The secret to more bookings? <span class="text-gradient-blue">Let them find you.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Your magic deserves an audience. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
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
