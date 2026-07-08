<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Watch Parties | Hosting Software</x-slot>
    <x-slot name="description">Free, open-source watch party scheduling software with registration, ticketing, and email notifications. Works with any streaming platform. Zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Watch Parties</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Watch Parties",
        "description": "Free, open-source watch party scheduling software with registration, ticketing, and email notifications. Works with any streaming platform. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Watch Party Hosts"
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
                "name": "What streaming platforms work with watch parties?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a streaming or meeting link. YouTube, Twitch, Discord, Zoom, and custom streaming solutions all work. Event Schedule is platform-agnostic - just paste your link and viewers join from your schedule."
                }
            },
            {
                "@type": "Question",
                "name": "Can I charge for watch party access?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up paid registration with Stripe for premium screenings, exclusive premieres, or VIP watch parties. You keep 100% of the ticket revenue - Event Schedule charges zero platform fees. Stripe charges its standard processing fee (2.9% + $0.30)."
                }
            },
            {
                "@type": "Question",
                "name": "Can I schedule recurring watch parties?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up weekly movie nights, monthly documentary screenings, or any recurring schedule. Viewers can follow your schedule and get notified whenever you add new screenings. Your full watch party calendar lives on one shareable page. Screenings also sync with Google Calendar."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free for hosting watch parties?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free, open-source watch party hosting software. The free plan includes unlimited events, viewer email notifications, follower features, and Google Calendar sync. There are zero platform fees on ticket sales at any plan level. You only pay Stripe's standard processing fee if you sell tickets. You can also selfhost Event Schedule on your own server."
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
        "name": "Event Schedule for Watch Parties",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Watch Party Scheduling Software",
        "operatingSystem": "Web",
        "description": "Schedule watch parties with built-in registration, viewer notifications, ticketing, and streaming link integration. Works with YouTube, Twitch, Discord, and any platform.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Email notifications to registered viewers",
            "One registration link for all watch parties",
            "Zero-fee ticket sales for paid screenings",
            "Google Calendar two-way sync",
            "Works with YouTube, Twitch, Discord, any platform",
            "Recurring watch party series scheduling",
            "Viewer registration management",
            "Follower notifications for new screenings",
            "Open source watch party platform",
            "Selfhosted watch party scheduling option"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "watch party platform, schedule watch parties, virtual watch party, online watch party hosting, group streaming events, watch party ticketing, movie night scheduling, free watch party app",
        "screenshot": "{{ asset('images/social/for-watch-parties.png') }}",
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
        "name": "How to host a watch party with Event Schedule",
        "description": "Three steps to schedule and host your watch party online.",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Create your screening",
                "text": "Add your date, streaming link, and tickets. Set up free or paid registration."
            },
            {
                "@type": "HowToStep",
                "name": "Share your link",
                "text": "Viewers register. Send viewing guides before the screening."
            },
            {
                "@type": "HowToStep",
                "name": "Watch together",
                "text": "Stream to your community. Discuss, react, and enjoy together."
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
        /* For-watch-parties "The Screening" styles. The shared es-* motion system
           lives in marketing.css; this holds the cinema glow gradient, the
           drifting now-playing card, and the flickering-screens motif. */
        .text-gradient-watchparty {
            background: linear-gradient(135deg, #dc2626, #f97316, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(220, 38, 38, 0.3);
        }
        .dark .text-gradient-watchparty {
            background: linear-gradient(135deg, #f87171, #fb923c, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(248, 113, 113, 0.3);
        }
        @keyframes es-wp-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-wp-float { animation: es-wp-float 6s ease-in-out infinite; }

        /* Screening motif: a wall of screens flickers to life in a wave,
           like everyone tuning in for the watch party. */
        .es-screen { display: flex; align-items: center; }
        .es-screen-tile {
            flex: 0 0 auto;
            width: 22px; height: 14px; border-radius: 3px;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.55), rgba(245, 158, 11, 0.55));
            animation: es-screen-flicker var(--sc-dur, 3s) ease-in-out infinite;
            animation-delay: var(--sc-delay, 0s);
        }
        @keyframes es-screen-flicker {
            0%, 100% { opacity: 0.18; }
            45% { opacity: 0.95; box-shadow: 0 0 10px rgba(239, 68, 68, 0.5); }
            55% { opacity: 0.6; }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-wp-float, .es-screen-tile { animation: none !important; }
            .es-screen-tile { opacity: 0.5; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: everyone watches together                          -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(220, 38, 38, 0.28), rgba(220, 38, 38, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(249, 115, 22, 0.28), rgba(249, 115, 22, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(245, 158, 11, 0.14), rgba(245, 158, 11, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Wall of screens along the bottom edge -->
            <div class="es-screen absolute bottom-0 left-0 right-0 hidden h-20 items-center justify-center gap-2 px-8 pb-6 opacity-40 md:flex" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                @for ($i = 0; $i < 24; $i++)
                    @php $dur = 2.6 + ($i % 5) * 0.3; $delay = ($i % 8) * 0.17; @endphp
                    <span class="es-screen-tile" style="--sc-dur: {{ $dur }}s; --sc-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Hosts, Communities & Creators</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Schedule watch parties that fill up.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-watchparty">Free. No platform fees.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-4 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                The free watch party platform for premiere screenings, movie nights, and group viewing events. Manage registrations, send viewer emails, and share one link with your community.
            </p>
            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-base text-gray-400 dark:text-gray-500">
                Event Schedule is a free, open-source watch party platform with built-in registration, viewer email notifications, paid ticketing, and Google Calendar sync. Host watch parties online with zero platform fees.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#journey" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it grows
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-red-600 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-red-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-red-500/40">
                    Create your watch party schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Watch-party-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Premiere Screenings', 'Movie Nights', 'Sports Watch Parties', 'Series Finales', 'Documentary Screenings', 'Gaming Events', 'Reaction Streams', 'Marathon Nights'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-100/80 px-4 py-1.5 text-xs font-semibold text-red-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-red-400 to-orange-400"></span>
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
                    <div class="mb-2 text-4xl font-black text-red-500 dark:text-red-400">~<span data-count-to="92">92</span>%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">of viewers prefer watching with others over solo</div>
                </div>
                <div data-reveal class="border-gray-200 p-6 dark:border-white/5 md:border-x">
                    <div class="mb-2 text-4xl font-black text-orange-500 dark:text-orange-400">2x</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">more engagement with scheduled group viewings vs solo</div>
                </div>
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-emerald-500 dark:text-emerald-400">$0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">platform fees on paid watch party events</div>
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
                    Everything you need to host <span class="text-gradient-watchparty">watch parties</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Email viewers (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 dark:border-red-800/30 dark:bg-red-900/40 dark:text-red-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Email Viewers
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Email viewers before screenings</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Send reminders, share viewing guides, and follow up with discussion prompts. Your audience, your inbox.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Session reminders</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Viewing guides</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Discussion prompts</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-red-300 bg-gradient-to-br from-red-100 to-orange-100 p-4 dark:border-red-400/30 dark:from-red-950 dark:to-orange-950">
                                        <div class="mb-3 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-red-500 to-orange-500 text-sm font-semibold text-white">WP</div>
                                            <div><div class="text-sm font-semibold text-gray-900 dark:text-white">Watch Party Host</div><div class="text-xs text-red-600 dark:text-red-300">Screening reminder</div></div>
                                        </div>
                                        <div class="rounded-xl border border-red-400/20 bg-gradient-to-br from-red-600/30 to-orange-600/30 p-3 text-center">
                                            <div class="mb-1 text-xs font-semibold text-gray-900 dark:text-white">FRIDAY AT 8 PM</div>
                                            <div class="text-sm font-bold text-red-700 dark:text-red-300">Movie Night</div>
                                            <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Grab your snacks and join us</div>
                                        </div>
                                        <div class="mt-3 flex gap-4 text-xs">
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-emerald-500 dark:text-emerald-400">72%</span> opened</div>
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-amber-500 dark:text-amber-400">38%</span> clicked</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Sell tickets to premium screenings -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sell tickets to premium screenings</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Charge for paid premieres or exclusive screenings. 100% of Stripe payments go to you. See all <a href="{{ marketing_url('/features/ticketing') }}" class="text-emerald-600 underline hover:no-underline dark:text-emerald-400">ticketing features</a>.</p>
                        <div class="mt-auto rounded-xl border border-emerald-400/30 bg-emerald-500/15 p-4" aria-hidden="true">
                            <div class="mb-3 text-center">
                                <div class="text-xs text-emerald-700 dark:text-emerald-300">You keep</div>
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">100%</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">of ticket sales</div>
                            </div>
                            <div class="border-t border-emerald-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">$0</span>
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
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            Share Link
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One link for all your watch parties</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Put it on your website, social profiles, or community channels. All your watch parties in one place.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-orange-400/30 bg-orange-500/20 p-2">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-orange-500 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span class="truncate font-mono text-xs text-gray-900 dark:text-white">yourparty.eventschedule.com</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1 text-center">
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-orange-600 dark:bg-white/5 dark:text-orange-300">Website</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-orange-600 dark:bg-white/5 dark:text-orange-300">Social</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-orange-600 dark:bg-white/5 dark:text-orange-300">Community</div>
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
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    Any Platform
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Works with any streaming platform</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">YouTube, Twitch, Discord, or any custom streaming link. Add your URL and viewers join from your schedule. Learn more about <a href="{{ marketing_url('/features/online-events') }}" class="text-sky-600 underline hover:no-underline dark:text-sky-400">online event features</a>.</p>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="flex items-center gap-4">
                                    <div class="w-36 rounded-xl border border-sky-400/30 bg-sky-500/15 p-4">
                                        <div class="mb-2 text-center text-xs font-semibold text-sky-600 dark:text-sky-300">Your Schedule</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-gray-300 dark:bg-white/20"></div>
                                            <div class="h-2 w-3/4 rounded bg-sky-400/40"></div>
                                        </div>
                                        <div class="mt-3 rounded-lg border border-sky-400/30 bg-sky-400/20 p-2">
                                            <div class="text-center text-[10px] font-medium text-sky-800 dark:text-white">Movie Night</div>
                                            <div class="mt-0.5 text-center text-[8px] text-sky-700 dark:text-sky-300">Fri 8:00 PM</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                        <span class="text-[10px] text-sky-500 dark:text-sky-400">join link</span>
                                    </div>
                                    <div class="w-36 rounded-xl border border-gray-300 bg-gray-200 p-4 dark:border-white/20 dark:bg-white/10">
                                        <div class="mb-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">Streaming</div>
                                        <div class="space-y-2 text-center">
                                            <div class="es-ai-field rounded bg-red-400/20 p-1.5 text-[10px] text-red-700 dark:text-red-300" style="--i: 0;">YouTube</div>
                                            <div class="es-ai-field rounded bg-orange-400/20 p-1.5 text-[10px] text-orange-700 dark:text-orange-300" style="--i: 1;">Twitch</div>
                                            <div class="es-ai-field rounded bg-sky-400/20 p-1.5 text-[10px] text-sky-700 dark:text-sky-300" style="--i: 2;">Discord</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Recurring watch party series -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Recurring
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Recurring watch party series</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Weekly movie nights or monthly screenings. Set it once and let viewers follow along.</p>
                        <div class="mt-auto rounded-xl border border-amber-400/30 bg-amber-500/15 p-3" aria-hidden="true">
                            <div class="space-y-1.5">
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/20 p-1.5" style="--i: 0;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] font-medium text-gray-900 dark:text-white">Fri - Movie Night</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 1;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Fri - Movie Night</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 2;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Fri - Movie Night</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-amber-400/10 p-1.5" style="--i: 3;"><div class="h-1.5 w-1.5 rounded-full bg-amber-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Fri - Movie Night</span></div>
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
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Google Calendar sync</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync. Screenings appear on your calendar automatically.</p>
                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="w-20 rounded-xl border border-blue-400/30 bg-blue-500/15 p-3">
                                <div class="mb-1 text-center text-[10px] text-blue-600 dark:text-blue-300">Schedule</div>
                                <div class="space-y-1">
                                    <div class="es-sync-dot h-1.5 rounded bg-red-400/60"></div>
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

                <!-- Viewers follow (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-red-200 bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 dark:border-red-800/30 dark:bg-red-900/40 dark:text-red-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Followers
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Viewers follow your schedule</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Followers get notified when you schedule new watch parties.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="flex items-center justify-center">
                                <div class="flex -space-x-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-red-500 to-orange-500 text-xs text-white dark:border-[#0a0a0f]">A</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-orange-500 to-amber-500 text-xs text-white dark:border-[#0a0a0f]">B</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-amber-500 to-red-500 text-xs text-white dark:border-[#0a0a0f]">C</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gray-300 text-xs text-gray-600 dark:border-[#0a0a0f] dark:bg-white/20 dark:text-white">+156</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center text-xs text-red-600 dark:text-red-300">159 following your watch parties</div>
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
            ['First watch party', 'Share a link and host your first group screening. Free registration gets your viewers in the door.', 'border-red-500/20 bg-red-500/10', 'text-red-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Recurring movie nights', 'Set up weekly or monthly screenings. Your viewers follow along and get notified.', 'border-orange-500/20 bg-orange-500/10', 'text-orange-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />'],
            ['Paid premiere screenings', 'Start charging for premium access. Sell tickets with zero platform fees.', 'border-emerald-500/20 bg-emerald-500/10', 'text-emerald-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Multi-timezone events', 'Viewers see screening times in their local timezone. No confusion, no missed showings.', 'border-sky-500/20 bg-sky-500/10', 'text-sky-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Community series', 'Build a following. Followers get notified when you announce new screenings.', 'border-teal-500/20 bg-teal-500/10', 'text-teal-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'],
            ['Festival watch parties', 'Multi-day film festivals or marathon screening events. Group events by category with sub-schedules.', 'border-amber-500/20 bg-amber-500/10', 'text-amber-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />'],
        ];
    @endphp
    <section id="journey" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(220, 38, 38, 0.26), rgba(220, 38, 38, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(249, 115, 22, 0.2), rgba(249, 115, 22, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-screen absolute bottom-0 left-0 right-0 flex h-16 items-center justify-center gap-2 px-8 pb-3 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                    @for ($i = 0; $i < 24; $i++)
                        @php $dur = 2.6 + ($i % 5) * 0.3; $delay = ($i % 8) * 0.17; @endphp
                        <span class="es-screen-tile" style="--sc-dur: {{ $dur }}s; --sc-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        From your first watch party to a <span class="text-gradient-watchparty">community screening series</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Event Schedule grows with your watch party program.
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
                    Watch party software for <span class="text-gradient-watchparty">every community</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether it's a film club or a sports viewing party, Event Schedule works for you. Also see Event Schedule for <a href="{{ marketing_url('/for-live-concerts') }}" class="text-gray-600 underline hover:no-underline dark:text-gray-300">Live Concerts</a> and <a href="{{ marketing_url('/for-virtual-conferences') }}" class="text-gray-600 underline hover:no-underline dark:text-gray-300">Virtual Conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Film Clubs & Cinephiles -->
                <x-sub-audience-card
                    name="Film Clubs & Cinephiles"
                    description="Use Event Schedule for classic film screenings, director retrospectives, and themed movie nights. Build a community around your love of cinema."
                    icon-color="amber"
                    blog-slug="for-film-clubs-cinephiles"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Content Creators & YouTubers -->
                <x-sub-audience-card
                    name="Content Creators & YouTubers"
                    description="Premiere new videos with your audience using Event Schedule. Host reaction watch-alongs, sell tickets to exclusive screenings, and celebrate milestones together."
                    icon-color="cyan"
                    blog-slug="for-content-creators-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Sports Fan Communities -->
                <x-sub-audience-card
                    name="Sports Fan Communities"
                    description="Game day watch parties, playoff screenings, and draft night events. Schedule group viewing events and bring fans together for every big moment."
                    icon-color="sky"
                    blog-slug="for-sports-fan-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Gaming Communities -->
                <x-sub-audience-card
                    name="Gaming Communities"
                    description="Esports watch parties, game launch events, and tournament screenings. Watch and chat with your gaming community."
                    icon-color="teal"
                    blog-slug="for-gaming-community-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Corporate & Team Building -->
                <x-sub-audience-card
                    name="Corporate & Team Building"
                    description="Virtual team movie nights, company screening events, and remote team bonding activities."
                    icon-color="emerald"
                    blog-slug="for-corporate-team-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Education & Documentary Groups -->
                <x-sub-audience-card
                    name="Education & Documentary Groups"
                    description="Documentary screenings with discussion, educational film series, and classroom viewing events."
                    icon-color="blue"
                    blog-slug="for-education-documentary-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
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
            ['1', 'Create your screening', 'Add your date, streaming link, and tickets. Set up free or paid registration.'],
            ['2', 'Share your link', 'Viewers register. Send viewing guides before the screening.'],
            ['3', 'Watch together', 'Stream to your community. Discuss, react, and enjoy together.'],
        ];
    @endphp
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    How to host a watch party online in <span class="text-gradient-watchparty">three steps</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-red-600 to-orange-600 text-xl font-bold text-white shadow-lg shadow-red-600/25">
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
                @foreach ([['/for-live-concerts', 'Live Concerts'], ['/for-bars', 'Bars'], ['/for-online-classes', 'Online Classes'], ['/for-live-qa-sessions', 'Live Q&A Sessions']] as [$relHref, $relName])
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
    @php
        $faqLink = 'text-red-600 underline hover:no-underline dark:text-red-400';
        $faqs = [
            ['What streaming platforms work with watch parties?', 'Any platform that gives you a streaming or meeting link. YouTube, Twitch, Discord, Zoom, and custom streaming solutions all work. Event Schedule is platform-agnostic - just paste your link and viewers join from your schedule. Learn more about <a href="'.marketing_url('/features/online-events').'" class="'.$faqLink.'">online event features</a>.'],
            ['Can I charge for watch party access?', 'Yes. Set up paid registration with Stripe for premium screenings, exclusive premieres, or VIP watch parties. You keep 100% of the ticket revenue - Event Schedule charges zero platform fees. Stripe charges its standard processing fee (2.9% + $0.30). See all <a href="'.marketing_url('/features/ticketing').'" class="'.$faqLink.'">ticketing features</a>.'],
            ['Can I schedule recurring watch parties?', 'Yes. Set up weekly movie nights, monthly documentary screenings, or any recurring schedule. Viewers can follow your schedule and get notified whenever you add new screenings. Your full watch party calendar lives on one shareable page. Screenings also sync with <a href="'.marketing_url('/features/calendar-sync').'" class="'.$faqLink.'">Google Calendar</a>.'],
            ['Is Event Schedule free for hosting watch parties?', 'Yes. Event Schedule is free, open-source watch party hosting software. The free plan includes unlimited events, viewer email notifications, follower features, and Google Calendar sync. There are zero platform fees on ticket sales at any plan level. You only pay Stripe\'s standard processing fee if you sell tickets. You can also <a href="https://github.com/eventschedule/eventschedule" class="'.$faqLink.'">selfhost Event Schedule</a> on your own server.'],
        ];
    @endphp
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-watchparty">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything watch party hosts ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{!! $a !!}</p>
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-red-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(220, 38, 38, 0.3), rgba(220, 38, 38, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-screen absolute bottom-0 left-0 right-0 flex h-14 items-center justify-center gap-2 px-8 pb-3 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 20%, black 80%, transparent);">
                        @for ($i = 0; $i < 20; $i++)
                            @php $dur = 2.6 + ($i % 5) * 0.3; $delay = ($i % 8) * 0.17; @endphp
                            <span class="es-screen-tile" style="--sc-dur: {{ $dur }}s; --sc-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your screenings. Your community. <span class="text-gradient-watchparty">No middleman.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        The free watch party app with registration, ticketing, and viewer notifications. No platform fees. Open source.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-party" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-red-600 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-red-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-red-500/40">
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
