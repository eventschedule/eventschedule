<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Spoken Word | Share Your Performances</x-slot>
    <x-slot name="description">Share your poetry readings, open mics, and workshops. Sell tickets directly and reach fans with newsletters. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Spoken Word</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Spoken Word",
        "description": "Share your poetry readings, open mics, and workshops. Sell tickets directly, reach fans with newsletters. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Spoken Word Artists"
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
                "name": "Is Event Schedule free for spoken word artists?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your performance schedule, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees."
                }
            },
            {
                "@type": "Question",
                "name": "Can I promote open mics, slams, and readings together?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by event type - open mics, poetry slams, book readings, workshops, and featured sets. Each event can include descriptions, featured performer info, venue details, and signup information."
                }
            },
            {
                "@type": "Question",
                "name": "How do poetry fans discover my events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your schedule and receive email notifications when you add new events. Share your schedule link on social media, literary websites, or embed it on your personal site."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to featured shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Set different prices for general admission and featured seating. Event Schedule charges zero platform fees."
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
        "name": "Event Schedule for Poets & Spoken Word",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Poetry Reading Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your poetry readings, open mics, and workshops. Sell tickets directly, reach fans with newsletters. Built for spoken word artists, slam poets, and writers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Open mic and reading tracking across multiple venues",
            "Zero-fee ticket sales with chapbook bundling",
            "Direct newsletter communication with readers",
            "Virtual reading and workshop support",
            "Two-way Google Calendar sync",
            "Workshop scheduling and registration",
            "Auto-generated promotional graphics for social media"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "spoken word schedule, poetry event calendar, spoken word booking, poetry slam management, free spoken word scheduling",
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
        /* For-spoken-word "The Reading" styles. The shared es-* motion system
           lives in marketing.css; this holds only the warm amber->rose gradient
           text and the faint serif words drifting behind the hero. */
        .text-gradient-poetry {
            background: linear-gradient(135deg, #d97706, #e11d48);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-poetry {
            background: linear-gradient(135deg, #fcd34d, #fb7185);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-word {
            position: absolute;
            font-family: Georgia, 'Times New Roman', serif;
            color: rgba(120, 53, 15, 0.06);
            pointer-events: none;
            user-select: none;
        }
        .dark .es-word { color: rgba(253, 230, 138, 0.05); }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the reading                                         -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.36), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(225, 29, 72, 0.32), rgba(225, 29, 72, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <span class="es-word text-6xl italic" style="top:15%;left:8%;transform:rotate(-8deg);">verse</span>
            <span class="es-word text-5xl" style="top:25%;right:12%;transform:rotate(5deg);">stanza</span>
            <span class="es-word text-4xl" style="top:46%;left:5%;transform:rotate(12deg);">breath</span>
            <span class="es-word text-7xl italic" style="bottom:28%;right:8%;transform:rotate(-5deg);">rhythm</span>
            <span class="es-word text-5xl" style="bottom:15%;left:15%;transform:rotate(3deg);">voice</span>
            <span class="es-word text-4xl italic" style="top:60%;right:22%;transform:rotate(-10deg);">line break</span>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full border border-amber-300 bg-amber-100 px-5 py-2.5 dark:border-amber-700/40 dark:bg-amber-900/30">
                <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-amber-700 dark:text-amber-200/90">For Poets, Spoken Word Artists & Writers</span>
            </div>

            <h1 class="es-balance mb-8 font-serif text-[2.75rem] font-bold leading-[1.08] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your words</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-poetry es-gradient-anim italic">deserve witnesses.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-4 max-w-2xl text-lg font-light text-gray-500 dark:text-gray-400 sm:text-xl">
                Not followers. Not subscribers. Not engagement metrics.
            </p>
            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-500">
                Real people in real rooms, listening. One link shows them where to find you.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it works
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-rose-500 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-rose-500/40">
                    Create your reading schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Typewriter-label marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['SLAM', 'OPEN MIC', 'FEATURED READING', 'WORKSHOP', 'BOOK LAUNCH', 'LIT FEST', 'CHAPBOOK', 'SALON'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-sm border border-amber-300 bg-amber-100/80 px-3 py-1.5 font-mono text-xs font-semibold tracking-wide text-amber-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
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
    <!-- 2. The reality (stats)                                       -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-16 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 text-center md:grid-cols-3" data-reveal-group="90">
                <div data-reveal>
                    <div class="mb-2 font-serif text-3xl text-amber-600 dark:text-amber-300">3 venues</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Different open mics. Different nights. One schedule.</div>
                </div>
                <div data-reveal>
                    <div class="mb-2 font-serif text-3xl text-amber-600 dark:text-amber-300">0 fees</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Keep every dollar from ticket sales. Poetry doesn't pay enough to share.</div>
                </div>
                <div data-reveal>
                    <div class="mb-2 font-serif text-3xl text-amber-600 dark:text-amber-300">Your fans</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Email readers directly. No algorithm between you and your audience.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Bento features                                            -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-amber-400 to-rose-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">The whole reading life</span>
                </div>
                <h2 class="es-balance font-serif text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything but the <span class="text-gradient-poetry">writing</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Your schedule (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    Your Schedule
                                </div>
                                <h3 class="mb-4 font-serif text-3xl font-bold tracking-tight text-gray-900 dark:text-white lg:text-4xl">Every reading. One place.</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Coffee shops on Tuesdays. The bar series on Thursdays. That bookstore feature next month. Stop telling people to check your Instagram - give them one link that's always current.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Open mics</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Features</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Slams</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Book events</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-xl border border-gray-800 bg-gray-900 p-5 font-mono text-sm shadow-lg">
                                        <div class="mb-4 text-xs uppercase tracking-wider text-gray-500">Upcoming Readings</div>
                                        <div class="space-y-4">
                                            <div class="es-ai-field border-l-2 border-amber-500/60 pl-3" style="--i: 0;">
                                                <div class="font-medium text-amber-300">Thu, Jan 30</div>
                                                <div class="text-gray-400">Open Mic @ Brewhaus</div>
                                                <div class="text-xs text-gray-600">7pm / sign-up 6:30</div>
                                            </div>
                                            <div class="es-ai-field border-l-2 border-rose-500/60 pl-3" style="--i: 1;">
                                                <div class="font-medium text-rose-300">Sat, Feb 1</div>
                                                <div class="text-gray-400">Featured @ City Lights</div>
                                                <div class="text-xs text-gray-600">8pm / $10</div>
                                            </div>
                                            <div class="es-ai-field border-l-2 border-gray-600 pl-3" style="--i: 2;">
                                                <div class="font-medium text-gray-400">Wed, Feb 5</div>
                                                <div class="text-gray-500">Writing Workshop</div>
                                                <div class="text-xs text-gray-600">6pm / 4 spots left</div>
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

                <!-- Email your readers -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Newsletter
                        </div>
                        <h3 class="mb-3 font-serif text-2xl font-bold text-gray-900 dark:text-white">Your readers, direct reach</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">New chapbook? Upcoming feature? Email the people who actually want to hear from you. No algorithm deciding who sees it.</p>
                        <div class="mt-auto rounded-lg border border-gray-800 bg-gray-900 p-4" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-rose-500/40 to-amber-500/40"><span class="font-serif text-xs text-amber-200">A</span></div>
                                <div>
                                    <div class="text-sm text-gray-300">New Reading Announced</div>
                                    <div class="text-xs text-gray-600">to 847 subscribers</div>
                                </div>
                            </div>
                            <div class="border-t border-gray-800 pt-3 text-xs italic text-gray-500">"I'll be featuring at..."</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Workshops -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            Workshops
                        </div>
                        <h3 class="mb-3 font-serif text-2xl font-bold text-gray-900 dark:text-white">Fill your workshops</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Teaching pays. List your writing workshops, craft classes, and generative sessions. Let students find and register easily.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center justify-between rounded border border-emerald-500/20 bg-emerald-500/10 p-2" style="--i: 0;">
                                <span class="text-sm text-gray-800 dark:text-gray-200">Persona Poems</span>
                                <span class="text-xs text-emerald-600 dark:text-emerald-400">2 spots</span>
                            </div>
                            <div class="es-ai-field flex items-center justify-between rounded bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Form & Constraint</span>
                                <span class="text-xs text-gray-400 dark:text-gray-600">FULL</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Tickets + chapbooks (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    Tickets & Books
                                </div>
                                <h3 class="mb-4 font-serif text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Keep what you earn</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Sell tickets to your readings. Bundle with your chapbook. Zero platform fees - because poetry doesn't pay enough to give away a percentage.</p>
                            </div>
                            <div class="rounded-xl border border-gray-800 bg-gray-900 p-5" aria-hidden="true">
                                <div class="space-y-3">
                                    <div class="es-ai-field flex items-center justify-between rounded-lg border border-gray-700 bg-gray-800/60 p-3" style="--i: 0;">
                                        <div><div class="font-medium text-gray-200">Reading Only</div><div class="text-xs text-gray-500">General admission</div></div>
                                        <div class="text-xl font-medium text-gray-300">$8</div>
                                    </div>
                                    <div class="es-ai-field flex items-center justify-between rounded-lg border border-amber-500/30 bg-amber-500/10 p-3" style="--i: 1;">
                                        <div><div class="font-medium text-amber-100">Reading + Chapbook</div><div class="text-xs text-amber-300/70">Signed copy included</div></div>
                                        <div class="text-xl font-medium text-amber-200">$20</div>
                                    </div>
                                </div>
                                <div class="mt-4 border-t border-gray-800 pt-4 text-center"><span class="text-xs text-gray-600">Stripe processes payment. You keep 100%.</span></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Virtual readings (link) -->
                <a href="{{ marketing_url('/features/online-events') }}" class="es-bento group relative block" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            Online
                        </div>
                        <h3 class="mb-3 font-serif text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400">Read to anywhere</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Host virtual readings and workshops. Your audience isn't just local - poetry travels well over Zoom.</p>
                        <span class="mt-auto inline-flex items-center gap-1 text-sm font-medium text-sky-600 transition-all group-hover:gap-2 dark:text-sky-400">
                            Learn more
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </span>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- Google Calendar sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Sync
                        </div>
                        <h3 class="mb-3 font-serif text-2xl font-bold text-gray-900 dark:text-white">Google Calendar sync</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync. Add a reading to your personal calendar, it shows up on your public schedule.</p>
                        <div class="relative mt-auto flex items-center justify-center gap-8 py-2" aria-hidden="true">
                            <div class="w-20 rounded-lg border border-amber-400/30 bg-amber-500/15 p-2">
                                <div class="mb-1 text-center text-[10px] font-medium text-amber-600 dark:text-amber-300">Yours</div>
                                <div class="mb-1 h-1.5 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                <div class="h-1.5 w-3/4 rounded bg-amber-400/40"></div>
                            </div>
                            <div class="absolute left-1/2 top-1/2 h-px w-10 -translate-x-1/2 -translate-y-1/2 border-t border-dashed border-amber-300 dark:border-amber-500/40"></div>
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

                <!-- Promo graphics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 font-serif text-2xl font-bold text-gray-900 dark:text-white">Post-ready images</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate promo graphics. Share to Instagram without opening Canva.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative">
                                <div class="flex h-28 w-28 flex-col items-center justify-center rounded-lg border border-gray-700 bg-gradient-to-br from-gray-800 to-gray-900 p-3 text-center">
                                    <div class="mb-1 text-[8px] uppercase tracking-wider text-gray-500">Poetry Reading</div>
                                    <div class="font-serif text-xs text-amber-200">Maya Torres</div>
                                    <div class="mt-1 text-[8px] text-gray-500">City Lights / 8pm</div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-orange-500/90">
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
    <!-- 4. Where poetry happens                                      -->
    <!-- ============================================================ -->
    @php
        $poetryVenues = [
            ['Coffee Shops', 'bg-amber-100 dark:bg-amber-900/30', 'text-amber-600 dark:text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />'],
            ['Bookstores', 'bg-rose-100 dark:bg-rose-900/30', 'text-rose-600 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />'],
            ['Bars & Lounges', 'bg-blue-100 dark:bg-blue-900/30', 'text-blue-600 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />'],
            ['Universities', 'bg-blue-100 dark:bg-blue-900/30', 'text-blue-600 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />'],
            ['Lit Festivals', 'bg-orange-100 dark:bg-orange-900/30', 'text-orange-600 dark:text-orange-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />'],
            ['Virtual', 'bg-sky-100 dark:bg-sky-900/30', 'text-sky-600 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />'],
        ];
    @endphp
    <section class="bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <h2 class="es-balance mb-3 font-serif text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Where poetry happens
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">From open mics to festival stages</p>
            </div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6" data-reveal-group="60">
                @foreach ($poetryVenues as [$vName, $vChip, $vText, $vIcon])
                    <div data-reveal class="group rounded-2xl border border-gray-200 bg-white p-6 text-center transition-all duration-200 hover:-translate-y-1 hover:border-amber-300 hover:shadow-md dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-amber-500/30">
                        <div class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $vChip }} transition-transform group-hover:scale-110">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $vText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $vIcon !!}</svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $vName }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 font-serif text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Built for how poets <span class="text-gradient-poetry">actually work</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether you're on the slam circuit or launching a collection
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Slam Poets -->
                <x-sub-audience-card
                    name="Slam Poets"
                    description="Competition circuit, team slams, regional bouts. Track your season and let fans follow your scores."
                    icon-color="rose"
                    blog-slug="for-slam-poets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Spoken Word Artists -->
                <x-sub-audience-card
                    name="Spoken Word Artists"
                    description="Performance poetry with music, movement, multimedia. Share your theatrical shows and collaborations."
                    icon-color="amber"
                    blog-slug="for-spoken-word-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Page Poets -->
                <x-sub-audience-card
                    name="Page Poets"
                    description="Book launches, literary readings, publication events. Promote your collections alongside appearances."
                    icon-color="purple"
                    blog-slug="for-page-poets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Open Mic Hosts -->
                <x-sub-audience-card
                    name="Open Mic Hosts"
                    description="Running your own series? Build a following for your recurring nights and featured readers."
                    icon-color="orange"
                    blog-slug="for-poetry-open-mic-hosts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Literary Curators -->
                <x-sub-audience-card
                    name="Literary Curators"
                    description="Organizing reading series, festivals, salon events. Aggregate your programming in one place."
                    icon-color="indigo"
                    blog-slug="for-literary-curators"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Storytellers -->
                <x-sub-audience-card
                    name="Storytellers"
                    description="Oral storytelling, narrative performance, and story slams. Share your upcoming shows and captivate new audiences."
                    icon-color="emerald"
                    blog-slug="for-storytellers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. How it works (dark band)                                  -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.28), rgba(245, 158, 11, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 60%, rgba(225, 29, 72, 0.24), rgba(225, 29, 72, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance font-serif text-3xl font-bold tracking-tight text-white md:text-5xl" data-reveal>
                        Three <span class="text-gradient-poetry">steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-rose-500 font-serif text-xl font-bold text-white shadow-lg shadow-amber-500/30">1</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Add your readings</h3>
                        <p class="text-sm text-gray-400">Open mics, features, workshops, launches. Import from Google Calendar or add them yourself.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-rose-500 font-serif text-xl font-bold text-white shadow-lg shadow-amber-500/30">2</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Share one link</h3>
                        <p class="text-sm text-gray-400">Put it in your bio. Your website. The back of your chapbook. Done.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-rose-500 font-serif text-xl font-bold text-white shadow-lg shadow-amber-500/30">3</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Build your audience</h3>
                        <p class="text-sm text-gray-400">Readers follow you. They get notified when you're reading near them.</p>
                    </div>
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
                @foreach ([['/for-comedians', 'Comedians'], ['/for-musicians', 'Musicians'], ['/for-theater-performers', 'Theater Performers'], ['/for-libraries', 'Libraries']] as [$relHref, $relName])
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
                <h2 class="es-balance mb-4 font-serif text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-poetry">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything spoken word artists ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for spoken word artists?', 'Yes. Event Schedule is free forever for sharing your performance schedule, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees.'],
                    ['Can I promote open mics, slams, and readings together?', 'Yes. Use sub-schedules to organize by event type - open mics, poetry slams, book readings, workshops, and featured sets. Each event can include descriptions, featured performer info, venue details, and signup information.'],
                    ['How do poetry fans discover my events?', 'Fans can follow your schedule and receive email notifications when you add new events. Share your schedule link on social media, literary websites, or embed it on your personal site.'],
                    ['Can I sell tickets to featured shows?', 'Yes. Connect your Stripe account and sell tickets directly from your schedule. Set different prices for general admission and featured seating. Event Schedule charges zero platform fees.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-amber-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(245, 158, 11, 0.3), rgba(245, 158, 11, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <span class="es-word text-6xl italic" style="top:20%;left:10%;transform:rotate(-8deg);color:rgba(253,230,138,0.06);">verse</span>
                    <span class="es-word text-5xl" style="bottom:24%;right:12%;transform:rotate(6deg);color:rgba(253,230,138,0.06);">rhythm</span>
                </div>

                <div class="relative z-10">
                    <p class="mb-6 text-sm uppercase tracking-[0.2em] text-gray-400">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl font-serif text-3xl font-bold tracking-tight text-white md:text-5xl">
                        Stop shouting into <span class="text-gradient-poetry italic">the algorithm.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Your words already found their audience once. Help them find you again.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 to-rose-500 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-rose-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Create your schedule
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
