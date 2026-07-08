<x-marketing-layout>
    <x-slot name="title">White-Label SaaS Platform - Run Your Own Ticketing Business</x-slot>
    <x-slot name="description">Launch your own white-label ticketing SaaS platform at zero cost. Set your own prices, keep 100% of revenue through your branded platform.</x-slot>
    <x-slot name="breadcrumbTitle">SaaS</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule White-Label SaaS Platform",
        "description": "Launch your own white-label ticketing SaaS platform at zero cost. Set your own prices, keep 100% of revenue through your branded platform.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "White-Label SaaS Platform",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free to start, keep 100% of revenue"
        },
        "url": "{{ url()->current() }}"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Do I get access to all platform features as an operator?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. As a SaaS operator, you run the full platform on your own server with access to every feature. Your customers get features based on the plan tier they subscribe to (Free, Pro, or Enterprise), which you control."
                }
            },
            {
                "@type": "Question",
                "name": "Is there a limit on the number of clients I can have?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. There are no limits on the number of customers or schedules on your platform. Each customer gets their own subdomain (customer.yourdomain.com), and you can grow without restrictions."
                }
            },
            {
                "@type": "Question",
                "name": "Are there limits on event attendees or participants?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "There are no platform-imposed limits on event attendees. Event creators can optionally set their own capacity limits per ticket type, but the platform itself does not restrict attendance numbers."
                }
            },
            {
                "@type": "Question",
                "name": "Can I customize features across different pricing tiers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The platform includes built-in Free, Pro, and Enterprise tiers with automatic feature gating. You set your own prices via Stripe and can configure trial length. Features are gated per tier out of the box."
                }
            },
            {
                "@type": "Question",
                "name": "Will the platform be hosted on your servers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. You host it on your own servers. You control the infrastructure, the data, and the entire installation. It runs on your hardware, under your domain, with your branding."
                }
            },
            {
                "@type": "Question",
                "name": "How is GDPR compliance handled?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Since you host the platform yourself, you are the data controller and manage compliance for your jurisdiction. The open-source codebase means you can audit every line of code and customize it to meet any regulatory requirements."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (blue to sky to cyan) */
        .text-gradient-saas {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-saas {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-saas {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live multi-tenant platform card */
        .es-saas-float { animation: es-saas-bob 5.5s ease-in-out infinite; }
        @keyframes es-saas-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a grid of tenant tiles (customers on your platform) */
        .es-tenant {
            flex: 0 0 auto;
            width: 22px;
            height: 22px;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.7), rgba(14, 165, 233, 0.35));
            animation: es-tenant-pulse var(--tn-dur, 3s) ease-in-out infinite;
            animation-delay: var(--tn-delay, 0s);
        }
        @keyframes es-tenant-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.86); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-saas-float, .es-tenant, .animate-pulse-slow, .animate-ping, .animate-float { animation: none !important; }
            .es-tenant { opacity: 0.5; transform: none; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- 1. Hero                                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(78svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Tenant-tile motif along the bottom edge -->
            <div class="es-tenants absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-3 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 20; $i++)
                    @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 8) * 0.26; @endphp
                    <span class="es-tenant" style="--tn-dur: {{ $dur }}s; --tn-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                </span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Free White-Label Platform</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your platform, your brand,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-saas">zero cost</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Launch your own ticketing SaaS business. Set your own prices, keep 100% of what you charge your customers, and build your brand.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('marketing.docs.saas.setup') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    View Setup Guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    GitHub Repository
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Try the Demo                                             -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-24 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Live Demo
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>See it <span class="text-gradient-saas">in action</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Explore both sides of Event Schedule - the admin portal where you manage everything, and the public calendar your attendees see.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="80">
                @php
                    $demos = [
                        [demo_url(), 'Admin Portal', 'Where schedule owners manage events, tickets, settings, and more. Create events, track sales, and customize your calendar.', 'Open Admin Demo', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                        ['https://simpsons.eventschedule.com', 'Guest Portal', 'The public-facing calendar your attendees see. Browse events, purchase tickets, and subscribe to updates.', 'Open Guest Demo', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ];
                @endphp
                @foreach ($demos as $d)
                    <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                        <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                            <div class="mb-4 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-sky-600 shadow-lg">
                                    <svg aria-hidden="true" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $d[4] }}" />
                                        @if ($loop->first)<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />@endif
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $d[1] }}</h3>
                            </div>
                            <p class="mb-6 flex-grow text-gray-600 dark:text-gray-400">{{ $d[2] }}</p>
                            <a href="{{ $d[0] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-3 font-medium text-white transition-colors hover:from-blue-500 hover:to-sky-500">
                                {{ $d[3] }}
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Features - Run it your way                               -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-sky-500/20 px-3 py-1 text-sm font-medium text-sky-700 dark:text-sky-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Your Business
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Run it <span class="text-gradient-saas">your way</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Full control over pricing, branding, and customer experience.</p>
            </div>

            <div class="mx-auto grid max-w-5xl gap-4 md:grid-cols-2" data-reveal-group="80">
                <!-- Set Your Own Prices -->
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Flexible Pricing
                        </div>
                        <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Set your own prices</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Charge whatever you want for your service. Offer free plans, premium tiers, or anything in between. Your pricing, your rules.</p>
                        <ul class="mt-auto space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            @foreach (['Offer free plans to your customers', 'Create premium subscription tiers', 'Build your own business model'] as $li)
                                <li class="flex items-center gap-2">
                                    <svg aria-hidden="true" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    {{ $li }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Configurable Trials -->
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Trial Management
                        </div>
                        <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Configurable trials</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Control how long your customers can try the platform before subscribing. Set the trial length via a simple environment variable.</p>
                        <div class="mb-4 rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                            <code class="font-mono text-sm text-gray-600 dark:text-gray-300"><span class="text-blue-500 dark:text-blue-400">TRIAL_DAYS</span>=<span class="text-green-500 dark:text-green-400">14</span></code>
                        </div>
                        <ul class="mt-auto space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            @foreach (['Set any trial length', 'No trial required (set to 0)'] as $li)
                                <li class="flex items-center gap-2">
                                    <svg aria-hidden="true" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    {{ $li }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- White-Label Branding -->
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            Your Brand
                        </div>
                        <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">White-label branding</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Make it yours with custom logos for both dark and light mode, your own domain, and your brand identity throughout the platform.</p>
                        <ul class="mt-auto space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            @foreach (['Custom logos (dark/light mode)', 'Your own domain', 'Custom app name'] as $li)
                                <li class="flex items-center gap-2">
                                    <svg aria-hidden="true" class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    {{ $li }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Your Revenue -->
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Keep Everything
                        </div>
                        <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Your revenue</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Keep 100% of what you charge your customers. No revenue share, no hidden fees, no platform commissions.</p>
                        <ul class="mt-auto space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            @foreach (['No revenue share', 'No platform fees', 'Your Stripe, your money'] as $li)
                                <li class="flex items-center gap-2">
                                    <svg aria-hidden="true" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    {{ $li }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Why It's Free                                            -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-24 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    The Catch
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Why is it <span class="text-gradient-saas">free?</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">We believe in building a community together. In exchange for the free platform, we ask for simple attribution.</p>
            </div>

            <div class="mx-auto grid max-w-3xl gap-6 md:grid-cols-2" data-reveal-group="90">
                @php
                    $catch = [
                        ['AAL License', 'Follow the Attribution Assurance License requirements', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        ['Small Backlink', 'Discreet link in corner of public schedule pages', 'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14'],
                    ];
                @endphp
                @foreach ($catch as $c)
                    <div data-reveal class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-sky-50 p-6 dark:border-blue-500/20 dark:from-blue-900/30 dark:to-sky-900/30">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/20">
                            <svg aria-hidden="true" class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c[2] }}" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $c[0] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $c[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. What Your Customers Get                                  -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-amber-500/20 px-3 py-1 text-sm font-medium text-amber-700 dark:text-amber-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        For Your Customers
                    </div>
                    <h2 class="mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">What your customers get</h2>
                    <p class="mb-8 text-lg text-gray-500 dark:text-gray-400 sm:text-xl">Your customers get a full-featured event management platform with everything they need to succeed.</p>
                    <ul class="space-y-4">
                        @php
                            $customerFeatures = [
                                ['Sell tickets via Stripe Connect', 'Let your customers sell tickets and charge their own customers'],
                                ['Create and manage events', 'Full event management with recurring events, groups, and more'],
                                ['Their own subdomains', 'Each customer gets customer.yourdomain.com'],
                                ['Google Calendar sync', 'Bidirectional sync with Google Calendar'],
                            ];
                        @endphp
                        @foreach ($customerFeatures as $cf)
                            <li class="flex items-start gap-4">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/20">
                                    <svg aria-hidden="true" class="h-4 w-4 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $cf[0] }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $cf[1] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="relative" data-reveal style="--reveal-delay: 0.1s;">
                    <div class="es-saas-float rounded-2xl border border-gray-200 bg-gradient-to-br from-amber-100 to-orange-100 p-6 shadow-2xl dark:border-white/10 dark:from-amber-900 dark:to-orange-900">
                        <div class="mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-sky-500 text-sm font-bold text-white">YB</div>
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">Your Brand</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">yourdomain.com</div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3" aria-hidden="true">
                            @php
                                $tenants = [
                                    ['acme.yourdomain.com', 'Pro Plan', 'bg-blue-500/30', 'text-blue-500 dark:text-blue-300'],
                                    ['startup.yourdomain.com', 'Free Plan', 'bg-emerald-500/30', 'text-emerald-500 dark:text-emerald-300'],
                                    ['events.yourdomain.com', 'Trial (12 days left)', 'bg-blue-500/30', 'text-blue-500 dark:text-blue-300'],
                                ];
                            @endphp
                            @foreach ($tenants as $t)
                                <div class="flex items-center gap-3 rounded-xl bg-gray-200 p-3 dark:bg-[#0f0f14]">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $t[2] }}">
                                        <svg aria-hidden="true" class="h-4 w-4 {{ $t[3] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ $t[0] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $t[1] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Coming Soon: Federation                                  -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-24 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-sky-500/20 px-3 py-1 text-sm font-medium text-sky-700 dark:text-sky-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Coming Soon
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal><span class="text-gradient-saas">Federation</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">An optional feature that benefits the entire community.</p>
            </div>

            <div class="mx-auto max-w-4xl" data-reveal="panel">
                <div class="es-bento group relative" data-tilt="3">
                    <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="grid gap-8 md:grid-cols-2">
                            <div>
                                <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Share virtual events</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400">Optionally share your customers' virtual/online events with the central eventschedule.com listing. Not required - completely opt-in.</p>
                                <ul class="space-y-3">
                                    @foreach (['Get SEO traffic to your installation', 'Expose events to wider audience', 'Completely optional'] as $li)
                                        <li class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            {{ $li }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div>
                                <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Benefits the community</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400">When everyone participates, everyone benefits. More events means more users, which means a better platform for all.</p>
                                <div class="rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/30">
                                            <svg aria-hidden="true" class="h-4 w-4 text-sky-500 dark:text-sky-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">Global event discovery</span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Virtual events from participating SaaS installations appear on eventschedule.com with links back to your platform.</p>
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
    <!-- 7. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Common Questions
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-saas">questions</span></h2>
                <p class="mx-auto max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common questions from SaaS operators considering the platform.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['Do I get access to all platform features as an operator?', 'Yes. As a SaaS operator, you run the full platform on your own server with access to every feature. Your customers get features based on the plan tier they subscribe to (Free, Pro, or Enterprise), which you control.'],
                        ['Is there a limit on the number of clients I can have?', 'No. There are no limits on the number of customers or schedules on your platform. Each customer gets their own subdomain (customer.yourdomain.com), and you can grow without restrictions.'],
                        ['Are there limits on event attendees or participants?', 'There are no platform-imposed limits on event attendees. Event creators can optionally set their own capacity limits per ticket type, but the platform itself does not restrict attendance numbers.'],
                        ['Can I customize features across different pricing tiers?', 'Yes. The platform includes built-in Free, Pro, and Enterprise tiers with automatic feature gating. You set your own prices via Stripe and can configure trial length. Features are gated per tier out of the box.'],
                        ['Will the platform be hosted on your servers?', 'No. You host it on your own servers. You control the infrastructure, the data, and the entire installation. It runs on your hardware, under your domain, with your branding.'],
                        ['How is GDPR compliance handled?', 'Since you host the platform yourself, you are the data controller and manage compliance for your jurisdiction. The open-source codebase means you can audit every line of code and customize it to meet any regulatory requirements.'],
                    ];
                @endphp
                @foreach ($faqs as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="pr-4 text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <!-- 8. Community                                                -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-24 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <div class="mx-auto mb-8 inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gray-200 dark:bg-white/10" data-reveal>
                <svg aria-hidden="true" class="h-10 w-10 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
            </div>
            <h2 class="es-balance mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Building <span class="text-gradient-saas">together</span></h2>
            <p class="mx-auto mb-8 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">We're all working toward the same goal: a better event management platform. Open source means everyone benefits from shared improvements.</p>

            <div class="mb-10 grid gap-6 sm:grid-cols-3" data-reveal-group="80">
                @foreach ([['100%', 'Open Source'], ['Free', 'Forever'], ['AAL', 'Licensed']] as $stat)
                    <div data-reveal class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-sky-50 p-6 dark:border-blue-500/20 dark:from-blue-900/30 dark:to-sky-900/30">
                        <div class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stat[0] }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $stat[1] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap justify-center gap-4" data-reveal>
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-colors hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                    <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    Contribute on GitHub
                </a>
                <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-colors hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                    Join Discussions
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Finale                                                   -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-tenants absolute bottom-6 left-0 right-0 mx-auto flex h-12 items-center justify-center gap-3 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 16; $i++)
                            @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 8) * 0.26; @endphp
                            <span class="es-tenant" style="--tn-dur: {{ $dur }}s; --tn-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to launch your <span class="text-gradient-saas">SaaS?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Get started with the setup guide. Everything you need to run your own white-label ticketing platform.
                    </p>

                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('marketing.docs.saas.setup') }}" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Read Setup Guide
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center rounded-2xl border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white transition-all hover:bg-white/10">
                            Or try eventschedule.com
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
