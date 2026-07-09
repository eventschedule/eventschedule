<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Virtual Conferences | Hosting Software</x-slot>
    <x-slot name="description">Schedule and sell virtual conferences with multi-day agendas, tiered ticketing, and attendee email notifications. Works with any platform. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Virtual Conferences</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Virtual Conferences",
        "description": "Schedule and sell virtual conferences with multi-day agendas, tiered ticketing, speaker lineups, and attendee email notifications. Works with Zoom, Teams, YouTube Live, and any platform. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Virtual Conference Organizers"
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
                "name": "Can I schedule a multi-day virtual conference?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Add sessions across as many days as you need. Organize them into groups or tracks so attendees can browse by day, topic, or session type. Your full virtual conference agenda lives on one shareable page - a complete online conference schedule your attendees can bookmark."
                }
            },
            {
                "@type": "Question",
                "name": "What streaming platforms work with Event Schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a meeting or streaming link. Zoom, Microsoft Teams, Google Meet, YouTube Live, Twitch, and any other platform. Event Schedule is platform-agnostic - just paste your link and attendees join from the conference agenda."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell different ticket types for my conference?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Create multiple virtual conference ticket types with different prices - general admission, VIP, early bird, speaker passes, or any custom tier. You keep 100% of the revenue. Event Schedule charges zero platform fees at any plan level. Stripe charges its standard processing fee (2.9% + $0.30)."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free for virtual conferences?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free virtual conference software. The free plan includes unlimited events, attendee email notifications, follower features, and Google Calendar sync. There are zero platform fees on payments at any plan level. You only pay Stripe's standard processing fee if you charge for tickets."
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
        "name": "Event Schedule for Virtual Conferences",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Virtual Conference Scheduling Software",
        "operatingSystem": "Web",
        "description": "Schedule and sell virtual conferences with multi-day agendas, tiered ticketing, attendee email notifications, and payment processing. Works with Zoom, Teams, YouTube Live, and any platform.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Multi-day conference scheduling",
            "Tiered ticket types with zero platform fees",
            "One shareable link for full conference agenda",
            "Works with Zoom, Teams, YouTube Live, any platform",
            "Email notifications to all attendees",
            "Google Calendar two-way sync",
            "Follower notifications for future conferences",
            "QR code tickets for hybrid events",
            "Attendee management dashboard",
            "Open source virtual conference platform",
            "Selfhosted conference scheduling option"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "virtual conference platform, online conference scheduling, virtual summit, conference ticketing",
        "screenshot": "{{ asset('images/social/for-virtual-conferences.png') }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to host a virtual conference with Event Schedule",
        "description": "Three steps to schedule and host your virtual conference online.",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Build your agenda",
                "text": "Add sessions, speakers, and streaming links. Organize by day and track."
            },
            {
                "@type": "HowToStep",
                "name": "Share your conference",
                "text": "One link for the full schedule. Sell tickets with tiered pricing."
            },
            {
                "@type": "HowToStep",
                "name": "Go live",
                "text": "Attendees join sessions from the agenda. You focus on content."
            }
        ]
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
        /* For-virtual-conferences "The Agenda" styles. The shared es-* motion system
           lives in marketing.css; this holds the navy-plus-electric-cyan conference
           gradient (separating it from the site's brand blue), the color-coded
           multi-track agenda tiles, a hanging lanyard badge that sways in the hero,
           and the ticket-foil and approved-check moments. */
        .text-gradient-conference {
            background: linear-gradient(135deg, #1e3a8a, #0e7490, #0891b2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(8, 145, 178, 0.25);
        }
        .dark .text-gradient-conference {
            background: linear-gradient(135deg, #60a5fa, #22d3ee, #67e8f9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(34, 211, 238, 0.35);
        }

        /* Agenda board: session tiles across a timeline illuminate in a wave, now
           color-coded by track (electric cyan / amber / emerald) so the multi-track
           conference program reads at a glance. */
        .es-agenda { display: flex; align-items: center; }
        .es-agenda-tile {
            flex: 0 0 auto;
            height: 10px; border-radius: 3px;
            background: var(--ag-bg, linear-gradient(to right, rgba(56, 189, 248, 0.65), rgba(37, 99, 235, 0.65)));
            animation: es-agenda-glow var(--ag-dur, 3s) ease-in-out infinite;
            animation-delay: var(--ag-delay, 0s);
        }
        .es-agenda-tile.is-cyan { --ag-bg: linear-gradient(to right, rgba(34, 211, 238, 0.7), rgba(6, 182, 212, 0.6)); --ag-glow: rgba(34, 211, 238, 0.55); }
        .es-agenda-tile.is-amber { --ag-bg: linear-gradient(to right, rgba(251, 191, 36, 0.7), rgba(245, 158, 11, 0.6)); --ag-glow: rgba(251, 191, 36, 0.5); }
        .es-agenda-tile.is-emerald { --ag-bg: linear-gradient(to right, rgba(52, 211, 153, 0.7), rgba(16, 185, 129, 0.6)); --ag-glow: rgba(52, 211, 153, 0.5); }
        @keyframes es-agenda-glow {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 0.9; box-shadow: 0 0 8px var(--ag-glow, rgba(56, 189, 248, 0.5)); }
        }

        /* Multi-track tabs on the 3-day agenda mock rows. */
        .es-track-tab { position: absolute; left: 0; top: 6px; bottom: 6px; width: 3px; border-radius: 3px; }
        .es-track-cyan { background: #22d3ee; box-shadow: 0 0 6px rgba(34, 211, 238, 0.6); }
        .es-track-amber { background: #f59e0b; box-shadow: 0 0 6px rgba(245, 158, 11, 0.5); }
        .es-track-emerald { background: #34d399; box-shadow: 0 0 6px rgba(52, 211, 153, 0.5); }

        /* Hanging conference badge: a card on a thin lanyard that sways like a slow
           pendulum from the clip at the top. */
        @keyframes es-lanyard-sway {
            0%, 100% { transform: rotate(-3.5deg); }
            50% { transform: rotate(3.5deg); }
        }
        .es-lanyard { transform-origin: top center; animation: es-lanyard-sway 5s ease-in-out infinite; }
        .es-lanyard-strap { width: 2px; height: 2.5rem; margin: 0 auto; background: linear-gradient(to bottom, rgba(34, 211, 238, 0.75), rgba(37, 99, 235, 0.75)); }
        .es-lanyard-clip { width: 1.75rem; height: 0.5rem; margin: -3px auto 0; border-radius: 9999px; background: linear-gradient(to right, #22d3ee, #2563eb); }
        .es-badge { width: 11rem; margin-top: 0.55rem; border-radius: 0.9rem; padding: 0.85rem; background: rgba(255, 255, 255, 0.85); border: 1px solid rgba(34, 211, 238, 0.4); box-shadow: 0 14px 30px rgba(8, 145, 178, 0.25); backdrop-filter: blur(8px); }
        .dark .es-badge { background: rgba(255, 255, 255, 0.06); border-color: rgba(34, 211, 238, 0.3); box-shadow: 0 14px 30px rgba(34, 211, 238, 0.18); }
        .es-badge-chip { display: inline-block; border-radius: 9999px; padding: 2px 9px; font-size: 9px; font-weight: 700; letter-spacing: 0.09em; text-transform: uppercase; color: #fff; background: linear-gradient(to right, #06b6d4, #2563eb); }
        .es-badge-name { margin-top: 0.5rem; font-size: 0.9rem; font-weight: 700; color: #0f172a; }
        .dark .es-badge-name { color: #fff; }
        .es-badge-role { font-size: 10px; font-weight: 600; color: #0891b2; }
        .dark .es-badge-role { color: #67e8f9; }
        .es-badge-strip { margin-top: 0.6rem; height: 1.4rem; border-radius: 0.35rem; background: repeating-linear-gradient(90deg, rgba(34, 211, 238, 0.4) 0 3px, transparent 3px 7px); }

        /* Badge-level chips on the tiered-ticket mock. */
        .es-tier-chip { display: inline-block; margin-left: 6px; border-radius: 9999px; padding: 1px 7px; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; vertical-align: middle; }
        .es-tier-std { background: rgba(30, 58, 138, 0.14); color: #1e3a8a; }
        .dark .es-tier-std { background: rgba(96, 165, 250, 0.2); color: #93c5fd; }
        .es-tier-vip { background: linear-gradient(to right, rgba(34, 211, 238, 0.3), rgba(37, 99, 235, 0.25)); color: #0e7490; }
        .dark .es-tier-vip { color: #a5f3fc; }
        .es-tier-early { background: rgba(6, 182, 212, 0.18); color: #0891b2; }
        .dark .es-tier-early { background: rgba(34, 211, 238, 0.2); color: #67e8f9; }

        /* VIP foil edge: a cyan metallic ring with a slow light sweep. */
        @keyframes es-foil { 0% { background-position: 180% 0; } 100% { background-position: -80% 0; } }
        .es-vip-foil { position: relative; overflow: hidden; box-shadow: inset 0 0 0 1px rgba(34, 211, 238, 0.55), 0 0 12px rgba(34, 211, 238, 0.22); }
        .es-vip-foil::after {
            content: ""; position: absolute; inset: 0; pointer-events: none;
            background: linear-gradient(115deg, transparent 38%, rgba(255, 255, 255, 0.5) 50%, transparent 62%);
            background-size: 220% 100%;
            animation: es-foil 3.6s ease-in-out infinite;
        }

        /* Approved-check pop on the attendee-feedback mock. */
        @keyframes es-approve-pop {
            0%, 35% { transform: scale(0.2); opacity: 0; }
            55% { transform: scale(1.25); opacity: 1; }
            70% { transform: scale(0.9); }
            85%, 100% { transform: scale(1); opacity: 1; }
        }
        .es-approve-check { transform-origin: center; animation: es-approve-pop 3.2s ease-in-out infinite; }

        /* Accent link + related-card hover recolor (navy/cyan, not brand blue). */
        .vc-link { color: #0e7490; font-weight: 500; }
        .vc-link:hover { text-decoration: underline; }
        .dark .vc-link { color: #22d3ee; }
        .vc-related-card:hover { border-color: #67e8f9; background-color: #ecfeff; }
        .dark .vc-related-card:hover { border-color: rgba(34, 211, 238, 0.3); background-color: rgba(34, 211, 238, 0.06); }
        .vc-related-card:hover .vc-related-title { color: #0891b2; }
        .dark .vc-related-card:hover .vc-related-title { color: #22d3ee; }
        .vc-related-card:hover .vc-related-arrow { color: #0891b2; }
        .dark .vc-related-card:hover .vc-related-arrow { color: #22d3ee; }

        @media (prefers-reduced-motion: reduce) {
            .es-agenda-tile, .es-lanyard, .es-approve-check { animation: none !important; }
            .es-agenda-tile { opacity: 0.55; }
            .es-lanyard { transform: none; }
            .es-vip-foil::after { animation: none; opacity: 0; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: your whole conference on one page                   -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(2, 132, 199, 0.3), rgba(2, 132, 199, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Agenda timeline along the bottom edge -->
            <div class="es-agenda absolute bottom-0 left-0 right-0 hidden h-20 items-center justify-center gap-1.5 px-8 opacity-40 md:flex" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                @for ($i = 0; $i < 32; $i++)
                    @php $w = [26, 40, 54, 34, 46][$i % 5]; $dur = 2.6 + ($i % 5) * 0.3; $delay = ($i % 8) * 0.16; $track = ['is-cyan', 'is-amber', 'is-emerald'][$i % 3]; @endphp
                    <span class="es-agenda-tile {{ $track }}" style="width: {{ $w }}px; --ag-dur: {{ $dur }}s; --ag-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <!-- Hanging conference badge on a lanyard -->
        <div class="es-lanyard absolute right-8 top-24 z-20 hidden opacity-90 lg:block" aria-hidden="true">
            <div class="es-lanyard-strap"></div>
            <div class="es-lanyard-clip"></div>
            <div class="es-badge">
                <div><span class="es-badge-chip">All Access</span></div>
                <div class="es-badge-name">Alex Rivera</div>
                <div class="es-badge-role">Keynote Speaker</div>
                <div class="es-badge-strip"></div>
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Conference Organizers & Event Planners</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Host virtual conferences that feel professional.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-conference">Zero platform fees.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-4 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Multi-day virtual conference agendas, multiple ticket types, speaker lineups. Schedule your conference, sell tickets, and let attendees browse the full agenda from one link.
            </p>
            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-base text-gray-400 dark:text-gray-500">
                The virtual conference platform with built-in multi-day scheduling, tiered ticketing, attendee email notifications, and payment processing for conference organizers.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#journey" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it scales
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-sky-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-sky-500/40">
                    Create your conference schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Conference-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Tech Summits', 'Industry Conferences', 'Company Retreats', 'Professional Summits', 'Annual Meetings', 'Panel Events', 'Developer Cons', 'Hybrid Events'] as $tag)
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
    <!-- 2. Stats                                                     -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-16 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-6 text-center md:grid-cols-3" data-reveal-group="90">
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-sky-500 dark:text-sky-400">~<span data-count-to="73">73</span>%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">of organizations now host virtual or hybrid events</div>
                </div>
                <div data-reveal class="border-gray-200 p-6 dark:border-white/5 md:border-x">
                    <div class="mb-2 text-4xl font-black text-blue-500 dark:text-blue-400">3-5x</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">wider reach compared to in-person only</div>
                </div>
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-cyan-500 dark:text-cyan-400">$0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">platform fees on conference tickets</div>
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
                    Everything you need to run a <span class="text-gradient-conference">virtual conference</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Multi-day agenda (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Multi-Day Agenda
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Multi-day conference schedule</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Organize keynotes, breakout sessions, and workshops across multiple days of your virtual conference. Attendees browse the full agenda and find the sessions they care about. Have a printed conference program? Scan it with AI to populate all your sessions automatically.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Keynotes</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Breakout sessions</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Workshops</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">AI agenda scanning</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-sky-300 bg-gradient-to-br from-sky-100 to-blue-100 p-4 dark:border-sky-400/30 dark:from-sky-950 dark:to-blue-950">
                                        <div class="mb-3 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-blue-500 text-sm font-semibold text-white">TC</div>
                                            <div><div class="text-sm font-semibold text-gray-900 dark:text-white">Tech Conference 2026</div><div class="text-xs text-sky-600 dark:text-sky-300">3-day agenda</div></div>
                                        </div>
                                        <div class="space-y-1.5">
                                            <div class="es-ai-field relative rounded-lg border border-sky-400/20 bg-gradient-to-br from-sky-600/30 to-blue-600/30 p-2 pl-3" style="--i: 0;">
                                                <span class="es-track-tab es-track-cyan" aria-hidden="true"></span>
                                                <div class="text-[10px] font-semibold text-gray-900 dark:text-white">DAY 1 - Opening Keynote</div>
                                                <div class="mt-0.5 text-[9px] text-gray-500 dark:text-gray-400">9:00 AM - Main Stage</div>
                                            </div>
                                            <div class="es-ai-field relative rounded-lg border border-blue-400/20 bg-gradient-to-br from-blue-600/20 to-cyan-600/20 p-2 pl-3" style="--i: 1;">
                                                <span class="es-track-tab es-track-amber" aria-hidden="true"></span>
                                                <div class="text-[10px] font-semibold text-gray-900 dark:text-white">DAY 2 - AI Workshop</div>
                                                <div class="mt-0.5 text-[9px] text-gray-500 dark:text-gray-400">10:00 AM - Track B</div>
                                            </div>
                                            <div class="es-ai-field relative rounded-lg border border-cyan-400/20 bg-gradient-to-br from-cyan-600/20 to-sky-600/20 p-2 pl-3" style="--i: 2;">
                                                <span class="es-track-tab es-track-emerald" aria-hidden="true"></span>
                                                <div class="text-[10px] font-semibold text-gray-900 dark:text-white">DAY 3 - Closing Panel</div>
                                                <div class="mt-0.5 text-[9px] text-gray-500 dark:text-gray-400">2:00 PM - Main Stage</div>
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

                <!-- Tiered tickets -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Tiered Tickets
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sell tiered conference tickets</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">General admission, VIP, speaker passes, early bird pricing. 100% of Stripe payments go to you. See all <a href="{{ marketing_url('/features/ticketing') }}" class="text-blue-600 underline hover:no-underline dark:text-blue-400">ticketing features</a>.</p>
                        <div class="mt-auto rounded-xl border border-blue-400/30 bg-blue-500/15 p-4" aria-hidden="true">
                            <div class="mb-3 space-y-2">
                                <div class="es-ai-field flex items-center justify-between rounded-lg bg-blue-400/20 p-2" style="--i: 0;"><span class="text-xs font-medium text-gray-900 dark:text-white">General Admission<span class="es-tier-chip es-tier-std">Standard</span></span><span class="text-xs font-semibold text-blue-600 dark:text-blue-400">$49</span></div>
                                <div class="es-ai-field es-vip-foil flex items-center justify-between rounded-lg bg-sky-400/20 p-2" style="--i: 1;"><span class="text-xs font-medium text-gray-900 dark:text-white">VIP Pass<span class="es-tier-chip es-tier-vip">Premium</span></span><span class="text-xs font-semibold text-sky-600 dark:text-sky-400">$149</span></div>
                                <div class="es-ai-field flex items-center justify-between rounded-lg bg-cyan-400/20 p-2" style="--i: 2;"><span class="text-xs font-medium text-gray-900 dark:text-white">Early Bird<span class="es-tier-chip es-tier-early">Limited</span></span><span class="text-xs font-semibold text-cyan-600 dark:text-cyan-400">$29</span></div>
                            </div>
                            <div class="border-t border-blue-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="font-semibold text-blue-600 dark:text-blue-400">$0</span>
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
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            Share Link
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One link for your conference</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Share a single URL that attendees use to browse the full agenda, buy tickets, and join sessions.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-cyan-400/30 bg-cyan-500/20 p-2">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span class="truncate font-mono text-xs text-gray-900 dark:text-white">yourconf.eventschedule.com</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1 text-center">
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-cyan-600 dark:bg-white/5 dark:text-cyan-300">Website</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-cyan-600 dark:bg-white/5 dark:text-cyan-300">LinkedIn</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-cyan-600 dark:bg-white/5 dark:text-cyan-300">Email</div>
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
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    Any Platform
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Works with any streaming platform</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Zoom, Microsoft Teams, YouTube Live, or custom RTMP. Add your streaming link per session, attendees join from the agenda. Learn more about <a href="{{ marketing_url('/features/online-events') }}" class="text-teal-600 underline hover:no-underline dark:text-teal-400">online event features</a>.</p>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="flex items-center gap-4">
                                    <div class="w-36 rounded-xl border border-teal-400/30 bg-teal-500/15 p-4">
                                        <div class="mb-2 text-center text-xs font-semibold text-teal-600 dark:text-teal-300">Your Agenda</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-gray-300 dark:bg-white/20"></div>
                                            <div class="h-2 w-3/4 rounded bg-teal-400/40"></div>
                                        </div>
                                        <div class="mt-3 rounded-lg border border-teal-400/30 bg-teal-400/20 p-2">
                                            <div class="text-center text-[10px] font-medium text-teal-800 dark:text-white">Opening Keynote</div>
                                            <div class="mt-0.5 text-center text-[8px] text-teal-700 dark:text-teal-300">Day 1 - 9:00 AM</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                        <span class="text-[10px] text-teal-500 dark:text-teal-400">stream link</span>
                                    </div>
                                    <div class="w-36 rounded-xl border border-gray-300 bg-gray-200 p-4 dark:border-white/20 dark:bg-white/10">
                                        <div class="mb-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">Platform</div>
                                        <div class="space-y-2 text-center">
                                            <div class="es-ai-field rounded bg-blue-400/20 p-1.5 text-[10px] text-blue-700 dark:text-blue-300" style="--i: 0;">Zoom</div>
                                            <div class="es-ai-field rounded bg-sky-400/20 p-1.5 text-[10px] text-sky-700 dark:text-sky-300" style="--i: 1;">MS Teams</div>
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

                <!-- Email all attendees (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Email Attendees
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Email all attendees</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Send updates, schedule changes, speaker announcements, and post-conference resources directly to attendees.</p>
                            </div>
                            <div class="rounded-xl border border-amber-400/30 bg-amber-500/15 p-3" aria-hidden="true">
                                <div class="space-y-1.5">
                                    <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/20 p-1.5" style="--i: 0;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] font-medium text-gray-900 dark:text-white">Schedule update</span></div>
                                    <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 1;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Speaker announcement</span></div>
                                    <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 2;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Post-conference resources</span></div>
                                </div>
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
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Google Calendar sync</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync keeps conference sessions organized alongside your other meetings and planning.</p>
                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="w-20 rounded-xl border border-blue-400/30 bg-blue-500/15 p-3">
                                <div class="mb-1 text-center text-[10px] text-blue-600 dark:text-blue-300">Schedule</div>
                                <div class="space-y-1">
                                    <div class="es-sync-dot h-1.5 rounded bg-sky-400/60"></div>
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

                <!-- Attendees follow -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 dark:border-slate-700/40 dark:bg-slate-800/40 dark:text-slate-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Followers
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Attendees follow your events</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Followers get notified for next year's conference or related events you organize.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="flex items-center justify-center">
                                <div class="flex -space-x-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-sky-500 to-blue-500 text-xs text-white dark:border-[#0a0a0f]">A</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-blue-500 to-cyan-500 text-xs text-white dark:border-[#0a0a0f]">B</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-cyan-500 to-teal-500 text-xs text-white dark:border-[#0a0a0f]">C</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gray-300 text-xs text-gray-600 dark:border-[#0a0a0f] dark:bg-white/20 dark:text-white">+520</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center text-xs text-slate-600 dark:text-slate-400">523 attendees following your conference</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Attendee feedback (2 cols, bottom) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                    Attendee Feedback
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Attendee feedback</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Let attendees leave comments on individual sessions. All feedback is approved by you before going live.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Per-session comments</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Organizer approval</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="max-w-xs rounded-2xl border border-emerald-300 bg-gradient-to-br from-emerald-50 to-sky-50 p-4 dark:border-emerald-400/30 dark:from-emerald-950 dark:to-sky-950">
                                    <div class="mb-2 text-xs text-gray-500 dark:text-white/70">AI Workshop - Day 2</div>
                                    <div class="space-y-2">
                                        <div class="es-ai-field flex items-start gap-2" style="--i: 0;">
                                            <div class="mt-0.5 h-5 w-5 shrink-0 rounded-full bg-emerald-300 dark:bg-emerald-500/40"></div>
                                            <div class="rounded-lg bg-white px-2 py-1 text-[10px] text-gray-600 dark:bg-white/10 dark:text-gray-300">Great session on LLMs!</div>
                                        </div>
                                        <div class="es-ai-field flex items-start gap-2" style="--i: 1;">
                                            <div class="mt-0.5 h-5 w-5 shrink-0 rounded-full bg-sky-300 dark:bg-sky-500/40"></div>
                                            <div class="rounded-lg bg-white px-2 py-1 text-[10px] text-gray-600 dark:bg-white/10 dark:text-gray-300">Very practical demos</div>
                                        </div>
                                        <div class="flex items-center gap-1 pt-1">
                                            <svg aria-hidden="true" class="es-approve-check h-3 w-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400">Approved by organizer</span>
                                        </div>
                                    </div>
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
    <!-- 4. Journey (dark band)                                       -->
    <!-- ============================================================ -->
    @php
        $journey = [
            ['First virtual meetup', 'Share a link and host your first online session. Free registration gets attendees in the door.', 'border-sky-500/20 bg-sky-500/10', 'text-sky-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Single-day summit', 'Organize multiple sessions in one day. Attendees browse the agenda and join the talks they want.', 'border-blue-500/20 bg-blue-500/10', 'text-blue-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />'],
            ['Paid conference', 'Start selling tickets. Offer tiered pricing for general admission, VIP, and speaker passes.', 'border-cyan-500/20 bg-cyan-500/10', 'text-cyan-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Multi-day conference', 'Keynotes, breakouts, and workshops across multiple days. Organize tracks for different audiences.', 'border-teal-500/20 bg-teal-500/10', 'text-teal-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />'],
            ['Conference series', 'Run quarterly or annual conferences. Followers get notified when you announce the next edition.', 'border-amber-500/20 bg-amber-500/10', 'text-amber-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />'],
            ['Hybrid events', 'Combine in-person and virtual attendance. Sell different ticket types for on-site and remote participants.', 'border-sky-500/20 bg-sky-500/10', 'text-sky-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
        ];
    @endphp
    <section id="journey" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(2, 132, 199, 0.26), rgba(2, 132, 199, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(37, 99, 235, 0.2), rgba(37, 99, 235, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-agenda absolute bottom-0 left-0 right-0 flex h-16 items-center justify-center gap-1.5 px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                    @for ($i = 0; $i < 32; $i++)
                        @php $w = [26, 40, 54, 34, 46][$i % 5]; $dur = 2.6 + ($i % 5) * 0.3; $delay = ($i % 8) * 0.16; $track = ['is-cyan', 'is-amber', 'is-emerald'][$i % 3]; @endphp
                        <span class="es-agenda-tile {{ $track }}" style="width: {{ $w }}px; --ag-dur: {{ $dur }}s; --ag-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        From your first virtual event to a <span class="text-gradient-conference">conference series</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Event Schedule grows with your conference program.
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
                    Perfect for every type of <span class="text-gradient-conference">virtual conference</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether it's a tech summit or an annual meeting, Event Schedule works for conference organizers of all kinds. Also see <a href="{{ marketing_url('/for-webinars') }}" class="text-gray-600 underline hover:no-underline dark:text-gray-300">Event Schedule for Webinars</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Tech Companies -->
                <x-sub-audience-card
                    name="Tech Companies"
                    description="Product launches, developer conferences, hackathons. Share streaming links and sell tickets to a global audience."
                    icon-color="cyan"
                    blog-slug="for-tech-company-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Professional Associations -->
                <x-sub-audience-card
                    name="Professional Associations"
                    description="Annual meetings, certification events, member summits. Organize multi-day agendas with tiered access."
                    icon-color="teal"
                    blog-slug="for-professional-association-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Nonprofits & NGOs -->
                <x-sub-audience-card
                    name="Nonprofits & NGOs"
                    description="Fundraising galas, awareness conferences, volunteer summits. Reach supporters worldwide with zero platform fees."
                    icon-color="sky"
                    blog-slug="for-nonprofit-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Corporate Teams -->
                <x-sub-audience-card
                    name="Corporate Teams"
                    description="All-hands meetings, training summits, leadership retreats. One link for your entire team to follow the agenda."
                    icon-color="blue"
                    blog-slug="for-corporate-team-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Academic Institutions -->
                <x-sub-audience-card
                    name="Academic Institutions"
                    description="Research symposiums, faculty conferences, student events. Schedule sessions across days and tracks."
                    icon-color="amber"
                    blog-slug="for-academic-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Industry Groups -->
                <x-sub-audience-card
                    name="Industry Groups"
                    description="Trade shows, networking events, expert panels. Build a following and notify attendees about future events."
                    icon-color="emerald"
                    blog-slug="for-industry-group-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
            ['1', 'Build your agenda', 'Add sessions, speakers, and streaming links. Organize by day and track.'],
            ['2', 'Share your conference', 'One link for the full schedule. Sell tickets with tiered pricing.'],
            ['3', 'Go live', 'Attendees join sessions from the agenda. You focus on content.'],
        ];
    @endphp
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Three steps to your <span class="text-gradient-conference">virtual conference</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-600 to-blue-600 text-xl font-bold text-white shadow-lg shadow-sky-600/25">
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
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key <span class="text-gradient-conference">features</span></h2>
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
                <a href="{{ marketing_url('/features') }}" class="vc-link inline-flex items-center">
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
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related <span class="text-gradient-conference">pages</span></h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-webinars', 'Webinars'], ['/for-online-classes', 'Online Classes'], ['/for-live-qa-sessions', 'Live Q&A Sessions'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="vc-related-card group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="vc-related-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="vc-related-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="vc-link inline-flex items-center">
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
                    Frequently asked <span class="text-gradient-conference">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything conference organizers ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Can I schedule a multi-day virtual conference?', 'Yes. Add sessions across as many days as you need. Organize them into groups or tracks so attendees can browse by day, topic, or session type. Your full virtual conference agenda lives on one shareable page - a complete online conference schedule your attendees can bookmark.'],
                    ['What streaming platforms work with Event Schedule?', 'Any platform that gives you a meeting or streaming link. Zoom, Microsoft Teams, Google Meet, YouTube Live, Twitch, and any other platform. Event Schedule is platform-agnostic - just paste your link and attendees join from the conference agenda.'],
                    ['Can I sell different ticket types for my conference?', 'Yes. Create multiple virtual conference ticket types with different prices - general admission, VIP, early bird, speaker passes, or any custom tier. You keep 100% of the revenue. Event Schedule charges zero platform fees at any plan level. Stripe charges its standard processing fee (2.9% + $0.30).'],
                    ['Is Event Schedule free for virtual conferences?', 'Yes. Event Schedule is free virtual conference software. The free plan includes unlimited events, attendee email notifications, follower features, and Google Calendar sync. There are zero platform fees on payments at any plan level. You only pay Stripe\'s standard processing fee if you charge for tickets.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-sky-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(2, 132, 199, 0.3), rgba(2, 132, 199, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-agenda absolute bottom-0 left-0 right-0 flex h-14 items-center justify-center gap-1.5 px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                        @for ($i = 0; $i < 26; $i++)
                            @php $w = [26, 40, 54, 34, 46][$i % 5]; $dur = 2.6 + ($i % 5) * 0.3; $delay = ($i % 8) * 0.16; $track = ['is-cyan', 'is-amber', 'is-emerald'][$i % 3]; @endphp
                            <span class="es-agenda-tile {{ $track }}" style="width: {{ $w }}px; --ag-dur: {{ $dur }}s; --ag-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your conference. Your audience. <span class="text-gradient-conference">No middleman.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stop paying platform fees. Start hosting virtual conferences. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-conf" autocomplete="off" spellcheck="false" maxlength="30"
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
