<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Webinars | Hosting Software</x-slot>
    <x-slot name="description">Host webinars with built-in registration, ticketing, email notifications, and streaming link integration. Works with Zoom, Google Meet, and any platform. Zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Webinars</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Webinars",
        "description": "Host webinars with built-in registration, ticketing, email notifications, and streaming link integration. Works with Zoom, Google Meet, and any platform. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Webinar Hosts"
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
                "name": "What video platforms does Event Schedule work with?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, Webex, YouTube Live, and custom solutions. Event Schedule is platform-agnostic - just paste your link and attendees join from your schedule."
                }
            },
            {
                "@type": "Question",
                "name": "Can I charge for webinars?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up paid registration with Stripe. You keep 100% of the ticket revenue - Event Schedule charges zero platform fees. Stripe charges its standard processing fee (2.9% + $0.30)."
                }
            },
            {
                "@type": "Question",
                "name": "Can I schedule a recurring webinar series?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up weekly, biweekly, or monthly webinar series with a single recurring event. Attendees can follow your schedule and get notified when new sessions are added."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free for hosting webinars?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The free plan includes unlimited events, attendee email notifications, and registration features. There are zero platform fees on ticket sales at any plan level. You only pay Stripe's standard processing fee if you sell tickets."
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
        "name": "Event Schedule for Webinars",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Webinar Scheduling Software",
        "operatingSystem": "Web",
        "description": "Host webinars with built-in registration, ticketing, email notifications, and streaming link integration. Works with Zoom, Google Meet, and any platform.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Email notifications to registered attendees",
            "One registration link for all webinars",
            "Zero-fee ticket sales for paid webinars",
            "Google Calendar two-way sync",
            "Works with Zoom, Google Meet, Microsoft Teams",
            "Recurring webinar series scheduling",
            "Attendee registration management",
            "Team collaboration for multiple hosts",
            "Multi-track webinar programs"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "webinar hosting, webinar scheduling, webinar registration, paid webinars",
        "screenshot": "{{ asset('images/social/for-webinars.png') }}",
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
        /* For-webinars "On Air" styles. The shared es-* motion system lives in
           marketing.css; this holds the teal glow gradient, the drifting
           registration card, and the broadcast-signal motif. */
        .text-gradient-webinar {
            background: linear-gradient(135deg, #0d9488, #06b6d4, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(13, 148, 136, 0.3);
        }
        .dark .text-gradient-webinar {
            background: linear-gradient(135deg, #2dd4bf, #22d3ee, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(45, 212, 191, 0.3);
        }
        @keyframes es-reg-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-reg-float { animation: es-reg-float 6s ease-in-out infinite; }

        /* Broadcast signal - concentric rings going live */
        .es-signal span {
            position: absolute;
            left: 50%;
            top: 50%;
            height: 20px;
            width: 20px;
            margin: -10px 0 0 -10px;
            border-radius: 9999px;
            border: 1.5px solid rgba(45, 212, 191, 0.4);
            transform: scale(0.2);
            opacity: 0;
            animation: es-signal var(--sig-dur, 4s) ease-out infinite;
            animation-delay: var(--sig-delay, 0s);
        }
        @keyframes es-signal {
            0% { transform: scale(0.2); opacity: 0.6; }
            100% { transform: scale(6); opacity: 0; }
        }

        /* Broadcast-studio hardware layer: a slow-pulsing red ON-AIR tally light,
           a mic-check VU strip (one amber peak), a control-room monitor frame,
           and steel-slate eyebrow / episode chips, plus teal link / hover / FAQ
           states. */
        .webinar-tally {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 9999px;
            background: #ef4444;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.55);
            animation: es-tally 2.6s ease-in-out infinite;
        }
        @keyframes es-tally {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.55); opacity: 1; }
            50% { box-shadow: 0 0 0 4px rgba(239, 68, 68, 0); opacity: 0.6; }
        }
        .webinar-onair {
            top: 1.25rem;
            right: 1.25rem;
            border: 1px solid rgba(239, 68, 68, 0.3);
            background: rgba(239, 68, 68, 0.1);
        }
        .webinar-vu {
            display: inline-flex;
            align-items: flex-end;
            gap: 2px;
            height: 14px;
        }
        .webinar-vu i {
            display: block;
            width: 3px;
            border-radius: 1px;
            background: #0d9488;
            transform-origin: bottom;
            animation: es-vu 1.1s ease-in-out infinite;
        }
        .dark .webinar-vu i { background: #2dd4bf; }
        .webinar-vu i:nth-child(1) { height: 55%; animation-delay: 0s; }
        .webinar-vu i:nth-child(2) { height: 90%; animation-delay: 0.15s; }
        .webinar-vu i:nth-child(3) { height: 40%; animation-delay: 0.3s; background: #f59e0b; }
        .dark .webinar-vu i:nth-child(3) { background: #fbbf24; }
        .webinar-vu i:nth-child(4) { height: 72%; animation-delay: 0.45s; }
        .webinar-vu i:nth-child(5) { height: 50%; animation-delay: 0.6s; }
        @keyframes es-vu {
            0%, 100% { transform: scaleY(0.4); }
            50% { transform: scaleY(1); }
        }
        .webinar-monitor {
            position: relative;
            border-radius: 0.9rem;
            padding: 0.4rem;
            background: linear-gradient(180deg, #e2e8f0, #cbd5e1);
            border: 1px solid rgba(100, 116, 139, 0.35);
            box-shadow: 0 8px 20px -8px rgba(15, 23, 42, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }
        .dark .webinar-monitor {
            background: linear-gradient(180deg, #2d2d30, #1e1e1e);
            border-color: rgba(148, 163, 184, 0.2);
            box-shadow: 0 8px 20px -8px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }
        .webinar-monitor-stand {
            width: 34%;
            height: 11px;
            margin: 0 auto;
            background: linear-gradient(180deg, #cbd5e1, #94a3b8);
            border-radius: 0 0 6px 6px;
        }
        .dark .webinar-monitor-stand { background: linear-gradient(180deg, #3a3a3d, #232326); }
        .webinar-monitor-base {
            width: 52%;
            height: 5px;
            margin: 3px auto 0;
            border-radius: 9999px;
            background: #94a3b8;
        }
        .dark .webinar-monitor-base { background: #3a3a3d; }
        .webinar-eyebrow {
            border: 1px solid rgba(100, 116, 139, 0.3);
            box-shadow: inset 0 0 8px rgba(100, 116, 139, 0.08);
        }
        .dark .webinar-eyebrow {
            border-color: rgba(148, 163, 184, 0.22);
            box-shadow: inset 0 0 8px rgba(148, 163, 184, 0.06);
        }
        .webinar-ep-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 4px;
            padding: 0 0.3rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.05em;
            background: rgba(100, 116, 139, 0.16);
            color: #475569;
            border: 1px solid rgba(100, 116, 139, 0.28);
        }
        .dark .webinar-ep-chip {
            background: rgba(148, 163, 184, 0.16);
            color: #cbd5e1;
            border-color: rgba(148, 163, 184, 0.28);
        }
        .webinar-link-accent { color: #0d9488; }
        .dark .webinar-link-accent { color: #2dd4bf; }
        .webinar-related-card:hover {
            border-color: rgba(13, 148, 136, 0.4);
            background-color: rgba(13, 148, 136, 0.06);
        }
        .dark .webinar-related-card:hover {
            border-color: rgba(45, 212, 191, 0.28);
            background-color: rgba(45, 212, 191, 0.06);
        }
        .group:hover .webinar-related-title { color: #0d9488; }
        .dark .group:hover .webinar-related-title { color: #2dd4bf; }
        .group:hover .webinar-related-arrow { color: #0d9488; }
        .dark .group:hover .webinar-related-arrow { color: #2dd4bf; }
        .webinar-faq { transition: border-color 0.2s ease; }
        .webinar-faq:hover { border-color: rgba(13, 148, 136, 0.35); }
        .dark .webinar-faq:hover { border-color: rgba(45, 212, 191, 0.28); }
        @media (prefers-reduced-motion: reduce) {
            .es-reg-float, .es-signal span, .webinar-tally, .webinar-vu i { animation: none !important; }
            .es-signal span { opacity: 0; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: host webinars on your terms                         -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(13, 148, 136, 0.3), rgba(13, 148, 136, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(6, 182, 212, 0.3), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.14), rgba(14, 165, 233, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Broadcast signal, centered behind the headline -->
            <div class="es-signal absolute left-1/2 top-1/2 h-0 w-0 -translate-x-1/2 -translate-y-1/2">
                <span style="--sig-dur: 4s; --sig-delay: 0s;"></span>
                <span style="--sig-dur: 4s; --sig-delay: 1.3s;"></span>
                <span style="--sig-dur: 4s; --sig-delay: 2.6s;"></span>
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 webinar-eyebrow mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Webinar Hosts & Organizers</span>
                <span class="inline-flex items-center gap-1.5" aria-hidden="true">
                    <span class="webinar-tally"></span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-red-500 dark:text-red-400">On Air</span>
                </span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Host webinars on your terms.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-webinar">No platform lock-in.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-4 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From product demos to company all-hands. One link for registration. Reach attendees directly - no middleman taking a cut.
            </p>
            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-base text-gray-400 dark:text-gray-500">
                The webinar scheduling platform with built-in registration, attendee email notifications, paid ticketing, and Google Calendar sync for educators, marketers, and teams.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#journey" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it scales
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-teal-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
                    Create your webinar schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Webinar-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Product Demos', 'Training Sessions', 'Workshops', 'Panel Discussions', 'All-Hands', 'Lectures', 'Q&A Sessions', 'Onboarding'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-100/80 px-4 py-1.5 text-xs font-semibold text-teal-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-teal-400 to-cyan-400"></span>
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
                    <div class="mb-2 text-4xl font-black text-teal-500 dark:text-teal-400">~<span data-count-to="40">40</span>%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">average webinar attendance rate</div>
                </div>
                <div data-reveal class="border-gray-200 p-6 dark:border-white/5 md:border-x">
                    <div class="mb-2 text-4xl font-black text-amber-500 dark:text-amber-400">3-5x</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">higher engagement via email vs. social posts</div>
                </div>
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-sky-500 dark:text-sky-400">$0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">platform fees on paid webinars</div>
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
                    Everything you need to host <span class="text-gradient-webinar">webinars</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Email attendees (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Email Attendees
                                    <span class="webinar-vu ml-1" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Email webinar attendees directly</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Notify registrants before you go live. Send reminders, share materials, and follow up after sessions. Your audience, your inbox.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Webinar reminders</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Series announcements</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Follow-up emails</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-teal-300 bg-gradient-to-br from-teal-100 to-cyan-100 p-4 dark:border-teal-400/30 dark:from-teal-950 dark:to-cyan-950">
                                        <div class="mb-3 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 text-sm font-semibold text-white">WH</div>
                                            <div><div class="text-sm font-semibold text-gray-900 dark:text-white">Webinar Host</div><div class="text-xs text-teal-600 dark:text-teal-300">Live session reminder</div></div>
                                        </div>
                                        <div class="rounded-xl border border-teal-400/20 bg-gradient-to-br from-teal-600/30 to-cyan-600/30 p-3 text-center">
                                            <div class="mb-1 text-xs font-semibold text-gray-900 dark:text-white">TOMORROW AT 2 PM</div>
                                            <div class="text-sm font-bold text-teal-700 dark:text-teal-300">Product Deep Dive</div>
                                            <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Join via Zoom - Free</div>
                                        </div>
                                        <div class="mt-3 flex gap-4 text-xs">
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-emerald-500 dark:text-emerald-400">68%</span> opened</div>
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

                <!-- Zero platform fees -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Zero platform fees on paid webinars</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Charge for premium content. 100% of Stripe payments go to you. See all <a href="{{ marketing_url('/features/ticketing') }}" class="text-emerald-600 underline hover:no-underline dark:text-emerald-400">ticketing features</a>.</p>
                        <div class="mt-auto rounded-xl border border-emerald-400/30 bg-emerald-500/15 p-4" aria-hidden="true">
                            <div class="mb-3 text-center">
                                <div class="text-xs text-emerald-600 dark:text-emerald-300">You keep</div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white"><span data-count-to="100">100</span>%</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">of ticket sales</div>
                            </div>
                            <div class="border-t border-emerald-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="font-semibold text-emerald-500 dark:text-emerald-400">$0</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- One registration link -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            Share Link
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One link for all your webinars</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Put it on your website, email signature, or social profiles. All your sessions in one place.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-sky-400/30 bg-sky-500/20 p-2">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span class="truncate font-mono text-xs text-gray-900 dark:text-white">yourwebinars.eventschedule.com</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1 text-center">
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Website</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Email</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Social</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Works with any platform (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    Any Platform
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Works with Zoom, Meet, Teams, and more</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Add your streaming link to any webinar. Attendees register on your schedule and join via whatever platform you use. Learn more about <a href="{{ marketing_url('/features/online-events') }}" class="text-blue-600 underline hover:no-underline dark:text-blue-400">online event features</a>.</p>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="flex items-center gap-4">
                                    <div class="w-36 rounded-xl border border-blue-400/30 bg-blue-500/15 p-4">
                                        <div class="mb-2 text-center text-xs font-semibold text-blue-600 dark:text-blue-300">Your Schedule</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-gray-300 dark:bg-white/20"></div>
                                            <div class="h-2 w-3/4 rounded bg-blue-400/40"></div>
                                        </div>
                                        <div class="mt-3 rounded-lg border border-blue-400/30 bg-blue-400/20 p-2">
                                            <div class="text-center text-[10px] font-medium text-blue-700 dark:text-white">Product Demo</div>
                                            <div class="mt-0.5 text-center text-[8px] text-blue-600 dark:text-blue-300">Thu 2:00 PM</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                        <span class="text-[10px] text-blue-500 dark:text-blue-400">join link</span>
                                    </div>
                                    <div class="flex flex-col items-center">
                                        <div class="webinar-monitor w-36">
                                            <div class="rounded-lg bg-gray-200 p-3 dark:bg-white/10">
                                                <div class="mb-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">Streaming</div>
                                                <div class="space-y-2 text-center">
                                                    <div class="es-ai-field rounded bg-blue-400/20 p-1.5 text-[10px] text-blue-700 dark:text-blue-300" style="--i: 0;">Zoom</div>
                                                    <div class="es-ai-field rounded bg-green-400/20 p-1.5 text-[10px] text-green-700 dark:text-green-300" style="--i: 1;">Google Meet</div>
                                                    <div class="es-ai-field rounded bg-purple-400/20 p-1.5 text-[10px] text-purple-700 dark:text-purple-300" style="--i: 2;">Teams</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="webinar-monitor-stand"></div>
                                        <div class="webinar-monitor-base"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Recurring webinar series -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Recurring
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Schedule recurring webinar series</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Weekly or monthly sessions. Set it once and let attendees follow along.</p>
                        <div class="mt-auto rounded-xl border border-amber-400/30 bg-amber-500/15 p-3" aria-hidden="true">
                            <div class="space-y-1.5">
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/20 p-1.5" style="--i: 0;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="webinar-ep-chip">E01</span><span class="text-[10px] font-medium text-gray-900 dark:text-white">Mon - Product Update</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 1;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="webinar-ep-chip">E02</span><span class="text-[10px] text-gray-600 dark:text-gray-300">Mon - Product Update</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 2;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="webinar-ep-chip">E03</span><span class="text-[10px] text-gray-600 dark:text-gray-300">Mon - Product Update</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 3;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="webinar-ep-chip">E04</span><span class="text-[10px] text-gray-600 dark:text-gray-300">Mon - Product Update</span></div>
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
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sync webinars with Google Calendar</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync. Webinars, prep sessions, and follow-ups all in one place.</p>
                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="w-20 rounded-xl border border-blue-400/30 bg-blue-500/15 p-3">
                                <div class="mb-1 text-center text-[10px] text-blue-600 dark:text-blue-300">Schedule</div>
                                <div class="space-y-1">
                                    <div class="es-sync-dot h-1.5 rounded bg-teal-400/60"></div>
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

                <!-- Attendee management (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Attendees
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Track webinar registrations</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Track registrations and know your audience before going live.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="es-reg-float flex items-center justify-center">
                                <div class="flex -space-x-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-teal-500 to-cyan-500 text-xs text-white dark:border-[#0a0a0f]">A</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-cyan-500 to-blue-500 text-xs text-white dark:border-[#0a0a0f]">B</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-blue-500 to-sky-500 text-xs text-white dark:border-[#0a0a0f]">C</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gray-300 text-xs text-gray-600 dark:border-[#0a0a0f] dark:bg-white/20 dark:text-white">+84</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center text-xs text-cyan-600 dark:text-cyan-300">87 registered for next session</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Webinar program journey (dark band)                       -->
    <!-- ============================================================ -->
    @php
        $journey = [
            ['Single webinars', 'Hosting your first webinar. Share a registration link and get attendees signed up.', 'border-teal-500/20 bg-teal-500/10', 'text-teal-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />'],
            ['Webinar series', 'Weekly or monthly sessions. Set up recurring schedules and let attendees follow along.', 'border-cyan-500/20 bg-cyan-500/10', 'text-cyan-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />'],
            ['Multi-track programs', 'Different topics, different tracks. Organize webinars into groups so attendees find what matters.', 'border-blue-500/20 bg-blue-500/10', 'text-blue-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />'],
            ['Paid webinars', 'Premium content deserves fair compensation. Sell tickets with zero platform fees.', 'border-sky-500/20 bg-sky-500/10', 'text-sky-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Team collaboration', 'Multiple hosts and presenters. Invite your team so everyone can manage sessions.', 'border-blue-500/20 bg-blue-500/10', 'text-blue-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'],
            ['Full webinar library', 'Past and upcoming sessions in one place. A professional schedule your audience can browse.', 'border-amber-500/20 bg-amber-500/10', 'text-amber-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
        ];
    @endphp
    <section id="journey" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 30%, rgba(13, 148, 136, 0.26), rgba(13, 148, 136, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(6, 182, 212, 0.2), rgba(6, 182, 212, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-signal absolute left-1/2 top-1/3 h-0 w-0 -translate-x-1/2">
                    <span style="--sig-dur: 5s; --sig-delay: 0s;"></span>
                    <span style="--sig-dur: 5s; --sig-delay: 1.6s;"></span>
                    <span style="--sig-dur: 5s; --sig-delay: 3.2s;"></span>
                </div>
            </div>

            <div class="webinar-onair pointer-events-none absolute z-10 hidden items-center gap-1.5 rounded-full px-2.5 py-1 backdrop-blur-sm sm:inline-flex" aria-hidden="true">
                <span class="webinar-tally"></span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-red-400">On Air</span>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        From one-off sessions to a <span class="text-gradient-webinar">full program</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Event Schedule grows with your webinar needs.
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
                    Perfect for all types of <span class="text-gradient-webinar">webinars</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether it's a product demo or a company all-hands, Event Schedule works for you. Also see Event Schedule for <a href="{{ marketing_url('/for-virtual-conferences') }}" class="text-gray-600 underline hover:no-underline dark:text-gray-300">Virtual Conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Product Demos"
                    description="Showcase your product to prospects with scheduled demo sessions and built-in registration."
                    icon-color="cyan"
                    blog-slug="for-product-demos"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Training & Onboarding"
                    description="Run recurring training sessions for employees or customers with automatic scheduling."
                    icon-color="teal"
                    blog-slug="for-training-onboarding"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Educational Lectures"
                    description="Share knowledge with scheduled lecture series and attendee registration."
                    icon-color="sky"
                    blog-slug="for-educational-lectures"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Industry Panels"
                    description="Coordinate panelists and promote expert discussions to a targeted audience."
                    icon-color="blue"
                    blog-slug="for-industry-panels"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Company All-Hands"
                    description="Schedule internal meetings with the whole team and keep everyone informed."
                    icon-color="amber"
                    blog-slug="for-company-all-hands"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Customer Workshops"
                    description="Teach customers how to get the most from your product with hands-on sessions."
                    icon-color="emerald"
                    blog-slug="for-customer-workshops"
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
            ['1', 'Create your webinar', 'Add your topic, date, and streaming link. Set up free or paid registration.'],
            ['2', 'Share your registration link', 'Add it to your website, email signature, or social profiles. Attendees register in one click.'],
            ['3', 'Go live', 'Attendees get notified. You focus on delivering great content. No platform fees, no lock-in.'],
        ];
    @endphp
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Three steps to a <span class="text-gradient-webinar">packed webinar</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-600 to-cyan-600 text-xl font-bold text-white shadow-lg shadow-teal-600/25">
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
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key <span class="text-gradient-webinar">features</span></h2>
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
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium hover:underline webinar-link-accent">
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
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related <span class="text-gradient-webinar">pages</span></h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-virtual-conferences', 'Virtual Conferences'], ['/for-online-classes', 'Online Classes'], ['/for-live-qa-sessions', 'Live Q&A Sessions'], ['/for-workshop-instructors', 'Workshop Instructors']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group webinar-related-card flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="text-lg font-semibold text-gray-900 transition-colors webinar-related-title dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors webinar-related-arrow rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium hover:underline webinar-link-accent">
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
                    Frequently asked <span class="text-gradient-webinar">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything webinar hosts ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What video platforms does Event Schedule work with?', 'Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, Webex, YouTube Live, and custom solutions. Event Schedule is platform-agnostic - just paste your link and attendees join from your schedule.'],
                    ['Can I charge for webinars?', 'Yes. Set up paid registration with Stripe. You keep 100% of the ticket revenue - Event Schedule charges zero platform fees. Stripe charges its standard processing fee (2.9% + $0.30).'],
                    ['Can I schedule a recurring webinar series?', 'Yes. Set up weekly, biweekly, or monthly webinar series with a single recurring event. Attendees can follow your schedule and get notified when new sessions are added.'],
                    ['Is Event Schedule free for hosting webinars?', 'Yes. The free plan includes unlimited events, attendee email notifications, and registration features. There are zero platform fees on ticket sales at any plan level. You only pay Stripe\'s standard processing fee if you sell tickets.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="webinar-faq group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-teal-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(13, 148, 136, 0.3), rgba(13, 148, 136, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-signal absolute left-1/2 top-1/3 h-0 w-0 -translate-x-1/2">
                        <span style="--sig-dur: 5s; --sig-delay: 0s;"></span>
                        <span style="--sig-dur: 5s; --sig-delay: 1.6s;"></span>
                        <span style="--sig-dur: 5s; --sig-delay: 3.2s;"></span>
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your webinars. Your audience. <span class="text-gradient-webinar">No middleman.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stop paying platform fees. Fill your webinars. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-webinars" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-teal-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
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
