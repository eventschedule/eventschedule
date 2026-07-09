<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Comedians | Share Your Show Dates</x-slot>
    <x-slot name="description">One link for every set. Track your mics, guest spots, and headlines. Email fans directly - algorithms can't bury it. Zero fees when you sell tickets. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Comedians</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Comedians",
        "description": "Track your mics, guest spots, and headlines. Email fans directly. Zero fees when you sell tickets. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Comedians"
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
                "name": "Is Event Schedule free for comedians?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your show dates and building a fan following. Ticketing and newsletters are available on the Pro and Enterprise plans, with zero platform fees on any ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to my comedy shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Create multiple ticket types like general admission, VIP, and early bird. Every ticket includes a QR code for check-in at the door. Zero platform fees - you only pay Stripe's standard processing."
                }
            },
            {
                "@type": "Question",
                "name": "How do fans know when I have a show near them?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans follow your schedule and get notified when you add new shows. You can also send newsletters directly to your followers with upcoming dates. Share your schedule link in your social bios, on podcasts, or anywhere fans find you."
                }
            },
            {
                "@type": "Question",
                "name": "Can comedy clubs add me to their lineup?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. When a comedy club adds you to their event on Event Schedule, the show automatically appears on your schedule too. No need to add the same gig in two places. Both calendars stay in sync so your fans always see your latest bookings."
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
        "name": "Event Schedule for Comedians",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Comedy Show Scheduling Software",
        "operatingSystem": "Web",
        "description": "Track your open mics, guest spots, and headlining gigs. Email fans directly - no algorithm burying your posts. Zero fees on ticket sales. Built for stand-up comics.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Open mic tracking",
            "Guest spot management",
            "Direct fan newsletters",
            "Zero-fee ticketing",
            "Late night show support",
            "Comedy club integration",
            "Tour date management",
            "Promo graphic generator"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "comedian schedule, comedy show calendar, stand-up comedy booking, comedy event management, free comedian scheduling",
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
           For-comedians "The Set" styles. The shared es-* motion system
           (aurora, reveals, bento, odometer, finale) lives in
           marketing.css; this block holds only this page's own effects:
           the neon amber gradient and the single stage spotlight.
           ============================================================== */

        /* Heading gradient - the amber-to-rose stage light. Light mode uses
           deeper amber/orange/rose so it stays legible on white; dark mode
           brightens to the original amber-rose with a warm glow over warm-black. */
        .neon-text {
            background: linear-gradient(135deg, #d97706, #ea580c, #e11d48);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .neon-text {
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #fb7185);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(251, 191, 36, 0.3);
        }

        /* One warm spotlight cone from above (the comic under the light) */
        .es-spot-cone {
            position: absolute;
            top: -6%;
            left: 50%;
            width: 62%;
            height: 128%;
            margin-left: -31%;
            pointer-events: none;
            transform-origin: 50% 0;
            background: conic-gradient(from 172deg at 50% 0%, transparent 0deg, rgba(251, 191, 36, 0.12) 8deg, rgba(251, 146, 60, 0.06) 16deg, transparent 26deg);
            animation: es-spot-sway 9s ease-in-out infinite alternate;
        }
        @keyframes es-spot-sway {
            from { transform: rotate(-3.5deg); }
            to   { transform: rotate(3.5deg); }
        }
        .es-spot-dot {
            position: absolute;
            top: -2px;
            left: 50%;
            width: 12px;
            height: 12px;
            margin-left: -6px;
            border-radius: 9999px;
            background: #fde68a;
            box-shadow: 0 0 22px 6px rgba(251, 191, 36, 0.55);
        }

        /* Mic-stand silhouette beside the lineup tracker (thin outline, amber rim-light) */
        .es-micstand {
            position: absolute;
            bottom: 0;
            left: 0.75rem;
            width: 44px;
            height: 150px;
            color: rgba(180, 83, 9, 0.16);
            filter: drop-shadow(0 0 6px rgba(245, 158, 11, 0.38));
            pointer-events: none;
        }
        .dark .es-micstand { color: rgba(251, 191, 36, 0.24); }

        /* Set-list paper edge on the lineup tracker mock (stacked pages behind) */
        .es-setlist { box-shadow: 4px 5px 0 -1px #ecebe9, 9px 11px 0 -2px #e2e1de; }
        .dark .es-setlist { box-shadow: 4px 5px 0 -1px #1b1b22, 9px 11px 0 -2px #141419; }

        /* Spotlight-dot marker on the open-mic-to-headliner journey cards */
        .es-spot-marker {
            position: absolute;
            top: 0.9rem;
            right: 0.9rem;
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #fde68a;
            box-shadow: 0 0 14px 3px rgba(251, 191, 36, 0.5);
        }

        /* Amber hover for FAQ items and related-page cards + inline links */
        .es-amber-hover { transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }
        .es-amber-hover:hover { border-color: #fcd34d; background-color: rgba(245, 158, 11, 0.06); }
        .dark .es-amber-hover:hover { border-color: rgba(251, 191, 36, 0.4); background-color: rgba(251, 191, 36, 0.07); }
        .es-amber-hover:hover .es-amber-hover-title { color: #d97706; }
        .dark .es-amber-hover:hover .es-amber-hover-title { color: #fbbf24; }
        .es-amber-hover:hover .es-amber-hover-arrow { color: #d97706; }
        .dark .es-amber-hover:hover .es-amber-hover-arrow { color: #fbbf24; }
        .es-amber-link { color: #b45309; }
        .dark .es-amber-link { color: #fbbf24; }

        @media (prefers-reduced-motion: reduce) {
            .es-spot-cone { animation: none !important; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: step into the light                                 -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0f0808] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(251, 191, 36, 0.4), rgba(251, 191, 36, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(251, 113, 133, 0.4), rgba(251, 113, 133, 0) 65%);"></div>
            <div class="es-spot-cone"><span class="es-spot-dot"></span></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Built for Comics</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">The grind is real.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="neon-text">One link for every set.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Open mic Monday. Barking Tuesday. Guest set Wednesday. Headlining Friday. One link shows fans every set - yours, not the algorithm's.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the grind
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 px-8 py-4 text-lg font-semibold text-black shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Get your link
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- The journey: open mic to headliner -->
            <div class="es-fade-up es-d-4 mt-14 flex flex-wrap items-center justify-center gap-3">
                <span class="inline-flex items-center rounded-lg border border-red-300 bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 dark:border-red-800/50 dark:bg-red-900/40 dark:text-red-300">Open Mic</span>
                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="inline-flex items-center rounded-lg border border-amber-300 bg-amber-100 px-3 py-1.5 text-xs font-medium text-amber-700 dark:border-amber-800/50 dark:bg-amber-900/40 dark:text-amber-300">Bringer</span>
                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="inline-flex items-center rounded-lg border border-orange-300 bg-orange-100 px-3 py-1.5 text-xs font-medium text-orange-700 dark:border-orange-800/50 dark:bg-orange-900/40 dark:text-orange-300">Guest Spot</span>
                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="inline-flex items-center rounded-lg border border-rose-300 bg-rose-100 px-3 py-1.5 text-xs font-medium text-rose-700 dark:border-rose-800/50 dark:bg-rose-900/40 dark:text-rose-300">Feature</span>
                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="inline-flex items-center rounded-lg border border-amber-500/50 bg-gradient-to-r from-amber-500 to-rose-500 px-4 py-1.5 text-xs font-bold text-white shadow-lg shadow-amber-500/20">Headliner</span>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. The problem: posting into the void (dark)                 -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0f0808] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-spot-cone" style="opacity: 0.8;"><span class="es-spot-dot"></span></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">The grind</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        You put in the work. The algorithm <span class="neon-text">buries it.</span>
                    </h2>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="120">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mb-2 text-4xl font-black text-red-400">7</div>
                        <div class="text-sm text-gray-400">mics a week just to stay sharp</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mb-2 text-4xl font-black text-amber-400">5</div>
                        <div class="text-sm text-gray-400">clubs where you're trying to get regular</div>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mb-2 text-4xl font-black text-rose-400">~3%</div>
                        <div class="text-sm text-gray-400">of your followers actually see your show posts</div>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-400" data-reveal>
                    There is a better way to fill the room.
                    <a href="#features" class="inline-flex items-center gap-1 font-semibold text-amber-400 transition-all hover:gap-2">
                        Here it is
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Features                                                  -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0a0606] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-amber-400 to-rose-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">Your whole week</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything a working comic <span class="neon-text">actually needs</span>
                </h2>
            </div>

            <!-- The Weekly Lineup Tracker -->
            <div class="es-bento group relative mb-4" data-tilt="2.5" data-reveal="panel">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-12">
                    <svg class="es-micstand" viewBox="0 0 44 150" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <rect x="15" y="6" width="14" height="26" rx="7" />
                        <line x1="19" y1="13" x2="25" y2="13" />
                        <line x1="19" y1="19" x2="25" y2="19" />
                        <line x1="19" y1="25" x2="25" y2="25" />
                        <path d="M9 26a13 12 0 0 0 26 0" />
                        <line x1="22" y1="38" x2="22" y2="126" />
                        <line x1="9" y1="140" x2="35" y2="140" />
                        <line x1="22" y1="126" x2="11" y2="140" />
                        <line x1="22" y1="126" x2="33" y2="140" />
                    </svg>
                    <div class="relative grid items-center gap-10 lg:grid-cols-2">
                        <div>
                            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                </svg>
                                Your Weekly Lineup
                            </div>
                            <h3 class="mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">See your whole week.<br>Never double-book again.</h3>
                            <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Running between clubs? Texting yourself set times? One calendar shows every mic, every guest set, every headline - plus your total stage time for the week.</p>
                            <ul class="space-y-3 text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-3">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Track set lengths (tight 5, 10, 15, feature, headline)
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    See your weekly stage time at a glance
                                </li>
                                <li class="flex items-center gap-3">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Syncs with Google Calendar (both ways)
                                </li>
                            </ul>
                        </div>

                        <div class="es-setlist rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-black/40" aria-hidden="true">
                            <div class="mb-4 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">This Week</span>
                                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400"><span data-count-to="47">47</span> min stage time</span>
                            </div>
                            <div class="space-y-3">
                                <div class="es-ai-field flex items-center gap-4 rounded-xl border border-red-200 bg-red-100 p-3 dark:border-red-800/30 dark:bg-red-900/20" style="--i: 0;">
                                    <div class="w-12 text-center"><div class="text-xs font-bold text-red-600 dark:text-red-300">MON</div><div class="text-lg font-bold text-gray-900 dark:text-white">12</div></div>
                                    <div class="flex-1"><div class="font-semibold text-gray-900 dark:text-white">Stand Up NY</div><div class="text-sm text-gray-500 dark:text-gray-400">Open mic · 7 PM signup</div></div>
                                    <div class="inline-flex items-center rounded bg-red-100 px-2 py-1 text-xs font-medium text-red-700 dark:bg-red-900/40 dark:text-red-300">5 min</div>
                                </div>
                                <div class="es-ai-field flex items-center gap-4 rounded-xl border border-amber-200 bg-amber-100 p-3 dark:border-amber-800/30 dark:bg-amber-900/20" style="--i: 1;">
                                    <div class="w-12 text-center"><div class="text-xs font-bold text-amber-600 dark:text-amber-300">WED</div><div class="text-lg font-bold text-gray-900 dark:text-white">14</div></div>
                                    <div class="flex-1"><div class="font-semibold text-gray-900 dark:text-white">Comedy Cellar</div><div class="text-sm text-gray-500 dark:text-gray-400">Guest set · 9:30 PM</div></div>
                                    <div class="inline-flex items-center rounded bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">12 min</div>
                                </div>
                                <div class="es-ai-field flex items-center gap-4 rounded-xl border border-orange-200 bg-orange-100 p-3 dark:border-orange-800/30 dark:bg-orange-900/20" style="--i: 2;">
                                    <div class="w-12 text-center"><div class="text-xs font-bold text-orange-600 dark:text-orange-300">THU</div><div class="text-lg font-bold text-gray-900 dark:text-white">15</div></div>
                                    <div class="flex-1"><div class="font-semibold text-gray-900 dark:text-white">Gotham Comedy</div><div class="text-sm text-gray-500 dark:text-gray-400">Late show · 11 PM</div></div>
                                    <div class="inline-flex items-center rounded bg-orange-100 px-2 py-1 text-xs font-medium text-orange-700 dark:bg-orange-900/40 dark:text-orange-300">10 min</div>
                                </div>
                                <div class="es-ai-field flex items-center gap-4 rounded-xl border border-rose-200 bg-gradient-to-r from-rose-100 to-amber-100 p-3 dark:border-rose-700/40 dark:from-rose-900/30 dark:to-amber-900/30" style="--i: 3;">
                                    <div class="w-12 text-center"><div class="text-xs font-bold text-rose-600 dark:text-rose-300">SAT</div><div class="text-lg font-bold text-gray-900 dark:text-white">17</div></div>
                                    <div class="flex-1"><div class="font-semibold text-gray-900 dark:text-white">Carolines</div><div class="text-sm text-gray-500 dark:text-gray-400">Two shows: 8 PM & 10:30 PM</div></div>
                                    <div class="inline-flex items-center rounded bg-gradient-to-r from-rose-500 to-amber-500 px-2 py-1 text-xs font-bold text-white">Headlining</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="es-glare" aria-hidden="true"></div>
                    <div class="es-ring-glow" aria-hidden="true"></div>
                </div>
            </div>

            <!-- Newsletter + Ticket Sales -->
            <div class="mb-4 grid gap-4 md:grid-cols-2" data-reveal-group="110">
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Direct to Fans
                        </div>
                        <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Instagram buried your post.<br>Email won't.</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">You posted about your show. 3% of your followers saw it. The rest saw ads. With email, everyone who signed up actually gets notified. No algorithm deciding who deserves to see it.</p>

                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-500/20">
                                    <svg aria-hidden="true" class="h-4 w-4 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8" /></svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Headlining Saturday!</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Sent to <span data-count-to="1,247">1,247</span> fans</div>
                                </div>
                            </div>
                            <div class="flex gap-4 text-xs">
                                <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-emerald-500 dark:text-emerald-400"><span data-count-to="68">68</span>%</span> opened</div>
                                <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-amber-500 dark:text-amber-400"><span data-count-to="23">23</span>%</span> clicked</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Sell Tickets
                        </div>
                        <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Your show, your money.<br>Zero platform fees.</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Producing your own show? Sell tickets directly. Money goes straight to your Stripe - we don't take a cut. Your hustle, your earnings.</p>

                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-4 flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Saturday Late Show</span>
                                <span class="text-sm font-semibold text-emerald-500 dark:text-emerald-400"><span data-count-to="73">73</span> sold</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between rounded-lg bg-gray-100 p-2 dark:bg-white/5"><span class="text-sm text-gray-900 dark:text-white">General Admission</span><span class="font-medium text-gray-900 dark:text-white">$20</span></div>
                                <div class="flex items-center justify-between rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-2"><span class="text-sm text-gray-900 dark:text-white">Front Row + Meet & Greet</span><span class="font-medium text-gray-900 dark:text-white">$50</span></div>
                            </div>
                            <div class="mt-4 flex justify-between border-t border-gray-200 pt-3 dark:border-white/10"><span class="text-sm text-gray-500 dark:text-gray-400">Platform fee</span><span class="font-bold text-emerald-500 dark:text-emerald-400">$0</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>

            <!-- Three small features -->
            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40">
                            <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">Built for late nights</h3>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">10:30 show? Midnight mic that runs until 2? We get it. No more calendar apps showing tomorrow's date for tonight's set.</p>
                        <div class="mt-auto inline-flex items-center gap-2 self-start rounded-lg border border-amber-200 bg-amber-100 px-3 py-1.5 dark:border-amber-800/30 dark:bg-amber-900/30">
                            <svg aria-hidden="true" class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="text-sm font-medium text-amber-600 dark:text-amber-300">10:30 PM - 12:30 AM</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40">
                            <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">Booked by a club? It shows up automatically.</h3>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">When a club adds you to their lineup, your schedule updates. No copy-paste. No 'wait, what time did they say?'</p>
                        <div class="mt-auto flex items-center gap-2">
                            <div class="flex h-6 w-6 items-center justify-center rounded bg-amber-100 text-[10px] font-bold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">CC</div>
                            <div class="flex h-6 w-6 items-center justify-center rounded bg-rose-100 text-[10px] font-bold text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">GC</div>
                            <div class="flex h-6 w-6 items-center justify-center rounded bg-red-100 text-[10px] font-bold text-red-700 dark:bg-red-900/40 dark:text-red-300">SU</div>
                            <span class="ml-1 text-xs text-gray-500">+ more</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 dark:bg-orange-900/40">
                            <svg aria-hidden="true" class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">Show graphics in one click</h3>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Download Instagram-ready flyers for any set. Stop begging your friend who 'knows Canva.'</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="flex h-20 w-16 -rotate-3 items-center justify-center rounded-lg border border-orange-500/30 bg-gradient-to-br from-orange-500/40 to-rose-500/40 transition-transform duration-300 group-hover:rotate-0">
                                <svg aria-hidden="true" class="h-6 w-6 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
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
    <!-- 4. The journey                                               -->
    <!-- ============================================================ -->
    @php
        $comedyJourney = [
            ['Grinding the mics', 'Track your spots across every open mic in the city. Know where you\'re signed up tonight.', 'bg-red-100 dark:bg-red-900/30', 'text-red-500 dark:text-red-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Getting regular', 'Guest spots coming in? Track which rooms you\'re regular at and build your schedule.', 'bg-amber-100 dark:bg-amber-900/30', 'text-amber-500 dark:text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />'],
            ['Featuring', '20-30 minute sets opening for headliners. Start selling tickets to your own fans.', 'bg-orange-100 dark:bg-orange-900/30', 'text-orange-500 dark:text-orange-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />'],
            ['Headlining', 'Your name on the marquee. Email your fans directly and sell out your shows.', 'bg-rose-100 dark:bg-rose-900/30', 'text-rose-500 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />'],
            ['On the road', 'Touring clubs across the country? One link shows fans in every city when you\'re coming through.', 'bg-blue-100 dark:bg-blue-900/30', 'text-blue-500 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Improv & Sketch', 'Coordinate your troupe\'s schedule. Everyone knows when the next Harold night is.', 'bg-teal-100 dark:bg-teal-900/30', 'text-teal-500 dark:text-teal-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />'],
        ];
    @endphp
    <section class="bg-white py-20 dark:bg-[#0f0808] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    From open mic to <span class="neon-text">headliner</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Event Schedule grows with your career
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                @foreach ($comedyJourney as [$jTitle, $jDesc, $jChip, $jText, $jIcon])
                    <div data-reveal class="relative rounded-2xl border border-gray-200 bg-white p-6 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04]">
                        <span class="es-spot-marker" aria-hidden="true"></span>
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $jChip }}">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $jText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $jIcon !!}</svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $jTitle }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $jDesc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Perfect for                                               -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all types of <span class="neon-text">comedy</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether you're doing tight fives or touring theaters, Event Schedule has you sorted.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                <div data-reveal>
                    <x-sub-audience-card name="Stand-Up Comics" description="Share your sets and build a following. One link shows fans everywhere you're performing." icon-color="rose" blog-slug="for-stand-up-comics">
                        <x-slot:icon><svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg></x-slot:icon>
                    </x-sub-audience-card>
                </div>
                <div data-reveal>
                    <x-sub-audience-card name="Improv Performers" description="Promote weekly shows with your troupe. Coordinate Harold nights and jam sessions." icon-color="pink" blog-slug="for-improv-performers">
                        <x-slot:icon><svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg></x-slot:icon>
                    </x-sub-audience-card>
                </div>
                <div data-reveal>
                    <x-sub-audience-card name="Sketch Comedy Groups" description="Coordinate ensemble schedules and share show runs. Everyone knows when the next performance is." icon-color="fuchsia" blog-slug="for-sketch-comedy-groups">
                        <x-slot:icon><svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" /></svg></x-slot:icon>
                    </x-sub-audience-card>
                </div>
                <div data-reveal>
                    <x-sub-audience-card name="Open Mic Regulars" description="Track spots across multiple venues. Never double-book a mic night again." icon-color="purple" blog-slug="for-open-mic-comics">
                        <x-slot:icon><svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot:icon>
                    </x-sub-audience-card>
                </div>
                <div data-reveal>
                    <x-sub-audience-card name="Touring Headliners" description="Share tour dates with fans across the country. One link for your entire run." icon-color="violet" blog-slug="for-touring-comedians">
                        <x-slot:icon><svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot:icon>
                    </x-sub-audience-card>
                </div>
                <div data-reveal>
                    <x-sub-audience-card name="Comedy Hosts & MCs" description="Showcase hosting gigs and show bookers your availability. Build your reputation as the go-to host." icon-color="amber" blog-slug="for-comedy-podcasters">
                        <x-slot:icon><svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg></x-slot:icon>
                    </x-sub-audience-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. How it works                                              -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-white py-20 dark:bg-[#0a0606] lg:py-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-spot-cone"><span class="es-spot-dot"></span></div>
        </div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Three steps. More butts in <span class="neon-text">seats.</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                <div class="text-center" data-reveal>
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 to-red-700 text-xl font-bold text-white shadow-lg shadow-red-600/25">1</div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Add your sets</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Import from Google Calendar or add your mics, guest sets, and headlining gigs.</p>
                </div>
                <div class="text-center" data-reveal>
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-600 to-amber-700 text-xl font-bold text-white shadow-lg shadow-amber-600/25">2</div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Share one link</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Drop it in your bio. Fans see all your upcoming shows in one place.</p>
                </div>
                <div class="text-center" data-reveal>
                    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-600 to-rose-700 text-xl font-bold text-white shadow-lg shadow-rose-600/25">3</div>
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">Fill the room</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Fans follow you and get notified. No more posting into the algorithm void.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key <span class="neon-text">features</span></h2>
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
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium es-amber-link hover:underline">
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
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related <span class="neon-text">pages</span></h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-musicians', 'Musicians'], ['/for-magicians', 'Magicians'], ['/for-spoken-word', 'Spoken Word Artists'], ['/for-theater-performers', 'Theater Performers']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group es-amber-hover flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="es-amber-hover-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-amber-hover-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium es-amber-link hover:underline">
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
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="neon-text">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything comedians ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for comedians?', 'Yes. Event Schedule is free forever for sharing your show dates and building a fan following. Ticketing and newsletters are available on the Pro and Enterprise plans, with zero platform fees on any ticket sales.'],
                    ['Can I sell tickets to my comedy shows?', 'Yes. Connect your Stripe account and sell tickets directly from your schedule. Create multiple ticket types like general admission, VIP, and early bird. Every ticket includes a QR code for check-in at the door. Zero platform fees - you only pay Stripe\'s standard processing.'],
                    ['How do fans know when I have a show near them?', 'Fans follow your schedule and get notified when you add new shows. You can also send newsletters directly to your followers with upcoming dates. Share your schedule link in your social bios, on podcasts, or anywhere fans find you.'],
                    ['Can comedy clubs add me to their lineup?', 'Yes. When a comedy club adds you to their event on Event Schedule, the show automatically appears on your schedule too. No need to add the same gig in two places. Both calendars stay in sync so your fans always see your latest bookings.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq es-amber-hover overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
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
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0f0808] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-amber-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-spot-cone" style="opacity: 0.85;"><span class="es-spot-dot"></span></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your fans want to see you. <span class="neon-text">Give them one link.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        No catch. No 'premium tier.' Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 px-8 py-4 text-lg font-semibold text-black shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get your link
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
