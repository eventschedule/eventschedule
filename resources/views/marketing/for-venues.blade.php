<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Venues | Manage Your Event Calendar</x-slot>
    <x-slot name="description">Fill your calendar with great events. Accept booking requests, sell tickets with QR check-in, and manage multiple rooms or stages. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Venues</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Venues",
        "description": "Fill your calendar with great events. Accept booking requests, sell tickets with QR check-in, and manage multiple rooms or stages. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Event Venues"
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
                "name": "Can I manage multiple rooms or stages?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize events by room, stage, or area. Main stage, rooftop bar, private room - each gets its own filterable view within your calendar. Visitors can filter by space or see everything at once."
                }
            },
            {
                "@type": "Question",
                "name": "How do performers request to play at my venue?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Enable the booking inbox on your schedule, and musicians, DJs, and other performers can submit requests to play at your venue. You review each request and approve or decline from your dashboard. Approved events are automatically added to your calendar."
                }
            },
            {
                "@type": "Question",
                "name": "Can I embed the calendar on my venue's website?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Copy a simple embed code and paste it into your website. The calendar updates automatically whenever you add or change events. It works with any website builder including WordPress, Squarespace, and Wix."
                }
            },
            {
                "@type": "Question",
                "name": "Can multiple staff members manage the calendar?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Invite team members with different permission levels. Owners have full control, managers can add and edit events, and door staff can scan tickets for check-in. Everyone works from the same calendar."
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
        "name": "Event Schedule for Venues",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Venue Management Software",
        "operatingSystem": "Web",
        "description": "Fill your calendar with great events. Accept booking requests, sell tickets with QR check-in, and manage multiple rooms or stages.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Public event calendar",
            "Booking request inbox",
            "QR code ticketing",
            "Multiple rooms/stages",
            "Team management",
            "Google Calendar sync"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "venue event calendar, venue booking management, venue schedule software, event space calendar, free venue scheduling",
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
           For-venues "The House" styles. The shared es-* motion system
           (aurora, reveals, bento, marquee, odometer, finale) lives in
           marketing.css; this block holds only this page's own effects:
           the chasing marquee bulbs and the panning house lights.
           ============================================================== */

        /* Theater marquee bulbs (a row of dots that light up in sequence) */
        .es-bulbs {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .es-bulbs i {
            width: 7px;
            height: 7px;
            border-radius: 9999px;
            background: rgba(14, 165, 233, 0.35);
            animation: es-bulb 1.4s ease-in-out infinite;
            animation-delay: calc(var(--i, 0) * 0.12s);
        }
        @keyframes es-bulb {
            0%, 100% { background: rgba(14, 165, 233, 0.3); box-shadow: none; }
            50% { background: #38bdf8; box-shadow: 0 0 10px rgba(56, 189, 248, 0.9); }
        }
        .es-bulbs-sm i { width: 5px; height: 5px; }

        /* Panning house lights (dark surfaces) */
        .es-houselight {
            position: absolute;
            top: -12%;
            width: 46%;
            height: 135%;
            pointer-events: none;
            transform-origin: 50% 0;
            animation: es-house-pan 10s ease-in-out infinite alternate;
        }
        .es-houselight-1 { left: 3%; background: conic-gradient(from 197deg at 50% 0%, transparent 0deg, rgba(56, 189, 248, 0.15) 11deg, transparent 24deg); }
        .es-houselight-2 { right: 3%; background: conic-gradient(from 149deg at 50% 0%, transparent 0deg, rgba(34, 211, 238, 0.13) 11deg, transparent 24deg); animation-delay: -4s; animation-duration: 12s; }
        .es-houselight-3 { left: 33%; background: conic-gradient(from 178deg at 50% 0%, transparent 0deg, rgba(78, 129, 250, 0.11) 9deg, transparent 20deg); animation-delay: -7s; animation-duration: 14s; }
        @keyframes es-house-pan { from { transform: rotate(-7deg); } to { transform: rotate(7deg); } }

        @media (prefers-reduced-motion: reduce) {
            .es-bulbs i,
            .es-houselight {
                animation: none !important;
            }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the house lights come up                            -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(56, 189, 248, 0.5), rgba(56, 189, 248, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(34, 211, 238, 0.45), rgba(34, 211, 238, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <!-- Marquee bulbs along the base -->
        <div class="pointer-events-none absolute inset-x-0 bottom-8 opacity-70 [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" aria-hidden="true">
            <div class="es-bulbs">
                @for ($i = 0; $i < 40; $i++)
                    <i style="--i: {{ $i % 10 }};"></i>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="es-bulbs es-bulbs-sm" aria-hidden="true"><i style="--i:0;"></i><i style="--i:1;"></i><i style="--i:2;"></i></span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Bars, Clubs & Event Spaces</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Fill your calendar</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient es-gradient-anim">with great events</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Showcase what's happening at your venue. Accept bookings, sell tickets, and keep your audience engaged.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the features
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                    Create your calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Venue-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($typeCopy = 0; $typeCopy < 2; $typeCopy++)
                                @foreach (['Bars', 'Nightclubs', 'Music Venues', 'Theaters', 'Comedy Clubs', 'Restaurants', 'Breweries', 'Art Galleries', 'Community Centers', 'Hotels', 'Libraries', 'Farmers Markets'] as $type)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-sky-400 to-cyan-400"></span>
                                        {{ $type }}
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
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-sky-400 to-cyan-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Front of house to back office</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything to run a <span class="text-gradient">packed room</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Public Event Calendar (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Public Calendar
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Beautiful event calendar</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Showcase all your upcoming events on a mobile-friendly calendar. Embed it on your website or share the link directly.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Embeddable</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Mobile-friendly</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Custom branding</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-sky-300 bg-gradient-to-br from-sky-50 to-cyan-50 p-4 shadow-lg dark:border-sky-400/30 dark:from-sky-950 dark:to-cyan-950">
                                        <div class="mb-3 text-center">
                                            <div class="font-semibold text-gray-900 dark:text-white">The Blue Note</div>
                                            <div class="text-sm text-sky-600 dark:text-sky-300">March 2024</div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-sky-400/30 bg-sky-500/15 p-2" style="--i: 0;">
                                                <div class="w-12 font-mono text-xs text-sky-600 dark:text-sky-300">Mar 15</div>
                                                <span class="text-sm text-gray-900 dark:text-white">Jazz Night</span>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;">
                                                <div class="w-12 font-mono text-xs text-gray-500 dark:text-gray-400">Mar 18</div>
                                                <span class="text-sm text-gray-600 dark:text-gray-300">Open Mic</span>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;">
                                                <div class="w-12 font-mono text-xs text-gray-500 dark:text-gray-400">Mar 22</div>
                                                <span class="text-sm text-gray-600 dark:text-gray-300">DJ Night</span>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 3;">
                                                <div class="w-12 font-mono text-xs text-gray-500 dark:text-gray-400">Mar 29</div>
                                                <span class="text-sm text-gray-600 dark:text-gray-300">Live Band</span>
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

                <!-- Accept Booking Requests -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Booking Inbox
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Accept performance requests</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Artists can request to play at your venue. Review, approve, or decline right from your dashboard.</p>

                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-sky-400/30 bg-sky-500/15 p-3" style="--i: 0;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-blue-500 text-xs font-semibold text-white">SJ</div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Sarah Johnson Trio</div>
                                    <div class="text-xs text-sky-700 dark:text-sky-300">Requesting Mar 22</div>
                                </div>
                                <div class="flex gap-1">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20">
                                        <svg aria-hidden="true" class="h-3 w-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500/20">
                                        <svg aria-hidden="true" class="h-3 w-3 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </div>
                                </div>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl bg-gray-100 p-3 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-500 text-xs font-semibold text-white">DJ</div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-600 dark:text-gray-300">DJ Nova</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Requesting Apr 5</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Ticketing with QR -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sell tickets with QR check-in</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Multiple ticket types, Stripe payments, and contactless check-in. Use any smartphone to scan.</p>

                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative rounded-xl bg-white p-2 shadow-md ring-1 ring-gray-200 dark:ring-white/10">
                                <svg class="h-24 w-24 text-gray-900" viewBox="0 0 29 29" fill="currentColor" aria-hidden="true">
                                    <path d="M0 0h9v9H0V0zm2 2v5h5V2H2zm1 1h3v3H3V3zm17-3h9v9h-9V0zm2 2v5h5V2h-5zm1 1h3v3h-3V3zM0 20h9v9H0v-9zm2 2v5h5v-5H2zm1 1h3v3H3v-3zM12 0h2v2h-2V0zm3 0h2v4h-2V0zm-3 4h2v3h-2V4zm3 3h4v2h-4V7zm-3 3h3v2h-3v-2zm5 0h2v3h-2v-3zm7 1h2v2h-2v-2zm3-1h2v4h-2v-4zM0 12h2v2H0v-2zm3 0h4v2H3v-2zm5 1h2v4H8v-4zm3 3h2v2h-2v-2zm3-2h3v2h-3v-2zm5 1h2v3h-2v-3zm3 1h4v2h-4v-2zm5 1h2v2h-2v-2zm-15 4h4v2h-4v-2zm5 1h2v2h-2v-2zm3-2h2v4h-2v-4zm3 2h4v2h-4v-2zm-7 3h2v4h-2v-4zm-3 1h2v3h-2v-3zm8 0h3v2h-3v-2zm5-1h2v4h-2v-4z"/>
                                </svg>
                                <div class="es-laser"></div>
                            </div>
                        </div>
                        <div class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">Scan to check in</div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Sub-schedules (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    Sub-schedules
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Multiple rooms & stages</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Organize events by space. Main stage, rooftop bar, private room. Each gets its own filterable schedule.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">Filter by room</div>
                                <div class="space-y-2">
                                    <div class="es-ai-field flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/15 p-2" style="--i: 0;">
                                        <div class="h-2 w-2 rounded-full bg-emerald-400"></div>
                                        <span class="text-sm text-gray-900 dark:text-white">Main Stage</span>
                                        <span class="ml-auto text-xs text-emerald-700 dark:text-emerald-300">12 events</span>
                                    </div>
                                    <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;">
                                        <div class="h-2 w-2 rounded-full bg-teal-400"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Rooftop Bar</span>
                                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">8 events</span>
                                    </div>
                                    <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;">
                                        <div class="h-2 w-2 rounded-full bg-cyan-400"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Acoustic Lounge</span>
                                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">5 events</span>
                                    </div>
                                    <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 3;">
                                        <div class="h-2 w-2 rounded-full bg-green-400"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Private Room</span>
                                        <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">3 events</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Team Management -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Team
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Staff management</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Give your team access to manage events. Set permissions so everyone has the right level of control.</p>

                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/10" style="--i: 0;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 text-xs font-semibold text-white">MO</div>
                                <div class="flex-1 text-sm text-gray-900 dark:text-white">Mike</div>
                                <span class="inline-flex items-center rounded bg-cyan-500/20 px-1.5 py-0.5 text-[10px] text-cyan-700 dark:text-cyan-300">Owner</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-xs font-semibold text-white">LR</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Lisa</div>
                                <span class="inline-flex items-center rounded bg-teal-500/20 px-1.5 py-0.5 text-[10px] text-teal-700 dark:text-teal-300">Manager</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 2;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-green-500 text-xs font-semibold text-white">TC</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Tom</div>
                                <span class="inline-flex items-center rounded bg-emerald-500/20 px-1.5 py-0.5 text-[10px] text-emerald-700 dark:text-emerald-300">Door Staff</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Google Calendar Sync (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Calendar Sync
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Google Calendar sync</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Two-way sync keeps your venue calendar and Google Calendar in perfect harmony. Changes flow both ways automatically.</p>
                            </div>
                            <div class="relative flex items-center justify-center gap-8 py-2" aria-hidden="true">
                                <div class="w-24 rounded-xl border border-blue-400/30 bg-blue-500/15 p-3">
                                    <div class="mb-1 text-center text-[10px] font-medium text-blue-700 dark:text-blue-300">Venue</div>
                                    <div class="space-y-1">
                                        <div class="rounded bg-sky-400/60 px-1 text-[6px] text-white">Jazz</div>
                                        <div class="rounded bg-blue-400/60 px-1 text-[6px] text-white">Open Mic</div>
                                    </div>
                                </div>
                                <div class="absolute left-1/2 top-1/2 h-px w-10 -translate-x-1/2 -translate-y-1/2 border-t border-dashed border-blue-300 dark:border-blue-500/40"></div>
                                <div class="es-sync-dot" style="left: calc(50% - 30px);"></div>
                                <div class="w-24 rounded-xl border border-gray-300 bg-gray-100 p-3 dark:border-white/20 dark:bg-white/10">
                                    <div class="mb-1 text-center text-[10px] font-medium text-gray-600 dark:text-gray-300">Google</div>
                                    <div class="space-y-1">
                                        <div class="h-2 rounded bg-blue-400/50"></div>
                                        <div class="h-2 rounded bg-green-400/50"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Event Graphics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Event graphics</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate promotional images for social media. Perfect for Instagram and Facebook posts.</p>

                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-32 w-32 -rotate-3 rounded-xl border border-amber-400/30 bg-gradient-to-br from-amber-500/25 to-orange-500/25 p-2 transition-transform duration-300 group-hover:rotate-0">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br from-sky-600/50 to-cyan-600/50">
                                    <div class="mb-1 text-[10px] font-semibold text-white">THIS FRIDAY</div>
                                    <div class="text-xs font-bold text-amber-300">Jazz Night</div>
                                    <div class="mt-1 text-[8px] text-white/80">The Blue Note • 9PM</div>
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

                <!-- Fan Videos & Comments (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    Fan Engagement
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Fan videos & comments</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">After the show, fans share YouTube clips and comments on your events. You approve what goes live. Run polls to let guests vote on upcoming themes or events.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">YouTube videos</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Comments</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Event polls</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Organizer approval</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="max-w-xs rounded-2xl border border-rose-300 bg-gradient-to-br from-rose-50 to-orange-50 p-4 dark:border-rose-400/30 dark:from-rose-950 dark:to-orange-950">
                                    <div class="mb-2 text-xs text-gray-500 dark:text-white/70">Jazz Night</div>
                                    <div class="mb-3 text-[10px] text-gray-400 dark:text-gray-500">Fri, Mar 15 at 9 PM</div>
                                    <div class="mb-3 flex items-center justify-center rounded-lg bg-gray-800 p-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-600">
                                            <svg aria-hidden="true" class="ml-0.5 h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-start gap-2">
                                            <div class="h-5 w-5 shrink-0 rounded-full bg-rose-300 dark:bg-rose-500/40"></div>
                                            <div class="rounded-lg bg-white px-2 py-1 text-[10px] text-gray-600 dark:bg-white/10 dark:text-gray-300">Incredible show!</div>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <div class="h-5 w-5 shrink-0 rounded-full bg-orange-300 dark:bg-orange-500/40"></div>
                                            <div class="rounded-lg bg-white px-2 py-1 text-[10px] text-gray-600 dark:bg-white/10 dark:text-gray-300">Best venue in town</div>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center gap-1 pt-1">
                                        <svg aria-hidden="true" class="h-3 w-3 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-[10px] text-rose-600 dark:text-rose-400">Approved by venue</span>
                                    </div>
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
    <!-- 3. Perfect for: every kind of venue                          -->
    <!-- ============================================================ -->
    @php
        $venueTypes = [
            ['/for-bars', 'Bars & Pubs', 'Promote live music nights, trivia, and special events to regulars.', 'bg-sky-100 dark:bg-sky-500/15', 'text-sky-600 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />'],
            ['/for-nightclubs', 'Nightclubs', 'Manage DJ lineups, themed nights, and VIP table reservations.', 'bg-cyan-100 dark:bg-cyan-500/15', 'text-cyan-600 dark:text-cyan-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />'],
            ['/for-music-venues', 'Music Venues', 'Book bands, sell tickets, and manage your concert calendar.', 'bg-blue-100 dark:bg-blue-500/15', 'text-blue-600 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.464a5 5 0 01-1.414-1.414m-2.828 9.9a9 9 0 01-2.728-2.728" />'],
            ['/for-theaters', 'Theaters', 'Schedule productions, manage show runs, and sell season tickets.', 'bg-rose-100 dark:bg-rose-500/15', 'text-rose-600 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />'],
            ['/for-comedy-clubs', 'Comedy Clubs', 'Book comedians, host open mics, and fill seats for headliners.', 'bg-amber-100 dark:bg-amber-500/15', 'text-amber-600 dark:text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['/for-restaurants', 'Restaurants', 'Promote wine tastings, chef\'s tables, and live entertainment.', 'bg-orange-100 dark:bg-orange-500/15', 'text-orange-600 dark:text-orange-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['/for-breweries-and-wineries', 'Breweries & Wineries', 'Host tastings, tours, and live music in your taproom.', 'bg-sky-100 dark:bg-sky-500/15', 'text-sky-600 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />'],
            ['/for-art-galleries', 'Art Galleries & Studios', 'Announce openings, exhibitions, and artist meet-and-greets.', 'bg-blue-100 dark:bg-blue-500/15', 'text-blue-600 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
            ['/for-community-centers', 'Community Centers', 'Organize classes, workshops, and community gatherings.', 'bg-emerald-100 dark:bg-emerald-500/15', 'text-emerald-600 dark:text-emerald-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />'],
            ['/for-farmers-markets', 'Farmers Markets', 'Share your market schedule and build a loyal shopper community.', 'bg-lime-100 dark:bg-lime-500/15', 'text-lime-600 dark:text-lime-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />'],
            ['/for-hotels-and-resorts', 'Hotels & Resorts', 'Elevate the guest experience with activity calendars and events.', 'bg-slate-100 dark:bg-slate-500/15', 'text-slate-600 dark:text-slate-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />'],
            ['/for-libraries', 'Libraries', 'Share programs, author events, and community activities.', 'bg-sky-100 dark:bg-sky-500/15', 'text-sky-600 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />'],
        ];
    @endphp
    <section id="perfect-for" class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all types of <span class="text-gradient">venues</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From intimate lounges to large event spaces, Event Schedule adapts to your needs.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="55">
                @foreach ($venueTypes as [$vHref, $vName, $vDesc, $vChip, $vText, $vIcon])
                    <a href="{{ marketing_url($vHref) }}" data-reveal class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:border-sky-300 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-sky-500/30">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $vChip }}">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $vText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $vIcon !!}</svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $vName }}</h3>
                        <p class="flex-grow text-sm text-gray-600 dark:text-gray-400">{{ $vDesc }}</p>
                        <span class="mt-3 inline-flex items-center gap-1 text-sm font-medium {{ $vText }} transition-all group-hover:gap-2">
                            Learn more
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Stream to the world                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" data-reveal="panel" class="es-bento group block">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Online Events
                            </div>
                            <h3 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400 lg:text-3xl">Stream to the world</h3>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Share live performances with fans worldwide. Add your streaming URL and sell tickets to viewers anywhere. No venue required.</p>
                            <div class="mb-4 flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Live streaming</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Global ticket sales</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Any platform</span>
                            </div>
                            <span class="inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                Learn more
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="w-52 rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-300">Online Event</span>
                                    <div class="relative h-5 w-10 rounded-full bg-sky-500">
                                        <div class="absolute right-0.5 top-0.5 h-4 w-4 rounded-full bg-white"></div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-blue-500"></div><span class="text-xs text-gray-600 dark:text-gray-300">Zoom</span></div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-red-500"></div><span class="text-xs text-gray-600 dark:text-gray-300">YouTube Live</span></div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-blue-500"></div><span class="text-xs text-gray-600 dark:text-gray-300">Twitch</span></div>
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
    <!-- 5. How it works: after dark (dark band)                      -->
    <!-- ============================================================ -->
    <section class="relative bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-houselight es-houselight-1"></div>
                <div class="es-houselight es-houselight-2"></div>
                <div class="es-houselight es-houselight-3"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-6 opacity-70 [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" aria-hidden="true">
                <div class="es-bulbs">
                    @for ($i = 0; $i < 40; $i++)
                        <i style="--i: {{ $i % 10 }};"></i>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="es-bulbs es-bulbs-sm" aria-hidden="true"><i style="--i:0;"></i><i style="--i:1;"></i><i style="--i:2;"></i></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">Quick setup</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Get your venue calendar online in <span class="text-gradient">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-xl font-bold text-white shadow-lg shadow-sky-500/30">1</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Create Your Calendar</h3>
                        <p class="text-sm text-gray-400">Sign up and add your venue details. Set up rooms or stages if you have multiple spaces.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-xl font-bold text-white shadow-lg shadow-sky-500/30">2</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Add Your Events</h3>
                        <p class="text-sm text-gray-400">Add events manually, import from Google Calendar, or accept booking requests from performers.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-xl font-bold text-white shadow-lg shadow-sky-500/30">3</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Share & Sell</h3>
                        <p class="text-sm text-gray-400">Embed on your website, share on social, and start selling tickets with QR check-in.</p>
                    </div>
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
                        name="Analytics"
                        description="Track page views, devices, and traffic sources"
                        :url="marketing_url('/features/analytics')"
                        icon-color="emerald"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Sub-Schedules"
                        description="Organize events into categories and groups"
                        :url="marketing_url('/features/sub-schedules')"
                        icon-color="rose"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
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
    <!-- 7. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-music-venues', 'Music Venues'], ['/for-comedy-clubs', 'Comedy Clubs'], ['/for-theaters', 'Theaters'], ['/for-bars', 'Bars']] as [$relHref, $relName])
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
                    Frequently asked <span class="text-gradient">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything venues ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Can I manage multiple rooms or stages?', 'Yes. Use sub-schedules to organize events by room, stage, or area. Main stage, rooftop bar, private room - each gets its own filterable view within your calendar. Visitors can filter by space or see everything at once.'],
                    ['How do performers request to play at my venue?', 'Enable the booking inbox on your schedule, and musicians, DJs, and other performers can submit requests to play at your venue. You review each request and approve or decline from your dashboard. Approved events are automatically added to your calendar.'],
                    ['Can I embed the calendar on my venue\'s website?', 'Yes. Copy a simple embed code and paste it into your website. The calendar updates automatically whenever you add or change events. It works with any website builder including WordPress, Squarespace, and Wix.'],
                    ['Can multiple staff members manage the calendar?', 'Yes. Invite team members with different permission levels. Owners have full control, managers can add and edit events, and door staff can scan tickets for check-in. Everyone works from the same calendar.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-sky-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-houselight es-houselight-1"></div>
                    <div class="es-houselight es-houselight-2"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="pointer-events-none absolute inset-x-0 bottom-6 opacity-70 [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" aria-hidden="true">
                    <div class="es-bulbs">
                        @for ($i = 0; $i < 40; $i++)
                            <i style="--i: {{ $i % 10 }};"></i>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Keep your venue calendar <span class="text-gradient">packed</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your calendar in minutes. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-sky-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
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
