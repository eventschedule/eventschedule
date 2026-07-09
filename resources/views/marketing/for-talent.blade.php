<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Performers & Artists | Share Your Shows</x-slot>
    <x-slot name="description">Free event scheduling for performers and artists. Share your shows, sell tickets with zero platform fees, and let venues add you to their own schedules.</x-slot>
    <x-slot name="breadcrumbTitle">For Talent</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Performers & Artists",
        "description": "Free event scheduling for performers and artists of every kind. Share your shows, sell tickets, sync with Google Calendar, and let venues add you to their schedule. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Performers & Artists"
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
                "name": "Is Event Schedule free for performers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your show schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I share my schedule on my website and social profiles?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Embed your schedule on any website with a single code snippet, or share your unique schedule URL on social media profiles, EPKs, and booking platforms. Your schedule is always up to date across all channels."
                }
            },
            {
                "@type": "Question",
                "name": "How do fans find out about my upcoming shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your schedule and receive email notifications when you add new shows. You can also send newsletters directly to followers with your upcoming dates and share your schedule link anywhere online."
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
        "name": "Event Schedule for Performers & Artists",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Performer Scheduling Software",
        "operatingSystem": "Web",
        "description": "Free event scheduling for performers and artists. Share your shows, sell tickets, sync with Google Calendar, and let venues add you to their schedule.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Custom schedule URL",
            "Google Calendar sync",
            "Direct ticket sales",
            "Venue linking",
            "Availability tracking",
            "Team collaboration",
            "Fan videos and comments on events"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "performer schedule, share tour dates, artist event calendar, performer booking, gig management, free event scheduling",
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
           For-talent "Center Stage" styles. The shared es-* motion
           system (aurora, reveals, bento, marquee, finale) lives in
           marketing.css; this block holds only this page's own theme:
           the multi-gel "Center Stage" heading gradient (amber -> rose
           -> sky), converging spotlight beams reused across the hero,
           dark band and finale, gel-washed availability states, and the
           themed CTA / step / link treatments. Broader palette than any
           single niche page: this is the every-performer front door.
           ============================================================== */

        /* Center Stage multi-gel heading gradient */
        .text-gradient-stage {
            background: linear-gradient(120deg, #f59e0b, #fb7185 52%, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-stage {
            background: linear-gradient(120deg, #fbbf24, #fda4af 52%, #7dd3fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Converging spotlight beams (hero, dark band, finale). Angled
           from the top corners so the cones cross mid-surface, each a
           different stage gel. */
        .es-spotbeam {
            position: absolute;
            top: -10%;
            width: 52%;
            height: 150%;
            pointer-events: none;
            transform-origin: 50% 0;
            animation: es-spot-pan 12s ease-in-out infinite alternate;
        }
        .es-spotbeam-1 { left: -6%; background: conic-gradient(from 150deg at 50% 0%, transparent 0deg, rgba(245, 158, 11, 0.13) 13deg, transparent 27deg); }
        .es-spotbeam-2 { right: -6%; background: conic-gradient(from 194deg at 50% 0%, transparent 0deg, rgba(56, 189, 248, 0.13) 13deg, transparent 27deg); animation-delay: -4.5s; animation-duration: 14s; }
        .es-spotbeam-3 { left: 30%; background: conic-gradient(from 172deg at 50% 0%, transparent 0deg, rgba(251, 113, 133, 0.11) 10deg, transparent 22deg); animation-delay: -7s; animation-duration: 16s; }
        .dark .es-spotbeam-1 { background: conic-gradient(from 150deg at 50% 0%, transparent 0deg, rgba(245, 158, 11, 0.18) 13deg, transparent 27deg); }
        .dark .es-spotbeam-2 { background: conic-gradient(from 194deg at 50% 0%, transparent 0deg, rgba(56, 189, 248, 0.17) 13deg, transparent 27deg); }
        .dark .es-spotbeam-3 { background: conic-gradient(from 172deg at 50% 0%, transparent 0deg, rgba(251, 113, 133, 0.15) 10deg, transparent 22deg); }
        @keyframes es-spot-pan {
            from { transform: rotate(-6deg); }
            to   { transform: rotate(6deg); }
        }

        /* Multi-gel CTA (hero + finale), tuned deep so white text stays legible */
        .es-stage-cta {
            background-image: linear-gradient(115deg, #b45309, #e11d48 50%, #0369a1);
            box-shadow: 0 10px 25px -6px rgba(245, 158, 11, 0.35);
        }
        .es-stage-cta:hover {
            box-shadow: 0 20px 45px -8px rgba(251, 113, 133, 0.45);
        }
        .dark .es-stage-cta {
            background-image: linear-gradient(115deg, #c2410c, #e11d48 50%, #0284c7);
            box-shadow: 0 10px 25px -6px rgba(245, 158, 11, 0.4);
        }

        /* Step-number badges (dark band) */
        .es-stage-step {
            background-image: linear-gradient(135deg, #ea580c, #e11d48 52%, #0284c7);
            box-shadow: 0 10px 20px -6px rgba(251, 113, 133, 0.4);
        }

        /* Finale panel gel glow */
        .es-stage-finale {
            box-shadow: 0 25px 55px -12px rgba(245, 158, 11, 0.28);
        }
        .dark .es-stage-finale {
            box-shadow: 0 25px 60px -12px rgba(251, 113, 133, 0.3);
        }

        /* Roster cards: gel-glow hover (per-card icon colors are kept) */
        .es-roster-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .es-roster-card:hover {
            transform: translateY(-4px);
            border-color: rgba(245, 158, 11, 0.45);
            box-shadow: 0 16px 34px -12px rgba(245, 158, 11, 0.28), 0 0 0 1px rgba(56, 189, 248, 0.18);
        }
        .dark .es-roster-card:hover {
            border-color: rgba(251, 113, 133, 0.4);
            box-shadow: 0 18px 38px -12px rgba(56, 189, 248, 0.3), 0 0 0 1px rgba(245, 158, 11, 0.2);
        }

        /* Availability grid: stage-native day states */
        .es-gel-available { background: rgba(56, 189, 248, 0.22); color: #0369a1; }
        .es-gel-booked { background: rgba(245, 158, 11, 0.24); color: #b45309; }
        .es-gel-blocked { background: rgba(251, 113, 133, 0.22); color: #be123c; }
        .dark .es-gel-available { background: rgba(56, 189, 248, 0.28); color: #7dd3fc; }
        .dark .es-gel-booked { background: rgba(245, 158, 11, 0.3); color: #fcd34d; }
        .dark .es-gel-blocked { background: rgba(251, 113, 133, 0.28); color: #fda4af; }
        .es-gel-dot-available { background: #38bdf8; }
        .es-gel-dot-booked { background: #f59e0b; }
        .es-gel-dot-blocked { background: #fb7185; }

        /* Small gel bullet (eyebrows, marquee) */
        .es-gel-bullet { background-image: linear-gradient(120deg, #f59e0b, #fb7185 55%, #38bdf8); }

        /* Themed inline "see all" links */
        .es-stage-link { color: #b45309; }
        .es-stage-link:hover { text-decoration: underline; }
        .dark .es-stage-link { color: #fbbf24; }

        /* Related-page cards: gel hover */
        .es-related-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-related-card:hover {
            border-color: rgba(245, 158, 11, 0.4);
            background-color: rgba(245, 158, 11, 0.06);
        }
        .dark .es-related-card:hover {
            border-color: rgba(56, 189, 248, 0.3);
            background-color: rgba(56, 189, 248, 0.07);
        }
        .es-related-card:hover .es-related-title,
        .es-related-card:hover .es-related-arrow { color: #b45309; }
        .dark .es-related-card:hover .es-related-title,
        .dark .es-related-card:hover .es-related-arrow { color: #fbbf24; }

        @media (prefers-reduced-motion: reduce) {
            .es-spotbeam { animation: none !important; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the lineup                                          -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.42), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(56, 189, 248, 0.4), rgba(56, 189, 248, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="es-spotbeam es-spotbeam-1"></div>
            <div class="es-spotbeam es-spotbeam-2"></div>
            <div class="es-spotbeam es-spotbeam-3"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Performers & Artists of Every Kind</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.75rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Share your shows</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-stage es-gradient-anim">with fans</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Musicians, comedians, DJs, dancers, magicians, and more. One link for all your shows. Let your audience know where to find you next.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See what you get
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group es-stage-cta pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                    Create your schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Performer-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-4xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Musicians', 'DJs', 'Comedians', 'Dancers', 'Magicians', 'Poets', 'Acrobats', 'Actors', 'Bands', 'Instructors', 'Artists', 'Vendors'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full es-gel-bullet"></span>
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
                    <span class="h-1.5 w-1.5 rounded-full es-gel-bullet" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Everything included</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    One home for your whole <span class="text-gradient-stage">performing life</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Share Your Schedule (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                                    Share Everywhere
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">One link for all your gigs</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Get a custom URL for your schedule. Share it on social media, your website, or anywhere else. Fans can see all your upcoming shows in one place.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Custom URL</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Website embed</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Social sharing</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-blue-300 bg-gradient-to-br from-blue-50 to-sky-50 p-4 shadow-lg dark:border-blue-400/30 dark:from-blue-950 dark:to-sky-950">
                                        <div class="mb-4 flex items-center gap-2 rounded-lg bg-white px-3 py-2 dark:bg-gray-900">
                                            <svg aria-hidden="true" class="h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" /></svg>
                                            <span class="font-mono text-sm text-gray-600 dark:text-gray-300">yourband.eventschedule.com</span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-blue-500/10 p-2" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-blue-500 dark:bg-blue-400"></div><span class="text-sm text-gray-900 dark:text-white">Fri Mar 15 - Blue Note</span></div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-white/60 p-2 dark:bg-white/5" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-sky-500 dark:bg-sky-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Sat Mar 22 - The Roxy</span></div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-white/60 p-2 dark:bg-white/5" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-blue-500 dark:bg-blue-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Fri Mar 29 - Jazz Club</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Play at Multiple Venues -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            Venues
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Play at multiple venues</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Link your schedule to venue calendars. When they post your show, it appears on your schedule automatically.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-sky-400/30 bg-sky-500/15 p-3" style="--i: 0;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-200 dark:bg-sky-500/30"><span class="text-xs font-bold text-sky-700 dark:text-sky-300">BN</span></div>
                                <div><div class="text-sm font-medium text-gray-900 dark:text-white">Blue Note</div><div class="text-xs text-sky-600 dark:text-sky-300">Linked</div></div>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl bg-gray-100 p-3 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-200 dark:bg-white/10"><span class="text-xs font-bold text-gray-500 dark:text-gray-400">TR</span></div>
                                <div><div class="text-sm font-medium text-gray-700 dark:text-gray-300">The Roxy</div><div class="text-xs text-gray-500 dark:text-gray-400">Linked</div></div>
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
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Calendar Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Two-way Google sync</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Connect your Google Calendar. Changes sync both ways automatically via real-time webhooks.</p>
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

                <!-- Sell Tickets (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    Ticketing
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Sell tickets direct</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">No middleman. Accept payments via Stripe with zero platform fees. You keep what you earn.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="space-y-3">
                                    <div class="es-ai-field flex items-center justify-between rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 0;">
                                        <div><div class="font-medium text-gray-900 dark:text-white">Early Bird</div><div class="text-xs text-emerald-600 dark:text-emerald-400">50 remaining</div></div>
                                        <div class="text-xl font-bold text-gray-900 dark:text-white">$15</div>
                                    </div>
                                    <div class="es-ai-field flex items-center justify-between rounded-xl border border-sky-400/30 bg-sky-500/15 p-3" style="--i: 1;">
                                        <div><div class="font-medium text-gray-900 dark:text-white">VIP Meet & Greet</div><div class="text-xs text-sky-600 dark:text-sky-300">10 remaining</div></div>
                                        <div class="text-xl font-bold text-gray-900 dark:text-white">$75</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Track Availability -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Availability
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Track availability</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Block off dates when you're unavailable. Venues can see when you're free before reaching out.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                <div class="text-gray-500 dark:text-gray-400">M</div>
                                <div class="text-gray-500 dark:text-gray-400">T</div>
                                <div class="text-gray-500 dark:text-gray-400">W</div>
                                <div class="text-gray-500 dark:text-gray-400">T</div>
                                <div class="text-gray-500 dark:text-gray-400">F</div>
                                <div class="text-gray-500 dark:text-gray-400">S</div>
                                <div class="text-gray-500 dark:text-gray-400">S</div>
                                <div class="rounded es-gel-available p-1">1</div>
                                <div class="rounded es-gel-available p-1">2</div>
                                <div class="rounded es-gel-blocked p-1">3</div>
                                <div class="rounded es-gel-available p-1">4</div>
                                <div class="rounded es-gel-booked p-1">5</div>
                                <div class="rounded es-gel-booked p-1">6</div>
                                <div class="rounded es-gel-available p-1">7</div>
                            </div>
                            <div class="mt-4 flex gap-4 text-xs">
                                <div class="flex items-center gap-1"><div class="h-2 w-2 rounded es-gel-dot-available"></div><span class="text-gray-500 dark:text-gray-400">Available</span></div>
                                <div class="flex items-center gap-1"><div class="h-2 w-2 rounded es-gel-dot-booked"></div><span class="text-gray-500 dark:text-gray-400">Booked</span></div>
                                <div class="flex items-center gap-1"><div class="h-2 w-2 rounded es-gel-dot-blocked"></div><span class="text-gray-500 dark:text-gray-400">Blocked</span></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Team Collaboration -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Team
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Band collaboration</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Invite band members to manage the schedule together. Everyone stays in sync.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/10" style="--i: 0;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-500 text-xs font-semibold text-white">JD</div>
                                <div class="flex-1 text-sm text-gray-900 dark:text-white">Jake</div>
                                <span class="inline-flex items-center rounded bg-blue-100 px-1.5 py-0.5 text-[10px] text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Owner</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-sky-500 text-xs font-semibold text-white">MK</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Maya</div>
                                <span class="inline-flex items-center rounded bg-blue-100 px-1.5 py-0.5 text-[10px] text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Admin</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 2;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-blue-500 text-xs font-semibold text-white">SR</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Sam</div>
                                <span class="inline-flex items-center rounded bg-sky-100 px-1.5 py-0.5 text-[10px] text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">Member</span>
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
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Event graphics</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate promotional images for social media. Perfect for Instagram and Facebook posts.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-32 w-32 -rotate-3 rounded-xl border border-amber-400/30 bg-gradient-to-br from-amber-500/25 to-orange-500/25 p-2 transition-transform duration-300 group-hover:rotate-0">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br from-blue-600/50 to-sky-600/50">
                                    <div class="mb-1 text-[10px] font-semibold text-white">LIVE TONIGHT</div>
                                    <div class="text-xs font-bold text-amber-300">The Jazz Trio</div>
                                    <div class="mt-1 text-[8px] text-white/80">Blue Note · 8PM</div>
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
    <!-- 3. The lineup: perfect for every performer                   -->
    <!-- ============================================================ -->
    @php
        $roster = [
            ['route' => 'marketing.for_musicians', 'name' => 'Musicians & Bands', 'desc' => "Share your tour dates and let fans know where you're playing next.", 'chip' => 'bg-blue-100 dark:bg-blue-500/20', 'text' => 'text-blue-600 dark:text-blue-400', 'hover' => 'hover:border-blue-200 dark:hover:border-blue-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />'],
            ['route' => 'marketing.for_djs', 'name' => 'DJs', 'desc' => 'List your residencies and guest spots across multiple venues.', 'chip' => 'bg-sky-100 dark:bg-sky-500/20', 'text' => 'text-sky-600 dark:text-sky-400', 'hover' => 'hover:border-sky-200 dark:hover:border-sky-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />'],
            ['route' => 'marketing.for_comedians', 'name' => 'Comedians', 'desc' => 'Promote your stand-up shows and open mic appearances.', 'chip' => 'bg-sky-100 dark:bg-sky-500/20', 'text' => 'text-sky-600 dark:text-sky-400', 'hover' => 'hover:border-sky-200 dark:hover:border-sky-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['route' => 'marketing.for_circus_acrobatics', 'name' => 'Circus & Acrobatics', 'desc' => 'Share your aerial shows, circus acts, and acrobatic performances.', 'chip' => 'bg-cyan-100 dark:bg-cyan-500/20', 'text' => 'text-cyan-600 dark:text-cyan-400', 'hover' => 'hover:border-cyan-200 dark:hover:border-cyan-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />'],
            ['route' => 'marketing.for_magicians', 'name' => 'Magicians & Variety', 'desc' => 'Book more gigs by showing your availability to event planners.', 'chip' => 'bg-blue-100 dark:bg-blue-500/20', 'text' => 'text-blue-600 dark:text-blue-400', 'hover' => 'hover:border-blue-200 dark:hover:border-blue-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />'],
            ['route' => 'marketing.for_spoken_word', 'name' => 'Spoken Word & Poetry', 'desc' => 'Share your readings and open mic nights with your audience.', 'chip' => 'bg-rose-100 dark:bg-rose-500/20', 'text' => 'text-rose-600 dark:text-rose-400', 'hover' => 'hover:border-rose-200 dark:hover:border-rose-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />'],
            ['route' => 'marketing.for_dance_groups', 'name' => 'Dance Groups', 'desc' => 'Coordinate performances and rehearsals across your ensemble.', 'chip' => 'bg-cyan-100 dark:bg-cyan-500/20', 'text' => 'text-cyan-600 dark:text-cyan-400', 'hover' => 'hover:border-cyan-200 dark:hover:border-cyan-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />'],
            ['route' => 'marketing.for_theater_performers', 'name' => 'Theater Performers', 'desc' => 'List your show runs and auditions for fans and casting directors.', 'chip' => 'bg-amber-100 dark:bg-amber-500/20', 'text' => 'text-amber-600 dark:text-amber-400', 'hover' => 'hover:border-amber-200 dark:hover:border-amber-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />'],
            ['route' => 'marketing.for_food_trucks_and_vendors', 'name' => 'Food Trucks & Vendors', 'desc' => 'Let customers know where to find you at markets and events.', 'chip' => 'bg-emerald-100 dark:bg-emerald-500/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'hover' => 'hover:border-emerald-200 dark:hover:border-emerald-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />'],
            ['route' => 'marketing.for_fitness_and_yoga', 'name' => 'Fitness & Yoga Instructors', 'desc' => 'Share your class schedule and let students follow your sessions.', 'chip' => 'bg-emerald-100 dark:bg-emerald-500/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'hover' => 'hover:border-emerald-200 dark:hover:border-emerald-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />'],
            ['route' => 'marketing.for_workshop_instructors', 'name' => 'Workshop Instructors', 'desc' => 'List your workshops and courses to fill every seat.', 'chip' => 'bg-sky-100 dark:bg-sky-500/20', 'text' => 'text-sky-600 dark:text-sky-400', 'hover' => 'hover:border-sky-200 dark:hover:border-sky-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />'],
            ['route' => 'marketing.for_visual_artists', 'name' => 'Visual Artists', 'desc' => 'Announce exhibitions and build your collector base.', 'chip' => 'bg-sky-100 dark:bg-sky-500/20', 'text' => 'text-sky-600 dark:text-sky-400', 'hover' => 'hover:border-sky-200 dark:hover:border-sky-500/30', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
        ];
    @endphp
    <section id="perfect-for" class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full es-gel-bullet" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">The lineup</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Perfect for all types of <span class="text-gradient-stage">performers</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.14s;">
                    Whether you're a solo act or a full ensemble, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="60">
                @foreach ($roster as $r)
                    <a href="{{ route($r['route']) }}" data-reveal class="es-roster-card group block rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $r['chip'] }} transition-transform group-hover:scale-110">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $r['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $r['svg'] !!}</svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $r['name'] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $r['desc'] }}</p>
                        <span class="mt-3 inline-flex items-center gap-1 text-sm font-medium {{ $r['text'] }} transition-all group-hover:gap-2">
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
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                Online Events
                            </div>
                            <h3 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400 lg:text-3xl">Stream to the world</h3>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Share live performances with fans worldwide. Add your streaming URL and sell tickets to viewers anywhere - no venue required.</p>
                            <div class="mb-4 flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Live streaming</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Global ticket sales</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Any platform</span>
                            </div>
                            <span class="inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                Learn more
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
    <!-- 5. How it works (dark band)                                  -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-spotbeam es-spotbeam-1"></div>
                <div class="es-spotbeam es-spotbeam-2"></div>
                <div class="es-spotbeam es-spotbeam-3"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="h-1.5 w-1.5 rounded-full bg-sky-400" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">Quick setup</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Get your schedule online in <span class="text-gradient-stage">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl es-stage-step text-xl font-bold text-white">1</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Create Your Schedule</h3>
                        <p class="text-sm text-gray-400">Sign up and add your upcoming shows. Import from Google Calendar or add them manually.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl es-stage-step text-xl font-bold text-white">2</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Share Your Link</h3>
                        <p class="text-sm text-gray-400">Get a custom URL for your schedule. Add it to your bio, website, or social profiles.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl es-stage-step text-xl font-bold text-white">3</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Build Your Audience</h3>
                        <p class="text-sm text-gray-400">Fans can follow your schedule, get notified about new shows, and share videos and comments on your events (all approved by you before going live).</p>
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
                <a href="{{ marketing_url('/features') }}" class="es-stage-link inline-flex items-center font-medium">
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
                @foreach ([['/for-curators', 'Curators'], ['/for-musicians', 'Musicians'], ['/for-comedians', 'Comedians'], ['/for-venues', 'Venues']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-related-card group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="es-related-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-related-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-stage-link inline-flex items-center font-medium">
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
                    Everything performers ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for performers?', 'Yes. Event Schedule is free forever for sharing your show schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I share my schedule on my website and social profiles?', 'Yes. Embed your schedule on any website with a single code snippet, or share your unique schedule URL on social media profiles, EPKs, and booking platforms. Your schedule is always up to date across all channels.'],
                    ['How do fans find out about my upcoming shows?', 'Fans can follow your schedule and receive email notifications when you add new shows. You can also send newsletters directly to followers with your upcoming dates and share your schedule link anywhere online.'],
                    ['What happens when a venue books me for a show?', 'When a venue adds you to their event on Event Schedule, it automatically appears on your schedule too. No need to manually add the same gig in two places. Both calendars stay in sync.'],
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
            <div class="es-finale-panel es-stage-finale noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-spotbeam es-spotbeam-1"></div>
                    <div class="es-spotbeam es-spotbeam-2"></div>
                    <div class="es-spotbeam es-spotbeam-3"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Let fans know where <span class="text-gradient-stage">you're playing</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your schedule in minutes. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group es-stage-cta relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
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
