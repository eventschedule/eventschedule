<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Online Classes | Virtual Teaching</x-slot>
    <x-slot name="description">Schedule and sell online classes with built-in registration, recurring sessions, and student email notifications. Works with any platform. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Online Classes</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Online Classes",
        "description": "Schedule and sell online classes with built-in registration, recurring sessions, student email notifications, and payment processing. Works with Zoom, Google Meet, and any platform. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Online Instructors"
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
                "name": "What video platforms can I use to teach?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, YouTube Live, and any other platform. Event Schedule is platform-agnostic - just paste your link and students join from your class schedule."
                }
            },
            {
                "@type": "Question",
                "name": "Can I charge for online classes?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set per-class pricing with Stripe. You keep 100% of the revenue - Event Schedule charges zero platform fees at any plan level. Stripe charges its standard processing fee (2.9% + $0.30)."
                }
            },
            {
                "@type": "Question",
                "name": "Can I schedule recurring weekly classes?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up daily, weekly, or monthly recurring classes. Create your schedule once and it repeats automatically. Students can follow your schedule and get notified when new sessions are added."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free for teaching online classes?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The free plan includes unlimited classes, student email notifications, recurring schedules, and follower features. There are zero platform fees on payments at any plan level. You only pay Stripe's standard processing fee if you charge for classes."
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
        "name": "Event Schedule for Online Classes",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Online Class Scheduling Software",
        "operatingSystem": "Web",
        "description": "Schedule and sell online classes with built-in registration, recurring sessions, student email notifications, and payment processing. Works with Zoom, Google Meet, and any platform.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Email notifications to registered students",
            "One link for your full class schedule",
            "Zero-fee payments for paid classes",
            "Google Calendar two-way sync",
            "Works with Zoom, Google Meet, YouTube Live",
            "Recurring class scheduling",
            "Student registration management",
            "Follower notifications for new classes",
            "Multi-level class organization"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "online class scheduling, virtual class platform, sell online classes, online teaching",
        "screenshot": "{{ asset('images/social/for-online-classes.png') }}",
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
        /* For-online-classes "The Studio" styles. The shared es-* motion system
           lives in marketing.css; this holds the teaching glow gradient, the
           drifting class-reminder card, and the curriculum progress rail motif. */
        .text-gradient-classes {
            background: linear-gradient(135deg, #059669, #10b981, #0d9488);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(5, 150, 105, 0.3);
        }
        .dark .text-gradient-classes {
            background: linear-gradient(135deg, #34d399, #6ee7b7, #2dd4bf);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(52, 211, 153, 0.3);
        }
        @keyframes es-class-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-class-float { animation: es-class-float 6s ease-in-out infinite; }

        /* Curriculum progress rail: lesson nodes light up in sequence,
           like watching a course advance session by session. */
        .es-lesson { display: flex; align-items: center; }
        .es-lesson-node {
            flex: 0 0 auto;
            width: 7px; height: 7px; border-radius: 9999px;
            background: rgba(16, 185, 129, 0.85);
            animation: es-lesson-pulse var(--ln-dur, 2.4s) ease-in-out infinite;
            animation-delay: var(--ln-delay, 0s);
        }
        .es-lesson-link {
            flex: 1 1 auto; min-width: 8px; height: 2px;
            background: linear-gradient(to right, rgba(16, 185, 129, 0.45), rgba(13, 148, 136, 0.45));
        }
        @keyframes es-lesson-pulse {
            0%, 100% { opacity: 0.25; transform: scale(0.8); box-shadow: 0 0 0 rgba(16, 185, 129, 0); }
            50% { opacity: 1; transform: scale(1.35); box-shadow: 0 0 10px rgba(16, 185, 129, 0.7); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-class-float, .es-lesson-node { animation: none !important; }
            .es-lesson-node { opacity: 0.6; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: teach on your own schedule                          -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(5, 150, 105, 0.3), rgba(5, 150, 105, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(13, 148, 136, 0.3), rgba(13, 148, 136, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.14), rgba(16, 185, 129, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Curriculum progress rail along the bottom edge -->
            <div class="es-lesson absolute bottom-0 left-0 right-0 hidden h-20 items-center px-8 opacity-40 md:flex" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                @for ($i = 0; $i < 28; $i++)
                    @php $dur = 2 + ($i % 5) * 0.25; $delay = ($i % 9) * 0.16; @endphp
                    <span class="es-lesson-node" style="--ln-dur: {{ $dur }}s; --ln-delay: {{ $delay }}s;"></span>
                    @if ($i < 27)
                        <span class="es-lesson-link"></span>
                    @endif
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Instructors, Coaches & Educators</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Teach online classes your way.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-classes">No platform fees.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-4 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From yoga sessions to coding bootcamps. Schedule recurring classes, manage enrollments, and get paid directly. Your students, your schedule.
            </p>
            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-base text-gray-400 dark:text-gray-500">
                The online class scheduling platform with built-in student registration, recurring session management, email notifications, and payment processing for instructors and educators.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#journey" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it grows
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                    Create your class schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Class-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Yoga & Fitness', 'Cooking Classes', 'Art & Music', 'Language Courses', 'Coding Bootcamps', 'Tutoring', 'Masterclasses', 'Kids Classes'] as $tag)
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
    <!-- 2. Stats                                                     -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-16 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 text-center md:grid-cols-3" data-reveal-group="90">
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-emerald-500 dark:text-emerald-400"><span data-count-to="62">62</span>%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">of adults have taken an online class</div>
                </div>
                <div data-reveal class="border-gray-200 p-6 dark:border-white/5 md:border-x">
                    <div class="mb-2 text-4xl font-black text-teal-500 dark:text-teal-400">2x</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">higher retention with recurring class schedules</div>
                </div>
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-green-500 dark:text-green-400">$0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">platform fees on class payments</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Bento features                                            -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Everything you need to teach <span class="text-gradient-classes">online classes</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Email students (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Email Students
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Email students before each class</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Send class reminders, share materials and prep instructions, and follow up with recordings. Your students, your inbox.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Class reminders</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Material sharing</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Recording links</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-emerald-300 bg-gradient-to-br from-emerald-100 to-teal-100 p-4 dark:border-emerald-400/30 dark:from-emerald-950 dark:to-teal-950">
                                        <div class="mb-3 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 text-sm font-semibold text-white">YS</div>
                                            <div><div class="text-sm font-semibold text-gray-900 dark:text-white">Yoga Studio</div><div class="text-xs text-emerald-600 dark:text-emerald-300">Class reminder</div></div>
                                        </div>
                                        <div class="rounded-xl border border-emerald-400/20 bg-gradient-to-br from-emerald-600/30 to-teal-600/30 p-3 text-center">
                                            <div class="mb-1 text-xs font-semibold text-gray-900 dark:text-white">TOMORROW AT 7 AM</div>
                                            <div class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Morning Flow Yoga</div>
                                            <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Join via Zoom - $12</div>
                                        </div>
                                        <div class="mt-3 flex gap-4 text-xs">
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-emerald-500 dark:text-emerald-400">74%</span> opened</div>
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-amber-500 dark:text-amber-400">51%</span> clicked</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Sell class spots -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-green-200 bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700 dark:border-green-800/30 dark:bg-green-900/40 dark:text-green-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Class Payments
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sell class spots with zero fees</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Per-class pricing or free sessions. 100% of Stripe payments go to you. See all <a href="{{ marketing_url('/features/ticketing') }}" class="text-green-600 underline hover:no-underline dark:text-green-400">ticketing features</a>.</p>
                        <div class="mt-auto rounded-xl border border-green-400/30 bg-green-500/15 p-4" aria-hidden="true">
                            <div class="mb-3 space-y-2">
                                <div class="es-ai-field flex items-center justify-between rounded-lg bg-green-400/20 p-2" style="--i: 0;"><span class="text-xs font-medium text-gray-900 dark:text-white">Drop-in Class</span><span class="text-xs font-semibold text-green-600 dark:text-green-400">$15</span></div>
                                <div class="es-ai-field flex items-center justify-between rounded-lg bg-emerald-400/20 p-2" style="--i: 1;"><span class="text-xs font-medium text-gray-900 dark:text-white">Free Trial</span><span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Free</span></div>
                            </div>
                            <div class="border-t border-green-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="font-semibold text-green-600 dark:text-green-400">$0</span>
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
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Share on your website, social profiles, or email signature. Students see your full schedule at a glance.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-sky-400/30 bg-sky-500/20 p-2">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span class="truncate font-mono text-xs text-gray-900 dark:text-white">yourclasses.eventschedule.com</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1 text-center">
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Website</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Instagram</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Email</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Works with any video platform (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    Any Platform
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Teach on any video platform</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Zoom, Google Meet, YouTube Live, or your own streaming setup. Add your class link, students join from your schedule. Learn more about <a href="{{ marketing_url('/features/online-events') }}" class="text-teal-600 underline hover:no-underline dark:text-teal-400">online event features</a>.</p>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="flex items-center gap-4">
                                    <div class="w-36 rounded-xl border border-teal-400/30 bg-teal-500/15 p-4">
                                        <div class="mb-2 text-center text-xs font-semibold text-teal-600 dark:text-teal-300">Your Schedule</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-gray-300 dark:bg-white/20"></div>
                                            <div class="h-2 w-3/4 rounded bg-teal-400/40"></div>
                                        </div>
                                        <div class="mt-3 rounded-lg border border-teal-400/30 bg-teal-400/20 p-2">
                                            <div class="text-center text-[10px] font-medium text-teal-800 dark:text-white">Morning Yoga</div>
                                            <div class="mt-0.5 text-center text-[8px] text-teal-700 dark:text-teal-300">Mon 7:00 AM</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                        <span class="text-[10px] text-teal-500 dark:text-teal-400">class link</span>
                                    </div>
                                    <div class="w-36 rounded-xl border border-gray-300 bg-gray-200 p-4 dark:border-white/20 dark:bg-white/10">
                                        <div class="mb-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">Platform</div>
                                        <div class="space-y-2 text-center">
                                            <div class="es-ai-field rounded bg-blue-400/20 p-1.5 text-[10px] text-blue-700 dark:text-blue-300" style="--i: 0;">Zoom</div>
                                            <div class="es-ai-field rounded bg-green-400/20 p-1.5 text-[10px] text-green-700 dark:text-green-300" style="--i: 1;">Google Meet</div>
                                            <div class="es-ai-field rounded bg-red-400/20 p-1.5 text-[10px] text-red-700 dark:text-red-300" style="--i: 2;">YouTube Live</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Recurring classes -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Recurring
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Schedule recurring classes automatically</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Weekly yoga, biweekly workshops, monthly masterclasses. Set it once and let students follow along.</p>
                        <div class="mt-auto rounded-xl border border-amber-400/30 bg-amber-500/15 p-3" aria-hidden="true">
                            <div class="space-y-1.5">
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/20 p-1.5" style="--i: 0;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] font-medium text-gray-900 dark:text-white">Mon - Morning Flow</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 1;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Wed - Power Vinyasa</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 2;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Fri - Restorative Yoga</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 3;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Sat - Weekend Workshop</span></div>
                            </div>
                            <div class="mt-2 text-center text-[10px] text-amber-600 dark:text-amber-300">Repeats weekly</div>
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
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sync classes with Google Calendar</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync keeps your class schedule, private lessons, and prep time all in one place.</p>
                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="w-20 rounded-xl border border-blue-400/30 bg-blue-500/15 p-3">
                                <div class="mb-1 text-center text-[10px] text-blue-600 dark:text-blue-300">Schedule</div>
                                <div class="space-y-1">
                                    <div class="es-sync-dot h-1.5 rounded bg-emerald-400/60"></div>
                                    <div class="es-sync-dot h-1.5 rounded bg-amber-400/60" style="--i: 1;"></div>
                                </div>
                            </div>
                            <div class="flex flex-col items-center gap-0.5">
                                <svg aria-hidden="true" class="h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                <svg aria-hidden="true" class="h-4 w-4 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                            </div>
                            <div class="w-20 rounded-xl border border-gray-300 bg-gray-200 p-3 dark:border-white/20 dark:bg-white/10">
                                <div class="mb-1 text-center text-[10px] text-gray-600 dark:text-gray-300">Google</div>
                                <div class="space-y-1">
                                    <div class="es-sync-dot h-1.5 rounded bg-blue-400/60" style="--i: 2;"></div>
                                    <div class="es-sync-dot h-1.5 rounded bg-green-400/60" style="--i: 3;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Students follow (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Followers
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Students follow your class schedule</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Students get notified when you add new classes or update your schedule.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="flex items-center justify-center">
                                <div class="flex -space-x-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-emerald-500 to-teal-500 text-xs text-white dark:border-[#0a0a0f]">A</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-teal-500 to-cyan-500 text-xs text-white dark:border-[#0a0a0f]">B</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-cyan-500 to-blue-500 text-xs text-white dark:border-[#0a0a0f]">C</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gray-300 text-xs text-gray-600 dark:border-[#0a0a0f] dark:bg-white/20 dark:text-white">+340</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center text-xs text-cyan-600 dark:text-cyan-300">343 students following your schedule</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Journey (dark band)                                       -->
    <!-- ============================================================ -->
    @php
        $journey = [
            ['First online class', 'Share a link and start teaching. Free registration gets students in the door.', 'border-emerald-500/20 bg-emerald-500/10', 'text-emerald-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Weekly class schedule', 'Set up recurring classes. Students follow your schedule and sign up each week.', 'border-teal-500/20 bg-teal-500/10', 'text-teal-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />'],
            ['Paid classes', 'Start charging for your expertise. Sell individual class spots with zero platform fees.', 'border-green-500/20 bg-green-500/10', 'text-green-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Multi-level curriculum', 'Organize classes into beginner, intermediate, and advanced groups so students find the right level.', 'border-cyan-500/20 bg-cyan-500/10', 'text-cyan-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />'],
            ['Student community', 'Build a following. Students subscribe, get notified, and keep coming back week after week.', 'border-sky-500/20 bg-sky-500/10', 'text-sky-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'],
            ['Full teaching studio', 'A professional schedule with past and upcoming classes. Your online studio that students can browse.', 'border-emerald-500/20 bg-emerald-500/10', 'text-emerald-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
        ];
    @endphp
    <section id="journey" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(5, 150, 105, 0.26), rgba(5, 150, 105, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(13, 148, 136, 0.2), rgba(13, 148, 136, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-lesson absolute bottom-0 left-0 right-0 flex h-16 items-center px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                    @for ($i = 0; $i < 28; $i++)
                        @php $dur = 2 + ($i % 5) * 0.25; $delay = ($i % 9) * 0.16; @endphp
                        <span class="es-lesson-node" style="--ln-dur: {{ $dur }}s; --ln-delay: {{ $delay }}s;"></span>
                        @if ($i < 27)
                            <span class="es-lesson-link"></span>
                        @endif
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        From your first class to a <span class="text-gradient-classes">full teaching business</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Event Schedule grows with your online teaching practice.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($journey as [$title, $desc, $iconBg, $iconText, $icon])
                        <div data-reveal class="rounded-2xl border border-white/10 bg-white/[0.04] p-6 transition-all hover:-translate-y-1 hover:bg-white/[0.07]">
                            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl border {{ $iconBg }}">
                                <svg aria-hidden="true" class="h-6 w-6 {{ $iconText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $icon !!}</svg>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for every type of <span class="text-gradient-classes">online class</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether it's a fitness class or a coding bootcamp, Event Schedule works for instructors of all kinds. Also see Event Schedule for <a href="{{ marketing_url('/for-webinars') }}" class="text-gray-600 underline hover:no-underline dark:text-gray-300">Webinars</a> and <a href="{{ marketing_url('/for-virtual-conferences') }}" class="text-gray-600 underline hover:no-underline dark:text-gray-300">Virtual Conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Yoga & Fitness -->
                <x-sub-audience-card
                    name="Yoga & Fitness Instructors"
                    description="Schedule daily or weekly classes, manage drop-ins, and build a community of regular students."
                    icon-color="cyan"
                    blog-slug="for-yoga-fitness-instructors-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Cooking Classes -->
                <x-sub-audience-card
                    name="Cooking Instructors"
                    description="Share ingredient lists beforehand, teach live, and follow up with recipes. Perfect for interactive cooking sessions."
                    icon-color="teal"
                    blog-slug="for-cooking-instructors-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Art & Music Teachers -->
                <x-sub-audience-card
                    name="Art & Music Teachers"
                    description="Teach drawing, painting, guitar, piano, or any creative skill with scheduled live sessions."
                    icon-color="sky"
                    blog-slug="for-art-music-teachers-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Language Tutors -->
                <x-sub-audience-card
                    name="Language Tutors"
                    description="Run group language lessons or private tutoring sessions with recurring weekly schedules."
                    icon-color="blue"
                    blog-slug="for-language-tutors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Coding & Tech -->
                <x-sub-audience-card
                    name="Coding & Tech Educators"
                    description="Bootcamps, workshops, and study groups. Organize classes by skill level and let students find their track."
                    icon-color="amber"
                    blog-slug="for-coding-tech-educators"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Business & Professional -->
                <x-sub-audience-card
                    name="Business Coaches"
                    description="Host coaching sessions, masterminds, and professional development classes with paid registration."
                    icon-color="emerald"
                    blog-slug="for-business-coaches-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
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
            ['1', 'Create your class', 'Add the topic, date, and video link. Set up free or paid registration.'],
            ['2', 'Share your schedule', 'Students register in one click. They get reminders before each class.'],
            ['3', 'Teach', 'Focus on your students. No platform fees, no middleman.'],
        ];
    @endphp
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Three steps to your <span class="text-gradient-classes">first online class</span>
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
    <section class="border-t border-gray-200 bg-gray-50 py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Online Events" description="Host virtual events with any streaming platform" :url="marketing_url('/features/online-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
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
    <!-- 8. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-workshop-instructors', 'Workshop Instructors'], ['/for-webinars', 'Webinars'], ['/for-fitness-and-yoga', 'Fitness & Yoga'], ['/for-virtual-conferences', 'Virtual Conferences']] as [$relHref, $relName])
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
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-classes">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything online instructors ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What video platforms can I use to teach?', 'Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, YouTube Live, and any other platform. Event Schedule is platform-agnostic - just paste your link and students join from your class schedule.'],
                    ['Can I charge for online classes?', 'Yes. Set per-class pricing with Stripe. You keep 100% of the revenue - Event Schedule charges zero platform fees at any plan level. Stripe charges its standard processing fee (2.9% + $0.30).'],
                    ['Can I schedule recurring weekly classes?', 'Yes. Set up daily, weekly, or monthly recurring classes. Create your schedule once and it repeats automatically. Students can follow your schedule and get notified when new sessions are added.'],
                    ['Is Event Schedule free for teaching online classes?', 'Yes. The free plan includes unlimited classes, student email notifications, recurring schedules, and follower features. There are zero platform fees on payments at any plan level. You only pay Stripe\'s standard processing fee if you charge for classes.'],
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
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(5, 150, 105, 0.3), rgba(5, 150, 105, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-lesson absolute bottom-0 left-0 right-0 flex h-14 items-center px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                        @for ($i = 0; $i < 24; $i++)
                            @php $dur = 2 + ($i % 5) * 0.25; $delay = ($i % 9) * 0.16; @endphp
                            <span class="es-lesson-node" style="--ln-dur: {{ $dur }}s; --ln-delay: {{ $delay }}s;"></span>
                            @if ($i < 23)
                                <span class="es-lesson-link"></span>
                            @endif
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your classes. Your students. <span class="text-gradient-classes">No middleman.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stop paying platform fees. Start teaching online. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-classes" autocomplete="off" spellcheck="false" maxlength="30"
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
