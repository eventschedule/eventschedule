<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Theaters | Manage Your Season</x-slot>
    <x-slot name="description">Sell out every performance. Manage show runs, sell tickets with zero platform fees, and email your patrons directly. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Theaters</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Theaters",
        "description": "Manage show runs from opening night to closing curtain, sell tickets with zero platform fees, and email your patrons directly.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Theaters"
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
                "name": "Is Event Schedule free for theaters?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your season schedule, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage an entire season of productions?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. List all your productions with multiple performance dates each. Use sub-schedules to organize by series - mainstage, second stage, children's theater, or special events. Each production can have its own description, cast info, and ticket options."
                }
            },
            {
                "@type": "Question",
                "name": "How do theatergoers discover our shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Audiences can follow your theater's schedule and receive email notifications when new productions are announced. Embed your season calendar on your website, share on social media, or send newsletters to subscribers."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets with different seating options?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Create multiple ticket types per show - orchestra, mezzanine, balcony, student rush, or group rates. Connect your Stripe account and sell directly with zero platform fees. Every ticket includes a QR code for door scanning."
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
        "name": "Event Schedule for Theaters",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Theater Management Software",
        "operatingSystem": "Web",
        "description": "Sell out every performance. Manage show runs from opening night to closing curtain, sell tickets with zero platform fees, and email your patrons directly.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Show run management",
            "Multiple ticket types",
            "Zero-fee ticketing",
            "QR code box office",
            "Direct patron newsletter",
            "Multi-venue scheduling"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "theater event calendar, theater season schedule, theater ticket sales, theater management software, free theater scheduling",
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
        /* For-theaters "The Marquee" styles. The shared es-* motion system
           lives in marketing.css; this holds the rose->amber marquee gradient
           text, the chasing marquee bulbs (now the exclusive owner of this
           motif: hero, how-it-works band, and finale), red-velvet curtain
           folds, a playbill serif, a gilded season-pass frame, and the
           rose/amber bento chips and hover states. */
        .text-gradient-stage {
            background: linear-gradient(90deg, #e11d48, #dc2626, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-stage {
            background: linear-gradient(90deg, #fb7185, #f87171, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Chasing marquee bulbs */
        @keyframes es-marquee-bulb {
            0%, 100% { opacity: 0.3; box-shadow: 0 0 2px rgba(251, 191, 36, 0.3); }
            50% { opacity: 1; box-shadow: 0 0 8px rgba(251, 191, 36, 0.8), 0 0 12px rgba(251, 191, 36, 0.4); }
        }
        .es-bulb {
            animation: es-marquee-bulb 1.6s ease-in-out infinite;
            animation-delay: var(--d, 0s);
        }

        /* Red-velvet curtain: soft vertical folds at the hero and finale edges */
        .stage-curtain {
            background-image: repeating-linear-gradient(90deg,
                rgba(120, 12, 20, 0.00) 0px,
                rgba(150, 20, 28, 0.30) 8px,
                rgba(74, 8, 14, 0.46) 16px,
                rgba(150, 20, 28, 0.30) 24px,
                rgba(120, 12, 20, 0.00) 32px);
        }
        .dark .stage-curtain {
            background-image: repeating-linear-gradient(90deg,
                rgba(140, 14, 22, 0.00) 0px,
                rgba(162, 24, 32, 0.44) 8px,
                rgba(52, 5, 10, 0.68) 16px,
                rgba(162, 24, 32, 0.44) 24px,
                rgba(140, 14, 22, 0.00) 32px);
        }
        .stage-curtain-l {
            -webkit-mask-image: linear-gradient(90deg, #000 8%, transparent);
            mask-image: linear-gradient(90deg, #000 8%, transparent);
        }
        .stage-curtain-r {
            -webkit-mask-image: linear-gradient(-90deg, #000 8%, transparent);
            mask-image: linear-gradient(-90deg, #000 8%, transparent);
        }

        /* Playbill serif with act/scene small caps for the show-run mock */
        .playbill { font-family: Georgia, 'Times New Roman', serif; }
        .playbill-caps {
            font-family: Georgia, 'Times New Roman', serif;
            font-variant: small-caps;
            letter-spacing: 0.08em;
        }

        /* Gilded frame edge for the season-pass / show-run card */
        .gilded-frame {
            box-shadow:
                inset 0 0 0 1px rgba(253, 224, 138, 0.55),
                inset 0 0 0 4px rgba(202, 138, 4, 0.18),
                inset 0 2px 22px rgba(217, 168, 74, 0.12),
                0 8px 24px -12px rgba(180, 132, 47, 0.45);
        }
        .dark .gilded-frame {
            box-shadow:
                inset 0 0 0 1px rgba(253, 224, 138, 0.45),
                inset 0 0 0 4px rgba(180, 132, 47, 0.30),
                inset 0 2px 26px rgba(217, 168, 74, 0.16),
                0 8px 28px -12px rgba(0, 0, 0, 0.6);
        }

        /* Rose / amber bento chips */
        .stage-chip { background-color: #ffe4e6; color: #9f1239; }
        .dark .stage-chip { background-color: rgba(244, 63, 94, 0.14); color: #fda4af; }
        .stage-chip-amber { background-color: #fef3c7; color: #92400e; }
        .dark .stage-chip-amber { background-color: rgba(245, 158, 11, 0.14); color: #fcd34d; }

        /* Rose hover for related-page cards and FAQ rows */
        .stage-rel { transition: border-color 0.2s ease, background-color 0.2s ease; }
        .stage-rel:hover { border-color: #fda4af; background-color: #fff1f2; }
        .dark .stage-rel:hover { border-color: rgba(244, 63, 94, 0.35); background-color: rgba(244, 63, 94, 0.06); }
        .stage-rel:hover .stage-rel-title,
        .stage-rel:hover .stage-rel-arrow { color: #e11d48; }
        .dark .stage-rel:hover .stage-rel-title,
        .dark .stage-rel:hover .stage-rel-arrow { color: #fb7185; }
        .stage-faq { transition: border-color 0.2s ease; }
        .stage-faq:hover { border-color: #fda4af; }
        .dark .stage-faq:hover { border-color: rgba(244, 63, 94, 0.35); }
        .stage-faq summary:hover h3 { color: #e11d48; }
        .dark .stage-faq summary:hover h3 { color: #fb7185; }

        @media (prefers-reduced-motion: reduce) {
            .es-bulb { animation: none !important; opacity: 0.7 !important; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the marquee                                         -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute left-0 right-0 top-0 z-20 flex justify-center overflow-hidden" aria-hidden="true">
            <div class="flex gap-4 px-8 py-1.5">
                @for ($i = 0; $i < 30; $i++)
                    <span class="es-bulb h-2 w-2 shrink-0 rounded-full bg-amber-400" style="--d: {{ $i * 0.1 }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(225, 29, 72, 0.34), rgba(225, 29, 72, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(245, 158, 11, 0.36), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <!-- Red-velvet curtains at the wings -->
        <div class="stage-curtain stage-curtain-l pointer-events-none absolute left-0 top-0 h-full w-32" aria-hidden="true"></div>
        <div class="stage-curtain stage-curtain-r pointer-events-none absolute right-0 top-0 h-full w-32" aria-hidden="true"></div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Theaters & Performing Arts Venues</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.75rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Sell out every show.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-stage es-gradient-anim">Build your audience.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From opening night to closing curtain. Sell tickets directly, manage show runs, and email your patrons without paying for ads.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the season
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-rose-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-rose-500/40">
                    Create your theater's calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Genre marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Drama', 'Musical', 'Comedy', 'Dance', 'Opera', 'Improv', 'Shakespeare', 'Cabaret'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-100/80 px-4 py-1.5 text-xs font-semibold text-rose-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-rose-400 to-amber-400"></span>
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
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-rose-400 to-amber-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">The whole run</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything to fill the <span class="text-gradient-stage">house</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Show runs (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Show Runs
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Opening night to closing curtain</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Set up your production once with all performance dates. Thursday preview, weekend matinees, final Sunday - all linked together. Patrons see the full run at a glance.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="stage-chip inline-flex items-center rounded-full px-3 py-1 text-sm">Show runs</span>
                                    <span class="stage-chip-amber inline-flex items-center rounded-full px-3 py-1 text-sm">Season passes</span>
                                    <span class="stage-chip inline-flex items-center rounded-full px-3 py-1 text-sm">Multi-performance</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="playbill gilded-frame max-w-xs rounded-2xl border border-rose-300 bg-gradient-to-br from-rose-50 to-red-50 p-4 dark:border-rose-400/30 dark:from-rose-950 dark:to-red-950">
                                        <div class="mb-3 text-center"><div class="playbill-caps text-lg font-semibold text-gray-900 dark:text-white">Hamlet</div><div class="playbill-caps text-sm text-rose-500 dark:text-rose-300">March 2024 Run</div></div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-amber-400/30 bg-amber-500/15 p-2" style="--i: 0;"><div class="w-12 font-mono text-xs text-amber-600 dark:text-amber-300">Mar 7</div><span class="playbill-caps text-sm text-gray-900 dark:text-white">Preview</span><span class="ml-auto text-xs text-amber-600 dark:text-amber-300">$25</span></div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-rose-400/30 bg-rose-500/15 p-2" style="--i: 1;"><div class="w-12 font-mono text-xs text-rose-600 dark:text-rose-300">Mar 8</div><span class="playbill-caps text-sm text-gray-900 dark:text-white">Opening Night</span><span class="ml-auto text-xs text-rose-600 dark:text-rose-300">$45</span></div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><div class="w-12 font-mono text-xs text-gray-500 dark:text-gray-400">Mar 9</div><span class="playbill-caps text-sm text-gray-600 dark:text-gray-300">Sat Matinee</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">$35</span></div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 3;"><div class="w-12 font-mono text-xs text-gray-500 dark:text-gray-400">Mar 9</div><span class="playbill-caps text-sm text-gray-600 dark:text-gray-300">Sat Evening</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">$40</span></div>
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
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Newsletter
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Your audience. No middleman.</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">New show announced? One click emails every subscriber. No algorithm, no paying Facebook to reach your own patrons.</p>
                        <div class="mt-auto rounded-xl border border-amber-400/20 bg-gray-100 p-4 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">To: 1,847 patrons</div>
                            <div class="rounded-lg bg-gradient-to-r from-amber-100 to-yellow-100 p-3 dark:from-amber-950 dark:to-yellow-950">
                                <div class="mb-1 text-xs font-medium text-amber-600 dark:text-amber-300">NOW ON SALE</div>
                                <div class="font-semibold text-gray-900 dark:text-white">Our Town</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">April 12-28</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Seating -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-red-200 bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 dark:border-red-800/30 dark:bg-red-900/40 dark:text-red-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Seating
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Orchestra. Mezzanine. Balcony.</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Different prices for different seats. Student rush, senior discounts, preview pricing - set it all up once.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center justify-between rounded-lg border border-red-400/30 bg-red-500/15 p-2" style="--i: 0;"><span class="text-sm text-gray-900 dark:text-white">Orchestra</span><span class="font-semibold text-red-600 dark:text-red-300">$45</span></div>
                            <div class="es-ai-field flex items-center justify-between rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><span class="text-sm text-gray-600 dark:text-gray-300">Mezzanine</span><span class="font-semibold text-gray-500 dark:text-gray-400">$35</span></div>
                            <div class="es-ai-field flex items-center justify-between rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><span class="text-sm text-gray-600 dark:text-gray-300">Balcony</span><span class="font-semibold text-gray-500 dark:text-gray-400">$25</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Box office (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                    Box Office
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Scan tickets at the door</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Use any smartphone as a box office scanner. See who's checked in, catch duplicates, track attendance. No expensive hardware.</p>
                                <div class="mt-6 flex flex-wrap gap-3">
                                    <span class="stage-chip inline-flex items-center rounded-full px-3 py-1 text-sm">Will call</span>
                                    <span class="stage-chip-amber inline-flex items-center rounded-full px-3 py-1 text-sm">E-tickets</span>
                                    <span class="stage-chip inline-flex items-center rounded-full px-3 py-1 text-sm">Any device</span>
                                </div>
                            </div>
                            <div class="flex justify-center" aria-hidden="true">
                                <div class="relative">
                                    <div class="relative h-32 w-32 rounded-xl bg-white p-3 shadow-sm">
                                        <div class="h-full w-full bg-contain bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%231f2937%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')]"></div>
                                        <div class="absolute inset-x-0 top-3 h-0.5 animate-pulse bg-gradient-to-r from-rose-500 to-amber-500"></div>
                                    </div>
                                    <div class="absolute -bottom-3 -right-3 flex gap-2">
                                        <span class="inline-flex items-center rounded border border-emerald-500/30 bg-emerald-500/20 px-2 py-1 text-xs text-emerald-600 dark:text-emerald-300">Will Call</span>
                                        <span class="inline-flex items-center rounded border border-rose-500/30 bg-rose-500/20 px-2 py-1 text-xs text-rose-600 dark:text-rose-300">E-Ticket</span>
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
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            Venues
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Main stage. Black box. Studio.</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Separate calendars for each theater space. Patrons filter by venue, you avoid double-booking.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-rose-500/30 bg-rose-500/15 p-2" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-rose-400"></div><span class="text-sm text-gray-900 dark:text-white">Main Stage</span><span class="ml-auto text-xs text-rose-600 dark:text-rose-300">8 shows</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-amber-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Black Box</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">12 shows</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-red-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Studio</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">5 shows</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Analytics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Which shows sell out?</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Comedies vs. dramas? Matinees vs. evenings? Data to plan your next season.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            @foreach ([['Musical', '92'], ['Drama', '78'], ['Comedy', '85']] as [$genre, $pct])
                                <div>
                                    <div class="mb-1 flex justify-between text-xs"><span class="text-gray-500 dark:text-gray-400">{{ $genre }}</span><span class="text-amber-600 dark:text-amber-300">{{ $pct }}%</span></div>
                                    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-yellow-500" style="width: {{ $pct }}%"></div></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Promo graphics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-yellow-200 bg-yellow-100 px-3 py-1.5 text-sm font-medium text-yellow-700 dark:border-yellow-800/30 dark:bg-yellow-900/40 dark:text-yellow-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Show posters, ready to share</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate promotional images sized for Instagram, Facebook, and your lobby display.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-36 w-28 rounded-lg border border-amber-400/30 bg-gradient-to-br from-rose-500/25 to-amber-500/25 p-2 shadow-lg">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded bg-gradient-to-br from-rose-600/50 to-red-600/50 text-center">
                                    <div class="mb-1 text-[8px] font-bold text-yellow-300">MARCH 8-24</div>
                                    <div class="text-sm font-bold leading-tight text-white">HAMLET</div>
                                    <div class="mt-1 text-[7px] text-white/80">The Grand Theater</div>
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
    <!-- 3. Stream to the world                                       -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" data-reveal="panel" class="es-bento group block">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-gray-50 p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                Online Events
                            </div>
                            <h2 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-rose-600 dark:text-white dark:group-hover:text-rose-400 lg:text-3xl">Stream to the world</h2>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Can't make it to the theater? Sell tickets to viewers at home. Live stream your productions worldwide. Virtual ticket buyers get the link, everyone else stays out.</p>
                            <div class="mb-4 flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="stage-chip inline-flex items-center rounded-full px-3 py-1 text-sm">Live streaming</span>
                                <span class="stage-chip-amber inline-flex items-center rounded-full px-3 py-1 text-sm">Virtual tickets</span>
                                <span class="stage-chip inline-flex items-center rounded-full px-3 py-1 text-sm">Global reach</span>
                            </div>
                            <span class="inline-flex items-center gap-2 font-medium text-rose-600 transition-all group-hover:gap-3 dark:text-rose-400">
                                Learn more
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="w-56 rounded-2xl border border-rose-400/30 bg-gradient-to-br from-rose-950 to-red-950 p-5">
                                <div class="mb-3 flex items-center gap-2"><div class="h-2 w-2 animate-pulse rounded-full bg-red-500"></div><span class="text-xs font-medium text-red-300">LIVE</span><span class="ml-auto text-xs text-gray-400">247 watching</span></div>
                                <div class="mb-3 rounded-lg bg-[#0f0f14] p-3"><div class="text-sm font-semibold text-white">Hamlet - Opening Night</div><div class="text-xs text-gray-400">The Grand Theater</div></div>
                                <div class="text-xs text-emerald-400">Virtual tickets: $20</div>
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
    <!-- 4. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Built for every kind of <span class="text-gradient-stage">theater</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From intimate black boxes to historic playhouses, Event Schedule adapts to your stage.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Community Theaters"
                    description="Local productions, volunteer casts, and beloved classics. Reach your audience directly in your hometown."
                    icon-color="rose"
                    blog-slug="for-community-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Regional Theaters"
                    description="Professional productions with show runs. Manage season subscriptions and single tickets."
                    icon-color="red"
                    blog-slug="for-regional-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Black Box Theaters"
                    description="Intimate experimental work. Flexible seating, limited capacity - every ticket matters."
                    icon-color="slate"
                    blog-slug="for-black-box-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Dinner Theaters"
                    description="Combine show tickets with meal packages. Track dietary preferences alongside seat selections."
                    icon-color="amber"
                    blog-slug="for-dinner-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Children's Theaters"
                    description="Family-friendly productions and school group bookings. Manage matinee rushes with ease."
                    icon-color="pink"
                    blog-slug="for-childrens-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Outdoor Amphitheaters"
                    description="Shakespeare in the park, summer stock. Handle weather-dependent scheduling and rain dates."
                    icon-color="emerald"
                    blog-slug="for-outdoor-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. How it works (dark band)                                  -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="absolute left-0 right-0 top-0 z-20 flex justify-center overflow-hidden" aria-hidden="true">
                <div class="flex gap-4 px-8 py-1.5">
                    @for ($i = 0; $i < 30; $i++)
                        <span class="es-bulb h-2 w-2 shrink-0 rounded-full bg-amber-400" style="--d: {{ $i * 0.1 }}s;"></span>
                    @endfor
                </div>
            </div>
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(225, 29, 72, 0.26), rgba(225, 29, 72, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 60%, rgba(245, 158, 11, 0.24), rgba(245, 158, 11, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Get your theater online in <span class="text-gradient-stage">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    @foreach ([['1', 'Set up your theater', 'Add your venue, performance spaces, and branding. Tell patrons what you\'re about.'], ['2', 'Add your productions', 'Create show runs with all your performance dates. Set ticket types and prices.'], ['3', 'Fill every seat', 'Share your calendar. Patrons subscribe. New show? They hear about it first.']] as [$n, $title, $desc])
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-red-500 text-xl font-bold text-white shadow-lg shadow-rose-500/30">{{ $n }}</div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal><span class="text-gradient-stage">Key features</span></h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Fan Videos" description="Let fans share videos and comments from your events" :url="marketing_url('/features/fan-videos')" icon-color="orange">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium text-rose-600 hover:underline dark:text-rose-400">
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
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal><span class="text-gradient-stage">Related pages</span></h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-venues', 'Venues'], ['/for-theater-performers', 'Theater Performers'], ['/for-community-centers', 'Community Centers'], ['/for-libraries', 'Libraries']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="stage-rel flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="stage-rel-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="stage-rel-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium text-rose-600 hover:underline dark:text-rose-400">
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
                    Frequently asked <span class="text-gradient-stage">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything theaters ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for theaters?', 'Yes. Event Schedule is free forever for sharing your season schedule, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I manage an entire season of productions?', 'Yes. List all your productions with multiple performance dates each. Use sub-schedules to organize by series - mainstage, second stage, children\'s theater, or special events. Each production can have its own description, cast info, and ticket options.'],
                    ['How do theatergoers discover our shows?', 'Audiences can follow your theater\'s schedule and receive email notifications when new productions are announced. Embed your season calendar on your website, share on social media, or send newsletters to subscribers.'],
                    ['Can I sell tickets with different seating options?', 'Yes. Create multiple ticket types per show - orchestra, mezzanine, balcony, student rush, or group rates. Connect your Stripe account and sell directly with zero platform fees. Every ticket includes a QR code for door scanning.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="stage-faq group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $q }}</h3>
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-rose-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(225, 29, 72, 0.32), rgba(225, 29, 72, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="stage-curtain stage-curtain-l pointer-events-none absolute left-0 top-0 h-full w-32" aria-hidden="true"></div>
                <div class="stage-curtain stage-curtain-r pointer-events-none absolute right-0 top-0 h-full w-32" aria-hidden="true"></div>
                <div class="absolute left-0 right-0 top-0 z-20 flex justify-center overflow-hidden" aria-hidden="true">
                    <div class="flex gap-4 px-8 py-1.5">
                        @for ($i = 0; $i < 24; $i++)
                            <span class="es-bulb h-2 w-2 shrink-0 rounded-full bg-amber-400" style="--d: {{ $i * 0.1 }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your theater deserves a <span class="text-gradient-stage">full house</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Sell tickets. Build your audience. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-theater" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-rose-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-red-500/40">
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
