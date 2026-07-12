<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Live Concerts | Promote Your Shows</x-slot>
    <x-slot name="description">Stream live concerts to fans worldwide. Sell virtual tickets alongside venue tickets, email fans directly, and manage your schedule. Zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Live Concerts</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Live Concerts",
        "description": "Stream live concerts to fans worldwide. Sell virtual tickets alongside venue tickets, email fans directly, and manage your streaming schedule. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Live Concert Streamers"
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
                "name": "Do I need special equipment to stream a live concert?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Event Schedule works with any streaming platform. Stream from your phone with Instagram Live, use OBS with YouTube Live or Twitch, or set up a multi-camera production. Just add your streaming link to the event."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell virtual tickets and venue tickets for the same show?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Create multiple ticket types for each event. Fans choose between attending in person or watching the livestream. You keep 100% of the revenue (minus standard Stripe processing fees)."
                }
            },
            {
                "@type": "Question",
                "name": "What streaming platforms does Event Schedule work with?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a streaming URL. YouTube Live, Twitch, Instagram Live, Facebook Live, Vimeo, custom RTMP servers, and more. Event Schedule is platform-agnostic."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule really free for streaming concerts?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The free plan includes unlimited events, fan email notifications, and follower features. There are zero platform fees on ticket sales at any plan level. Stripe charges its standard processing fee (2.9% + $0.30)."
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
        "name": "Event Schedule for Live Concerts",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Live Concert Streaming & Scheduling Software",
        "operatingSystem": "Web",
        "description": "Stream live concerts to fans worldwide. Sell virtual tickets alongside venue tickets, email fans directly, and manage your streaming schedule.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Email fans before and after livestreams",
            "Virtual and in-person dual ticketing",
            "One link for all shows",
            "Works with YouTube Live, Twitch, Instagram Live",
            "Recurring show series scheduling",
            "Google Calendar two-way sync",
            "Fan follower notifications",
            "Zero platform fees on ticket sales"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "live concert streaming, virtual concert tickets, livestream concerts",
        "screenshot": "{{ asset('images/social/for-live-concerts.png') }}",
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
        /* For-live-concerts "Live On Stage" styles. The shared es-* motion system
           lives in marketing.css; this page owns the crowd-of-phone-lights motif
           (warm lighters held up over a dark crowd), a livestream screen frame,
           a pulsing LIVE badge, dual-ticket type chips, and the rose accent
           recolor of the hard-coded blue links. */
        .text-gradient-concert {
            background: linear-gradient(135deg, #e11d48, #f59e0b, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(225, 29, 72, 0.3);
        }
        .dark .text-gradient-concert {
            background: linear-gradient(135deg, #fb7185, #fbbf24, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(251, 113, 133, 0.3);
        }

        /* Crowd of phone lights: warm dots drifting and swaying above a dark
           crowd-silhouette edge (lighters held up at a concert). */
        .es-crowd {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            -webkit-mask-image: linear-gradient(to right, transparent, #000 12%, #000 88%, transparent);
            mask-image: linear-gradient(to right, transparent, #000 12%, #000 88%, transparent);
        }
        .es-phone-light {
            position: absolute;
            border-radius: 9999px;
            background: radial-gradient(circle, #fde9b8 0%, #f59e0b 48%, rgba(245, 158, 11, 0) 72%);
            box-shadow: 0 0 7px 1.5px rgba(245, 158, 11, 0.5);
            opacity: 0.85;
            animation: es-crowd-sway var(--sway-dur, 5s) ease-in-out infinite alternate;
            animation-delay: var(--sway-delay, 0s);
            will-change: transform, opacity;
        }
        .es-phone-light--rose {
            background: radial-gradient(circle, #fecdd3 0%, #fb7185 48%, rgba(251, 113, 133, 0) 72%);
            box-shadow: 0 0 7px 1.5px rgba(251, 113, 133, 0.5);
        }
        @keyframes es-crowd-sway {
            0%   { transform: translate3d(-2px, 3px, 0) scale(0.9); opacity: 0.5; }
            50%  { opacity: 0.95; }
            100% { transform: translate3d(2px, -5px, 0) scale(1.06); opacity: 0.8; }
        }
        .es-crowd-edge {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 52px;
            color: rgba(18, 7, 14, 0.16);
            background:
                radial-gradient(24px 30px at 50% 100%, currentColor 60%, transparent 63%) 0 0 / 52px 52px repeat-x,
                radial-gradient(19px 24px at 50% 100%, currentColor 60%, transparent 63%) 26px 0 / 44px 52px repeat-x,
                linear-gradient(currentColor, currentColor) bottom / 100% 16px no-repeat;
        }
        .dark .es-crowd-edge { color: rgba(0, 0, 0, 0.5); }
        .es-crowd--band .es-crowd-edge { color: rgba(0, 0, 0, 0.5); }

        /* Livestream screen frame: stage-through-screen POV (bezel + glare) */
        .es-stream-screen {
            position: relative;
            border-radius: 1.35rem;
            padding: 8px;
            background: linear-gradient(160deg, #2b2b31, #131316);
            box-shadow: 0 18px 34px -14px rgba(0, 0, 0, 0.55), inset 0 0 0 1px rgba(255, 255, 255, 0.07);
        }
        .dark .es-stream-screen {
            background: linear-gradient(160deg, #2e2e35, #0d0d10);
            box-shadow: 0 18px 34px -14px rgba(0, 0, 0, 0.7), inset 0 0 0 1px rgba(255, 255, 255, 0.05);
        }
        .es-stream-glare {
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            z-index: 2;
            background: linear-gradient(115deg, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0.05) 17%, rgba(255, 255, 255, 0) 42%);
            mix-blend-mode: screen;
        }

        /* Pulsing LIVE badge for the hero livestream mock */
        .es-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            flex: none;
            border-radius: 9999px;
            padding: 2px 7px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #dc2626;
            background: rgba(239, 68, 68, 0.14);
        }
        .dark .es-live-badge { color: #f87171; background: rgba(239, 68, 68, 0.2); }
        .es-live-dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background: #ef4444;
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.55);
            animation: es-live-pulse 1.6s ease-out infinite;
        }
        @keyframes es-live-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.55); }
            70%  { box-shadow: 0 0 0 7px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Dual-ticket type chips (in-person venue vs virtual stream) */
        .es-tix-chip {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            border-radius: 9999px;
            padding: 1px 6px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            line-height: 1.5;
        }
        .es-tix-chip svg { width: 9px; height: 9px; }
        .es-tix-chip--venue { color: #059669; background: rgba(16, 185, 129, 0.16); }
        .dark .es-tix-chip--venue { color: #34d399; background: rgba(16, 185, 129, 0.22); }
        .es-tix-chip--virtual { color: #d97706; background: rgba(245, 158, 11, 0.16); }
        .dark .es-tix-chip--virtual { color: #fbbf24; background: rgba(245, 158, 11, 0.22); }

        /* Rose accent (recolored from the hard-coded blue) for the "See all"
           links and related-page card hovers. */
        .es-accent-link { color: #e11d48; transition: color 0.2s ease; }
        .es-accent-link:hover { color: #be123c; }
        .dark .es-accent-link { color: #fb7185; }
        .dark .es-accent-link:hover { color: #fda4af; }
        .es-related-card:hover {
            border-color: #fda4af;
            background-color: #fff1f2;
        }
        .dark .es-related-card:hover {
            border-color: rgba(251, 113, 133, 0.3);
            background-color: rgba(251, 113, 133, 0.06);
        }
        .es-related-card:hover .es-related-title,
        .es-related-card:hover .es-related-arrow { color: #e11d48; }
        .dark .es-related-card:hover .es-related-title,
        .dark .es-related-card:hover .es-related-arrow { color: #fb7185; }

        @media (prefers-reduced-motion: reduce) {
            .es-phone-light,
            .es-live-dot { animation: none !important; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: stream to every screen                              -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(225, 29, 72, 0.3), rgba(225, 29, 72, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(245, 158, 11, 0.3), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(234, 88, 12, 0.14), rgba(234, 88, 12, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Crowd of phone lights held up over a dark crowd silhouette -->
            <div class="es-crowd hidden md:flex" style="height: 12rem;" aria-hidden="true">
                @for ($i = 0; $i < 44; $i++)
                    @php
                        $lx = round(fmod($i * 6.83 + 3, 100), 1);
                        $by = 48 + (($i * 31) % 128);
                        $sz = 5 + ($i % 3);
                        $du = 3.4 + ($i % 7) * 0.5;
                        $de = round(($i % 13) * 0.27, 2);
                    @endphp
                    <span class="es-phone-light @if ($i % 4 === 0) es-phone-light--rose @endif" style="left: {{ $lx }}%; bottom: {{ $by }}px; width: {{ $sz }}px; height: {{ $sz }}px; --sway-dur: {{ $du }}s; --sway-delay: {{ $de }}s;"></span>
                @endfor
                <div class="es-crowd-edge"></div>
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-rose-500 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Artists, Bands & Promoters Streaming Live</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Stream live concerts to every screen.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-concert">No audience limit.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-4 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From intimate acoustic sets to arena livestreams. Sell virtual tickets, reach fans worldwide, and keep 100% of the revenue.
            </p>
            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-base text-gray-400 dark:text-gray-500">
                The live concert streaming platform with built-in virtual ticket sales, fan email notifications, and scheduling for bands, solo artists, and promoters.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#journey" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it scales
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-rose-600 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-rose-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-rose-500/40">
                    Create your concert schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Concert-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Acoustic Sets', 'Rock Shows', 'Jazz Nights', 'Festival Streams', 'Album Release Shows', 'DJ Sets', 'Pay-Per-View', 'Tour Streams'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-100/80 px-4 py-1.5 text-xs font-semibold text-rose-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-rose-400 to-amber-400"></span>
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
                    <div class="mb-2 text-4xl font-black text-rose-500 dark:text-rose-400"><span data-count-to="73">73</span>%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">of music fans have watched a livestreamed concert</div>
                </div>
                <div data-reveal class="border-gray-200 p-6 dark:border-white/5 md:border-x">
                    <div class="mb-2 text-4xl font-black text-amber-500 dark:text-amber-400">4x</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">wider reach with virtual + in-person tickets</div>
                </div>
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-orange-500 dark:text-orange-400">$0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">platform fees on virtual ticket sales</div>
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
                    Everything you need to stream <span class="text-gradient-concert">live concerts</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Email fans (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Email Fans
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Email fans before your livestream</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Announce upcoming streams, send reminders, and follow up with recordings. Your fans, your inbox.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Show announcements</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Stream reminders</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Recording links</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="es-stream-screen">
                                        <div class="es-stream-glare" aria-hidden="true"></div>
                                        <div class="max-w-xs rounded-2xl border border-rose-300 bg-gradient-to-br from-rose-100 to-amber-100 p-4 dark:border-rose-400/30 dark:from-rose-950 dark:to-amber-950">
                                            <div class="mb-3 flex items-center gap-3">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-amber-500 text-sm font-semibold text-white">LV</div>
                                                <div class="flex-1"><div class="text-sm font-semibold text-gray-900 dark:text-white">Live Venue</div><div class="text-xs text-rose-600 dark:text-rose-300">Tonight's livestream</div></div>
                                                <span class="es-live-badge"><span class="es-live-dot"></span>Live</span>
                                            </div>
                                            <div class="rounded-xl border border-rose-400/20 bg-gradient-to-br from-rose-600/30 to-amber-600/30 p-3 text-center">
                                                <div class="mb-1 text-xs font-semibold text-gray-900 dark:text-white">TONIGHT AT 9 PM</div>
                                                <div class="text-sm font-bold text-rose-700 dark:text-rose-300">Live from The Fillmore</div>
                                                <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Watch from home - $15</div>
                                            </div>
                                            <div class="mt-3 flex gap-4 text-xs">
                                                <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-emerald-500 dark:text-emerald-400">72%</span> opened</div>
                                                <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-amber-500 dark:text-amber-400">48%</span> clicked</div>
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

                <!-- Dual ticketing -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Dual Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sell virtual tickets alongside venue tickets</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">In-person + virtual stream. 100% of Stripe payments go to you. See all <a href="{{ marketing_url('/features/ticketing') }}" class="text-emerald-600 underline hover:no-underline dark:text-emerald-400">ticketing features</a>.</p>
                        <div class="mt-auto rounded-xl border border-emerald-400/30 bg-emerald-500/15 p-4" aria-hidden="true">
                            <div class="mb-3 space-y-2">
                                <div class="es-ai-field flex items-center justify-between rounded-lg bg-emerald-400/20 p-2" style="--i: 0;"><span class="flex items-center gap-2"><span class="text-xs font-medium text-gray-900 dark:text-white">Venue Ticket</span><span class="es-tix-chip es-tix-chip--venue"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6a2.5 2.5 0 010 5.5z"/></svg>In person</span></span><span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">$35</span></div>
                                <div class="es-ai-field flex items-center justify-between rounded-lg bg-amber-400/20 p-2" style="--i: 1;"><span class="flex items-center gap-2"><span class="text-xs font-medium text-gray-900 dark:text-white">Virtual Stream</span><span class="es-tix-chip es-tix-chip--virtual"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>Stream</span></span><span class="text-xs font-semibold text-amber-600 dark:text-amber-400">$15</span></div>
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
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            Share Link
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One link for all your live shows</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Spotify bio, YouTube channel, social profiles. All shows in one place.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-sky-400/30 bg-sky-500/20 p-2">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span class="truncate font-mono text-xs text-gray-900 dark:text-white">yourband.eventschedule.com</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1 text-center">
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Spotify</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">YouTube</div>
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
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    Any Platform
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Works with any streaming platform</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">YouTube Live, Twitch, Instagram Live, custom RTMP. Add your streaming link, fans join from your schedule. Learn more about <a href="{{ marketing_url('/features/online-events') }}" class="text-amber-600 underline hover:no-underline dark:text-amber-400">online event features</a>.</p>
                            </div>
                            <div class="flex items-center justify-center" aria-hidden="true">
                                <div class="flex items-center gap-4">
                                    <div class="w-36 rounded-xl border border-amber-400/30 bg-amber-500/15 p-4">
                                        <div class="mb-2 text-center text-xs font-semibold text-amber-600 dark:text-amber-300">Your Schedule</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-gray-300 dark:bg-white/20"></div>
                                            <div class="h-2 w-3/4 rounded bg-amber-400/40"></div>
                                        </div>
                                        <div class="mt-3 rounded-lg border border-amber-400/30 bg-amber-400/20 p-2">
                                            <div class="text-center text-[10px] font-medium text-amber-800 dark:text-white">Live Concert</div>
                                            <div class="mt-0.5 text-center text-[8px] text-amber-700 dark:text-amber-300">Sat 9:00 PM</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                        <span class="text-[10px] text-amber-500 dark:text-amber-400">stream link</span>
                                    </div>
                                    <div class="w-36 rounded-xl border border-gray-300 bg-gray-200 p-4 dark:border-white/20 dark:bg-white/10">
                                        <div class="mb-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">Streaming</div>
                                        <div class="space-y-2 text-center">
                                            <div class="es-ai-field rounded bg-red-400/20 p-1.5 text-[10px] text-red-700 dark:text-red-300" style="--i: 0;">YouTube Live</div>
                                            <div class="es-ai-field rounded bg-purple-400/20 p-1.5 text-[10px] text-purple-700 dark:text-purple-300" style="--i: 1;">Twitch</div>
                                            <div class="es-ai-field rounded bg-pink-400/20 p-1.5 text-[10px] text-pink-700 dark:text-pink-300" style="--i: 2;">Instagram</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Recurring show series -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-red-200 bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 dark:border-red-800/30 dark:bg-red-900/40 dark:text-red-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Recurring
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Recurring show series</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Weekly live sessions, monthly showcases. Set once, fans follow.</p>
                        <div class="mt-auto rounded-xl border border-red-400/30 bg-red-500/15 p-3" aria-hidden="true">
                            <div class="space-y-1.5">
                                <div class="es-ai-field flex items-center gap-2 rounded bg-red-400/20 p-1.5" style="--i: 0;"><div class="h-1.5 w-1.5 rounded-full bg-red-400"></div><span class="text-[10px] font-medium text-gray-900 dark:text-white">Fri - Live Session</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-red-400/10 p-1.5" style="--i: 1;"><div class="h-1.5 w-1.5 rounded-full bg-red-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Fri - Live Session</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-red-400/10 p-1.5" style="--i: 2;"><div class="h-1.5 w-1.5 rounded-full bg-red-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Fri - Live Session</span></div>
                                <div class="es-ai-field flex items-center gap-2 rounded bg-red-400/10 p-1.5" style="--i: 3;"><div class="h-1.5 w-1.5 rounded-full bg-red-400"></div><span class="text-[10px] text-gray-600 dark:text-gray-300">Fri - Live Session</span></div>
                            </div>
                            <div class="mt-2 text-center text-[10px] text-red-600 dark:text-red-300">Repeats weekly</div>
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
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sync live shows with Google Calendar</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Shows, soundchecks, rehearsals all synced. Two-way sync keeps everything in one place.</p>
                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="w-20 rounded-xl border border-blue-400/30 bg-blue-500/15 p-3">
                                <div class="mb-1 text-center text-[10px] text-blue-600 dark:text-blue-300">Schedule</div>
                                <div class="space-y-1">
                                    <div class="es-sync-dot h-1.5 rounded bg-rose-400/60"></div>
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

                <!-- Fans follow (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Followers
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Fans follow your live shows</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Location-based notifications when you add shows near them.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="flex items-center justify-center">
                                <div class="flex -space-x-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-rose-500 to-amber-500 text-xs text-white dark:border-[#0a0a0f]">A</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-amber-500 to-orange-500 text-xs text-white dark:border-[#0a0a0f]">B</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-orange-500 to-red-500 text-xs text-white dark:border-[#0a0a0f]">C</div>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gray-300 text-xs text-gray-600 dark:border-[#0a0a0f] dark:bg-white/20 dark:text-white">+2k</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center text-xs text-orange-600 dark:text-orange-300">2,147 fans following your schedule</div>
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
            ['Living room sessions', 'First streams from your living room. Share a link and start building your audience.', 'border-rose-500/20 bg-rose-500/10', 'text-rose-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />'],
            ['Local venue streams', 'Hybrid shows with in-person and online audiences. Sell both ticket types.', 'border-amber-500/20 bg-amber-500/10', 'text-amber-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />'],
            ['Multi-camera productions', 'Professional streams with ticketed access. Charge what your production is worth.', 'border-orange-500/20 bg-orange-500/10', 'text-orange-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />'],
            ['Pay-per-view concerts', 'Premium livestream events with global reach. Zero platform fees on every sale.', 'border-red-500/20 bg-red-500/10', 'text-red-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
            ['Festival livestreams', 'Stream your festival sets to fans who couldn\'t make it. Reach a worldwide audience.', 'border-rose-500/20 bg-rose-500/10', 'text-rose-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />'],
            ['Global tours, streamed', 'Every tour stop available to fans worldwide. Build a global audience from every show.', 'border-amber-500/20 bg-amber-500/10', 'text-amber-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
        ];
    @endphp
    <section id="journey" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(225, 29, 72, 0.26), rgba(225, 29, 72, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-crowd es-crowd--band" style="height: 9rem;" aria-hidden="true">
                    @for ($i = 0; $i < 36; $i++)
                        @php
                            $lx = round(fmod($i * 7.19 + 4, 100), 1);
                            $by = 42 + (($i * 29) % 92);
                            $sz = 5 + ($i % 3);
                            $du = 3.6 + ($i % 6) * 0.52;
                            $de = round(($i % 11) * 0.29, 2);
                        @endphp
                        <span class="es-phone-light @if ($i % 4 === 0) es-phone-light--rose @endif" style="left: {{ $lx }}%; bottom: {{ $by }}px; width: {{ $sz }}px; height: {{ $sz }}px; --sway-dur: {{ $du }}s; --sway-delay: {{ $de }}s;"></span>
                    @endfor
                    <div class="es-crowd-edge"></div>
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        From bedroom streams to <span class="text-gradient-concert">sold-out livestreams</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Event Schedule grows with your music career.
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
                    Perfect for every <span class="text-gradient-concert">genre and stage</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether it's an intimate acoustic set or a festival stream, Event Schedule works for you. Also see <a href="{{ marketing_url('/for-musicians') }}" class="text-gray-600 underline hover:no-underline dark:text-gray-300">Event Schedule for Musicians</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Solo Acoustic Artists"
                    description="Intimate living room sessions and acoustic sets streamed to fans everywhere."
                    icon-color="cyan"
                    blog-slug="for-solo-acoustic-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Rock & Pop Bands"
                    description="High-energy performances streamed from venues and studios to fans worldwide."
                    icon-color="teal"
                    blog-slug="for-rock-pop-bands-live"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Jazz & Blues Acts"
                    description="Club sessions and late-night sets for a worldwide audience."
                    icon-color="sky"
                    blog-slug="for-jazz-blues-acts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="DJs & Electronic Artists"
                    description="Live DJ sets, producer sessions, and festival streams for dance music fans."
                    icon-color="blue"
                    blog-slug="for-djs-electronic-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Classical & Orchestra"
                    description="Concert hall performances and recitals for remote audiences worldwide."
                    icon-color="amber"
                    blog-slug="for-classical-orchestra"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Cover & Tribute Bands"
                    description="Fan-favorite shows streamed from bars and venues to audiences everywhere."
                    icon-color="emerald"
                    blog-slug="for-cover-tribute-bands-live"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
            ['1', 'Add your show', 'Set the date, add your streaming link, and create virtual tickets.'],
            ['2', 'Share your link', 'Fans register and get notified when you go live.'],
            ['3', 'Go live', 'Stream to fans everywhere. No platform fees, no middleman.'],
        ];
    @endphp
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Three steps to a <span class="text-gradient-concert">global audience</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-600 to-amber-600 text-xl font-bold text-white shadow-lg shadow-rose-600/25">
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
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium es-accent-link hover:underline">
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
                @foreach ([['/for-musicians', 'Musicians'], ['/for-music-venues', 'Music Venues'], ['/for-djs', 'DJs'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5 es-related-card">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="text-lg font-semibold text-gray-900 transition-colors dark:text-white es-related-title">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 transition-colors es-related-arrow rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium es-accent-link hover:underline">
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
                    Frequently asked <span class="text-gradient-concert">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything artists and promoters ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Do I need special equipment to stream a live concert?', 'No. Event Schedule works with any streaming platform. Stream from your phone with Instagram Live, use OBS with YouTube Live or Twitch, or set up a multi-camera production. Just add your streaming link to the event.'],
                    ['Can I sell virtual tickets and venue tickets for the same show?', 'Yes. Create multiple ticket types for each event. Fans choose between attending in person or watching the livestream. You keep 100% of the revenue (minus standard Stripe processing fees).'],
                    ['What streaming platforms does Event Schedule work with?', 'Any platform that gives you a streaming URL. YouTube Live, Twitch, Instagram Live, Facebook Live, Vimeo, custom RTMP servers, and more. Event Schedule is platform-agnostic.'],
                    ['Is Event Schedule really free for streaming concerts?', 'Yes. The free plan includes unlimited events, fan email notifications, and follower features. There are zero platform fees on ticket sales at any plan level. Stripe charges its standard processing fee (2.9% + $0.30).'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-rose-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(225, 29, 72, 0.3), rgba(225, 29, 72, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-crowd es-crowd--band" style="height: 8rem;" aria-hidden="true">
                        @for ($i = 0; $i < 30; $i++)
                            @php
                                $lx = round(fmod($i * 8.11 + 5, 100), 1);
                                $by = 38 + (($i * 33) % 78);
                                $sz = 5 + ($i % 3);
                                $du = 3.8 + ($i % 6) * 0.48;
                                $de = round(($i % 9) * 0.31, 2);
                            @endphp
                            <span class="es-phone-light @if ($i % 4 === 0) es-phone-light--rose @endif" style="left: {{ $lx }}%; bottom: {{ $by }}px; width: {{ $sz }}px; height: {{ $sz }}px; --sway-dur: {{ $du }}s; --sway-delay: {{ $de }}s;"></span>
                        @endfor
                        <div class="es-crowd-edge"></div>
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your stage reaches every screen. <span class="text-gradient-concert">Zero fees.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stream your shows. Sell virtual tickets. Own your audience. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-band" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-rose-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-rose-500/40">
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
