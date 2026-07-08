<x-marketing-layout>
    <x-slot name="title">Custom Domain | Use Your Own Domain - Event Schedule</x-slot>
    <x-slot name="description">Use your own domain name for your event schedule. Replace the default subdomain with a professional branded URL like events.yourdomain.com.</x-slot>
    <x-slot name="breadcrumbTitle">Custom Domain</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Custom Domain",
        "description": "Use your own domain name for your event schedule.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Custom domain mapping",
            "Branded schedule URLs",
            "Direct mode with automatic SSL",
            "Redirect mode via Cloudflare"
        ],
        "offers": {
            "@type": "Offer",
            "price": "15",
            "priceCurrency": "USD",
            "description": "Available on Enterprise plan"
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
                "name": "How do I set up a custom domain?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Choose between two modes: Direct mode (recommended) - add a CNAME record and SSL is provisioned automatically; or Redirect mode - set up Cloudflare to redirect your domain to your Event Schedule URL."
                }
            },
            {
                "@type": "Question",
                "name": "Do I need to buy a domain separately?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You need to own a domain name from any domain registrar. Event Schedule does not sell domains, but any domain you own can be used."
                }
            },
            {
                "@type": "Question",
                "name": "Is SSL/HTTPS included?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. In Direct mode, SSL is provisioned automatically. In Redirect mode, SSL is included for free through Cloudflare's free plan."
                }
            },
            {
                "@type": "Question",
                "name": "Which plan includes custom domains?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Custom domains are available on the Enterprise plan. Free and Pro plans use the default eventschedule.com subdomain."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (emerald to teal) */
        .text-gradient-domain {
            background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-domain {
            background: linear-gradient(135deg, #34d399 0%, #6ee7b7 50%, #a7f3d0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* On always-dark surfaces, keep the accent bright regardless of page mode */
        .es-band-dark .text-gradient-domain,
        .es-finale-panel .text-gradient-domain {
            background: linear-gradient(135deg, #34d399 0%, #6ee7b7 50%, #a7f3d0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live browser window on your own domain */
        .es-domain-float { animation: es-domain-bob 5.5s ease-in-out infinite; }
        @keyframes es-domain-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a row of address bars (domains) that breathe */
        .es-addrs { display: flex; }
        .es-addr {
            flex: 0 0 auto;
            height: 11px;
            border-radius: 9999px;
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.7), rgba(20, 184, 166, 0.3));
            position: relative;
            animation: es-addr-pulse var(--ad-dur, 3s) ease-in-out infinite;
            animation-delay: var(--ad-delay, 0s);
        }
        .es-addr::before {
            content: '';
            position: absolute;
            left: 4px;
            top: 50%;
            width: 6px;
            height: 6px;
            margin-top: -3px;
            border-radius: 9999px;
            background: rgba(16, 185, 129, 0.95);
        }
        @keyframes es-addr-pulse {
            0%, 100% { opacity: 0.25; }
            50% { opacity: 0.9; filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-domain-float, .es-addr, .animate-pulse-slow { animation: none !important; }
            .es-addr { opacity: 0.55; }
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
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(20, 184, 166, 0.28), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(52, 211, 153, 0.14), rgba(52, 211, 153, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Address-bar motif along the bottom edge -->
            <div class="es-addrs absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-4 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 14; $i++)
                    @php $w = [60, 92, 44, 74, 112][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-addr" style="width: {{ $w }}px; --ad-dur: {{ $dur }}s; --ad-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Custom Domain</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your domain,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-domain">your schedule</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Serve your schedule directly on your own domain with automatic SSL, or redirect via Cloudflare. Your audience visits <strong class="text-gray-900 dark:text-white">events.yourdomain.com</strong> instead of a third-party URL.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.saas.custom_domains') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Custom Domains guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento grid                                               -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>A URL that's <span class="text-gradient-domain">unmistakably yours</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Map your own domain to your schedule and keep your audience on your brand.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="80">

                <!-- Branded URL (spans 2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                    Branded URL
                                </div>
                                <h3 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white lg:text-4xl">Your own address</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Use a domain you own instead of the default subdomain. Your audience sees a professional URL that matches your brand identity.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Any domain registrar</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Free SSL via Cloudflare</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="space-y-3 lg:w-72">
                                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                        <div class="mb-2 text-xs text-gray-400">Default</div>
                                        <div class="rounded-lg bg-gray-200 px-3 py-2 font-mono text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                            myschedule.eventschedule.com
                                        </div>
                                    </div>
                                    <div class="flex justify-center">
                                        <svg aria-hidden="true" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                        </svg>
                                    </div>
                                    <div class="rounded-2xl border border-emerald-300 bg-gray-50 p-4 dark:border-emerald-500/30 dark:bg-[#0f0f14]">
                                        <div class="mb-2 text-xs text-emerald-500">Custom domain</div>
                                        <div class="rounded-lg bg-gray-200 px-3 py-2 font-mono text-sm font-medium text-emerald-600 dark:bg-white/5 dark:text-emerald-400">
                                            events.yourdomain.com
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Quick Setup -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Quick Setup
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Cloudflare powered</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Set up your custom domain using Cloudflare's free plan. Get free SSL, fast DNS, and reliable redirects in minutes.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            @foreach (['Create Cloudflare account', 'Configure DNS + page rule', 'Enter domain in settings'] as $si => $step)
                                <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5" style="--i: {{ $si }};">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">{{ $si + 1 }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $step }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Complete Branding -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Complete Branding
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Full brand experience</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Combine a custom domain with <a href="{{ marketing_url('/features/white-label') }}" class="text-emerald-600 hover:underline dark:text-emerald-400">white-label branding</a> for a completely branded experience. Your domain, your logo, your colors - no Event Schedule branding visible.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            @foreach (['Custom domain URL', 'No "Powered by" badge', 'Your logo and colors'] as $ci => $item)
                                <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 px-3 py-2 dark:bg-white/5" style="--i: {{ $ci }};">
                                    <svg aria-hidden="true" class="h-4 w-4 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Enterprise band (dark)                                   -->
    <!-- ============================================================ -->
    <section class="es-band-dark relative overflow-hidden py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(16, 185, 129, 0.24), rgba(16, 185, 129, 0) 60%); opacity: 0.6;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(20, 184, 166, 0.2), rgba(20, 184, 166, 0) 60%); opacity: 0.55;"></div>
            <div class="grid-overlay absolute inset-0 opacity-20"></div>
            <div class="es-addrs absolute bottom-6 left-0 right-0 mx-auto hidden h-14 max-w-4xl items-center justify-center gap-4 px-8 opacity-45 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 12; $i++)
                    @php $w = [60, 92, 44, 74, 112][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-addr" style="width: {{ $w }}px; --ad-dur: {{ $dur }}s; --ad-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="relative z-10 mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-500/10 px-3 py-1.5 text-sm font-medium text-emerald-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Enterprise Feature
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Included with <span class="text-gradient-domain">Enterprise</span></h2>
                <p class="mt-4 text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Custom domains are part of the Enterprise plan, alongside AI features, private events, multiple team members, and more. Start free and upgrade when you need a branded domain.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4" data-reveal-group="80">
                @php
                    $enterpriseTiles = [
                        ['label' => 'Custom Domain', 'path' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'],
                        ['label' => 'AI Features', 'path' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
                        ['label' => 'Private Events', 'path' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                        ['label' => 'Team Members', 'path' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ];
                @endphp
                @foreach ($enterpriseTiles as $tile)
                    <div data-reveal class="flex flex-col items-center rounded-2xl border border-white/10 bg-white/[0.04] p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/30 hover:bg-white/[0.06]">
                        <svg aria-hidden="true" class="mb-3 h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $tile['path'] }}" />
                        </svg>
                        <div class="text-sm font-medium text-emerald-300">{{ $tile['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Guide & Next                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">

                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.creating_schedules') }}#settings-general" data-reveal class="group flex flex-col rounded-3xl border border-gray-200 bg-white p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-emerald-600 dark:text-white dark:group-hover:text-emerald-400">Read the guide</h3>
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Learn how to set up a custom domain for your schedule.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-emerald-500 transition-all group-hover:gap-3 dark:text-emerald-400">
                        Read guide
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Next feature -->
                <a href="{{ marketing_url('/features/team-scheduling') }}" data-reveal class="group flex flex-col rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-100 to-teal-100 p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-emerald-900 dark:to-teal-900 lg:p-10">
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-emerald-600 dark:text-white dark:group-hover:text-emerald-300">Team Scheduling</h3>
                    <p class="mb-4 text-lg text-gray-500 dark:text-gray-300/80">Add multiple team members to collaborate on your schedule.</p>
                    <span class="mt-auto inline-flex items-center gap-2 font-medium text-emerald-600 transition-all group-hover:gap-3 dark:text-emerald-300">
                        Learn more
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Popular with -->
                <div data-reveal class="flex flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-emerald-500/20 bg-emerald-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-emerald-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-emerald-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Venues</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-emerald-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-hotels-and-resorts') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-emerald-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-emerald-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Hotels & Resorts</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-emerald-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-restaurants') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-emerald-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-emerald-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Restaurants</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-emerald-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <section class="bg-gray-100 py-24 dark:bg-black/30">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-domain">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about custom domains.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['How do I set up a custom domain?', 'Set up a free Cloudflare account, add your domain, configure DNS records and a page rule to redirect to your Event Schedule URL, then enter your custom domain in the schedule settings. See the <a href="' . route('marketing.docs.creating_schedules') . '#settings-general" class="text-emerald-600 hover:underline dark:text-emerald-400">setup guide</a> for detailed instructions.'],
                        ['Do I need to buy a domain separately?', 'Yes. You need to own a domain name from any domain registrar (e.g., Namecheap, GoDaddy, Google Domains). Event Schedule does not sell domains, but any domain you own can be used.'],
                        ['Is SSL/HTTPS included?', 'Yes. When you use Cloudflare for the domain setup (which is the recommended approach), SSL/HTTPS is included for free through Cloudflare\'s free plan. Your visitors get a secure connection at no extra cost.'],
                        ['Which plan includes custom domains?', 'Custom domains are available on the Enterprise plan. Free and Pro plans use the default eventschedule.com subdomain. You can upgrade at any time from your account settings.'],
                    ];
                @endphp
                @foreach ($faqs as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{!! $a !!}</p>
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
                        name="White Label"
                        description="Remove Event Schedule branding for a fully branded experience"
                        :url="marketing_url('/features/white-label')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom CSS"
                        description="Write your own CSS for pixel-perfect schedule styling"
                        :url="marketing_url('/features/custom-css')"
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
                        name="Team Scheduling"
                        description="Add multiple team members to collaborate on your schedule"
                        :url="marketing_url('/features/team-scheduling')"
                        icon-color="amber"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-emerald-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-addrs absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-4 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 10; $i++)
                            @php $w = [60, 92, 44, 74, 112][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                            <span class="es-addr" style="width: {{ $w }}px; --ad-dur: {{ $dur }}s; --ad-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready for your <span class="text-gradient-domain">own domain?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Upgrade to Enterprise and use your own domain for a fully branded schedule experience.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
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

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
