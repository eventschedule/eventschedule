<x-marketing-layout>
    <x-slot name="title">Virtual & Online Event Hosting - Event Schedule</x-slot>
    <x-slot name="description">Host virtual events with ease. Toggle between in-person and online, add your streaming URL, and sell tickets to attendees worldwide.</x-slot>
    <x-slot name="breadcrumbTitle">Online Events</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Online Events",
        "description": "Host virtual events with ease. Toggle between in-person and online, add your streaming URL, and sell tickets to attendees worldwide.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Toggle online/in-person events",
            "Any streaming platform support",
            "Streaming URL on tickets",
            "Virtual event ticketing",
            "QR code check-in"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free plan available"
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
                "name": "What platforms are supported for online events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event Schedule works with any platform that provides a link - Zoom, Google Meet, Microsoft Teams, YouTube Live, Twitch, and more. Simply paste your meeting or stream URL when creating the event."
                }
            },
            {
                "@type": "Question",
                "name": "When do attendees see the online event link?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The link is only visible to ticket holders or registered attendees. For free events, attendees see the link after following your schedule. You control who gets access."
                }
            },
            {
                "@type": "Question",
                "name": "How does timezone handling work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Events are displayed in each visitor's local timezone automatically. You set the event time in your timezone, and attendees see the correct time for their location."
                }
            }
        ]
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
        /* For online-events "Go Live" styles. The shared es-* motion system lives in
           marketing.css; this holds the sky/blue glow gradient, the drifting live card,
           and the broadcast-signal motif (live streams radiating out to the world). */
        .text-gradient-online {
            background: linear-gradient(135deg, #0284c7, #2563eb, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(37, 99, 235, 0.3);
        }
        .dark .text-gradient-online {
            background: linear-gradient(135deg, #38bdf8, #60a5fa, #22d3ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(56, 189, 248, 0.3);
        }
        @keyframes es-online-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-online-float { animation: es-online-float 6s ease-in-out infinite; }

        /* Broadcast-signal motif: dots emit expanding rings, like live streams going
           out to audiences everywhere. */
        .es-signals { display: flex; align-items: center; }
        .es-signal {
            position: relative; flex: 0 0 auto; width: 10px; height: 10px;
            border-radius: 9999px; background: rgba(56, 189, 248, 0.9);
        }
        .es-signal::before, .es-signal::after {
            content: ""; position: absolute; inset: 0; border-radius: 9999px;
            border: 1.5px solid rgba(56, 189, 248, 0.65);
            animation: es-signal-ring var(--sg-dur, 3s) ease-out infinite;
            animation-delay: var(--sg-delay, 0s);
        }
        .es-signal::after { animation-delay: calc(var(--sg-delay, 0s) + 1.4s); }
        @keyframes es-signal-ring {
            0% { transform: scale(1); opacity: 0.75; }
            100% { transform: scale(4.5); opacity: 0; }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-online-float, .animate-pulse-slow { animation: none !important; }
            .es-signal::before, .es-signal::after { animation: none !important; opacity: 0; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: online events                                       -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(37, 99, 235, 0.28), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Broadcast signal emitters along the bottom edge -->
            <div class="es-signals absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-3xl items-center justify-around px-8 opacity-60 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 9; $i++)
                    @php $dur = 2.8 + ($i % 4) * 0.4; $delay = ($i % 9) * 0.34; @endphp
                    <span class="es-signal" style="--sg-dur: {{ $dur }}s; --sg-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Online Events</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Go live,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-online">anywhere</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Host virtual events with any streaming platform. Share your link with ticket holders and reach audiences worldwide.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_events') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Events guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento features                                           -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Any event, <span class="text-gradient-online">online in a click</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">

                <!-- Easy toggle (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    Easy Toggle
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Switch in one click</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Toggle any event between in-person and online with a single checkbox. Add your streaming URL and you're ready to go live.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">In-Person</span>
                                    <span class="inline-flex items-center rounded-full border border-sky-400/30 bg-sky-500/20 px-3 py-1 text-sm text-sky-700 dark:text-sky-300">Online</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-64" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-4 flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Online Event</span>
                                        <div class="relative h-6 w-12 rounded-full bg-sky-500">
                                            <div class="absolute right-1 top-1 h-4 w-4 rounded-full bg-white"></div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs text-gray-500">Streaming URL</label>
                                        <div class="truncate rounded-lg bg-gray-200 px-3 py-2 font-mono text-sm text-sky-600 dark:bg-white/5 dark:text-sky-400">
                                            zoom.us/j/123456789
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Any platform -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            Any Platform
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Your platform, your choice</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Works with any streaming service. Just paste your URL and attendees will see it on the event page and their ticket.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5" style="--i: 0;">
                                <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Zoom</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5" style="--i: 1;">
                                <span class="h-2 w-2 rounded-full bg-red-400"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">YouTube Live</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5" style="--i: 2;">
                                <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Twitch</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5" style="--i: 3;">
                                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Google Meet</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Link on ticket -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            On Your Ticket
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Link on every ticket</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Ticket holders see the streaming URL when they view their ticket. No searching through emails for the link.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2">
                                <svg aria-hidden="true" class="h-4 w-4 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Your Ticket</span>
                            </div>
                            <div class="mb-1 text-xs text-gray-500">Join the stream:</div>
                            <div class="font-mono text-sm text-sky-600 dark:text-sky-400">zoom.us/j/123...</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Sell tickets (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Full Ticketing
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Sell tickets to virtual events</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">All ticketing features work for online events. Multiple ticket types, QR codes, attendee management, and secure payments with Stripe.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4" aria-hidden="true">
                                @php
                                    $ticketTiles = [
                                        ['Multiple Tiers', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                                        ['QR Codes', 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
                                        ['Attendees', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                                        ['Secure Pay', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                                    ];
                                @endphp
                                @foreach ($ticketTiles as $ti => $tile)
                                    <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-4 text-center dark:border-white/10 dark:bg-[#0f0f14]" style="--i: {{ $ti }};">
                                        <svg aria-hidden="true" class="mx-auto mb-2 h-8 w-8 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $tile[1] }}" />
                                        </svg>
                                        <div class="text-sm text-sky-600 dark:text-sky-400">{{ $tile[0] }}</div>
                                    </div>
                                @endforeach
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
    <!-- 3. Perfect for any virtual event (dark band)                -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(14, 165, 233, 0.24), rgba(14, 165, 233, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(37, 99, 235, 0.2), rgba(37, 99, 235, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-signals absolute bottom-6 left-0 right-0 mx-auto flex h-16 max-w-3xl items-center justify-around px-8 opacity-50" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    @for ($i = 0; $i < 9; $i++)
                        @php $dur = 2.8 + ($i % 4) * 0.4; $delay = ($i % 9) * 0.34; @endphp
                        <span class="es-signal" style="--sg-dur: {{ $dur }}s; --sg-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Perfect for any <span class="text-gradient-online">virtual event</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">From webinars to concerts, reach your audience wherever they are.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @php
                        $virtualUseCases = [
                            ['Webinars', 'Host educational sessions, product demos, or training workshops for attendees worldwide.', '/for-webinars', 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                            ['Live Concerts', 'Stream live performances to fans who can\'t make it in person. Sell virtual tickets alongside venue tickets.', '/for-live-concerts', 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
                            ['Online Classes', 'Yoga, cooking, art, or fitness classes. Schedule recurring sessions and manage enrollments.', '/for-online-classes', 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                            ['Virtual Conferences', 'Host multi-day conferences with different ticket types for keynotes, workshops, and networking sessions.', '/for-virtual-conferences', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                            ['Live Q&A Sessions', 'Interactive sessions where speakers answer audience questions in real-time.', '/for-live-qa-sessions', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                            ['Watch Parties', 'Premiere screenings, movie nights, or sports viewing events with your community.', '/for-watch-parties', 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ];
                    @endphp
                    @foreach ($virtualUseCases as $uc)
                        <a href="{{ marketing_url($uc[2]) }}" data-reveal class="group flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-sky-500/30 hover:bg-white/[0.06]">
                            <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                                <svg aria-hidden="true" class="h-8 w-8 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $uc[3] }}" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-white">{{ $uc[0] }}</h3>
                            <p class="text-sm text-gray-300">{{ $uc[1] }}</p>
                            <span class="mt-4 inline-flex items-center justify-center gap-2 text-sm font-medium text-sky-400 transition-all group-hover:gap-3">
                                Learn more
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Next feature                                             -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute left-1/4 top-10 h-[300px] w-[300px] rounded-full bg-sky-600/20 blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 h-[200px] w-[200px] rounded-full bg-cyan-600/20 blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="90">

                <a href="{{ marketing_url('/features/sub-schedules') }}" data-reveal class="group block">
                    <div class="flex h-full flex-col rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-100 to-cyan-100 p-8 transition-all duration-300 hover:scale-[1.02] dark:border-white/10 dark:from-sky-900 dark:to-cyan-900 lg:p-10">
                        <div class="flex flex-1 flex-col items-center gap-8 lg:flex-row">
                            <div class="flex flex-1 flex-col text-center lg:text-left">
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-300 lg:text-3xl">Sub-schedules</h3>
                                <p class="mb-4 text-lg text-gray-600 dark:text-white/80">Organize events into categories. Perfect for venues with multiple rooms or event series.</p>
                                <span class="mt-auto inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                    Learn more
                                    <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Mini mockup: category list with colored dots -->
                            <div class="flex-shrink-0">
                                <div class="w-48 rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 text-[10px] text-gray-500">Sub-schedules</div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 rounded-lg border border-sky-500/30 bg-sky-500/20 p-2">
                                            <span class="h-2 w-2 rounded-full bg-sky-400"></span>
                                            <span class="text-xs text-gray-900 dark:text-white">Main Stage</span>
                                        </div>
                                        <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                                            <span class="h-2 w-2 rounded-full bg-cyan-400"></span>
                                            <span class="text-xs text-gray-600 dark:text-gray-300">Acoustic Room</span>
                                        </div>
                                        <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                                            <span class="text-xs text-gray-600 dark:text-gray-300">Outdoor Patio</span>
                                        </div>
                                        <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                                            <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                                            <span class="text-xs text-gray-600 dark:text-gray-300">Jazz Lounge</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Popular with -->
                <div data-reveal class="flex h-full flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-webinars') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Webinars</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-online-classes') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Online Classes</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-virtual-conferences') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Virtual Conferences</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-online">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about online events.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What platforms are supported for online events?', 'Event Schedule works with any platform that provides a link - Zoom, Google Meet, Microsoft Teams, YouTube Live, Twitch, and more. Simply paste your meeting or stream URL when creating the event.'],
                    ['When do attendees see the online event link?', 'The link is only visible to ticket holders or registered attendees. For free events, attendees see the link after following your schedule. You control who gets access.'],
                    ['How does timezone handling work?', 'Events are displayed in each visitor\'s local timezone automatically. You set the event time in your timezone, and attendees see the correct time for their location.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <!-- 6. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Recurring Events"
                        description="Set events to repeat on any schedule automatically"
                        :url="marketing_url('/features/recurring-events')"
                        icon-color="green"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Embed your event schedule on any website"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Fan Videos"
                        description="Let fans share videos and comments on your events"
                        :url="marketing_url('/features/fan-videos')"
                        icon-color="orange"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-sky-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-signals absolute bottom-6 left-0 right-0 mx-auto flex h-16 max-w-2xl items-center justify-around px-8 opacity-50" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 7; $i++)
                            @php $dur = 2.8 + ($i % 4) * 0.4; $delay = ($i % 7) * 0.4; @endphp
                            <span class="es-signal" style="--sg-dur: {{ $dur }}s; --sg-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-online">go virtual?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Start hosting online events today. Toggle any event to online and add your streaming URL.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-sky-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
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
