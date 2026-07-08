<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Libraries | Share Your Programs</x-slot>
    <x-slot name="description">Keep patrons engaged. Share story times, author events, and workshops. Email your community directly - no algorithm. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Libraries</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Libraries",
        "description": "Keep patrons engaged. Share story times, author events, and workshops. Email your community directly. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Libraries"
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
                "name": "Is Event Schedule free for libraries?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your program schedule, building a patron following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage story times, author events, and workshops together?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by program type - children's story times, author talks, book clubs, technology workshops, and community meetings. Each program can have its own description, images, and registration details."
                }
            },
            {
                "@type": "Question",
                "name": "How do patrons find out about library programs?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Patrons can follow your library's schedule and receive email notifications for new programs. Embed your calendar on your library website, share on social media, or send newsletters to your community."
                }
            },
            {
                "@type": "Question",
                "name": "Can patrons register for programs?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Enable registration on any event to manage attendance and capacity. Patrons receive confirmation and reminders. For paid programs, connect Stripe to handle payments with zero platform fees."
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
        "name": "Event Schedule for Libraries",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Library Program Management Software",
        "operatingSystem": "Web",
        "description": "Keep patrons engaged. Share story times, author events, and workshops. Email your community directly. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Program announcement newsletters",
            "Recurring program scheduling",
            "Author and special event listings",
            "Programs organized by age group",
            "Zero-fee registration and ticketing",
            "Google Calendar two-way sync",
            "Program attendance analytics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "library program calendar, library event schedule, story time scheduling, author event management, free library scheduling",
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
        /* For-libraries "The Catalog" styles. The shared es-* motion system
           lives in marketing.css; this holds the sky gradient, the library-card
           badge, the card-catalog cards, the drifting programs card, and the
           dust-mote-in-lamplight motif. */
        .text-gradient-sky {
            background: linear-gradient(135deg, #0ea5e9, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-sky {
            background: linear-gradient(135deg, #38bdf8, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .library-card-badge {
            background: rgba(255, 251, 235, 0.8);
            border: 1px solid rgba(14, 165, 233, 0.2);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .dark .library-card-badge {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(14, 165, 233, 0.2);
        }
        .catalog-card { transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s; }
        .catalog-card:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12); }
        .dark .catalog-card:hover { box-shadow: 0 6px 16px rgba(0, 0, 0, 0.35); }

        @keyframes es-book-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-book-float { animation: es-book-float 6s ease-in-out infinite; }

        /* Dust motes in lamplight */
        .es-mote { pointer-events: none; overflow: hidden; }
        .es-mote span {
            position: absolute;
            bottom: -10px;
            border-radius: 9999px;
            background: radial-gradient(circle at 40% 35%, rgba(253, 230, 138, 0.95), rgba(217, 119, 6, 0.25));
            opacity: 0;
            animation: es-mote var(--mote-dur, 14s) linear infinite;
            animation-delay: var(--mote-delay, 0s);
        }
        @keyframes es-mote {
            0% { transform: translateY(0) translateX(0); opacity: 0; }
            15% { opacity: var(--mote-op, 0.5); }
            85% { opacity: var(--mote-op, 0.5); }
            100% { transform: translateY(-200px) translateX(18px); opacity: 0; }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-book-float, .es-mote span { animation: none !important; }
            .es-mote span { opacity: 0.3; transform: none; }
        }
    </style>

    @php
        // Drifting dust motes: [left, size(px), duration, delay, opacity]
        $motes = [
            ['9%', 4, '15s', '0s', '0.5'],
            ['21%', 6, '17s', '2.4s', '0.4'],
            ['33%', 3, '13s', '4s', '0.35'],
            ['45%', 5, '16s', '1s', '0.45'],
            ['57%', 4, '15s', '3s', '0.4'],
            ['69%', 6, '18s', '4.8s', '0.4'],
            ['81%', 3, '14s', '1.6s', '0.45'],
            ['92%', 5, '16s', '3.4s', '0.35'],
        ];
        // Card catalog programs: [name, time, meta, dewey, emoji, tab, iconBg, timeText, dot]
        $catalog = [
            ['Toddler Story Time', 'Tuesdays &bull; 10:00 AM', 'Ages 0-3 &bull; Children\'s Room', '028.5', '&#128214;', 'bg-sky-400/60', 'bg-sky-500/20', 'text-sky-300', 'bg-sky-400'],
            ['Teen Coding Club', 'Wednesdays &bull; 4:00 PM', 'Ages 13-18 &bull; Maker Space', '005.1', '&#128187;', 'bg-sky-400/60', 'bg-sky-500/20', 'text-sky-300', 'bg-sky-400'],
            ['Adult Book Club', '1st Thursday &bull; 7:00 PM', 'Adults &bull; Meeting Room', '813.5', '&#128218;', 'bg-blue-400/60', 'bg-blue-500/20', 'text-blue-300', 'bg-blue-400'],
            ['Senior Tech Help', 'Fridays &bull; 1:00 PM', 'Seniors &bull; Computer Lab', '004.0', '&#128421;', 'bg-teal-400/60', 'bg-teal-500/20', 'text-teal-300', 'bg-teal-400'],
            ['Author Readings', 'Saturdays &bull; 2:00 PM', 'All Ages &bull; Main Hall', '808.0', '&#9997;&#65039;', 'bg-blue-400/60', 'bg-blue-500/20', 'text-blue-300', 'bg-blue-400'],
            ['Film Screenings', '2nd Saturday &bull; 6:00 PM', 'All Ages &bull; Community Room', '791.4', '&#127916;', 'bg-rose-400/60', 'bg-rose-500/20', 'text-rose-300', 'bg-rose-400'],
        ];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: your programs, your patrons                         -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 30%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 42%, rgba(59, 130, 246, 0.28), rgba(59, 130, 246, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <!-- Reading lamp warm glow -->
            <div class="absolute left-0 top-0 h-3/5 w-3/5 rounded-full bg-amber-400/[0.06] blur-[150px] dark:bg-amber-400/[0.04]"></div>
            <!-- Bookshelf pattern -->
            <div class="absolute inset-0 overflow-hidden opacity-[0.05] dark:opacity-[0.07]">
                <svg aria-hidden="true" width="100%" height="100%">
                    <defs>
                        <pattern id="bookshelf" x="0" y="0" width="200" height="80" patternUnits="userSpaceOnUse">
                            <line x1="0" y1="78" x2="200" y2="78" stroke="#475569" stroke-width="2" />
                            <rect x="8" y="15" width="10" height="63" rx="1" fill="#0ea5e9" /><rect x="22" y="25" width="8" height="53" rx="1" fill="#0EA5E9" /><rect x="34" y="10" width="12" height="68" rx="1" fill="#3b82f6" /><rect x="50" y="20" width="9" height="58" rx="1" fill="#4E81FA" /><rect x="63" y="30" width="11" height="48" rx="1" fill="#0ea5e9" /><rect x="78" y="8" width="10" height="70" rx="1" fill="#475569" /><rect x="92" y="22" width="8" height="56" rx="1" fill="#0EA5E9" /><rect x="104" y="15" width="12" height="63" rx="1" fill="#3b82f6" /><rect x="120" y="28" width="9" height="50" rx="1" fill="#0ea5e9" /><rect x="133" y="12" width="11" height="66" rx="1" fill="#4E81FA" /><rect x="148" y="18" width="10" height="60" rx="1" fill="#475569" /><rect x="162" y="25" width="8" height="53" rx="1" fill="#0EA5E9" /><rect x="174" y="10" width="12" height="68" rx="1" fill="#3b82f6" /><rect x="190" y="20" width="8" height="58" rx="1" fill="#0ea5e9" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#bookshelf)" />
                </svg>
            </div>
            <div class="es-mote absolute inset-x-0 bottom-0 top-1/4">
                @foreach ($motes as [$l, $s, $d, $dl, $op])
                    <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; --mote-dur: {{ $d }}; --mote-delay: {{ $dl }}; --mote-op: {{ $op }};"></span>
                @endforeach
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 library-card-badge mb-8 inline-flex flex-col items-center rounded-sm px-5 py-2.5 backdrop-blur-sm">
                <div class="flex items-center gap-2">
                    <svg aria-hidden="true" class="h-4 w-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Libraries</span>
                </div>
                <div class="mt-1.5 h-px w-full bg-sky-400/30"></div>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your programs. Your patrons.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-sky es-gradient-anim">Always booked.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From story time to author readings. One calendar for all your programs. Keep patrons engaged and informed.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#catalog" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Browse the catalog
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=venue') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                    Create your program calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Program-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Author Readings', 'Book Clubs', 'Story Time', 'Film Screenings', 'Workshops', 'Maker Space', 'Lectures', 'Literacy Programs'] as $tag)
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
    <!-- 2. Bento features                                            -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Everything to fill your <span class="text-gradient-sky">programs</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Program updates newsletter (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Newsletter
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">New program? Your patrons are first to know.</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Summer reading kickoff, new author visit, special workshops - one click emails everyone who signed up. No algorithm decides who sees your announcements.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Your patrons, direct reach</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No middleman</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-sky-300 bg-gradient-to-br from-sky-100 to-blue-100 p-4 dark:border-sky-400/30 dark:from-sky-950 dark:to-blue-950">
                                        <div class="mb-4 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-600"><svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div>
                                            <div><div class="text-sm font-medium text-gray-900 dark:text-white">This Month's Programs</div><div class="text-xs text-sky-600 dark:text-sky-300">Sending to 1,847 patrons...</div></div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-sky-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Author Visit: Jane Smith</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Summer Reading Kickoff</span></div>
                                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-white p-2 dark:bg-white/10" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-sky-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Teen Maker Space Workshop</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Recurring programs -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Recurring
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Weekly programs, set once</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Story time every Tuesday, book club every month. Set it once and it appears automatically.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-blue-400/30 bg-blue-500/15 p-2" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-sm text-gray-900 dark:text-white">Toddler Story Time</span><span class="ml-auto text-xs text-blue-600 dark:text-blue-300">Tue</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-sky-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Teen Book Club</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">Wed</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-sky-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Craft Hour</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">Thu</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 3;"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Film Fridays</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">Fri</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Author events -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Author Events
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Showcase visiting authors</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Author readings, book signings, and Q&A sessions. Let patrons register and never miss an event.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="w-48 -rotate-2 rounded-xl border border-blue-300/50 bg-gradient-to-br from-blue-100 to-blue-50 p-4 text-center shadow-lg transition-transform group-hover:rotate-0 dark:border-blue-600/30 dark:from-blue-800 dark:to-blue-900">
                                <div class="text-[10px] uppercase tracking-widest text-blue-800 dark:text-blue-200">Author Visit</div>
                                <div class="mt-1 font-serif text-sm font-semibold text-blue-900 dark:text-blue-100">Jane Smith</div>
                                <div class="mx-auto mt-2 flex h-20 w-16 items-center justify-center rounded bg-gradient-to-br from-blue-200 to-blue-300 dark:from-blue-700 dark:to-blue-800">
                                    <svg aria-hidden="true" class="h-8 w-8 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                </div>
                                <div class="mt-2 text-[10px] text-blue-600 dark:text-blue-300">Sat, Mar 15 &bull; 2 PM</div>
                                <div class="mt-1 text-[9px] text-blue-500 dark:text-blue-400">Reading & Signing</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Community programs (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Community Programs
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Programs for every age group</h3>
                                <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">From toddler story time to senior tech help. Organize programs by audience so patrons find exactly what they need.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Filter by age group</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Easy to browse</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3" aria-hidden="true">
                                <div class="es-ai-field rounded-xl border border-sky-400/30 bg-sky-500/15 p-4 text-center" style="--i: 0;"><div class="mb-1 text-2xl">&#128118;</div><div class="text-sm font-semibold text-gray-900 dark:text-white">Children</div><div class="mt-1 text-xs text-sky-600 dark:text-sky-300">12 programs</div></div>
                                <div class="es-ai-field rounded-xl border border-sky-400/30 bg-sky-500/15 p-4 text-center" style="--i: 1;"><div class="mb-1 text-2xl">&#129680;</div><div class="text-sm font-semibold text-gray-900 dark:text-white">Teen</div><div class="mt-1 text-xs text-sky-600 dark:text-sky-300">8 programs</div></div>
                                <div class="es-ai-field rounded-xl border border-blue-400/30 bg-blue-500/15 p-4 text-center" style="--i: 2;"><div class="mb-1 text-2xl">&#128100;</div><div class="text-sm font-semibold text-gray-900 dark:text-white">Adult</div><div class="mt-1 text-xs text-blue-600 dark:text-blue-300">10 programs</div></div>
                                <div class="es-ai-field rounded-xl border border-blue-400/30 bg-blue-500/15 p-4 text-center" style="--i: 3;"><div class="mb-1 text-2xl">&#128116;</div><div class="text-sm font-semibold text-gray-900 dark:text-white">Senior</div><div class="mt-1 text-xs text-blue-600 dark:text-blue-300">6 programs</div></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Zero fee ticketing -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Free Tickets
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Zero fee registration</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Free tickets for free programs. Manage capacity for story time, workshops, and special events without any fees.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="w-44 rotate-1 rounded-xl border border-emerald-300/50 bg-gradient-to-br from-emerald-100 to-green-50 p-4 text-center shadow-lg transition-transform group-hover:rotate-0 dark:border-emerald-600/30 dark:from-emerald-800 dark:to-green-900">
                                <div class="text-[10px] uppercase tracking-widest text-emerald-800 dark:text-emerald-200">Free Event</div>
                                <div class="mt-1 font-serif text-sm font-semibold text-emerald-900 dark:text-emerald-100">Story Time</div>
                                <div class="mt-2 text-xl font-bold text-emerald-700 dark:text-emerald-300">FREE</div>
                                <div class="mt-1 text-[10px] text-emerald-600 dark:text-emerald-300">Tuesdays &bull; 10 AM</div>
                                <div class="mt-1 text-[9px] text-emerald-500 dark:text-emerald-400">15 spots remaining</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Google Calendar -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Google Calendar
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sync with Google Calendar</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync keeps your library's Google Calendar and Event Schedule in perfect harmony.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-lg dark:bg-gray-800">
                                    <svg aria-hidden="true" class="h-10 w-10" viewBox="0 0 24 24"><path fill="#4285F4" d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12s4.48 10 10 10 10-4.48 10-10z" opacity="0.1" /><path fill="#4285F4" d="M12 7v5l4.28 2.54.72-1.21-3.5-2.08V7H12z" /><path fill="#EA4335" d="M12 2C6.48 2 2 6.48 2 12h2c0-4.42 3.58-8 8-8V2z" /><path fill="#FBBC05" d="M2 12c0 5.52 4.48 10 10 10v-2c-4.42 0-8-3.58-8-8H2z" /><path fill="#34A853" d="M12 22c5.52 0 10-4.48 10-10h-2c0 4.42-3.58 8-8 8v2z" /></svg>
                                </div>
                                <div class="mt-3 flex items-center justify-center gap-2">
                                    <div class="h-0.5 w-8 bg-blue-400/50"></div>
                                    <svg aria-hidden="true" class="es-sync-dot h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    <div class="h-0.5 w-8 bg-blue-400/50"></div>
                                </div>
                                <div class="mt-2 text-center text-xs font-medium text-blue-600 dark:text-blue-300">Two-way sync</div>
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
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Track program attendance</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">See which programs draw the most patrons. Make data-driven decisions for your programming.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="es-ai-field" style="--i: 0;"><div class="mb-1 flex justify-between text-xs"><span class="text-gray-600 dark:text-gray-300">Story Time</span><span class="text-cyan-600 dark:text-cyan-300">94%</span></div><div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-500" style="width: 94%"></div></div></div>
                            <div class="es-ai-field" style="--i: 1;"><div class="mb-1 flex justify-between text-xs"><span class="text-gray-600 dark:text-gray-300">Book Club</span><span class="text-cyan-600 dark:text-cyan-300">78%</span></div><div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-500" style="width: 78%"></div></div></div>
                            <div class="es-ai-field" style="--i: 2;"><div class="mb-1 flex justify-between text-xs"><span class="text-gray-600 dark:text-gray-300">Tech Help</span><span class="text-cyan-600 dark:text-cyan-300">62%</span></div><div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-sky-500" style="width: 62%"></div></div></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Your month of programs (dark band - card catalog)         -->
    <!-- ============================================================ -->
    <section id="catalog" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 30%, rgba(14, 165, 233, 0.24), rgba(14, 165, 233, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(217, 119, 6, 0.14), rgba(217, 119, 6, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-mote absolute inset-0">
                    @foreach ($motes as [$l, $s, $d, $dl, $op])
                        <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; --mote-dur: {{ $d }}; --mote-delay: {{ $dl }}; --mote-op: {{ $op }};"></span>
                    @endforeach
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Your month of <span class="text-gradient-sky">programs</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Every program, every age group, every week. Patrons see what's happening and sign up in seconds.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($catalog as [$name, $time, $meta, $dewey, $emoji, $tab, $iconBg, $timeText, $dot])
                        <div data-reveal class="catalog-card relative overflow-hidden rounded-sm border border-white/10 bg-white/[0.04] p-5">
                            <div class="absolute -top-px left-4 h-2.5 w-12 rounded-b-sm {{ $tab }}"></div>
                            <div class="absolute right-3 top-2 font-mono text-[9px] text-gray-500">{{ $dewey }}</div>
                            <div class="mb-3 mt-1 flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $iconBg }}"><span class="text-sm">{!! $emoji !!}</span></div>
                                <div><div class="text-sm font-semibold text-white">{{ $name }}</div><div class="text-xs {{ $timeText }}">{!! $time !!}</div></div>
                            </div>
                            <div class="flex items-center gap-2"><div class="h-1.5 w-1.5 rounded-full {{ $dot }}"></div><span class="text-xs text-gray-400">{!! $meta !!}</span></div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 text-center" data-reveal>
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.06] px-4 py-2 backdrop-blur-sm">
                        <svg aria-hidden="true" class="h-4 w-4 text-sky-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="text-sm text-gray-300">All recurring programs in one place - patrons see what's happening every day</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all types of <span class="text-gradient-sky">libraries</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From public branches to university collections.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Public Libraries"
                    description="Community programs, story times, workshops, and author events. Keep your neighborhood informed and engaged."
                    icon-color="sky"
                    blog-slug="for-public-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="University Libraries"
                    description="Lectures, research workshops, study sessions, and academic events. Reach students and faculty directly."
                    icon-color="indigo"
                    blog-slug="for-university-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Community Reading Rooms"
                    description="Reading groups, literacy programs, and neighborhood book exchanges. Build a culture of reading."
                    icon-color="blue"
                    blog-slug="for-community-reading-rooms"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Children's Libraries"
                    description="Story times, crafts, summer reading programs, and educational events. Make reading fun for every child."
                    icon-color="pink"
                    blog-slug="for-childrens-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Archive & Research Centers"
                    description="Exhibitions, lectures, guided tours, and research workshops. Share your collections with the public."
                    icon-color="slate"
                    blog-slug="for-archive-research-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Mobile Libraries"
                    description="Bookmobile stops, pop-up reading events, and outreach programs. Bring the library to your community."
                    icon-color="teal"
                    blog-slug="for-mobile-libraries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. How it works                                              -->
    <!-- ============================================================ -->
    @php
        $steps = [
            ['1', 'Set up your library', 'Sign up and add your library details. Set up program categories for children, teens, adults, and seniors.'],
            ['2', 'Build your program calendar', 'Add story times, book clubs, author events, and workshops. Set up recurring programs once.'],
            ['3', 'Engage your community', 'Patrons follow your calendar and get direct updates. No algorithm between you and your community.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    How it <span class="text-gradient-sky">works</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Get your library's program calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-blue-500 text-2xl font-bold text-white shadow-lg shadow-sky-500/25">
                            {{ $num }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Add your schedule to any website with one snippet" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set events to repeat weekly on chosen days" :url="marketing_url('/features/recurring-events')" icon-color="lime">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send event updates directly to followers' inboxes" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
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
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-community-centers', 'Community Centers'], ['/for-spoken-word', 'Spoken Word'], ['/for-workshop-instructors', 'Workshop Instructors'], ['/for-theaters', 'Theaters']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50 hover:shadow-md dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
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
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-sky">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything librarians ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for libraries?', 'Yes. Event Schedule is free forever for sharing your program schedule, building a patron following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan.'],
                    ['Can I manage story times, author events, and workshops together?', 'Yes. Use sub-schedules to organize by program type - children\'s story times, author talks, book clubs, technology workshops, and community meetings. Each program can have its own description, images, and registration details.'],
                    ['How do patrons find out about library programs?', 'Patrons can follow your library\'s schedule and receive email notifications for new programs. Embed your calendar on your library website, share on social media, or send newsletters to your community.'],
                    ['Can patrons register for programs?', 'Yes. Enable registration on any event to manage attendance and capacity. Patrons receive confirmation and reminders. For paid programs, connect Stripe to handle payments with zero platform fees.'],
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
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(14, 165, 233, 0.3), rgba(14, 165, 233, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-mote absolute inset-0">
                        @foreach ($motes as [$l, $s, $d, $dl, $op])
                            <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; --mote-dur: {{ $d }}; --mote-delay: {{ $dl }}; --mote-op: {{ $op }};"></span>
                        @endforeach
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your patrons deserve better than a <span class="text-gradient-sky">bulletin board</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Email your community directly. Fill your programs. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-library" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-sky-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
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
