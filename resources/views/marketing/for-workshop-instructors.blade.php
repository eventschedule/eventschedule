<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Workshop Instructors | Share Classes</x-slot>
    <x-slot name="description">Fill every workshop seat. Announce classes, sell spots with zero platform fees, and email your students directly. Build multi-session series. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Workshop Instructors</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Workshop Instructors",
        "description": "Fill every workshop seat. Announce classes, sell spots with zero platform fees, and email your students directly. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Workshop Instructors & Educators"
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
                "name": "Is Event Schedule free for workshop instructors?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your workshop schedule, building a following, and syncing with Google Calendar. Paid registration and newsletters are available on the Pro plan, with zero platform fees."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage different types of workshops in one schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by workshop type - cooking classes, pottery, photography, or craft sessions. Each workshop can have its own description, images, pricing, capacity limits, and registration options."
                }
            },
            {
                "@type": "Question",
                "name": "How do students discover my workshops?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Students can follow your schedule and receive email notifications when you add new workshops. Share your schedule link on social media, embed it on your studio or school website, or send newsletters to followers with upcoming sessions."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell spots and manage registrations?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell workshop spots directly from your schedule. Set per-workshop pricing, limit capacity, and check in attendees with QR codes. Event Schedule charges zero platform fees."
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
        "name": "Event Schedule for Workshop Instructors",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Workshop and Class Scheduling Software",
        "operatingSystem": "Web",
        "description": "Fill every workshop seat. Announce classes, sell spots with zero platform fees, and email your students directly. Build multi-session series. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Workshop announcement newsletters",
            "Zero-fee ticketing and registration",
            "Custom schedule URL for one-link sharing",
            "Multi-session workshop series with bundle pricing",
            "Per-workshop capacity management",
            "Google Calendar two-way sync",
            "Booking and audience analytics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "workshop scheduling, class registration software, workshop calendar, teaching class management, free workshop scheduling",
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
        /* For-workshop-instructors "The Workshop" styles. The shared es-* motion
           system and brand .text-gradient live in marketing.css; this holds the
           unified chalk identity: chalk-white on-board text, the deep chalk-slate
           heading gradient for light surfaces, the board-green stat surface, the
           chalk strokes and bars, the post-it series cards, and the chalk-dust. */
        /* Chalk-white gradient - reserved for text sitting on the dark board
           surface (stat band, finale) and for dark mode. */
        .chalk-text {
            background: linear-gradient(135deg, #eef5ff, #cfe3ff, #b7d6f5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 10px rgba(191, 219, 254, 0.32);
        }
        .dark .chalk-text {
            background: linear-gradient(135deg, #f1f7ff, #cbe3ff, #b6f0ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 8px rgba(147, 197, 253, 0.3);
            filter: blur(0.2px);
        }
        /* Heading gradient for light surfaces: deep board-green to chalk-slate so
           it stays legible on white; flips to chalk-white in dark mode. */
        .chalk-heading {
            background: linear-gradient(135deg, #1f4a3d, #2b6b6e, #285f86);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .chalk-heading {
            background: linear-gradient(135deg, #bfdcff, #d8ecff, #b8f1ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 10px rgba(147, 197, 253, 0.28);
        }
        .postit-card {
            box-shadow: 2px 3px 12px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s ease;
        }
        .postit-card:hover { transform: rotate(0deg) !important; }

        /* Rising chalk dust */
        .es-chalk-dust { pointer-events: none; overflow: hidden; }
        .es-chalk-dust span {
            position: absolute;
            bottom: -12px;
            border-radius: 9999px;
            background: radial-gradient(circle at 40% 35%, rgba(191, 219, 254, 0.95), rgba(147, 197, 253, 0.25));
            opacity: 0;
            animation: es-dust var(--dust-dur, 9s) linear infinite;
            animation-delay: var(--dust-delay, 0s);
        }
        @keyframes es-dust {
            0% { transform: translateY(0) translateX(0); opacity: 0; }
            15% { opacity: var(--dust-op, 0.5); }
            85% { opacity: var(--dust-op, 0.5); }
            100% { transform: translateY(-170px) translateX(14px); opacity: 0; }
        }
        /* Deep board-green surface for the stat band */
        .chalk-board {
            background:
                radial-gradient(120% 100% at 50% 0%, rgba(31, 74, 61, 0.55) 0%, rgba(12, 30, 26, 0.6) 60%),
                radial-gradient(120% 100% at 50% 0%, rgba(11, 20, 36, 0.9) 0%, rgba(5, 8, 8, 0.99) 62%),
                #08110d;
        }
        /* Small chalkboard tile for the analytics chalk bar-chart */
        .chalk-tile {
            background: linear-gradient(160deg, rgba(36, 82, 68, 0.97), rgba(19, 46, 39, 0.98));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), inset 0 0 22px rgba(0, 0, 0, 0.4);
        }
        .chalk-bar {
            background: linear-gradient(180deg, rgba(240, 248, 255, 0.92), rgba(196, 221, 245, 0.6));
            border-radius: 3px 3px 0 0;
            box-shadow: 0 0 7px rgba(190, 225, 255, 0.35);
        }
        /* The chalkboard surfaces stay intentionally dark in both modes; these
           deepen them a touch in dark mode so they nest into the page. */
        .dark .chalk-board {
            background:
                radial-gradient(120% 100% at 50% 0%, rgba(28, 66, 55, 0.6) 0%, rgba(9, 24, 21, 0.72) 60%),
                radial-gradient(120% 100% at 50% 0%, rgba(9, 16, 30, 0.94) 0%, rgba(4, 6, 6, 0.99) 62%),
                #060d0a;
        }
        .dark .chalk-tile {
            background: linear-gradient(160deg, rgba(31, 72, 60, 0.98), rgba(15, 40, 34, 0.99));
            border-color: rgba(255, 255, 255, 0.06);
        }
        .dark .chalk-bar {
            background: linear-gradient(180deg, rgba(244, 250, 255, 0.95), rgba(205, 228, 248, 0.62));
            box-shadow: 0 0 8px rgba(190, 225, 255, 0.4);
        }
        .chalk-ink { color: #e8f2ff; }
        .chalk-ink-dim { color: rgba(226, 240, 255, 0.62); }
        /* Hand-drawn chalk strokes (hero underline + how-it-works arrows).
           currentColor renders the chalk; deep slate on light, chalk-white on dark. */
        .chalk-stroke { color: #2b6b6e; opacity: 0.75; }
        .dark .chalk-stroke { color: #bfe3ff; opacity: 0.6; }
        /* Folded post-it corner for a bento mock */
        .postit-fold {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 26px;
            height: 26px;
            background: linear-gradient(135deg, transparent 47%, rgba(0, 0, 0, 0.16) 49%);
            border-bottom-right-radius: 1rem;
        }
        .dark .postit-fold {
            background: linear-gradient(135deg, transparent 47%, rgba(0, 0, 0, 0.4) 49%);
        }
        /* Chalk-blue accent for the See-all links, related-page cards, FAQ hovers */
        .chalk-link { color: #2f6ea3; transition: color 0.2s ease; }
        .chalk-link:hover { text-decoration: underline; }
        .dark .chalk-link { color: #93c5fd; }
        .chalk-rel { transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }
        .chalk-rel:hover { border-color: #7db5e6; background-color: rgba(125, 181, 230, 0.08); }
        .dark .chalk-rel:hover { border-color: rgba(125, 181, 230, 0.35); background-color: rgba(125, 181, 230, 0.06); }
        .group:hover .chalk-rel-title { color: #2f6ea3; }
        .dark .group:hover .chalk-rel-title { color: #93c5fd; }
        .group:hover .chalk-rel-arrow { color: #2f6ea3; }
        .dark .group:hover .chalk-rel-arrow { color: #93c5fd; }
        .chalk-faq { transition: border-color 0.2s ease; }
        .chalk-faq:hover { border-color: rgba(125, 181, 230, 0.5); }
        .dark .chalk-faq:hover { border-color: rgba(125, 181, 230, 0.35); }

        @media (prefers-reduced-motion: reduce) {
            .es-chalk-dust span { animation: none !important; }
            .es-chalk-dust span { opacity: 0.3; transform: none; }
        }
    </style>

    @php
        // Rising chalk dust: [left, size(px), duration, delay, opacity]
        $dust = [
            ['10%', 4, '9s', '0s', '0.5'],
            ['22%', 6, '11s', '2s', '0.4'],
            ['34%', 3, '8s', '3.5s', '0.35'],
            ['46%', 5, '10s', '1s', '0.45'],
            ['58%', 7, '12s', '2.6s', '0.4'],
            ['69%', 4, '9s', '4.2s', '0.4'],
            ['80%', 5, '10.5s', '1.4s', '0.45'],
            ['91%', 3, '8.5s', '3.2s', '0.35'],
        ];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: teach what you love                                 -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 32%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 40%, rgba(59, 130, 246, 0.3), rgba(59, 130, 246, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-chalk-dust absolute inset-x-0 bottom-0 top-1/3">
                @foreach ($dust as [$l, $s, $d, $dl, $op])
                    <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; --dust-dur: {{ $d }}; --dust-delay: {{ $dl }}; --dust-op: {{ $op }};"></span>
                @endforeach
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Workshop Instructors & Educators</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Teach what you love.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="chalk-heading es-gradient-anim">Fill every seat.</span></span></span>
            </h1>

            <div class="chalk-stroke es-fade-up es-d-2 mx-auto -mt-2 mb-6 h-3 w-64 max-w-full" aria-hidden="true">
                <svg viewBox="0 0 220 12" fill="none" preserveAspectRatio="none" class="h-full w-full">
                    <path d="M4 7 Q 45 2 90 6 T 172 5 Q 198 7 216 4" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none" />
                    <path d="M10 10 Q 70 8 130 9 T 210 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none" opacity="0.5" />
                </svg>
            </div>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From pottery to photography, cooking to coding. One link for all your workshops. Reach students directly.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the tools
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                    Create your schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Workshop-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Cooking', 'Pottery', 'Photography', 'Woodworking', 'Painting', 'Music Lessons', 'Sewing', 'Coding'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100/80 px-4 py-1.5 text-xs font-semibold text-sky-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-sky-400 to-blue-400"></span>
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
    <!-- 2. On the board (chalkboard dark band)                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark chalk-board noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 30%, rgba(45, 138, 110, 0.32), rgba(45, 138, 110, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(74, 160, 132, 0.24), rgba(74, 160, 132, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-chalk-dust absolute inset-0">
                    @foreach ($dust as [$l, $s, $d, $dl, $op])
                        <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; --dust-dur: {{ $d }}; --dust-delay: {{ $dl }}; --dust-op: {{ $op }};"></span>
                    @endforeach
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-2xl text-center" data-reveal>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.06] px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-sky-300 backdrop-blur-sm">On the board</span>
                </div>
                <div class="grid grid-cols-1 gap-8 text-center md:grid-cols-3" data-reveal-group="90">
                    <div data-reveal>
                        <div class="chalk-text mb-2 text-4xl font-black md:text-5xl"><span data-count-to="68">68</span>%</div>
                        <p class="text-gray-300">workshop spots go unsold without direct marketing</p>
                    </div>
                    <div data-reveal>
                        <div class="chalk-text mb-2 text-4xl font-black md:text-5xl">$0</div>
                        <p class="text-gray-300">platform fees on ticket sales</p>
                    </div>
                    <div data-reveal>
                        <div class="chalk-text mb-2 text-4xl font-black md:text-5xl">1-click</div>
                        <p class="text-gray-300">email to your entire student list</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Bento features                                            -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Everything to fill every <span class="chalk-heading">workshop</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Workshop announcements (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Workshop Announcements
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Announce new workshops, fill seats instantly</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">One click sends your upcoming workshops to every student who follows you. No algorithm decides who sees it.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Email newsletters</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Direct to inbox</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No social media needed</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-sky-300 bg-gradient-to-br from-sky-100 to-blue-100 p-4 dark:border-sky-400/30 dark:from-sky-950 dark:to-blue-950">
                                        <div class="mb-3 text-[10px] font-semibold tracking-wide text-sky-600 dark:text-sky-300">UPCOMING WORKSHOPS</div>
                                        <div class="space-y-3">
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-sky-400/30 bg-sky-500/20 p-2" style="--i: 0;">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-orange-500 to-red-500 text-xs font-bold text-white">SAT</div>
                                                <div class="flex-1"><div class="text-sm font-semibold text-gray-900 dark:text-white">Italian Pasta Making</div><div class="text-xs text-sky-600 dark:text-sky-300">Sat, Feb 8 - 3 spots left</div></div>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-yellow-500 text-xs font-bold text-white">SUN</div>
                                                <div class="flex-1"><div class="text-sm font-medium text-gray-600 dark:text-gray-300">Wheel Throwing Basics</div><div class="text-xs text-gray-500 dark:text-gray-400">Sun, Feb 9 - 6 spots left</div></div>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 text-xs font-bold text-white">WED</div>
                                                <div class="flex-1"><div class="text-sm font-medium text-gray-600 dark:text-gray-300">Night Photography Walk</div><div class="text-xs text-gray-500 dark:text-gray-400">Wed, Feb 12 - 8 spots left</div></div>
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

                <!-- Zero-fee ticketing -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Zero-Fee Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Keep 100% of every sale</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">No platform fees. No per-ticket charges. Every dollar your students pay goes to you.</p>
                        <div class="mt-auto rounded-xl border border-emerald-400/30 bg-emerald-500/15 p-4 text-center" aria-hidden="true">
                            <div class="mb-1 text-5xl font-bold text-emerald-500 dark:text-emerald-400"><span data-count-to="100">100</span>%</div>
                            <div class="text-sm font-medium text-emerald-600 dark:text-emerald-300">you keep</div>
                            <div class="mt-3 flex items-center justify-center gap-2">
                                <div class="h-1 flex-1 rounded-full bg-emerald-400"></div>
                                <span class="text-xs text-emerald-600 dark:text-emerald-300">$0 platform fees</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- One link -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            One Link
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One link for everything</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Share one URL. Students see all your upcoming workshops, book, and pay.</p>
                        <div class="mt-auto rounded-xl border border-blue-400/30 bg-blue-500/15 p-4" aria-hidden="true">
                            <div class="flex items-center gap-2 rounded-lg border border-gray-300 bg-gray-200 px-3 py-2 dark:border-white/10 dark:bg-[#0f0f14]">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span class="truncate font-mono text-sm text-blue-600 dark:text-blue-300">yourworkshop.eventschedule.com</span>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <div class="h-2 flex-1 rounded-full bg-blue-400/40"></div>
                                <div class="h-2 flex-1 rounded-full bg-blue-400/25"></div>
                                <div class="h-2 flex-1 rounded-full bg-blue-400/15"></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Workshop series / curriculum (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    Workshop Series
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Build a curriculum that keeps students coming back</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Create multi-session series with progressive skill building. Offer series discounts to encourage full enrollment.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Multi-session series</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Progressive levels</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Bundle pricing</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="relative max-w-xs overflow-hidden rounded-2xl border border-blue-300 bg-gradient-to-br from-blue-100 to-sky-100 p-4 dark:border-blue-400/30 dark:from-blue-950 dark:to-sky-950">
                                        <div class="postit-fold" aria-hidden="true"></div>
                                        <div class="mb-3 flex items-center justify-between">
                                            <div class="text-[10px] font-semibold tracking-wide text-blue-600 dark:text-blue-300">POTTERY FUNDAMENTALS</div>
                                            <span class="inline-flex items-center rounded-full border border-green-300 bg-green-100 px-2 py-0.5 text-[10px] font-medium text-green-600 dark:border-green-500/30 dark:bg-green-500/20 dark:text-green-300">Series Discount</span>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-blue-300 bg-blue-200 p-2 dark:border-blue-400/30 dark:bg-blue-500/20" style="--i: 0;">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500/40 text-xs font-bold text-blue-600 dark:text-blue-200">1</div>
                                                <div class="flex-1 text-sm font-medium text-gray-900 dark:text-white">Hand Building</div>
                                                <svg aria-hidden="true" class="h-4 w-4 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-blue-300 bg-blue-200 p-2 dark:border-blue-400/30 dark:bg-blue-500/20" style="--i: 1;">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500/40 text-xs font-bold text-blue-600 dark:text-blue-200">2</div>
                                                <div class="flex-1 text-sm font-medium text-gray-900 dark:text-white">Wheel Throwing</div>
                                                <svg aria-hidden="true" class="h-4 w-4 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-100 p-2 dark:border-blue-400/20 dark:bg-blue-500/10" style="--i: 2;">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500/30 text-xs font-bold text-blue-600 dark:text-blue-300">3</div>
                                                <div class="flex-1 text-sm font-medium text-gray-600 dark:text-gray-300">Glazing</div>
                                                <div class="text-[10px] text-blue-600 dark:text-blue-400">Next</div>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 3;">
                                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-500/20 text-xs font-bold text-blue-500 dark:text-blue-400">4</div>
                                                <div class="flex-1 text-sm font-medium text-gray-600 dark:text-gray-400">Advanced Forms</div>
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

                <!-- Capacity management -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Capacity Management
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Never overbook a class</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Set seat limits per workshop. Students see availability in real time.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="es-ai-field flex items-center justify-between rounded-lg border border-red-400/30 bg-red-500/20 p-2" style="--i: 0;"><span class="text-sm font-medium text-gray-900 dark:text-white">Pasta Making</span><span class="text-xs font-medium text-red-600 dark:text-red-300">SOLD OUT</span></div>
                            <div class="es-ai-field flex items-center justify-between rounded-lg border border-amber-400/30 bg-amber-500/20 p-2" style="--i: 1;"><span class="text-sm font-medium text-gray-900 dark:text-white">Wheel Throwing</span><span class="text-xs font-medium text-amber-600 dark:text-amber-300">2 seats left</span></div>
                            <div class="es-ai-field flex items-center justify-between rounded-lg border border-green-400/30 bg-green-500/20 p-2" style="--i: 2;"><span class="text-sm font-medium text-gray-900 dark:text-white">Photography Walk</span><span class="text-xs font-medium text-green-600 dark:text-green-300">8 seats left</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Google Calendar sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Google Calendar
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Two-way calendar sync</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Your workshops appear in Google Calendar. Changes sync both ways automatically.</p>
                        <div class="mt-auto rounded-xl border border-blue-400/30 bg-blue-500/15 p-3" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white">
                                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="18" rx="2" fill="#4285F4" /><rect x="4" y="8" width="16" height="11" rx="1" fill="white" /><text x="12" y="17" text-anchor="middle" font-size="8" font-weight="bold" fill="#4285F4">31</text></svg>
                                </div>
                                <div class="flex-1"><div class="text-sm font-medium text-gray-900 dark:text-white">Google Calendar</div><div class="text-xs text-green-500 dark:text-green-400">Connected</div></div>
                            </div>
                            <div class="flex items-center justify-center gap-3">
                                <div class="text-xs text-blue-600 dark:text-blue-300">Event Schedule</div>
                                <svg aria-hidden="true" class="es-sync-dot h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                <div class="text-xs text-blue-600 dark:text-blue-300">Google Calendar</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Analytics (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Analytics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Know what sells</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">See which workshops fill fastest, which days work best, and how your audience grows.</p>
                        <div class="mt-auto chalk-tile rounded-xl p-3" aria-hidden="true">
                            <div class="flex h-24 items-end gap-2">
                                <div class="chalk-bar flex-1" style="height: 40%; opacity: 0.72;"></div>
                                <div class="chalk-bar flex-1" style="height: 55%; opacity: 0.8;"></div>
                                <div class="chalk-bar flex-1" style="height: 45%; opacity: 0.76;"></div>
                                <div class="chalk-bar flex-1" style="height: 70%; opacity: 0.86;"></div>
                                <div class="chalk-bar flex-1" style="height: 85%; opacity: 0.92;"></div>
                                <div class="chalk-bar flex-1" style="height: 65%; opacity: 0.84;"></div>
                                <div class="chalk-bar flex-1" style="height: 100%;"></div>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="chalk-ink-dim text-[10px]">Mon</span>
                                <span class="chalk-ink text-[10px] font-medium">This week: 47 bookings</span>
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
    <!-- 4. Workshop series progression (post-it desk)                -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    See how a workshop series <span class="chalk-heading">comes together</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Progressive skill building. Multi-session enrollment. Loyal students.
                </p>
            </div>

            <div class="relative rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-100 to-blue-100 p-8 dark:border-sky-500/30 dark:from-sky-900/60 dark:to-blue-900/60 lg:p-10" data-reveal="panel">
                <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pottery Fundamentals</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">4-session course - Saturdays in February</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-green-500/30 bg-green-500/20 px-3 py-1 text-sm font-medium text-green-600 dark:text-green-300">
                        <svg aria-hidden="true" class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                        Series Discount: 15% off
                    </span>
                </div>

                <div class="relative grid grid-cols-1 gap-4 md:grid-cols-4" data-reveal-group="90">
                    <div data-reveal class="postit-card relative rounded-lg border border-yellow-300 bg-gradient-to-br from-yellow-100 to-amber-100 p-6 text-center dark:border-sky-500/30 dark:from-sky-800/60 dark:to-sky-700/40" style="transform: rotate(-1.5deg);">
                        <div class="absolute right-0 top-0 h-6 w-6 bg-gradient-to-bl from-yellow-200/80 to-transparent dark:from-sky-600/40"></div>
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-sky-500/30"><span class="text-lg font-bold text-sky-700 dark:text-sky-200">1</span></div>
                        <h4 class="mb-1 text-lg font-bold text-gray-900 dark:text-white">Hand Building</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Feb 1 - Pinch & coil techniques</p>
                    </div>

                    <div class="absolute left-[24%] top-1/2 z-10 hidden -translate-y-1/2 md:flex">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </div>

                    <div data-reveal class="postit-card relative rounded-lg border border-orange-300 bg-gradient-to-br from-orange-100 to-yellow-100 p-6 text-center dark:border-blue-500/30 dark:from-blue-800/60 dark:to-blue-700/40" style="transform: rotate(1deg);">
                        <div class="absolute right-0 top-0 h-6 w-6 bg-gradient-to-bl from-orange-200/80 to-transparent dark:from-blue-600/40"></div>
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/30"><span class="text-lg font-bold text-blue-700 dark:text-blue-200">2</span></div>
                        <h4 class="mb-1 text-lg font-bold text-gray-900 dark:text-white">Wheel Throwing</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Feb 8 - Centering & pulling</p>
                    </div>

                    <div data-reveal class="postit-card relative rounded-lg border border-lime-300 bg-gradient-to-br from-lime-100 to-green-100 p-6 text-center dark:border-blue-500/30 dark:from-blue-800/60 dark:to-blue-700/40" style="transform: rotate(-0.5deg);">
                        <div class="absolute right-0 top-0 h-6 w-6 bg-gradient-to-bl from-lime-200/80 to-transparent dark:from-blue-600/40"></div>
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-500/30"><span class="text-lg font-bold text-blue-700 dark:text-blue-200">3</span></div>
                        <h4 class="mb-1 text-lg font-bold text-gray-900 dark:text-white">Glazing</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Feb 15 - Color & finish</p>
                    </div>

                    <div data-reveal class="postit-card relative rounded-lg border border-sky-300 bg-gradient-to-br from-sky-100 to-blue-100 p-6 text-center dark:border-sky-500/30 dark:from-sky-800/60 dark:to-sky-700/40" style="transform: rotate(1.5deg);">
                        <div class="absolute right-0 top-0 h-6 w-6 bg-gradient-to-bl from-sky-200/80 to-transparent dark:from-sky-600/40"></div>
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-sky-500/30"><span class="text-lg font-bold text-sky-700 dark:text-sky-200">4</span></div>
                        <h4 class="mb-1 text-lg font-bold text-gray-900 dark:text-white">Advanced Forms</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Feb 22 - Creative projects</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all <span class="chalk-heading">workshop instructors</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whatever you teach, Event Schedule helps you fill every class.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Cooking Class Instructors"
                    description="From pasta making to pastry arts. Share your recipes, sell spots, and build a community of food lovers."
                    icon-color="sky"
                    blog-slug="for-cooking-class-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Pottery & Ceramics Teachers"
                    description="Wheel throwing, hand building, and glazing workshops. Manage studio capacity and skill levels."
                    icon-color="blue"
                    blog-slug="for-pottery-ceramics-teachers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Photography Workshop Leaders"
                    description="Photo walks, studio sessions, and editing workshops. Collect gear requirements from students upfront."
                    icon-color="blue"
                    blog-slug="for-photography-workshop-leaders"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Craft & Maker Instructors"
                    description="Woodworking, metalwork, sewing, and beyond. List materials needed and manage workshop capacities."
                    icon-color="amber"
                    blog-slug="for-craft-maker-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Art Teachers"
                    description="Painting, drawing, and mixed media classes. Showcase student work and build a creative community."
                    icon-color="cyan"
                    blog-slug="for-art-teachers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Music Lesson Instructors"
                    description="Group lessons, masterclasses, and jam sessions. Schedule recurring classes and manage student enrollment."
                    icon-color="teal"
                    blog-slug="for-music-lesson-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. How it works                                              -->
    <!-- ============================================================ -->
    @php
        $steps = [
            ['1', 'List your workshops', 'Add your classes with descriptions, materials needed, skill levels, and pricing. Set capacity limits and recurring schedules.'],
            ['2', 'Share your link', 'One URL for all your workshops. Put it in your bio, share it with your audience, and let students browse and book directly.'],
            ['3', 'Fill your classes', 'Students follow you and get notified of new workshops. Send newsletters to fill spots. Keep 100% of ticket revenue.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    How it <span class="chalk-heading">works</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Get your workshops online in three steps.
                </p>
            </div>

            <div class="relative grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                <div class="chalk-stroke pointer-events-none absolute top-1/2 z-10 hidden -translate-y-1/2 md:block" style="left: 31.5%;" aria-hidden="true">
                    <svg viewBox="0 0 64 24" fill="none" class="h-6 w-16">
                        <path d="M4 13 Q 26 7 44 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" fill="none" />
                        <path d="M38 6 L 50 12 L 39 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                </div>
                <div class="chalk-stroke pointer-events-none absolute top-1/2 z-10 hidden -translate-y-1/2 md:block" style="left: 64.5%;" aria-hidden="true">
                    <svg viewBox="0 0 64 24" fill="none" class="h-6 w-16">
                        <path d="M4 12 Q 24 17 44 11" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" fill="none" />
                        <path d="M38 5 L 50 11 L 40 18" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                    </svg>
                </div>
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-blue-500 text-2xl font-bold text-white shadow-lg shadow-sky-500/25">
                            {{ $num }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key <span class="chalk-heading">features</span></h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set events to repeat weekly on chosen days" :url="marketing_url('/features/recurring-events')" icon-color="lime">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google Calendar" :url="marketing_url('/features/calendar-sync')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="chalk-link inline-flex items-center font-medium">
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
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related <span class="chalk-heading">pages</span></h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-online-classes', 'Online Classes'], ['/for-fitness-and-yoga', 'Fitness & Yoga'], ['/for-community-centers', 'Community Centers'], ['/for-libraries', 'Libraries']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="chalk-rel group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="chalk-rel-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="chalk-rel-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="chalk-link inline-flex items-center font-medium">
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
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="chalk-heading">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything workshop instructors ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for workshop instructors?', 'Yes. Event Schedule is free forever for sharing your workshop schedule, building a following, and syncing with Google Calendar. Paid registration and newsletters are available on the Pro plan, with zero platform fees.'],
                    ['Can I manage different types of workshops in one schedule?', 'Yes. Use sub-schedules to organize by workshop type - cooking classes, pottery, photography, or craft sessions. Each workshop can have its own description, images, pricing, capacity limits, and registration options.'],
                    ['How do students discover my workshops?', 'Students can follow your schedule and receive email notifications when you add new workshops. Share your schedule link on social media, embed it on your studio or school website, or send newsletters to followers with upcoming sessions.'],
                    ['Can I sell spots and manage registrations?', 'Yes. Connect your Stripe account and sell workshop spots directly from your schedule. Set per-workshop pricing, limit capacity, and check in attendees with QR codes. Event Schedule charges zero platform fees.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="chalk-faq group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-sky-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(14, 165, 233, 0.32), rgba(14, 165, 233, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-chalk-dust absolute inset-0">
                        @foreach ($dust as [$l, $s, $d, $dl, $op])
                            <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; --dust-dur: {{ $d }}; --dust-delay: {{ $dl }}; --dust-op: {{ $op }};"></span>
                        @endforeach
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your workshop deserves a <span class="chalk-text">full room</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stop losing students to clunky booking systems. One link, zero fees, full classes. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-workshop" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-sky-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
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
