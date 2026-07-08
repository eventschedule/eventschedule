<x-marketing-layout>
    <x-slot name="title">Private Events | Password-Protected Events - Event Schedule</x-slot>
    <x-slot name="description">Password-protect events for VIP audiences or invite-only gatherings. Control who sees what with per-event privacy settings.</x-slot>
    <x-slot name="breadcrumbTitle">Private Events</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Private Events",
        "description": "Password-protect events for VIP audiences or invite-only gatherings. Control who sees what with per-event privacy settings.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Password-protected events",
            "Hidden from public schedule",
            "Per-event privacy control",
            "Mix public and private events"
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
                "name": "How do I make an event private?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When creating or editing an event, toggle the 'Private' option and set a password. Only visitors who enter the correct password can view the event details."
                }
            },
            {
                "@type": "Question",
                "name": "Can I have both public and private events on the same schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Privacy is set per event, so you can mix public and private events on a single schedule. Public events appear normally, while private events require a password to view."
                }
            },
            {
                "@type": "Question",
                "name": "Are private events hidden from the public schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Private events are hidden from your public schedule and calendar views. They can only be accessed via a direct link with the correct password."
                }
            },
            {
                "@type": "Question",
                "name": "Which plan includes private events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Private events are available on the Enterprise plan. Free plans include unlimited public events."
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
        /* For private-events "The Vault" styles. The shared es-* motion system lives in
           marketing.css; this holds the amber/gold glow gradient, the drifting password
           prompt, and the masked-dot motif (hidden content behind a password). */
        .text-gradient-private {
            background: linear-gradient(135deg, #ca8a04, #d97706, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(217, 119, 6, 0.3);
        }
        .dark .text-gradient-private {
            background: linear-gradient(135deg, #facc15, #fbbf24, #fcd34d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(250, 204, 21, 0.3);
        }
        @keyframes es-private-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-private-float { animation: es-private-float 6s ease-in-out infinite; }

        /* Masked-dot motif: dots blink in clustered groups, like hidden characters in a
           password field guarding private content. */
        .es-masks { display: flex; align-items: center; }
        .es-maskdot {
            flex: 0 0 auto; width: 8px; height: 8px; border-radius: 9999px;
            background: rgba(245, 158, 11, 0.85);
            animation: es-mask-blink var(--mk-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--mk-delay, 0s);
        }
        @keyframes es-mask-blink {
            0%, 100% { opacity: 0.2; transform: scale(0.7); }
            50% { opacity: 1; transform: scale(1); box-shadow: 0 0 8px rgba(245, 158, 11, 0.5); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-private-float, .es-maskdot, .animate-pulse-slow { animation: none !important; }
            .es-maskdot { opacity: 0.6; transform: scale(0.85); }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: private events                                      -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(217, 119, 6, 0.3), rgba(217, 119, 6, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(245, 158, 11, 0.28), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(202, 138, 4, 0.14), rgba(202, 138, 4, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Masked-dot motif along the bottom edge -->
            <div class="es-masks absolute bottom-10 left-0 right-0 mx-auto hidden h-16 max-w-3xl items-center justify-center px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 32; $i++)
                    @php $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 13) * 0.18; $mr = ($i % 4 === 3) ? 22 : 8; @endphp
                    <span class="es-maskdot" style="margin-right: {{ $mr }}px; --mk-dur: {{ $dur }}s; --mk-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Private Events</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your events,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-private">your rules</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Password-protect any event for VIP audiences or invite-only gatherings. Share the link and password only with the people who matter.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-600 to-yellow-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_events') }}#privacy" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Privacy guide
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
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Control <span class="text-gradient-private">who sees what</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Password protection (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Password Protected
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Lock it down</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Set a password on any event and only visitors who enter the correct password can view the details. Share the link and password with your audience however you choose.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Per-event password</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Instant access</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-64" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-4 flex items-center gap-2">
                                        <svg aria-hidden="true" class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">Private Event</span>
                                    </div>
                                    <label class="text-xs text-gray-500">Enter password</label>
                                    <div class="mt-1 flex items-center gap-2 rounded-lg bg-gray-200 px-3 py-2 dark:bg-white/5">
                                        <span class="text-sm tracking-widest text-gray-400">&#8226; &#8226; &#8226; &#8226; &#8226;</span>
                                    </div>
                                    <div class="mt-3 rounded-lg bg-amber-500 px-3 py-2 text-center text-sm font-medium text-white">View Event</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Hidden from public -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            Hidden
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Hidden from public</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Private events are hidden from your public schedule and calendar views. Only people with the direct link and password can find them.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5" style="--i: 0;">
                                <svg aria-hidden="true" class="h-4 w-4 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Jazz Night</span>
                                <span class="ml-auto text-[10px] text-green-600 dark:text-green-400">Public</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 dark:border-amber-500/20 dark:bg-amber-500/10" style="--i: 1;">
                                <svg aria-hidden="true" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">VIP Launch</span>
                                <span class="ml-auto text-[10px] text-amber-600 dark:text-amber-400">Private</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5" style="--i: 2;">
                                <svg aria-hidden="true" class="h-4 w-4 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Open Mic</span>
                                <span class="ml-auto text-[10px] text-green-600 dark:text-green-400">Public</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Per-event control -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Flexible
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Per-event control</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Privacy is set per event, not per schedule. Mix public and private events freely. Perfect for schedules that host both open and exclusive content.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-300">Private Event</span>
                                <div class="relative h-6 w-12 rounded-full bg-amber-500">
                                    <div class="absolute right-1 top-1 h-4 w-4 rounded-full bg-white"></div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs text-gray-500">Event Password</label>
                                <div class="rounded-lg bg-gray-200 px-3 py-2 font-mono text-sm text-amber-600 dark:bg-white/5 dark:text-amber-400">vip-access-2026</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Included with Enterprise (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                    Enterprise Feature
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Included with Enterprise</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">Private events are part of the Enterprise plan, alongside custom domains, multiple team members, AI features, and more. Start free and upgrade when you need privacy controls.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4" aria-hidden="true">
                                @php
                                    $entTiles = [
                                        ['Private Events', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                                        ['Team Access', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                                        ['Custom Fields', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                                        ['Sub-schedules', 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                                    ];
                                @endphp
                                @foreach ($entTiles as $et => $tile)
                                    <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-4 text-center dark:border-white/10 dark:bg-[#0f0f14]" style="--i: {{ $et }};">
                                        <svg aria-hidden="true" class="mx-auto mb-2 h-8 w-8 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $tile[1] }}" />
                                        </svg>
                                        <div class="text-sm text-amber-600 dark:text-amber-400">{{ $tile[0] }}</div>
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
    <!-- 3. Perfect for exclusive events (dark band)                 -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(217, 119, 6, 0.24), rgba(217, 119, 6, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-masks absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    @for ($i = 0; $i < 30; $i++)
                        @php $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 13) * 0.18; $mr = ($i % 4 === 3) ? 22 : 8; @endphp
                        <span class="es-maskdot" style="margin-right: {{ $mr }}px; --mk-dur: {{ $dur }}s; --mk-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Perfect for <span class="text-gradient-private">exclusive events</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Keep the right events visible to the right people.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="80">
                    @php
                        $privUseCases = [
                            ['VIP Events', 'Exclusive experiences for your most valued audience members.', 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
                            ['Membership Content', 'Members-only sessions, workshops, or previews behind a shared password.', 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2'],
                            ['Invite-Only', 'Share the link and password via email or message for curated guest lists.', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                            ['Corporate Events', 'Internal meetings, team events, or company gatherings kept off the public schedule.', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ];
                    @endphp
                    @foreach ($privUseCases as $uc)
                        <div data-reveal class="flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/30 hover:bg-white/[0.06]">
                            <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl border border-amber-500/20 bg-amber-500/10">
                                <svg aria-hidden="true" class="h-8 w-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $uc[2] }}" />
                                </svg>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-white">{{ $uc[0] }}</h3>
                            <p class="text-sm text-gray-300">{{ $uc[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-private">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about private events.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['How do I make an event private?', 'When creating or editing an event, toggle the "Private" option and set a password. Only visitors who enter the correct password can view the event details.'],
                    ['Can I have both public and private events on the same schedule?', 'Yes. Privacy is set per event, so you can mix public and private events on a single schedule. Public events appear normally, while private events require a password to view.'],
                    ['Are private events hidden from the public schedule?', 'Private events are hidden from your public schedule and calendar views. They can only be accessed via a direct link with the correct password.'],
                    ['Which plan includes private events?', 'Private events are available on the Enterprise plan. Free plans include unlimited public events. You can upgrade at any time from your account settings.'],
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
    <!-- 5. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing & QR Check-ins"
                        description="Sell tickets and scan QR codes for fast check-ins"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Online Events"
                        description="Host virtual events with any streaming platform"
                        :url="marketing_url('/features/online-events')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom Fields"
                        description="Collect additional info from ticket buyers"
                        :url="marketing_url('/features/custom-fields')"
                        icon-color="amber"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-amber-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(217, 119, 6, 0.3), rgba(217, 119, 6, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-masks absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 24; $i++)
                            @php $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 13) * 0.18; $mr = ($i % 4 === 3) ? 22 : 8; @endphp
                            <span class="es-maskdot" style="margin-right: {{ $mr }}px; --mk-dur: {{ $dur }}s; --mk-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-private">go private?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Start hosting private events today. Set a password and control who sees your events.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 to-yellow-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
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
