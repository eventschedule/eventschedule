<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Circus & Acrobatics | Shows & Tickets</x-slot>
    <x-slot name="description">One link for every circus show, aerial class, and festival stop. Sell tickets with zero fees, share rigging specs, and email fans directly. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Circus & Acrobatics</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Circus & Acrobatics",
        "description": "One link for every circus show, aerial class, and festival stop. Sell tickets with zero fees, share rigging specs, and email fans directly. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Circus & Acrobatic Performers"
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
                "name": "Is Event Schedule free for circus performers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your performance schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage tour dates and local shows in one schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. List all your performances in one place - touring shows, local gigs, festival appearances, and private events. Use sub-schedules to organize by show type or tour leg. Fans see everything in one calendar."
                }
            },
            {
                "@type": "Question",
                "name": "How do audiences discover my performances?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your schedule and receive email notifications for new shows. Share your schedule link on social media, your booking page, or embed it on your website. Send newsletters to followers with upcoming tour dates."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to my shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Create different ticket types for different seating or experiences. Zero platform fees - you only pay Stripe's standard processing fees."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell class passes for my aerial or acro classes?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. On the Pro plan you can sell multi-use passes alongside regular tickets - like a 10-class pass or a monthly membership. Passes are redeemable across your events, usage is tracked automatically, and you can set a cancellation deadline for late drops. Zero platform fees apply to passes too."
                }
            },
            {
                "@type": "Question",
                "name": "Can my whole troupe manage one schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Every schedule includes one additional team member for free, and the Enterprise plan supports multiple team members - so your rigger, stage manager, and performers can all update the calendar. Enterprise also adds availability management to track who is free for each booking."
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
        "name": "Event Schedule for Circus & Acrobatics",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Circus Performer Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your circus performances, sell tickets with zero platform fees, and reach your audience directly. Built for aerialists, acrobats, and touring troupes.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Festival circuit tracking for summer tours",
            "Technical rigging specs for venue bookers",
            "Workshop scheduling with multi-class passes",
            "Troupe and ensemble collaboration",
            "Zero-fee ticket sales with QR door check-in",
            "Event planner booking kit",
            "Sub-schedules for shows, classes, and corporate gigs",
            "Direct newsletter announcements to fans",
            "Online event streaming and virtual tickets",
            "Auto-generated show posters"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "circus schedule, acrobat show calendar, circus performer booking, circus event management, free circus scheduling, aerial class passes, circus troupe schedule",
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
        "name": "How circus performers share their schedule with Event Schedule",
        "description": "Get your performance schedule online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your acts",
                "text": "Shows, workshops, festival appearances. Import from Google Calendar or add manually."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share one link",
                "text": "Add to your website, social bios, and booking portfolio. Planners see everything."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Build your following",
                "text": "Fans follow your schedule and get notified about performances in their area."
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
        /* ==============================================================
           For-circus-acrobatics "The Center Ring" styles. The shared
           es-* motion system (aurora, reveals, bento, marquee, finale)
           lives in marketing.css; this block is the page's own ring kit:
           the gold-to-crimson headline gradient, the striped ring curb
           and its medallions, the ringmaster serif announce voice and
           act numerals, the trapeze arc-swing reveal, swaying aerial
           silks, twinkling stars, canvas-stripe dividers, the finale
           silk drop, and their reduced-motion kills. No @supports probes
           here: a hex inside any parenthesized at-rule condition breaks
           Blade.
           ============================================================== */

        /* Headline gradient - gold to crimson */
        .es-circus-gold {
            background: linear-gradient(135deg, #b45309, #ea580c, #b91c3c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .es-circus-gold {
            background: linear-gradient(135deg, #FFD700, #FFA500, #DC143C);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Ringmaster announce voice: serif smallcaps with flanking rules */
        .es-circus-announce {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
            font-family: Georgia, 'Iowan Old Style', 'Palatino Linotype', 'Times New Roman', ui-serif, serif;
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #92400e;
        }
        .es-circus-announce::before,
        .es-circus-announce::after {
            content: "";
            width: 2.5rem;
            height: 1px;
            background: currentColor;
            opacity: 0.45;
            flex-shrink: 1;
        }
        .dark .es-circus-announce { color: #FFD700; }
        .es-band-dark .es-circus-announce,
        .es-finale-panel .es-circus-announce { color: #FFD700; }
        /* Rule-less variant for badges and small captions */
        .es-circus-announce-badge {
            font-family: Georgia, 'Iowan Old Style', 'Times New Roman', ui-serif, serif;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-size: 0.8125rem;
        }

        /* Serif act numerals (this page's sitewide-exclusive signature) */
        .es-circus-numeral {
            font-family: Georgia, 'Iowan Old Style', 'Times New Roman', ui-serif, serif;
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
            color: #b45309;
        }
        .dark .es-circus-numeral,
        .es-band-dark .es-circus-numeral { color: #FFD700; }

        /* Striped ring-curb medallions framing the numerals */
        .es-circus-medal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 9999px;
            background: repeating-conic-gradient(#b91c3c 0deg 15deg, #fdf1e3 15deg 30deg);
            box-shadow: 0 12px 28px -14px rgba(185, 28, 60, 0.5);
        }
        .es-circus-medal-core {
            display: flex;
            align-items: center;
            justify-content: center;
            width: calc(100% - 14px);
            height: calc(100% - 14px);
            border-radius: 9999px;
            background: #ffffff;
        }
        .dark .es-circus-medal { background: repeating-conic-gradient(#DC143C 0deg 15deg, rgba(255, 215, 0, 0.75) 15deg 30deg); }
        .dark .es-circus-medal-core,
        .es-band-dark .es-circus-medal-core,
        .es-finale-panel .es-circus-medal-core { background: #16121a; }
        .es-circus-medal-lg { width: 5.5rem; height: 5.5rem; }

        /* Hero ring curb: a huge striped annulus whose top arc cradles the
           content. Static - never animated, no blur. */
        .es-circus-ring {
            position: absolute;
            left: 50%;
            /* Anchor the arc's crest a fixed 340px above the hero's bottom
               edge so it reads at every viewport height (a percentage
               offset drifts with the svh-driven hero height). */
            bottom: calc(340px - min(1620px, 175vw));
            width: min(1620px, 175vw);
            aspect-ratio: 1 / 1;
            transform: translateX(-50%);
            border-radius: 9999px;
            background: repeating-conic-gradient(#b91c3c 0deg 6deg, #f3ddc9 6deg 12deg);
            opacity: 0.35;
            pointer-events: none;
            -webkit-mask-image: radial-gradient(closest-side, transparent calc(100% - 34px), #000 calc(100% - 33px));
            mask-image: radial-gradient(closest-side, transparent calc(100% - 34px), #000 calc(100% - 33px));
        }
        .dark .es-circus-ring {
            background: repeating-conic-gradient(#DC143C 0deg 6deg, rgba(255, 215, 0, 0.8) 6deg 12deg);
            opacity: 0.2;
        }
        .es-circus-floor {
            position: absolute;
            inset: auto 0 0 0;
            height: 40%;
            pointer-events: none;
            background: radial-gradient(60% 90% at 50% 100%, rgba(245, 158, 11, 0.12), transparent 70%);
        }
        .dark .es-circus-floor { background: radial-gradient(60% 90% at 50% 100%, rgba(255, 215, 0, 0.08), transparent 70%); }

        /* Trapeze arc-swing reveal. transform-origin sits on the always-on
           rule so the pivot holds while the shared transition runs to
           .is-revealed; the :not() rules only override the shared hidden
           transform. Duration/ease/delay come from the shared
           html.es-anim [data-reveal] rule in marketing.css. */
        html.es-anim .es-circus-swing[data-reveal],
        html.es-anim .es-circus-swing-r[data-reveal] { transform-origin: 50% -420px; }
        html.es-anim .es-circus-swing[data-reveal]:not(.is-revealed) {
            opacity: 0;
            transform: rotate(-5deg);
        }
        html.es-anim .es-circus-swing-r[data-reveal]:not(.is-revealed) {
            opacity: 0;
            transform: rotate(5deg);
        }

        /* Aerial silk lines hanging from the top edge */
        .es-circus-silk {
            position: absolute;
            top: 0;
            border-radius: 9999px;
            transform-origin: top center;
            animation: es-circus-silk-sway 6s ease-in-out infinite;
            animation-delay: var(--d, 0s);
            pointer-events: none;
        }
        @keyframes es-circus-silk-sway {
            0%, 100% { transform: translateX(0) scaleY(1); }
            25% { transform: translateX(5px) scaleY(1.02); }
            50% { transform: translateX(-3px) scaleY(0.98); }
            75% { transform: translateX(4px) scaleY(1.01); }
        }

        /* Star sparkles */
        .es-circus-star {
            position: absolute;
            border-radius: 9999px;
            animation: es-circus-star 3s ease-in-out infinite;
            animation-delay: var(--d, 0s);
            pointer-events: none;
        }
        @keyframes es-circus-star {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.25); }
        }

        /* Big-top canvas-stripe dividers between major sections */
        .es-circus-canvas {
            height: 0.75rem;
            background: repeating-linear-gradient(90deg,
                rgba(180, 83, 9, 0.16) 0, rgba(180, 83, 9, 0.16) 18px,
                rgba(185, 28, 60, 0.16) 18px, rgba(185, 28, 60, 0.16) 36px);
            -webkit-mask-image: linear-gradient(to right, transparent, #000 12%, #000 88%, transparent);
            mask-image: linear-gradient(to right, transparent, #000 12%, #000 88%, transparent);
        }
        .dark .es-circus-canvas {
            background: repeating-linear-gradient(90deg,
                rgba(255, 215, 0, 0.14) 0, rgba(255, 215, 0, 0.14) 18px,
                rgba(220, 20, 60, 0.16) 18px, rgba(220, 20, 60, 0.16) 36px);
        }

        /* Gold recolor of the shared cursor spotlight */
        .es-hero .es-spot { background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(180, 83, 9, 0.10), transparent 60%); }
        .dark .es-hero .es-spot { background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(255, 215, 0, 0.10), transparent 60%); }

        /* Big-top accent links + hover cards */
        .es-circus-link { color: #b45309; transition: color 0.2s ease; }
        .es-circus-link:hover { color: #92400e; }
        .dark .es-circus-link { color: #FFB300; }
        .dark .es-circus-link:hover { color: #FFD700; }
        .es-circus-hover { transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }
        .es-circus-hover:hover { border-color: rgba(217, 119, 6, 0.45); background-color: rgba(254, 243, 199, 0.6); }
        .dark .es-circus-hover:hover { border-color: rgba(255, 165, 0, 0.3); background-color: rgba(255, 165, 0, 0.06); }
        .es-circus-hover:hover .es-circus-hover-title,
        .es-circus-hover:hover .es-circus-hover-arrow { color: #b45309; }
        .dark .es-circus-hover:hover .es-circus-hover-title,
        .dark .es-circus-hover:hover .es-circus-hover-arrow { color: #FFB300; }

        /* Finale: silks unroll once the panel reveals */
        .es-circus-drop { transform-origin: 50% 0; }
        html.es-anim [data-reveal] .es-circus-drop {
            transform: scaleY(0);
            transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(1.05s + var(--dd, 0s));
        }
        html.es-anim [data-reveal].is-revealed .es-circus-drop { transform: scaleY(1); }

        @media (prefers-reduced-motion: reduce) {
            .es-circus-silk,
            .es-circus-star { animation: none !important; }
            .es-circus-swing[data-reveal],
            .es-circus-swing-r[data-reveal] { opacity: 1 !important; transform: none !important; }
            .es-circus-drop { transition: none !important; transform: none !important; }
        }
    </style>

    @php
        $circusSilks = [
            ['left:8%', '0.25rem', '16rem', 'from-amber-500/60 via-amber-500/25', '0s'],
            ['left:13%', '0.125rem', '12rem', 'from-rose-500/50 via-rose-500/20', '0.5s'],
            ['right:8%', '0.25rem', '16rem', 'from-amber-500/60 via-amber-500/25', '0.3s'],
            ['right:13%', '0.125rem', '12rem', 'from-rose-500/50 via-rose-500/20', '0.8s'],
        ];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: the center ring                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-[#fdf8f2] py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(220, 20, 60, 0.34), rgba(220, 20, 60, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(245, 158, 11, 0.4), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="es-circus-floor"></div>
            <div class="es-circus-ring"></div>
            @foreach ($circusSilks as [$pos, $w, $h, $grad, $delay])
                <span class="es-circus-silk bg-gradient-to-b {{ $grad }} to-transparent" style="{{ $pos }};width:{{ $w }};height:{{ $h }};--d:{{ $delay }};"></span>
            @endforeach
            <span class="es-circus-star bg-amber-400" style="top:22%;left:15%;width:0.5rem;height:0.5rem;--d:0s;"></span>
            <span class="es-circus-star bg-amber-400" style="top:34%;right:20%;width:0.375rem;height:0.375rem;--d:0.7s;"></span>
            <span class="es-circus-star bg-rose-400" style="bottom:26%;left:24%;width:0.4rem;height:0.4rem;--d:1.3s;"></span>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="h-2 w-2 motion-safe:animate-pulse rounded-full bg-amber-500 dark:bg-amber-400"></span>
                <span class="es-circus-announce-badge text-gray-600 dark:text-gray-300">Ladies and gentlemen, the circus is in town</span>
                <span class="h-2 w-2 motion-safe:animate-pulse rounded-full bg-amber-500 dark:bg-amber-400"></span>
            </div>

            <h1 class="es-balance mb-8 text-[2.75rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line"><span class="es-circus-gold es-gradient-anim">Defy gravity.</span></span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line">Fill every seat.</span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-600 dark:text-gray-400 sm:text-xl">
                From the training studio to the big top. One link for all your shows. Venues book you, fans follow you, no algorithm decides who sees it.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the program
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Create your performance schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Performance-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Aerial', 'Fire', 'Acrobatics', 'Juggling', 'Stilt Walking', 'Contortion', 'Trapeze', 'Hand Balancing'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-amber-400 to-rose-400"></span>
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

    <div class="es-circus-canvas" aria-hidden="true"></div>

    <!-- ============================================================ -->
    <!-- 2. The program: six center-ring acts                         -->
    <!-- ============================================================ -->
    @php
        $circusActs = [
            ['num' => 'I', 'eyebrow' => 'Festival Circuit', 'title' => 'Track Your Tour', 'desc' => 'Renaissance faires, Burning Man, Fringe festivals - show fans every stop on your summer circuit.'],
            ['num' => 'II', 'eyebrow' => 'Technical Requirements', 'title' => 'Rigging & Tech Specs', 'desc' => 'Ceiling height, rigging points, weight capacity, floor space - share specs venues actually need.'],
            ['num' => 'III', 'eyebrow' => 'Teaching', 'title' => 'Fill Your Workshops', 'desc' => 'Aerial basics, fire safety, acro fundamentals - share your class schedule with students.'],
            ['num' => 'IV', 'eyebrow' => 'Ensemble', 'title' => 'Coordinate Your Troupe', 'desc' => 'Aerialist, rigger, stage manager - everyone sees the schedule. No more group chat chaos.'],
            ['num' => 'V', 'eyebrow' => 'Ticketing', 'title' => 'Keep 100% of Sales', 'desc' => 'Your show, your revenue. Zero platform fees. QR tickets for door check-in.'],
            ['num' => 'VI', 'eyebrow' => 'Booking', 'title' => 'Event Planner Kit', 'desc' => 'One link with your availability, videos, specs, and rates. Perfect for corporate bookers and wedding planners.'],
        ];
    @endphp
    <section id="features" class="scroll-mt-24 overflow-x-clip bg-[#f8efe6] py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-circus-announce mx-auto max-w-sm" data-reveal>Tonight's program</p>
                <h2 class="es-balance mb-3 mt-5 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Six acts. One <span class="es-circus-gold">center ring.</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.14s;">Everything a circus and acrobatics career needs, announced one act at a time.</p>
            </div>

            <div class="space-y-6">

                <!-- Act I: Track Your Tour -->
                <article class="es-circus-swing scroll-mt-24" data-reveal>
                    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-white/[0.04] lg:p-12">
                        <header class="mb-8 text-center">
                            <p class="es-circus-announce mx-auto max-w-xs text-xs">And now, in the center ring</p>
                            <div class="es-circus-medal mx-auto mt-4"><span class="es-circus-medal-core"><span class="es-circus-numeral">{{ $circusActs[0]['num'] }}</span></span></div>
                            <div class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-400">{{ $circusActs[0]['eyebrow'] }}</div>
                        </header>
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div>
                                <h3 class="mb-4 text-2xl font-black tracking-tight text-gray-900 dark:text-white lg:text-3xl">{{ $circusActs[0]['title'] }}</h3>
                                <p class="mb-4 text-lg text-gray-600 dark:text-gray-400">{{ $circusActs[0]['desc'] }}</p>
                                <p class="text-gray-600 dark:text-gray-400">Split the run into sub-schedules by tour leg, import dates from Google Calendar, and fans follow the whole circuit from one link.</p>
                            </div>
                            <div aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-black/40">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Summer Circuit</span>
                                        <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">3 stops</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="es-ai-field flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 p-2.5 dark:border-amber-800/30 dark:bg-amber-900/20" style="--i: 0;">
                                            <span class="text-xs font-bold text-amber-700 dark:text-amber-300">JUN 14</span>
                                            <span class="flex-1 text-sm font-semibold text-gray-900 dark:text-white">Oregon Country Fair</span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Main stage</span>
                                        </div>
                                        <div class="es-ai-field flex items-center gap-3 rounded-lg border border-orange-200 bg-orange-50 p-2.5 dark:border-orange-800/30 dark:bg-orange-900/20" style="--i: 1;">
                                            <span class="text-xs font-bold text-orange-700 dark:text-orange-300">AUG 25 - SEP 1</span>
                                            <span class="flex-1 text-sm font-semibold text-gray-900 dark:text-white">Black Rock City</span>
                                        </div>
                                        <div class="es-ai-field flex items-center gap-3 rounded-lg border border-rose-200 bg-rose-50 p-2.5 dark:border-rose-800/30 dark:bg-rose-900/20" style="--i: 2;">
                                            <span class="text-xs font-bold text-rose-700 dark:text-rose-300">SEP 12</span>
                                            <span class="flex-1 text-sm font-semibold text-gray-900 dark:text-white">Edmonton Fringe</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-amber-300/60 bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:border-amber-400/30 dark:bg-amber-500/10 dark:text-amber-300">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                        One link for the whole run
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="es-circus-star text-amber-500 dark:text-amber-400" style="top:6%;right:4%;">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3" aria-hidden="true"><path d="M12 2l1.9 6.1L20 10l-6.1 1.9L12 18l-1.9-6.1L4 10l6.1-1.9z"/></svg>
                        </span>
                    </div>
                </article>

                <!-- Act II: Rigging & Tech Specs -->
                <article class="es-circus-swing-r scroll-mt-24" data-reveal>
                    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-white/[0.04] lg:p-12">
                        <header class="mb-8 text-center">
                            <p class="es-circus-announce mx-auto max-w-xs text-xs">And now, in the center ring</p>
                            <div class="es-circus-medal mx-auto mt-4"><span class="es-circus-medal-core"><span class="es-circus-numeral">{{ $circusActs[1]['num'] }}</span></span></div>
                            <div class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-400">{{ $circusActs[1]['eyebrow'] }}</div>
                        </header>
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div class="lg:order-last">
                                <h3 class="mb-4 text-2xl font-black tracking-tight text-gray-900 dark:text-white lg:text-3xl">{{ $circusActs[1]['title'] }}</h3>
                                <p class="mb-4 text-lg text-gray-600 dark:text-gray-400">{{ $circusActs[1]['desc'] }}</p>
                                <p class="text-gray-600 dark:text-gray-400">Put your technical rider on the schedule with custom fields, so every booker sees exactly what your act requires before they call.</p>
                            </div>
                            <div aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-black/40">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Technical Rider - Aerial Silks</span>
                                        <svg aria-hidden="true" class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <dl class="divide-y divide-gray-200 text-sm dark:divide-white/10">
                                        <div class="flex items-center justify-between py-2.5"><dt class="text-gray-500 dark:text-gray-400">Ceiling height</dt><dd class="font-semibold text-gray-900 [font-variant-numeric:tabular-nums] dark:text-white">6 m minimum</dd></div>
                                        <div class="flex items-center justify-between py-2.5"><dt class="text-gray-500 dark:text-gray-400">Rigging points</dt><dd class="font-semibold text-gray-900 [font-variant-numeric:tabular-nums] dark:text-white">2 certified points</dd></div>
                                        <div class="flex items-center justify-between py-2.5"><dt class="text-gray-500 dark:text-gray-400">Weight rating</dt><dd class="font-semibold text-gray-900 [font-variant-numeric:tabular-nums] dark:text-white">1,000 kg dynamic</dd></div>
                                        <div class="flex items-center justify-between py-2.5"><dt class="text-gray-500 dark:text-gray-400">Floor space</dt><dd class="font-semibold text-gray-900 [font-variant-numeric:tabular-nums] dark:text-white">6 x 6 m clear</dd></div>
                                    </dl>
                                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                                        Attached to every booking link
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="es-circus-star text-rose-500 dark:text-rose-400" style="top:6%;left:4%;">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3" aria-hidden="true"><path d="M12 2l1.9 6.1L20 10l-6.1 1.9L12 18l-1.9-6.1L4 10l6.1-1.9z"/></svg>
                        </span>
                    </div>
                </article>

                <!-- Act III: Fill Your Workshops -->
                <article class="es-circus-swing scroll-mt-24" data-reveal>
                    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-white/[0.04] lg:p-12">
                        <header class="mb-8 text-center">
                            <p class="es-circus-announce mx-auto max-w-xs text-xs">And now, in the center ring</p>
                            <div class="es-circus-medal mx-auto mt-4"><span class="es-circus-medal-core"><span class="es-circus-numeral">{{ $circusActs[2]['num'] }}</span></span></div>
                            <div class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-400">{{ $circusActs[2]['eyebrow'] }}</div>
                        </header>
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div>
                                <h3 class="mb-4 text-2xl font-black tracking-tight text-gray-900 dark:text-white lg:text-3xl">{{ $circusActs[2]['title'] }}</h3>
                                <p class="mb-4 text-lg text-gray-600 dark:text-gray-400">{{ $circusActs[2]['desc'] }}</p>
                                <p class="text-gray-600 dark:text-gray-400">Weekly classes repeat themselves with <a href="{{ marketing_url('/features/recurring-events') }}" class="es-circus-link font-medium hover:underline">recurring events</a>, and on Pro you can sell multi-class <a href="{{ marketing_url('/features/ticketing') }}" class="es-circus-link font-medium hover:underline">passes</a> your students redeem across the term.</p>
                            </div>
                            <div aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-black/40">
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Aerial Fundamentals</span>
                                        <span class="font-bold text-amber-700 dark:text-amber-300">$180</span>
                                    </div>
                                    <div class="mb-4 text-xs text-gray-500 dark:text-gray-400">10-class pass</div>
                                    <div class="mb-2 flex items-center gap-1.5">
                                        @for ($i = 0; $i < 10; $i++)
                                            <span class="h-4 w-4 rounded-full {{ $i < 6 ? 'bg-amber-500 dark:bg-amber-400' : 'border-2 border-gray-300 dark:border-white/20' }}"></span>
                                        @endfor
                                    </div>
                                    <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">6 of 10 classes used</div>
                                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/60 bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:border-amber-400/30 dark:bg-amber-500/10 dark:text-amber-300">
                                        Membership and drop-in options
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="es-circus-star text-amber-500 dark:text-amber-400" style="top:6%;right:4%;">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3" aria-hidden="true"><path d="M12 2l1.9 6.1L20 10l-6.1 1.9L12 18l-1.9-6.1L4 10l6.1-1.9z"/></svg>
                        </span>
                    </div>
                </article>

                <!-- Act IV: Coordinate Your Troupe -->
                <article class="es-circus-swing-r scroll-mt-24" data-reveal>
                    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-white/[0.04] lg:p-12">
                        <header class="mb-8 text-center">
                            <p class="es-circus-announce mx-auto max-w-xs text-xs">And now, in the center ring</p>
                            <div class="es-circus-medal mx-auto mt-4"><span class="es-circus-medal-core"><span class="es-circus-numeral">{{ $circusActs[3]['num'] }}</span></span></div>
                            <div class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-400">{{ $circusActs[3]['eyebrow'] }}</div>
                        </header>
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div class="lg:order-last">
                                <h3 class="mb-4 text-2xl font-black tracking-tight text-gray-900 dark:text-white lg:text-3xl">{{ $circusActs[3]['title'] }}</h3>
                                <p class="mb-4 text-lg text-gray-600 dark:text-gray-400">{{ $circusActs[3]['desc'] }}</p>
                                <p class="text-gray-600 dark:text-gray-400">One extra team member is included free. Enterprise adds your whole crew plus <a href="{{ marketing_url('/features/availability') }}" class="es-circus-link font-medium hover:underline">availability</a> tracking for every booking.</p>
                            </div>
                            <div aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-black/40">
                                    <div class="mb-3 text-sm font-medium text-gray-500 dark:text-gray-400">The company</div>
                                    <div class="space-y-2">
                                        <div class="es-ai-field flex items-center gap-3 rounded-lg bg-white p-2.5 dark:bg-white/5" style="--i: 0;">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-xs font-bold text-white">M</span>
                                            <span class="flex-1"><span class="block text-sm font-semibold text-gray-900 dark:text-white">Maya</span><span class="block text-[10px] text-gray-500 dark:text-gray-400">Aerialist</span></span>
                                            <svg aria-hidden="true" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div class="es-ai-field flex items-center gap-3 rounded-lg bg-white p-2.5 dark:bg-white/5" style="--i: 1;">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-red-500 text-xs font-bold text-white">J</span>
                                            <span class="flex-1"><span class="block text-sm font-semibold text-gray-900 dark:text-white">Jonas</span><span class="block text-[10px] text-gray-500 dark:text-gray-400">Rigger</span></span>
                                            <svg aria-hidden="true" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div class="es-ai-field flex items-center gap-3 rounded-lg bg-white p-2.5 dark:bg-white/5" style="--i: 2;">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-amber-500 text-xs font-bold text-white">P</span>
                                            <span class="flex-1"><span class="block text-sm font-semibold text-gray-900 dark:text-white">Priya</span><span class="block text-[10px] text-gray-500 dark:text-gray-400">Stage manager</span></span>
                                            <svg aria-hidden="true" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">Availability management on Enterprise</div>
                                </div>
                            </div>
                        </div>
                        <span class="es-circus-star text-rose-500 dark:text-rose-400" style="top:6%;left:4%;">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3" aria-hidden="true"><path d="M12 2l1.9 6.1L20 10l-6.1 1.9L12 18l-1.9-6.1L4 10l6.1-1.9z"/></svg>
                        </span>
                    </div>
                </article>

                <!-- Act V: Keep 100% of Sales -->
                <article class="es-circus-swing scroll-mt-24" data-reveal>
                    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-white/[0.04] lg:p-12">
                        <header class="mb-8 text-center">
                            <p class="es-circus-announce mx-auto max-w-xs text-xs">And now, in the center ring</p>
                            <div class="es-circus-medal mx-auto mt-4"><span class="es-circus-medal-core"><span class="es-circus-numeral">{{ $circusActs[4]['num'] }}</span></span></div>
                            <div class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-400">{{ $circusActs[4]['eyebrow'] }}</div>
                        </header>
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div>
                                <h3 class="mb-4 text-2xl font-black tracking-tight text-gray-900 dark:text-white lg:text-3xl">{{ $circusActs[4]['title'] }}</h3>
                                <p class="mb-4 text-lg text-gray-600 dark:text-gray-400">{{ $circusActs[4]['desc'] }}</p>
                                <p class="text-gray-600 dark:text-gray-400">Stripe pays you directly. Promo codes for your regulars, waitlists for the sold-out nights - all on Pro.</p>
                            </div>
                            <div aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-black/40">
                                    <div class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Saturday Night Spectacular</div>
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between rounded-lg bg-white p-2.5 text-sm dark:bg-white/5"><span class="text-gray-900 dark:text-white">General Admission</span><span class="font-medium text-gray-900 dark:text-white">$25</span></div>
                                        <div class="flex items-center justify-between rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-2.5 text-sm"><span class="text-gray-900 dark:text-white">Ringside</span><span class="font-medium text-gray-900 dark:text-white">$45</span></div>
                                    </div>
                                    <div class="mt-4 flex justify-between border-t border-gray-200 pt-3 dark:border-white/10"><span class="text-sm text-gray-500 dark:text-gray-400">Platform fee</span><span class="font-bold text-emerald-600 dark:text-emerald-400">$0</span></div>
                                    <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-amber-300/60 bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:border-amber-400/30 dark:bg-amber-500/10 dark:text-amber-300">
                                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                        QR check-in at the door
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="es-circus-star text-amber-500 dark:text-amber-400" style="top:6%;right:4%;">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3" aria-hidden="true"><path d="M12 2l1.9 6.1L20 10l-6.1 1.9L12 18l-1.9-6.1L4 10l6.1-1.9z"/></svg>
                        </span>
                    </div>
                </article>

                <!-- Act VI: Event Planner Kit -->
                <article class="es-circus-swing-r scroll-mt-24" data-reveal>
                    <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-white/[0.04] lg:p-12">
                        <header class="mb-8 text-center">
                            <p class="es-circus-announce mx-auto max-w-xs text-xs">For our final act</p>
                            <div class="es-circus-medal mx-auto mt-4"><span class="es-circus-medal-core"><span class="es-circus-numeral">{{ $circusActs[5]['num'] }}</span></span></div>
                            <div class="mt-3 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-400">{{ $circusActs[5]['eyebrow'] }}</div>
                        </header>
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div class="lg:order-last">
                                <h3 class="mb-4 text-2xl font-black tracking-tight text-gray-900 dark:text-white lg:text-3xl">{{ $circusActs[5]['title'] }}</h3>
                                <p class="mb-4 text-lg text-gray-600 dark:text-gray-400">{{ $circusActs[5]['desc'] }}</p>
                                <p class="text-gray-600 dark:text-gray-400">Keep private quotes as drafts until they are confirmed, so corporate gigs never leak onto your public calendar early.</p>
                            </div>
                            <div aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-black/40">
                                    <div dir="ltr" class="mb-3 flex items-center gap-2 rounded-xl border border-amber-300/60 bg-amber-50 px-4 py-2.5 dark:border-amber-400/30 dark:bg-amber-500/10">
                                        <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                        <span class="truncate font-mono text-sm font-semibold text-gray-900 dark:text-white">your-troupe.eventschedule.com</span>
                                    </div>
                                    <div class="mb-3 flex flex-wrap gap-1.5">
                                        <span class="rounded bg-gray-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-gray-600 dark:bg-white/10 dark:text-gray-300">Availability</span>
                                        <span class="rounded bg-gray-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-gray-600 dark:bg-white/10 dark:text-gray-300">Videos</span>
                                        <span class="rounded bg-gray-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-gray-600 dark:bg-white/10 dark:text-gray-300">Tech specs</span>
                                        <span class="rounded bg-gray-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-gray-600 dark:bg-white/10 dark:text-gray-300">Rates</span>
                                    </div>
                                    <div class="rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-4 py-2.5 text-center text-sm font-semibold text-white">Booking inquiry</div>
                                </div>
                            </div>
                        </div>
                        <span class="es-circus-star text-rose-500 dark:text-rose-400" style="top:6%;left:4%;">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3" aria-hidden="true"><path d="M12 2l1.9 6.1L20 10l-6.1 1.9L12 18l-1.9-6.1L4 10l6.1-1.9z"/></svg>
                        </span>
                    </div>
                </article>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Three rings: sub-schedules                                -->
    <!-- ============================================================ -->
    <section class="bg-[#fdf8f2] py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-circus-announce mx-auto max-w-sm" data-reveal>The three rings</p>
                <h2 class="es-balance mb-4 mt-5 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Three rings. One <span class="es-circus-gold">schedule.</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.14s;">
                    Sub-schedules split your calendar into rings fans can follow separately - or all at once.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="90">
                <div class="rounded-3xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                    <div class="es-circus-medal es-circus-medal-lg mx-auto"><span class="es-circus-medal-core"><span class="es-circus-numeral">I</span></span></div>
                    <div class="es-circus-announce-badge mt-4 text-xs text-amber-700 dark:text-amber-400">Ring I</div>
                    <h3 class="mb-2 mt-2 text-xl font-bold text-gray-900 dark:text-white">Troupe Shows</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Public performances under one banner. The schedule fans follow.</p>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                    <div class="es-circus-medal es-circus-medal-lg mx-auto"><span class="es-circus-medal-core"><span class="es-circus-numeral">II</span></span></div>
                    <div class="es-circus-announce-badge mt-4 text-xs text-amber-700 dark:text-amber-400">Ring II</div>
                    <h3 class="mb-2 mt-2 text-xl font-bold text-gray-900 dark:text-white">Classes & Workshops</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Weekly aerial basics and intensives on their own calendar link.</p>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-8 text-center shadow-sm dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                    <div class="es-circus-medal es-circus-medal-lg mx-auto"><span class="es-circus-medal-core"><span class="es-circus-numeral">III</span></span></div>
                    <div class="es-circus-announce-badge mt-4 text-xs text-amber-700 dark:text-amber-400">Ring III</div>
                    <h3 class="mb-2 mt-2 text-xl font-bold text-gray-900 dark:text-white">Corporate & Private</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Quotes, galas, and festival buyouts - visible only where you share it.</p>
                </div>
            </div>

            <div class="mt-8 text-center" data-reveal>
                <a href="{{ marketing_url('/features/sub-schedules') }}" class="inline-flex items-center font-medium es-circus-link hover:underline">
                    Sub-schedules are free
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The ring goes online (streaming)                          -->
    <!-- ============================================================ -->
    <section class="bg-[#f8efe6] py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <p class="es-circus-announce mx-auto max-w-md" data-reveal>And now, the ring goes online</p>
                <h2 class="es-balance mb-4 mt-5 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Stream your act to the <span class="es-circus-gold">world</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.14s;">
                    Sell tickets to virtual audiences worldwide. No venue needed. Your performance, broadcast from anywhere.
                </p>
            </div>

            <div class="grid items-center gap-10 md:grid-cols-2">
                <!-- Streaming stage mock -->
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/[0.04]" aria-hidden="true">
                        <div class="mb-4 rounded-xl border border-gray-200 bg-gray-900 p-4 dark:border-white/10">
                            <div class="relative flex aspect-video items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-rose-500/30 to-gray-900">
                                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(245,158,11,0.15),transparent_70%)]"></div>
                                <div class="absolute left-2 top-2 flex items-center gap-1.5 rounded bg-red-600 px-2 py-1 text-xs font-bold text-white">
                                    <span class="h-2 w-2 motion-safe:animate-pulse rounded-full bg-white"></span> LIVE
                                </div>
                                <svg aria-hidden="true" class="h-24 w-24 text-amber-400/60" viewBox="0 0 100 100" fill="none">
                                    <path d="M50 5 L50 95" stroke="currentColor" stroke-width="1.5" opacity="0.6"/>
                                    <circle cx="50" cy="30" r="5" fill="currentColor"/>
                                    <path d="M50 35 L50 55" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M50 40 Q35 45, 30 35" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M50 40 Q55 30, 52 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M50 55 Q60 70, 70 65" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M50 55 Q45 65, 40 75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                <div class="absolute bottom-2 right-2 flex items-center gap-1 rounded bg-black/60 px-2 py-1 text-xs text-white">
                                    <svg aria-hidden="true" class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                                    <span data-count-to="847">847</span> watching
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 max-h-20 space-y-1.5 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 p-2 text-xs dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-start gap-2"><span class="shrink-0 font-medium text-amber-600 dark:text-amber-400">CircusFan:</span><span class="text-gray-600 dark:text-gray-300">Amazing drop!</span></div>
                            <div class="flex items-start gap-2"><span class="shrink-0 font-medium text-rose-600 dark:text-rose-400">AerialLover:</span><span class="text-gray-600 dark:text-gray-300">The silk work is incredible</span></div>
                            <div class="flex items-start gap-2"><span class="shrink-0 font-medium text-emerald-600 dark:text-emerald-400">NewViewer:</span><span class="text-gray-600 dark:text-gray-300">First time seeing aerial live!</span></div>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border border-amber-300 bg-amber-100 px-3 py-2 dark:border-amber-400/20 dark:bg-amber-500/10">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Virtual Tickets Sold</span>
                            <span class="font-bold text-amber-700 dark:text-amber-300"><span data-count-to="234">234</span></span>
                        </div>
                        <div class="absolute -right-3 -top-3 motion-safe:animate-pulse rounded-full bg-gradient-to-r from-emerald-600 to-emerald-500 px-3 py-1.5 text-xs font-bold text-white shadow-lg">
                            $25 tip from Sarah!
                        </div>
                        <span class="es-circus-silk bg-gradient-to-b from-rose-500/45 via-rose-500/15 to-transparent" style="left:12%;width:0.25rem;height:9rem;--d:0.4s;" aria-hidden="true"></span>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="space-y-6" data-reveal-group="90">
                    <div class="flex gap-4" data-reveal>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20">
                            <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Perform for global audiences</h3>
                            <p class="text-gray-600 dark:text-gray-400">Reach fans across continents without leaving your studio</p>
                        </div>
                    </div>
                    <div class="flex gap-4" data-reveal>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20">
                            <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sell virtual tickets</h3>
                            <p class="text-gray-600 dark:text-gray-400">Monetize your streams with paid access - keep 100%</p>
                        </div>
                    </div>
                    <div class="flex gap-4" data-reveal>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/20">
                            <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Schedule recurring shows</h3>
                            <p class="text-gray-600 dark:text-gray-400">Build a regular online audience with scheduled streams</p>
                        </div>
                    </div>
                    <div data-reveal>
                        <a href="{{ marketing_url('/features/online-events') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 px-6 py-3 font-semibold text-white shadow-lg shadow-rose-500/25 transition-all hover:-translate-y-0.5 hover:shadow-xl">
                            Learn about online events
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Where circus artists perform                              -->
    <!-- ============================================================ -->
    @php
        $circusVenues = [
            ['Big Tops', 'bg-rose-100 dark:bg-rose-900/30', 'text-rose-600 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />'],
            ['Theaters', 'bg-rose-100 dark:bg-rose-900/30', 'text-rose-600 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />'],
            ['Street', 'bg-amber-100 dark:bg-amber-900/30', 'text-amber-600 dark:text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />'],
            ['Festivals', 'bg-orange-100 dark:bg-orange-900/30', 'text-orange-600 dark:text-orange-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />'],
            ['Corporate', 'bg-amber-100 dark:bg-amber-900/30', 'text-amber-600 dark:text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />'],
            ['Cruise Ships', 'bg-sky-100 dark:bg-sky-900/30', 'text-sky-600 dark:text-sky-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
        ];
    @endphp
    <section class="bg-[#fdf8f2] py-16 dark:bg-[#0a0a0f] lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <p class="es-circus-announce mx-auto max-w-xs" data-reveal>On every route</p>
                <h2 class="es-balance mb-3 mt-5 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.08s;">
                    Where circus artists <span class="es-circus-gold">perform</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400" data-reveal style="--reveal-delay: 0.14s;">From street corners to the big top - one schedule for every stage</p>
            </div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6" data-reveal-group="60">
                @foreach ($circusVenues as [$vName, $vChip, $vText, $vIcon])
                    <div data-reveal class="group rounded-2xl border border-gray-200 bg-white p-6 text-center transition-all duration-200 hover:-translate-y-1 hover:border-amber-300 hover:shadow-md dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-amber-500/30">
                        <div class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $vChip }} transition-transform group-hover:scale-110">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $vText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $vIcon !!}</svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $vName }}</h3>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-[#f8efe6] py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Trapeze rig (decorative) -->
            <div class="absolute -top-2 right-8 hidden lg:block" aria-hidden="true">
                <svg class="h-24 w-32 text-amber-500/40 dark:text-amber-400/30" viewBox="0 0 120 96" fill="none" stroke="currentColor" stroke-linecap="round">
                    <path d="M20 0 V46" stroke-width="2" />
                    <path d="M100 0 V46" stroke-width="2" />
                    <path d="M12 46 H108" stroke-width="3" />
                    <path d="M20 10 A 54 54 0 0 0 100 10" stroke-width="1.5" stroke-dasharray="3 6" opacity="0.5" />
                </svg>
                <span class="es-circus-star bg-amber-400" style="top:10%;right:-6px;width:0.35rem;height:0.35rem;--d:0.5s;"></span>
            </div>

            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all types of <span class="es-circus-gold">circus performers</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether you're a solo aerialist or a touring troupe, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Aerialists -->
                <x-sub-audience-card
                    name="Aerialists"
                    description="Share your aerial silk, trapeze, and hoop performances. Let fans know where to catch your next show."
                    icon-color="red"
                    blog-slug="for-aerialists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-7 h-7 text-red-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.5"/>
                            <circle cx="12" cy="7" r="2" fill="currentColor"/>
                            <path d="M12 9V14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 11L8 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 11L14 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 14L16 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 14L10 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Circus Troupes -->
                <x-sub-audience-card
                    name="Circus Troupes"
                    description="Coordinate your ensemble's schedule and let audiences follow your collective performances."
                    icon-color="rose"
                    blog-slug="for-circus-troupes"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-7 h-7 text-rose-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="7" cy="18" r="2" fill="currentColor"/>
                            <circle cx="17" cy="18" r="2" fill="currentColor"/>
                            <circle cx="12" cy="12" r="2" fill="currentColor"/>
                            <path d="M12 14V17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 16L7 18" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
                            <path d="M14 16L17 18" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
                            <circle cx="12" cy="6" r="1.5" fill="currentColor"/>
                            <path d="M12 7.5V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 9L14 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Fire Performers -->
                <x-sub-audience-card
                    name="Fire Performers"
                    description="Promote your fire dancing, breathing, and spinning shows at festivals and events."
                    icon-color="orange"
                    blog-slug="for-fire-performers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-7 h-7 text-orange-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="10" r="2" fill="currentColor"/>
                            <path d="M12 12V18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 16L8 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M14 16L16 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 13L6 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 13L18 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M5 10C4.5 8.5 5.5 7 6 8C6.5 9 5.5 10 5 10Z" fill="currentColor"/>
                            <path d="M19 10C19.5 8.5 18.5 7 18 8C17.5 9 18.5 10 19 10Z" fill="currentColor"/>
                            <circle cx="5.5" cy="10" r="1.5" fill="currentColor" opacity="0.6"/>
                            <circle cx="18.5" cy="10" r="1.5" fill="currentColor" opacity="0.6"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Contortionists -->
                <x-sub-audience-card
                    name="Contortionists"
                    description="Showcase your flexibility performances and build a dedicated following."
                    icon-color="rose"
                    blog-slug="for-contortionists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-7 h-7 text-rose-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="18" cy="14" r="2" fill="currentColor"/>
                            <path d="M16 14C14 14 12 16 10 16C8 16 6 14 6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M6 12C6 10 7 8 9 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9 8L10 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 16L10 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="10" cy="5.5" r="0.8" fill="currentColor"/>
                            <circle cx="10" cy="19.5" r="0.8" fill="currentColor"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Jugglers & Prop Artists -->
                <x-sub-audience-card
                    name="Jugglers & Prop Artists"
                    description="List your juggling, poi, and object manipulation shows and workshops."
                    icon-color="amber"
                    blog-slug="for-jugglers-prop-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-7 h-7 text-amber-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="14" r="2" fill="currentColor"/>
                            <path d="M12 16V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 19L8 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M14 19L16 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 15L8 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 15L16 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="6" cy="6" r="1.5" fill="currentColor"/>
                            <circle cx="12" cy="3" r="1.5" fill="currentColor"/>
                            <circle cx="18" cy="6" r="1.5" fill="currentColor"/>
                            <path d="M7 7C9 4 11 3 12 3" stroke="currentColor" stroke-width="0.75" stroke-linecap="round" opacity="0.4" stroke-dasharray="1 1"/>
                            <path d="M17 7C15 4 13 3 12 3" stroke="currentColor" stroke-width="0.75" stroke-linecap="round" opacity="0.4" stroke-dasharray="1 1"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Stilt Walkers -->
                <x-sub-audience-card
                    name="Stilt Walkers"
                    description="Share your larger-than-life performances at parades, festivals, and corporate events."
                    icon-color="amber"
                    blog-slug="for-stilt-walkers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-7 h-7 text-amber-700" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="4" r="2" fill="currentColor"/>
                            <path d="M12 6V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 8L8 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 8L16 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 12L10 22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 12L14 22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9 22H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M13 22H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. How it works (dark band)                                  -->
    <!-- ============================================================ -->
    <div class="es-circus-canvas" aria-hidden="true"></div>

    <section class="relative bg-[#fdf8f2] px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(220, 20, 60, 0.28), rgba(220, 20, 60, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 60%, rgba(245, 158, 11, 0.26), rgba(245, 158, 11, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <span class="es-circus-star bg-amber-400" style="top:16%;left:12%;width:0.4rem;height:0.4rem;--d:0s;"></span>
                <span class="es-circus-star bg-rose-400" style="bottom:20%;right:14%;width:0.35rem;height:0.35rem;--d:1s;"></span>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <p class="es-circus-announce mx-auto max-w-xs" data-reveal>Quick setup</p>
                    <h2 class="es-balance mt-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Get your performance schedule online in <span class="es-circus-gold">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="es-circus-medal mx-auto mb-5"><span class="es-circus-medal-core"><span class="es-circus-numeral">I</span></span></div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Add your acts</h3>
                        <p class="text-sm text-gray-400">Shows, workshops, festival appearances. Import from Google Calendar or add manually.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="es-circus-medal mx-auto mb-5"><span class="es-circus-medal-core"><span class="es-circus-numeral">II</span></span></div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Share one link</h3>
                        <p class="text-sm text-gray-400">Add to your website, social bios, and booking portfolio. Planners see everything.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="es-circus-medal mx-auto mb-5"><span class="es-circus-medal-core"><span class="es-circus-numeral">III</span></span></div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Build your following</h3>
                        <p class="text-sm text-gray-400">Fans follow your schedule and get notified about performances in their area.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-[#f8efe6] py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key <span class="es-circus-gold">features</span></h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Event Graphics" description="Show posters generated from your events" :url="marketing_url('/features/event-graphics')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send event updates directly to followers' inboxes" :url="marketing_url('/features/newsletters')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google Calendar" :url="marketing_url('/features/calendar-sync')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center font-medium es-circus-link hover:underline">
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
    <!-- 9. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-[#fdf8f2] py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related <span class="es-circus-gold">pages</span></h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-magicians', 'Magicians'], ['/for-dance-groups', 'Dance Groups'], ['/for-theater-performers', 'Theater Performers'], ['/for-visual-artists', 'Visual Artists']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-circus-hover group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="es-circus-hover-title text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-circus-hover-arrow w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="inline-flex items-center font-medium es-circus-link hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-[#f8efe6] py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="es-circus-gold">questions</span>
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything circus performers ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for circus performers?', 'Yes. Event Schedule is free forever for sharing your performance schedule, building a fan following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I manage tour dates and local shows in one schedule?', 'Yes. List all your performances in one place - touring shows, local gigs, festival appearances, and private events. Use sub-schedules to organize by show type or tour leg. Fans see everything in one calendar.'],
                    ['How do audiences discover my performances?', 'Fans can follow your schedule and receive email notifications for new shows. Share your schedule link on social media, your booking page, or embed it on your website. Send newsletters to followers with upcoming tour dates.'],
                    ['Can I sell tickets to my shows?', 'Yes. Connect your Stripe account and sell tickets directly from your schedule. Create different ticket types for different seating or experiences. Zero platform fees - you only pay Stripe\'s standard processing fees.'],
                    ['Can I sell class passes for my aerial or acro classes?', 'Yes. On the Pro plan you can sell multi-use passes alongside regular tickets - like a 10-class pass or a monthly membership. Passes are redeemable across your events, usage is tracked automatically, and you can set a cancellation deadline for late drops. Zero platform fees apply to passes too.'],
                    ['Can my whole troupe manage one schedule?', 'Yes. Every schedule includes one additional team member for free, and the Enterprise plan supports multiple team members - so your rigger, stage manager, and performers can all update the calendar. Enterprise also adds availability management to track who is free for each booking.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq es-circus-hover overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between gap-3 p-6">
                            <div class="flex items-center gap-3">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-amber-500 dark:text-amber-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 16.8 5.8 21.3l2.4-7.4L2 9.4h7.6z"/></svg>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            </div>
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
    <!-- 11. Grand finale: take your bow                              -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-[#fdf8f2] px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-rose-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(220, 20, 60, 0.32), rgba(220, 20, 60, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <span class="es-circus-star bg-amber-400" style="top:18%;left:14%;width:0.4rem;height:0.4rem;--d:0s;"></span>
                    <span class="es-circus-star bg-amber-400" style="bottom:22%;right:16%;width:0.35rem;height:0.35rem;--d:0.9s;"></span>
                    <span class="es-circus-drop absolute top-0 bg-gradient-to-b from-amber-400/50 via-amber-400/20 to-transparent" style="left:12%;width:0.25rem;height:11rem;"></span>
                    <span class="es-circus-drop absolute top-0 bg-gradient-to-b from-rose-400/50 via-rose-400/20 to-transparent" style="right:12%;width:0.25rem;height:9rem;--dd:0.25s;"></span>
                </div>

                <div class="relative z-10">
                    <p class="es-circus-announce mx-auto max-w-md" data-reveal>Ladies and gentlemen, take your bow</p>
                    <h2 class="es-balance mx-auto mb-6 mt-5 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.15s;">
                        The show must go on. <span class="es-circus-gold">Make sure they know where.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Your art deserves an audience. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-troupe" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
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

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
