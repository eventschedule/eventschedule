<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Dance Groups | Share Your Performances</x-slot>
    <x-slot name="description">Share your dance performances with fans worldwide. Email your fans directly and sell tickets to recitals and workshops with zero fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Dance Groups</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Dance Groups",
        "description": "Share your dance performances with fans worldwide. Sell tickets to recitals and workshops with zero fees. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Dance Groups"
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
                "name": "Is Event Schedule free for dance groups?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your performance and class schedule, building a following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage performances, classes, and rehearsals together?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by type - performances, classes, workshops, auditions, and rehearsals. Each event can include details, images, and ticket options. Keep public events visible while keeping rehearsals private."
                }
            },
            {
                "@type": "Question",
                "name": "How do audiences discover our performances?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your dance group's schedule and receive email notifications for new performances. Share your schedule link on social media, embed it on your website, or send newsletters to followers with upcoming shows."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to performances and recitals?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Set up different pricing for adults, students, and groups. Event Schedule charges zero platform fees - every dollar goes to your dance group."
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
        "name": "Event Schedule for Dance Groups",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Dance Performance Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your dance performances with fans worldwide. Email your audience directly - no algorithm. Sell tickets to recitals, workshops, and showcases with zero platform fees.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Season planning for dance companies",
            "Class and workshop scheduling",
            "Zero-fee ticket sales for performances",
            "Direct newsletter communication with audiences",
            "Virtual performance and livestream support",
            "Two-way Google Calendar sync",
            "Team collaboration for ensembles",
            "Auto-generated promotional graphics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "dance schedule, dance performance calendar, dance group booking, dance recital management, free dance scheduling",
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
        /* For-dance-groups "In Motion" styles. The shared es-* motion system
           lives in marketing.css; this holds the rose->cyan->sky gradient text,
           the drifting motion lines (now carried from the hero through the dark
           band and finale), motion-trail card hovers, and a curtain-to-curtain
           season progress line. Motion trails stay exclusive to this page. */
        .text-gradient-dance {
            background: linear-gradient(120deg, #f43f5e, #06b6d4, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-dance {
            background: linear-gradient(120deg, #fda4af, #67e8f9, #7dd3fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes es-flow {
            0%, 100% { transform: translateX(0) translateY(0); }
            50% { transform: translateX(12px) translateY(-8px); }
        }
        .es-flow { animation: es-flow 9s ease-in-out infinite; }

        /* Motion-trail hover: the card slides a few pixels and a rose edge
           lags behind and fades, like a body leaving a trail through a move. */
        .es-trail { position: relative; transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .es-trail::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            border: 1.5px solid rgba(244, 63, 94, 0.45);
            opacity: 0;
            transform: translate(0, 0);
            transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
            pointer-events: none;
        }
        .es-trail:hover { transform: translate(3px, -3px); }
        .es-trail:hover::after { opacity: 1; transform: translate(-6px, 6px); }

        /* Curtain-to-curtain season progress line; fills on reveal. */
        .es-season-progress {
            width: 0;
            transition: width 1.5s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: 0.35s;
        }
        [data-reveal].is-revealed .es-season-progress,
        html:not(.es-anim) .es-season-progress { width: 62%; }

        /* Accent recolor for the hard-coded links and related-page card hovers. */
        .es-link-dance { color: #0891b2; }
        .dark .es-link-dance { color: #67e8f9; }
        .es-relcard:hover { border-color: #fda4af; background-color: #fff1f2; }
        .dark .es-relcard:hover { border-color: rgba(244, 63, 94, 0.3); background-color: rgba(244, 63, 94, 0.06); }
        .es-relcard:hover .es-relcard-title,
        .es-relcard:hover .es-relcard-arrow { color: #e11d48; }
        .dark .es-relcard:hover .es-relcard-title,
        .dark .es-relcard:hover .es-relcard-arrow { color: #fda4af; }

        @media (prefers-reduced-motion: reduce) {
            .es-flow { animation: none !important; }
            .es-season-progress { width: 62%; transition: none; }
            .es-trail, .es-trail::after { transition: none; }
            .es-trail:hover { transform: none; }
            .es-trail:hover::after { opacity: 0; transform: none; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: in motion                                           -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 32%, rgba(244, 63, 94, 0.34), rgba(244, 63, 94, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 42%, rgba(6, 182, 212, 0.4), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 70%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <svg class="es-flow absolute left-6 top-24 h-64 w-64 text-rose-400/40" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                <path d="M20,100 Q60,20 100,100 T180,100" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.5"/>
                <path d="M20,120 Q70,40 110,120 T180,120" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.3"/>
            </svg>
            <svg class="es-flow absolute bottom-24 right-10 h-52 w-52 text-cyan-400/40" style="animation-delay: 2s;" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                <path d="M100,20 Q180,60 100,100 T100,180" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.5"/>
            </svg>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="h-2 w-2 rounded-full bg-gradient-to-r from-rose-400 to-cyan-400"></span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Dance Companies, Troupes & Studios</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.75rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">From studio</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-dance es-gradient-anim">to stage</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                One schedule for rehearsals, performances, and classes. One link for your audience to find you.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it flows
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-500 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-rose-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-rose-500/40">
                    Create your schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Dance-style marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Ballet', 'Hip-Hop', 'Contemporary', 'Ballroom', 'Tap', 'Jazz', 'Folk', 'Latin'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-rose-400 to-cyan-400"></span>
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
    <!-- 2. The dance life (emoji stats)                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-16 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-6 text-center md:grid-cols-4" data-reveal-group="70">
                @foreach ([
                    ['text-rose-500 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />', 'Rehearsals sync to your public schedule'],
                    ['text-cyan-500 dark:text-cyan-400', '<path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />', 'Sell tickets to performances directly'],
                    ['text-sky-500 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />', 'Email your fans directly, own the relationship'],
                    ['text-rose-500 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />', 'Fill your classes and workshops'],
                ] as [$statColor, $statIcon, $text])
                    <div data-reveal class="es-trail rounded-2xl border border-gray-200 bg-white p-6 transition-all hover:shadow-md dark:border-white/10 dark:bg-white/[0.04]">
                        <svg aria-hidden="true" class="mx-auto mb-3 h-8 w-8 {{ $statColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">{!! $statIcon !!}</svg>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $text }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Season planning                                           -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-16 lg:grid-cols-2">
                <div data-reveal>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300">
                        Season Planning
                    </div>
                    <h2 class="es-balance mb-6 text-3xl font-black leading-tight tracking-tight text-gray-900 dark:text-white md:text-5xl">
                        Your entire season, <span class="text-gradient-dance">one view</span>
                    </h2>
                    <p class="mb-8 text-lg leading-relaxed text-gray-500 dark:text-gray-400">
                        Fall program. Winter showcase. Spring recital. Nutcracker run. Import from Google Calendar or add performances manually. Your audience sees everything in one place.
                    </p>
                    <ul class="space-y-4">
                        @foreach (['Two-way Google Calendar sync for rehearsals and shows', 'Multiple venues - theater, studio, outdoor stages', 'Share one link everywhere - programs, posters, social bios'] as $item)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-gray-600 dark:text-gray-300">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-2xl dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                        <div class="mb-6 flex items-center justify-between">
                            <span class="font-medium text-gray-900 dark:text-white">2025-26 Season</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">City Dance Company</span>
                        </div>
                        <div class="space-y-3">
                            @foreach ([
                                ['OCT', '18-20', 'Fall Repertory', 'Mainstage Theater', 'border-amber-400', 'from-amber-500/10', 'text-amber-700 dark:text-amber-300'],
                                ['DEC', '6-22', 'The Nutcracker', 'Historic Opera House', 'border-rose-400', 'from-rose-500/10', 'text-rose-700 dark:text-rose-300'],
                                ['MAR', '14-16', 'Contemporary Showcase', 'Black Box Studio', 'border-cyan-400', 'from-cyan-500/10', 'text-cyan-700 dark:text-cyan-300'],
                                ['MAY', '30-31', 'Spring Gala', 'City Amphitheater', 'border-sky-400', 'from-sky-500/10', 'text-sky-700 dark:text-sky-300'],
                            ] as $idx => [$mo, $day, $name, $venue, $bcls, $gcls, $tcls])
                                <div class="es-ai-field flex items-center gap-4 rounded-xl border-l-2 {{ $bcls }} bg-gradient-to-r {{ $gcls }} to-transparent p-3" style="--i: {{ $idx }};">
                                    <div class="w-12 text-center"><div class="text-xs {{ $tcls }}">{{ $mo }}</div><div class="font-medium text-gray-900 dark:text-white">{{ $day }}</div></div>
                                    <div><div class="text-sm font-medium text-gray-900 dark:text-white">{{ $name }}</div><div class="text-xs text-gray-500 dark:text-gray-500">{{ $venue }}</div></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-5 border-t border-gray-200 pt-4 dark:border-white/10">
                            <div class="flex items-center gap-2">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 4v10a3 3 0 003-3V4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 4v10a3 3 0 01-3-3V4" />
                                </svg>
                                <div class="relative h-1.5 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10">
                                    <div class="es-season-progress h-full rounded-full bg-gradient-to-r from-rose-400 via-cyan-400 to-sky-400"></div>
                                </div>
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 4v10a3 3 0 003-3V4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 4v10a3 3 0 01-3-3V4" />
                                </svg>
                            </div>
                            <div class="mt-2 flex justify-between text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <span>Curtain up</span>
                                <span>Season close</span>
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
    <!-- 4. Classes & workshops                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-16 lg:grid-cols-2">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                        <div class="es-tilt-inner relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-white/[0.04]" aria-hidden="true">
                            <div class="mb-4 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Weekly Classes</div>
                            <div class="space-y-2">
                                <div class="es-ai-field flex items-center justify-between rounded-lg bg-gray-100 p-3 dark:bg-white/5" style="--i: 0;">
                                    <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-cyan-400"></span><span class="text-sm text-gray-900 dark:text-white">Ballet Fundamentals</span></div>
                                    <div class="text-right"><div class="text-xs text-gray-500 dark:text-gray-400">Mon/Wed 6pm</div><div class="mt-1"><span class="inline-flex items-center rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] font-semibold text-cyan-700 dark:bg-cyan-500/15 dark:text-cyan-300">3 spots left</span></div></div>
                                </div>
                                <div class="es-ai-field flex items-center justify-between rounded-lg bg-gray-100 p-3 dark:bg-white/5" style="--i: 1;">
                                    <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-blue-400"></span><span class="text-sm text-gray-900 dark:text-white">Contemporary Technique</span></div>
                                    <div class="text-right"><div class="text-xs text-gray-500 dark:text-gray-400">Tue/Thu 7pm</div><div class="mt-1"><span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-semibold text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">5 spots left</span></div></div>
                                </div>
                                <div class="es-ai-field flex items-center justify-between rounded-lg bg-gray-100 p-3 dark:bg-white/5" style="--i: 2;">
                                    <div class="flex items-center gap-3"><span class="h-2 w-2 rounded-full bg-amber-400"></span><span class="text-sm text-gray-900 dark:text-white">Hip-Hop Foundations</span></div>
                                    <div class="text-right"><div class="text-xs text-gray-500 dark:text-gray-400">Sat 2pm</div><div class="mt-1"><span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">FULL</span></div></div>
                                </div>
                            </div>
                            <div class="mt-4 border-t border-gray-200 pt-4 dark:border-white/10">
                                <div class="mb-3 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Upcoming Workshops</div>
                                <div class="rounded-lg border border-rose-500/20 bg-gradient-to-r from-rose-500/10 to-sky-500/10 p-3">
                                    <div class="flex items-center justify-between">
                                        <div><div class="text-sm font-medium text-gray-900 dark:text-white">Partnering Intensive</div><div class="text-xs text-gray-500 dark:text-gray-400">Feb 15-16 with Guest Artist</div></div>
                                        <div class="text-sm font-medium text-rose-500 dark:text-rose-300">$85</div>
                                    </div>
                                </div>
                            </div>
                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2" data-reveal>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                        Classes & Workshops
                    </div>
                    <h2 class="es-balance mb-6 text-3xl font-black leading-tight tracking-tight text-gray-900 dark:text-white md:text-5xl">
                        Teach and perform <span class="text-gradient-dance">from one schedule</span>
                    </h2>
                    <p class="mb-8 text-lg leading-relaxed text-gray-500 dark:text-gray-400">
                        Most dance groups teach alongside performing. List your weekly classes, drop-ins, and intensive workshops. Sell registrations with zero platform fees - you keep everything.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        @foreach (['Weekly technique', 'Drop-in classes', 'Masterclasses', 'Summer intensives'] as $tag)
                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-100 px-3 py-1.5 text-sm text-gray-600 dark:border-white/10 dark:bg-white/10 dark:text-gray-300">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Stop relying on the algorithm                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="es-balance mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                Stop relying on the <span class="text-gradient-dance">algorithm</span>
            </h2>
            <p class="mx-auto mb-12 max-w-2xl text-xl text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                You post about your show. Facebook shows it to 3% of your followers. Unless you pay. <span class="font-semibold text-rose-500 dark:text-rose-300">There's a better way.</span>
            </p>
            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'Email your fans directly', 'Fans follow your schedule. You email them directly - no algorithm in the way.'],
                    ['M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'Notify on new shows', 'New performance? Email goes out. Everyone who wants to know, knows.'],
                    ['M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'Zero platform fees', 'Sell tickets directly. Stripe processes payment. You keep 100%.'],
                ] as [$icon, $title, $desc])
                    <div data-reveal class="es-trail rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-rose-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" /></svg>
                        </div>
                        <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Virtual performances                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" data-reveal="panel" class="es-bento group block">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-12">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                Livestream
                            </div>
                            <h3 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400 lg:text-3xl">Perform for audiences anywhere</h3>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Livestream your showcase. Host a virtual masterclass. Reach audiences who can't make it to the theater - and sell tickets to viewers worldwide.</p>
                            <span class="inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                Learn about online events
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="relative flex h-32 w-48 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="absolute inset-0 bg-gradient-to-br from-sky-500/10 to-cyan-500/10"></div>
                                <div class="relative text-center">
                                    <div class="mx-auto mb-2 flex h-8 w-8 animate-pulse items-center justify-center rounded-full bg-red-500"><div class="h-2 w-2 rounded-full bg-white"></div></div>
                                    <div class="text-xs font-medium text-gray-900 dark:text-white">LIVE</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-500">347 watching</div>
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
    <!-- 7. Team coordination                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-16 lg:grid-cols-2">
                <div data-reveal>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300">
                        Company Management
                    </div>
                    <h2 class="es-balance mb-6 text-3xl font-black leading-tight tracking-tight text-gray-900 dark:text-white md:text-5xl">
                        Coordinate your <span class="text-gradient-dance">entire company</span>
                    </h2>
                    <p class="mb-8 text-lg leading-relaxed text-gray-500 dark:text-gray-400">
                        Invite your artistic director, choreographers, rehearsal directors, and company managers. Everyone can update the schedule. Changes sync everywhere instantly.
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-sm font-medium text-white">AD</div>
                            <div class="flex-1"><div class="text-sm text-gray-900 dark:text-white">Sarah Chen</div><div class="text-xs text-gray-500 dark:text-gray-500">Artistic Director</div></div>
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Owner</span>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-gray-200/50 bg-gray-50 p-3 dark:border-white/5 dark:bg-white/5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-cyan-600 text-sm font-medium text-white">MR</div>
                            <div class="flex-1"><div class="text-sm text-gray-600 dark:text-gray-300">Marcus Rivera</div><div class="text-xs text-gray-500 dark:text-gray-500">Choreographer</div></div>
                            <span class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-xs text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">Admin</span>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl border border-gray-200/50 bg-gray-50 p-3 dark:border-white/5 dark:bg-white/5">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-sm font-medium text-white">JT</div>
                            <div class="flex-1"><div class="text-sm text-gray-600 dark:text-gray-300">Jamie Torres</div><div class="text-xs text-gray-500 dark:text-gray-500">Company Manager</div></div>
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Admin</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center">
                    <div class="es-bento group relative w-full max-w-sm" data-tilt="5" data-reveal="panel">
                        <div class="es-tilt-inner relative overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-4 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Recent Activity</div>
                            <div class="space-y-4">
                                <div class="es-ai-field flex gap-3" style="--i: 0;"><div class="w-1 rounded-full bg-blue-500"></div><div><div class="text-sm text-gray-900 dark:text-white">Sarah added "Spring Gala"</div><div class="text-xs text-gray-500 dark:text-gray-500">2 hours ago</div></div></div>
                                <div class="es-ai-field flex gap-3" style="--i: 1;"><div class="w-1 rounded-full bg-rose-500"></div><div><div class="text-sm text-gray-900 dark:text-white">Marcus updated rehearsal times</div><div class="text-xs text-gray-500 dark:text-gray-500">Yesterday</div></div></div>
                                <div class="es-ai-field flex gap-3" style="--i: 2;"><div class="w-1 rounded-full bg-amber-500"></div><div><div class="text-sm text-gray-900 dark:text-white">Jamie added ticket link</div><div class="text-xs text-gray-500 dark:text-gray-500">2 days ago</div></div></div>
                            </div>
                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Built for every style of <span class="text-gradient-dance">dance</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From classical technique to street dance, we understand how you work.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Ballet Companies -->
                <x-sub-audience-card
                    name="Ballet Companies"
                    description="Season planning for classical and contemporary repertoire. Nutcracker runs, spring galas, studio performances."
                    icon-color="rose"
                    blog-slug="for-ballet-companies"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🩰</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Hip-Hop Crews -->
                <x-sub-audience-card
                    name="Hip-Hop Crews"
                    description="Battles, showcases, cyphers, and workshops. Street dance, breaking, popping, locking - share where you're performing."
                    icon-color="violet"
                    blog-slug="for-hip-hop-crews"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🎤</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Ballroom & Latin Studios -->
                <x-sub-audience-card
                    name="Ballroom & Latin Studios"
                    description="Class schedules, social dances, showcases, and competition prep. Salsa nights to formal balls."
                    icon-color="amber"
                    blog-slug="for-ballroom-latin-studios"
                >
                    <x-slot:icon>
                        <span class="text-2xl">💃</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Contemporary & Modern Troupes -->
                <x-sub-audience-card
                    name="Contemporary & Modern"
                    description="Experimental work, site-specific performances, residencies, and immersive experiences. Art that moves."
                    icon-color="cyan"
                    blog-slug="for-contemporary-modern-dance"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🌊</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Folk & Cultural Ensembles -->
                <x-sub-audience-card
                    name="Folk & Cultural Ensembles"
                    description="Traditional dance, heritage celebrations, cultural festivals. Keeping traditions alive through movement."
                    icon-color="emerald"
                    blog-slug="for-folk-cultural-dance"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🌍</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Dance Schools & Academies -->
                <x-sub-audience-card
                    name="Dance Schools & Academies"
                    description="Structured dance education, student recitals, and showcase performances. Manage class schedules and enrollment."
                    icon-color="pink"
                    blog-slug="for-dance-schools-academies"
                >
                    <x-slot:icon>
                        <span class="text-2xl">❤️‍🔥</span>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Social promo graphics                                     -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-500/20 dark:bg-orange-500/10 dark:text-orange-400">
                        Promo Graphics
                    </div>
                    <h2 class="es-balance mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">
                        Share-ready images for every performance
                    </h2>
                    <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">
                        Auto-generate promotional graphics sized perfectly for Instagram, Facebook, and stories. No design skills needed - just download and post.
                    </p>
                    <ul class="space-y-3 text-gray-600 dark:text-gray-400">
                        @foreach (['Square posts for feed', 'Vertical for stories', 'Your branding, automatically applied'] as $item)
                            <li class="flex items-center gap-2">
                                <svg aria-hidden="true" class="h-5 w-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="flex justify-center gap-4" data-reveal aria-hidden="true">
                    <div class="flex h-36 w-36 rotate-[-4deg] flex-col items-center justify-center rounded-xl border border-rose-200 bg-gradient-to-br from-rose-100 to-cyan-100 p-3 text-center shadow-lg">
                        <div class="mb-1 text-[10px] uppercase tracking-wider text-rose-600">Spring Gala</div>
                        <div class="text-sm font-semibold text-stone-800">City Dance Co.</div>
                        <div class="mt-1 text-[10px] text-stone-500">May 30 · 7:30pm</div>
                        <div class="mt-2 text-[10px] font-medium text-rose-600">Tickets Available</div>
                    </div>
                    <div class="mt-8 flex h-40 w-24 rotate-[4deg] flex-col items-center justify-center rounded-xl border border-sky-200 bg-gradient-to-br from-sky-100 to-blue-100 p-2 text-center shadow-lg">
                        <div class="mb-1 text-[8px] uppercase tracking-wider text-sky-600">Story</div>
                        <div class="text-xs font-semibold text-stone-800">Tonight!</div>
                        <div class="mt-1 text-[8px] text-stone-500">Nutcracker</div>
                        <div class="mt-2 text-[8px] text-sky-600">Swipe up</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. How it works (dark band)                                 -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(244, 63, 94, 0.26), rgba(244, 63, 94, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 60%, rgba(6, 182, 212, 0.26), rgba(6, 182, 212, 0) 60%); opacity: 0.55;"></div>
                <svg class="es-flow absolute -left-10 top-8 h-72 w-72 text-rose-400/40" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                    <path d="M20,100 Q60,20 100,100 T180,100" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.6"/>
                    <path d="M20,120 Q70,40 110,120 T180,120" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.4"/>
                </svg>
                <svg class="es-flow absolute -right-8 bottom-6 h-64 w-64 text-cyan-400/40" style="animation-delay: 2.4s;" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                    <path d="M100,20 Q180,60 100,100 T100,180" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.55"/>
                </svg>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        From sign-up to <span class="text-gradient-dance">sold-out</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    @foreach ([['1', 'Add your season', 'Import from Google Calendar or add performances manually. Classes, workshops, shows - all in one place.'], ['2', 'Share your link', 'Put it on your website, in programs, on posters, in your social bios. One URL that\'s always current.'], ['3', 'Fill your seats', 'Fans subscribe and get notified about new performances. Sell tickets directly. Build your audience.']] as [$n, $title, $desc])
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-cyan-600 text-xl font-bold text-white shadow-lg shadow-rose-500/30">{{ $n }}</div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Key features                                             -->
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
                <a href="{{ marketing_url('/features') }}" class="es-link-dance inline-flex items-center font-medium hover:underline">
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
    <!-- 12. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-musicians', 'Musicians'], ['/for-theater-performers', 'Theater Performers'], ['/for-fitness-and-yoga', 'Fitness & Yoga'], ['/for-circus-acrobatics', 'Circus & Acrobatics']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-relcard group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="es-relcard-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-relcard-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-link-dance inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-dance">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything dance groups ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for dance groups?', 'Yes. Event Schedule is free forever for sharing your performance and class schedule, building a following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees.'],
                    ['Can I manage performances, classes, and rehearsals together?', 'Yes. Use sub-schedules to organize by type - performances, classes, workshops, auditions, and rehearsals. Each event can include details, images, and ticket options. Keep public events visible while keeping rehearsals private.'],
                    ['How do audiences discover our performances?', 'Fans can follow your dance group\'s schedule and receive email notifications for new performances. Share your schedule link on social media, embed it on your website, or send newsletters to followers with upcoming shows.'],
                    ['Can I sell tickets to performances and recitals?', 'Yes. Connect your Stripe account and sell tickets directly from your schedule. Set up different pricing for adults, students, and groups. Event Schedule charges zero platform fees - every dollar goes to your dance group.'],
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
    <!-- 14. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-rose-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 35% 25%, rgba(244, 63, 94, 0.3), rgba(244, 63, 94, 0) 60%); opacity: 0.7;"></div>
                    <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 65% 70%, rgba(6, 182, 212, 0.26), rgba(6, 182, 212, 0) 60%); opacity: 0.6;"></div>
                    <svg class="es-flow absolute -left-8 top-6 h-64 w-64 text-rose-400/40" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                        <path d="M20,100 Q60,20 100,100 T180,100" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.5"/>
                    </svg>
                    <svg class="es-flow absolute -right-6 bottom-4 h-56 w-56 text-cyan-400/40" style="animation-delay: 1.8s;" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                        <path d="M100,20 Q180,60 100,100 T100,180" stroke="currentColor" stroke-width="1.5" fill="none" opacity="0.5"/>
                    </svg>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="mb-6 text-sm uppercase tracking-[0.2em] text-gray-400">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Let audiences find <span class="text-gradient-dance">your next performance</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Join dance companies, studios, and troupes who've simplified how they share their schedule.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-company" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-rose-500 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-rose-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Create Your Schedule
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
