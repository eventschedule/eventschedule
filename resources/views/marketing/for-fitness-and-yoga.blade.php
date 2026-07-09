<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Fitness & Yoga | Class Schedule</x-slot>
    <x-slot name="description">Share your class schedule, sell drop-in passes, and reach students directly with newsletters. No algorithm. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Fitness & Yoga</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Fitness & Yoga",
        "description": "Share your class schedule, sell drop-in passes, and reach students directly with newsletters. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Fitness & Yoga Instructors"
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
                "name": "Is Event Schedule free for fitness and yoga instructors?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your class schedule, building a student following, and syncing with Google Calendar. Paid class registration and newsletters are available on the Pro plan, with zero platform fees."
                }
            },
            {
                "@type": "Question",
                "name": "Can I schedule recurring weekly classes?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up daily, weekly, or monthly recurring classes. Create your schedule once and it repeats automatically. Students can follow your schedule and get notified when new sessions are added or times change."
                }
            },
            {
                "@type": "Question",
                "name": "How do students find and follow my classes?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Students can follow your schedule and receive email notifications for new classes. Share your schedule link on social media, embed it on your website, or include it in your studio's listing. You can also send newsletters to followers."
                }
            },
            {
                "@type": "Question",
                "name": "Can I charge for classes and manage registrations?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account to sell class spots directly from your schedule. Set per-class pricing, manage registrations, and check in students with QR codes. Event Schedule charges zero platform fees - you only pay Stripe's processing fees."
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
        "name": "Event Schedule for Fitness & Yoga Instructors",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Fitness Class Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your class schedule, sell drop-in passes, and reach students directly with newsletters. Built for yoga teachers, personal trainers, and fitness instructors.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Weekly class schedule newsletters to students",
            "Custom schedule URL for Instagram, website, flyers",
            "Zero-fee drop-in ticketing and class passes",
            "Google Calendar two-way sync",
            "Recurring class schedule management",
            "Multi-instructor team collaboration",
            "Student follower notifications"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "fitness class schedule, yoga class calendar, gym event management, fitness studio scheduling, free fitness scheduling",
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
        /* For-fitness-and-yoga "The Flow" styles. The shared es-* motion system
           lives in marketing.css; this holds the emerald glow gradient, the
           breathing-circle motif, a warm dawn wash blended into the hero
           aurora, a slower breath-cadence hero reveal, and the 1-3 intensity
           dot / class-pass punch-card micro-language. */
        .text-gradient-fitness {
            background: linear-gradient(135deg, #10b981, #14b8a6, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(16, 185, 129, 0.3);
        }
        .dark .text-gradient-fitness {
            background: linear-gradient(135deg, #34d399, #2dd4bf, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(52, 211, 153, 0.3);
        }
        /* Breathing circles - inhale/exhale rhythm */
        @keyframes es-breathe {
            0%, 100% { transform: scale(0.95); opacity: 0.5; }
            50% { transform: scale(1.08); opacity: 0.9; }
        }
        .es-breathe { animation: es-breathe 6s ease-in-out infinite; }

        /* Dawn: a soft sunrise amber wash blended low into the hero aurora
           field only. Static (no drift) so it reads as warm horizon light. */
        .es-dawn {
            width: 900px; height: 440px;
            left: 50%; bottom: -14%; margin-left: -450px;
            background: radial-gradient(ellipse at 50% 100%, rgba(251, 191, 36, 0.34), rgba(251, 191, 36, 0) 62%);
        }
        .dark .es-dawn {
            background: radial-gradient(ellipse at 50% 100%, rgba(251, 191, 36, 0.28), rgba(251, 191, 36, 0) 62%);
        }

        /* Breath cadence: stretch the shared hero reveal to a ~4s inhale so the
           headline and content rise on a slow, even breath. Scoped to this hero
           and still gated behind html.es-anim (so reduced-motion sees none). */
        html.es-anim .es-breath-reveal .es-mask .es-mask-line {
            animation-duration: 1.7s;
            animation-delay: 0.2s;
            animation-timing-function: cubic-bezier(0.37, 0, 0.34, 1);
        }
        html.es-anim .es-breath-reveal .es-mask-2 .es-mask-line { animation-delay: 0.8s; }
        html.es-anim .es-breath-reveal .es-fade-up {
            animation-duration: 1.6s;
            animation-timing-function: cubic-bezier(0.37, 0, 0.34, 1);
        }
        html.es-anim .es-breath-reveal .es-d-1 { animation-delay: 0.35s; }
        html.es-anim .es-breath-reveal .es-d-2 { animation-delay: 1.15s; }
        html.es-anim .es-breath-reveal .es-d-3 { animation-delay: 1.9s; }
        html.es-anim .es-breath-reveal .es-d-4 { animation-delay: 2.55s; }

        /* 1-3 intensity dots (from the week grid) reused in the analytics
           bento, plus a class-pass punch-card row. Emerald throughline. */
        .es-idot { display: inline-block; height: 0.375rem; width: 0.375rem; border-radius: 9999px; }
        .es-idot-on { background-color: #059669; }
        .es-idot-off { background-color: rgba(16, 185, 129, 0.2); }
        .dark .es-idot-on { background-color: #34d399; }
        .dark .es-idot-off { background-color: rgba(52, 211, 153, 0.22); }

        .es-punch { display: inline-block; height: 0.7rem; width: 0.7rem; border-radius: 9999px; }
        .es-punch-used {
            background: radial-gradient(circle at 50% 38%, #34d399, #047857);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.4);
        }
        .es-punch-open {
            border: 1.5px dashed rgba(5, 150, 105, 0.5);
            background-color: rgba(16, 185, 129, 0.05);
        }
        .dark .es-punch-open {
            border-color: rgba(52, 211, 153, 0.45);
            background-color: rgba(52, 211, 153, 0.07);
        }

        /* Related-page card hover recoloured from blue to emerald (custom so it
           never depends on Tailwind hover utilities being in the bundle). */
        .es-rel-card:hover { border-color: #6ee7b7; background-color: #ecfdf5; }
        .es-rel-card:hover .es-rel-title, .es-rel-card:hover .es-rel-arrow { color: #059669; }
        .dark .es-rel-card:hover { border-color: rgba(16, 185, 129, 0.3); background-color: rgba(16, 185, 129, 0.06); }
        .dark .es-rel-card:hover .es-rel-title, .dark .es-rel-card:hover .es-rel-arrow { color: #34d399; }

        @media (prefers-reduced-motion: reduce) {
            .es-breathe { animation: none !important; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: your classes, your students                         -->
    <!-- ============================================================ -->
    <section class="es-hero es-breath-reveal relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(16, 185, 129, 0.32), rgba(16, 185, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 30%, rgba(20, 184, 166, 0.3), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(5, 150, 105, 0.14), rgba(5, 150, 105, 0) 60%);"></div>
            <div class="es-aurora es-dawn"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Breathing circles -->
            <div class="es-breathe absolute right-[15%] top-24 hidden h-32 w-32 rounded-full border-2 border-emerald-500/20 dark:border-emerald-400/20 md:block"></div>
            <div class="es-breathe absolute bottom-32 left-[10%] hidden h-24 w-24 rounded-full border border-teal-500/15 dark:border-teal-400/15 md:block" style="animation-delay: 2.5s;"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Fitness & Yoga Instructors</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your classes. Your students.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-fitness es-gradient-anim">No middleman.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From morning flows to evening power classes. One link for all your sessions. Reach students directly - no algorithm burying your posts.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the week
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                    Create your schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Activity marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Yoga', 'Pilates', 'CrossFit', 'HIIT', 'Meditation', 'Barre', 'Spin', 'Bootcamp'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100/80 px-4 py-1.5 text-xs font-semibold text-emerald-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-emerald-400 to-teal-400"></span>
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
    <!-- 2. Struggle stats                                            -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-16 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 text-center md:grid-cols-3" data-reveal-group="90">
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-emerald-500 dark:text-emerald-400">Most</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">of your social media followers never see your posts about classes</div>
                </div>
                <div data-reveal class="border-gray-200 p-6 dark:border-white/5 md:border-x">
                    <div class="mb-2 text-4xl font-black text-amber-500 dark:text-amber-400">Too many</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">students forget class times without a direct reminder</div>
                </div>
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-sky-500 dark:text-sky-400">$0/mo</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">for scheduling, newsletters, and ticketing. Free forever.</div>
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
                    Everything to fill your <span class="text-gradient-fitness">classes</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Class newsletter (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Class Newsletter
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Send weekly class updates directly to students</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Schedule changes? New class added? Sub instructor this week? Send branded class schedules directly to your students' inbox. No algorithm gatekeeping.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Weekly schedule</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Class changes</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Workshop alerts</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-emerald-300 bg-gradient-to-br from-emerald-100 to-teal-100 p-4 dark:border-emerald-400/30 dark:from-emerald-950 dark:to-teal-950">
                                        <div class="mb-3 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 text-sm font-semibold text-white">YS</div>
                                            <div><div class="text-sm font-semibold text-gray-900 dark:text-white">Your Studio</div><div class="text-xs text-emerald-600 dark:text-emerald-300">This Week's Classes</div></div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="es-ai-field rounded-lg border border-emerald-400/20 bg-gradient-to-br from-emerald-600/30 to-teal-600/30 p-2" style="--i: 0;"><div class="flex items-center justify-between"><div class="text-xs font-medium text-gray-900 dark:text-white">Morning Flow</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Mon/Wed/Fri 7am</div></div></div>
                                            <div class="es-ai-field rounded-lg border border-emerald-400/15 bg-gradient-to-br from-emerald-600/20 to-teal-600/20 p-2" style="--i: 1;"><div class="flex items-center justify-between"><div class="text-xs font-medium text-gray-900 dark:text-white">Power Yoga</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Tue/Thu 6pm</div></div></div>
                                            <div class="es-ai-field rounded-lg border border-emerald-400/15 bg-gradient-to-br from-emerald-600/20 to-teal-600/20 p-2" style="--i: 2;"><div class="flex items-center justify-between"><div class="text-xs font-medium text-gray-900 dark:text-white">HIIT & Stretch</div><div class="text-[10px] text-gray-500 dark:text-gray-400">Sat 9am</div></div></div>
                                        </div>
                                        <div class="mt-3 flex gap-4 text-xs">
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-emerald-500 dark:text-emerald-400">78%</span> opened</div>
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-amber-500 dark:text-amber-400">42%</span> clicked</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Drop-in ticketing -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-green-200 bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700 dark:border-green-800/30 dark:bg-green-900/40 dark:text-green-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Drop-in Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Zero platform fees on class passes</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Sell drop-in passes and class packs. 100% of Stripe payments go to you. No monthly booking fees.</p>
                        <div class="mt-auto rounded-xl border border-green-400/30 bg-green-500/15 p-4" aria-hidden="true">
                            <div class="mb-3 text-center">
                                <div class="text-xs text-green-600 dark:text-green-300">You keep</div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white"><span data-count-to="100">100</span>%</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">of class payments</div>
                            </div>
                            <div class="border-t border-green-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="font-semibold text-green-500 dark:text-green-400">$0</span>
                                </div>
                            </div>
                            <div class="mt-3 border-t border-green-400/20 pt-3">
                                <div class="mb-1.5 flex items-center justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">10-class pass</span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">3 used</span>
                                </div>
                                <div class="flex flex-wrap justify-center gap-1.5">
                                    @for ($p = 0; $p < 10; $p++)
                                        <span class="es-punch {{ $p < 3 ? 'es-punch-used' : 'es-punch-open' }}"></span>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- One link -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            Share Link
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One link for all your classes</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Put it in your Instagram bio, website, or printed on your studio flyers. All your classes in one place.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-sky-400/30 bg-sky-500/20 p-2">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span class="truncate font-mono text-xs text-gray-900 dark:text-white">yourstudio.eventschedule.com</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1 text-center">
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Instagram</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Website</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Flyers</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Recurring classes (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    Recurring Classes
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Set it once, runs every week</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Create your weekly class schedule once. Recurring events auto-populate your calendar so students always know what's coming up.</p>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="w-full max-w-xs rounded-xl border border-blue-400/20 bg-blue-500/10 p-3">
                                    <div class="mb-2 grid grid-cols-7 gap-1 text-center">
                                        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d)
                                            <div class="text-[10px] font-semibold text-blue-600 dark:text-blue-300">{{ $d }}</div>
                                        @endforeach
                                    </div>
                                    <div class="mb-1 grid grid-cols-7 gap-1">
                                        <div class="rounded border border-emerald-400/30 bg-emerald-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">7am Flow</div>
                                        <div class="rounded border border-amber-400/30 bg-amber-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">7am HIIT</div>
                                        <div class="rounded border border-emerald-400/30 bg-emerald-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">7am Flow</div>
                                        <div class="rounded border border-amber-400/30 bg-amber-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">7am HIIT</div>
                                        <div class="rounded border border-emerald-400/30 bg-emerald-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">7am Flow</div>
                                        <div class="rounded border border-teal-400/30 bg-teal-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">9am Power</div>
                                        <div class="rounded border border-blue-400/30 bg-blue-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">10am Gentle</div>
                                    </div>
                                    <div class="grid grid-cols-7 gap-1">
                                        <div class="rounded border border-teal-400/30 bg-teal-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">6pm Power</div>
                                        <div class="rounded border border-cyan-400/30 bg-cyan-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">6pm Pilates</div>
                                        <div class="rounded border border-teal-400/30 bg-teal-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">6pm Power</div>
                                        <div class="rounded border border-cyan-400/30 bg-cyan-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">6pm Pilates</div>
                                        <div class="rounded border border-cyan-400/30 bg-cyan-500/30 p-1 text-center text-[8px] text-gray-900 dark:text-white">6pm Stretch</div>
                                        <div class="rounded border border-gray-400/10 bg-gray-500/20 p-1 text-center text-[8px] text-gray-400">-</div>
                                        <div class="rounded border border-gray-400/10 bg-gray-500/20 p-1 text-center text-[8px] text-gray-400">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multi-instructor teams -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Team
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Multi-instructor teams</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Invite your teaching team. Everyone can add classes and manage the schedule together.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/10" style="--i: 0;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-xs font-semibold text-white">SR</div>
                                <div class="flex-1 text-sm text-gray-900 dark:text-white">Sarah</div>
                                <span class="inline-flex items-center rounded bg-amber-500/20 px-1.5 py-0.5 text-[10px] text-amber-600 dark:text-amber-300">Lead</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-red-500 text-xs font-semibold text-white">JP</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Jamie</div>
                                <span class="inline-flex items-center rounded bg-orange-500/20 px-1.5 py-0.5 text-[10px] text-orange-600 dark:text-orange-300">Instructor</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-yellow-500 to-amber-500 text-xs font-semibold text-white">MK</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Maya</div>
                                <span class="inline-flex items-center rounded bg-yellow-500/20 px-1.5 py-0.5 text-[10px] text-yellow-600 dark:text-yellow-300">Sub</span>
                            </div>
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
                            Calendar Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Google Calendar two-way sync</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Classes, private sessions, workshops - all synced with your personal calendar automatically.</p>
                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="w-20 rounded-xl border border-blue-400/30 bg-blue-500/20 p-3">
                                <div class="mb-1 text-center text-[10px] text-blue-600 dark:text-blue-300">Schedule</div>
                                <div class="space-y-1">
                                    <div class="es-sync-dot h-1.5 rounded bg-emerald-400/50"></div>
                                    <div class="es-sync-dot h-1.5 rounded bg-amber-400/50" style="--i: 1;"></div>
                                </div>
                            </div>
                            <div class="flex flex-col items-center gap-0.5">
                                <svg aria-hidden="true" class="h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                <svg aria-hidden="true" class="h-4 w-4 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            </div>
                            <div class="w-20 rounded-xl border border-gray-300 bg-gray-200 p-3 dark:border-white/20 dark:bg-white/10">
                                <div class="mb-1 text-center text-[10px] text-gray-600 dark:text-gray-300">Google</div>
                                <div class="space-y-1">
                                    <div class="es-sync-dot h-1.5 rounded bg-blue-400/50" style="--i: 2;"></div>
                                    <div class="es-sync-dot h-1.5 rounded bg-green-400/50" style="--i: 3;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Class analytics (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Analytics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">See which classes are most popular</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Track views, clicks, and ticket sales per class to optimize your schedule.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="flex h-20 items-end justify-center gap-1.5">
                                <div class="w-6 rounded-t border border-cyan-400/30 bg-cyan-500/40" style="height: 90%"><div class="mt-1 text-center text-[8px] text-gray-900 dark:text-white">Flow</div></div>
                                <div class="w-6 rounded-t border border-cyan-400/20 bg-cyan-500/30" style="height: 65%"><div class="mt-1 text-center text-[8px] text-gray-900 dark:text-white">HIIT</div></div>
                                <div class="w-6 rounded-t border border-cyan-400/30 bg-cyan-500/40" style="height: 80%"><div class="mt-1 text-center text-[8px] text-gray-900 dark:text-white">Power</div></div>
                                <div class="w-6 rounded-t border border-cyan-400/15 bg-cyan-500/20" style="height: 45%"><div class="mt-1 text-center text-[8px] text-gray-900 dark:text-white">Pilates</div></div>
                                <div class="w-6 rounded-t border border-cyan-400/20 bg-cyan-500/30" style="height: 55%"><div class="mt-1 text-center text-[8px] text-gray-900 dark:text-white">Gentle</div></div>
                            </div>
                            <div class="flex justify-center gap-1.5">
                                @foreach ([3, 2, 3, 1, 1] as $intensity)
                                    <div class="flex w-6 justify-center gap-0.5">
                                        @for ($d = 0; $d < 3; $d++)
                                            <span class="es-idot {{ $d < $intensity ? 'es-idot-on' : 'es-idot-off' }}"></span>
                                        @endfor
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-center text-[10px] text-gray-500 dark:text-gray-400">Popularity and intensity this month</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Your week at a glance (dark band)                         -->
    <!-- ============================================================ -->
    @php
        $week = [
            ['Mon', 'Morning Flow', '7:00 AM', 'border-emerald-400/30 bg-emerald-500/15', 'bg-emerald-400', 'bg-emerald-400/20', 1, '', ''],
            ['Tue', 'Lunch Express', '12:00 PM', 'border-amber-400/30 bg-amber-500/15', 'bg-amber-400', 'bg-amber-400/20', 2, '', ''],
            ['Wed', 'Power Yoga', '6:00 PM', 'border-teal-400/40 bg-teal-500/20 ring-2 ring-teal-400/40', 'bg-teal-400', 'bg-teal-400/20', 3, 'Popular', 'text-teal-300'],
            ['Thu', 'Rest Day', 'Recovery', 'border-white/10 bg-white/[0.04]', '', '', 0, '', ''],
            ['Fri', 'HIIT & Stretch', '5:30 PM', 'border-cyan-400/30 bg-cyan-500/15', 'bg-cyan-400', 'bg-cyan-400/20', 3, '', ''],
            ['Sat', 'Weekend Workshop', '9:00 AM', 'border-blue-400/40 bg-blue-500/20 ring-2 ring-blue-400/40', 'bg-blue-400', 'bg-blue-400/20', 2, 'Popular', 'text-blue-300'],
            ['Sun', 'Gentle Sunday', '10:00 AM', 'border-green-400/30 bg-green-500/15', 'bg-green-400', 'bg-green-400/20', 1, '', ''],
        ];
    @endphp
    <section class="bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(16, 185, 129, 0.26), rgba(16, 185, 129, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(20, 184, 166, 0.22), rgba(20, 184, 166, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-breathe absolute left-[8%] top-16 h-40 w-40 rounded-full border border-emerald-400/15"></div>
                <div class="es-breathe absolute bottom-16 right-[10%] h-28 w-28 rounded-full border border-teal-400/15" style="animation-delay: 3s;"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Your week at a <span class="text-gradient-fitness">glance</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Students see your full week - no more DMs asking "when's your next class?"
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7" data-reveal-group="60">
                    @foreach ($week as [$day, $name, $time, $card, $dotOn, $dotOff, $intensity, $badge, $badgeClass])
                        <div data-reveal class="text-center">
                            <div class="mb-3 text-sm font-semibold text-emerald-300">{{ $day }}</div>
                            <div class="rounded-xl border p-3 {{ $card }}">
                                @if ($intensity === 0)
                                    <div class="text-xs font-semibold text-gray-400">{{ $name }}</div>
                                    <div class="mt-1 text-[10px] text-gray-500">{{ $time }}</div>
                                    <div class="mt-2 h-1 w-full rounded-full bg-white/10"></div>
                                @else
                                    <div class="text-xs font-semibold text-white">{{ $name }}</div>
                                    <div class="mt-1 text-[10px] text-gray-400">{{ $time }}</div>
                                    <div class="mt-2 flex justify-center gap-0.5">
                                        @for ($d = 0; $d < 3; $d++)
                                            <div class="h-1.5 w-1.5 rounded-full {{ $d < $intensity ? $dotOn : $dotOff }}"></div>
                                        @endfor
                                    </div>
                                    @if ($badge)
                                        <div class="mt-1 text-[9px] font-medium {{ $badgeClass }}">{{ $badge }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
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
                    Perfect for all types of <span class="text-gradient-fitness">fitness professionals</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether you teach yoga or coach CrossFit, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Yoga Teachers"
                    description="Share your flow classes, workshops, and retreats with your students. Build a dedicated community around your practice."
                    icon-color="emerald"
                    blog-slug="for-yoga-teachers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Personal Trainers"
                    description="Show your availability for one-on-one sessions and group training. Let clients book directly from your schedule."
                    icon-color="teal"
                    blog-slug="for-personal-trainers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Pilates Instructors"
                    description="Manage your mat and reformer classes in one place. Students always know your latest schedule."
                    icon-color="green"
                    blog-slug="for-pilates-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="CrossFit Coaches"
                    description="Post your WOD schedule and special events. Keep your box members informed and engaged."
                    icon-color="amber"
                    blog-slug="for-crossfit-coaches"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Group Fitness Instructors"
                    description="Share your spin, Zumba, and bootcamp schedule across multiple gyms and studios."
                    icon-color="cyan"
                    blog-slug="for-group-fitness-instructors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Meditation Guides"
                    description="List your guided sessions, sound baths, and mindfulness workshops. Build a calm, connected community."
                    icon-color="violet"
                    blog-slug="for-meditation-guides"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
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
            ['1', 'Add your classes', 'Import from Google Calendar or add classes manually. Set up recurring schedules and drop-in ticketing.'],
            ['2', 'Share your link', 'Add it to your Instagram bio, website, studio flyers, or anywhere students find you.'],
            ['3', 'Grow your community', 'Students follow your schedule and get notified about new classes. Build your audience on your terms.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Three steps to a <span class="text-gradient-fitness">full class</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-xl font-bold text-white shadow-lg shadow-emerald-600/25">
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
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
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
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google Calendar" :url="marketing_url('/features/calendar-sync')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium text-emerald-600 hover:underline dark:text-emerald-400">
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
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-workshop-instructors', 'Workshop Instructors'], ['/for-dance-groups', 'Dance Groups'], ['/for-online-classes', 'Online Classes'], ['/for-community-centers', 'Community Centers']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-rel-card group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="es-rel-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-rel-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium text-emerald-600 hover:underline dark:text-emerald-400">
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
                    Frequently asked <span class="text-gradient-fitness">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything fitness and yoga instructors ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for fitness and yoga instructors?', 'Yes. Event Schedule is free forever for sharing your class schedule, building a student following, and syncing with Google Calendar. Paid class registration and newsletters are available on the Pro plan, with zero platform fees.'],
                    ['Can I schedule recurring weekly classes?', 'Yes. Set up daily, weekly, or monthly recurring classes. Create your schedule once and it repeats automatically. Students can follow your schedule and get notified when new sessions are added or times change.'],
                    ['How do students find and follow my classes?', 'Students can follow your schedule and receive email notifications for new classes. Share your schedule link on social media, embed it on your website, or include it in your studio\'s listing. You can also send newsletters to followers.'],
                    ['Can I charge for classes and manage registrations?', 'Yes. Connect your Stripe account to sell class spots directly from your schedule. Set per-class pricing, manage registrations, and check in students with QR codes. Event Schedule charges zero platform fees - you only pay Stripe\'s processing fees.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-emerald-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.32), rgba(16, 185, 129, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-breathe absolute left-1/2 top-1/2 h-56 w-56 -translate-x-1/2 -translate-y-1/2 rounded-full border border-emerald-400/15"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your classes. Your students. <span class="text-gradient-fitness">No middleman.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stop posting into the void. Fill your classes. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-studio" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
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
