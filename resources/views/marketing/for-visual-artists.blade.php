<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Visual Artists | Share Your Exhibitions</x-slot>
    <x-slot name="description">Build your collector base directly. Announce exhibitions, sell tickets to openings, and email collectors. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Visual Artists</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Visual Artists",
        "description": "Build your collector base directly. Announce exhibitions, sell tickets to openings, and email collectors. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Visual Artists"
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
                "name": "Is Event Schedule free for visual artists?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your exhibition and event schedule, building a collector following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan."
                }
            },
            {
                "@type": "Question",
                "name": "Can I list exhibitions, open studios, and art fairs together?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by event type - solo exhibitions, group shows, open studios, art fairs, and workshops. Each event can include descriptions, images, venue details, and ticketing options."
                }
            },
            {
                "@type": "Question",
                "name": "How do collectors and art lovers discover my events?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Followers can receive email notifications when you add new exhibitions or events. Share your schedule link in your artist bio, on social media, or embed it on your portfolio website."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets to openings and workshops?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Connect your Stripe account and sell tickets directly from your schedule. Perfect for ticketed opening receptions, art workshops, or studio tours. Zero platform fees - you only pay Stripe's processing fees."
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
        "name": "Event Schedule for Visual Artists",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Artist Exhibition and Event Scheduling Software",
        "operatingSystem": "Web",
        "description": "Build your collector base directly. Announce exhibitions, sell tickets to openings, and email collectors. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Exhibition announcement newsletters",
            "Zero-fee ticketing for openings and workshops",
            "Custom schedule URL for portfolio and bio",
            "Venue sync with galleries",
            "Auto-generated social media graphics",
            "Google Calendar two-way sync",
            "Audience and collector analytics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "artist exhibition calendar, visual artist scheduling, gallery show management, art event calendar, free artist scheduling",
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
        /* For-visual-artists "The Studio Wall" styles - the maker's studio.
           The shared es-* motion system lives in marketing.css; this holds the
           painterly pigment gradient (vermilion / ochre / teal, sampled from the
           drifting dabs), the framed badge, the hanging gallery-wall cards, the
           drifting easel card, the paint-dab motif, the self-drawing brushstroke
           underline, the paint-smear edge and the stretched-canvas frame. */
        .text-gradient-artist {
            background: linear-gradient(135deg, #ef4444, #d97706, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(20, 184, 166, 0.25);
        }
        .dark .text-gradient-artist {
            background: linear-gradient(135deg, #f87171, #fbbf24, #2dd4bf);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(45, 212, 191, 0.3);
        }
        .artist-frame-badge {
            border: 2px solid rgba(161, 98, 7, 0.3);
            box-shadow: inset 0 0 0 2px rgba(161, 98, 7, 0.1), 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .dark .artist-frame-badge {
            border-color: rgba(217, 119, 6, 0.3);
            box-shadow: inset 0 0 0 2px rgba(217, 119, 6, 0.1), 0 1px 3px rgba(0, 0, 0, 0.3);
        }
        .gallery-wall-card {
            box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.1), -1px -1px 4px rgba(0, 0, 0, 0.03);
        }
        .dark .gallery-wall-card {
            box-shadow: 4px 4px 16px rgba(0, 0, 0, 0.3), -1px -1px 4px rgba(0, 0, 0, 0.1);
        }
        /* Drifting paint dabs */
        .es-dab { pointer-events: none; overflow: hidden; }
        .es-dab span {
            position: absolute;
            bottom: -12px;
            border-radius: 9999px;
            opacity: 0;
            animation: es-dab var(--dab-dur, 10s) linear infinite;
            animation-delay: var(--dab-delay, 0s);
        }
        @keyframes es-dab {
            0% { transform: translateY(0) translateX(0); opacity: 0; }
            15% { opacity: var(--dab-op, 0.5); }
            85% { opacity: var(--dab-op, 0.5); }
            100% { transform: translateY(-180px) translateX(16px); opacity: 0; }
        }
        /* Warm-pigment CTA fill: vermilion to orange, white text */
        .es-btn-artist {
            background-image: linear-gradient(to right, #dc2626, #ea580c);
            box-shadow: 0 10px 22px -6px rgba(220, 38, 38, 0.45);
        }
        .es-btn-artist:hover { box-shadow: 0 18px 36px -8px rgba(234, 88, 12, 0.5); }
        .dark .es-btn-artist { background-image: linear-gradient(to right, #ef4444, #f97316); }

        /* Teal accent recolor for the hard-coded blue text links */
        .es-accent-link { color: #0d9488; }
        .es-accent-link:hover { color: #0f766e; }
        .dark .es-accent-link { color: #2dd4bf; }
        .dark .es-accent-link:hover { color: #5eead4; }

        /* Related-page card hover in studio teal */
        .es-related-card:hover { border-color: rgba(13, 148, 136, 0.4); background-color: rgba(20, 184, 166, 0.06); }
        .dark .es-related-card:hover { border-color: rgba(45, 212, 191, 0.3); background-color: rgba(45, 212, 191, 0.06); }
        .es-related-title, .es-related-arrow { transition: color 0.2s; }
        .es-related-card:hover .es-related-title, .es-related-card:hover .es-related-arrow { color: #0d9488; }
        .dark .es-related-card:hover .es-related-title, .dark .es-related-card:hover .es-related-arrow { color: #2dd4bf; }

        /* Frame-badge chip label (bronze frame, ochre text) */
        .es-frame-chip { color: #b45309; }
        .dark .es-frame-chip { color: #fbbf24; }

        /* Paint-smear edge: a soft multicolor pigment strip along one border */
        .es-smear { position: relative; }
        .es-smear::before {
            content: '';
            position: absolute;
            top: 10%;
            bottom: 10%;
            left: -3px;
            width: 6px;
            border-radius: 9999px;
            background: linear-gradient(to bottom, #ef4444, #d97706 45%, #14b8a6);
            filter: blur(2px);
            opacity: 0.85;
            pointer-events: none;
        }
        .dark .es-smear::before { opacity: 0.9; }

        /* Stretched-canvas frame around the promo-image mock */
        .es-canvas-frame {
            border: 6px solid #e7e5e4;
            border-radius: 2px;
            box-shadow: inset 0 0 0 1px rgba(120, 113, 108, 0.45), 0 6px 14px rgba(0, 0, 0, 0.18);
        }
        .dark .es-canvas-frame {
            border-color: #57534e;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.55), 0 6px 16px rgba(0, 0, 0, 0.45);
        }

        /* Self-drawing brushstroke underline (pure CSS stroke-dashoffset) */
        .es-brush { position: relative; display: inline-block; }
        .es-brush-stroke {
            position: absolute;
            left: -0.03em;
            right: -0.03em;
            bottom: -0.2em;
            width: calc(100% + 0.06em);
            height: 0.42em;
            overflow: visible;
            pointer-events: none;
        }
        .es-brush-stroke path {
            fill: none;
            stroke: #d97706;
            stroke-width: 6;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 1;
            stroke-dashoffset: 0;
        }
        .dark .es-brush-stroke path { stroke: #fbbf24; }
        html.es-anim .es-brush-stroke path { stroke-dashoffset: 1; }
        html.es-anim [data-reveal].is-revealed .es-brush-stroke path { animation: es-brush-draw 1s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes es-brush-draw { from { stroke-dashoffset: 1; } to { stroke-dashoffset: 0; } }

        @media (prefers-reduced-motion: reduce) {
            .es-dab span { animation: none !important; }
            .es-dab span { opacity: 0.3; transform: none; }
            .es-brush-stroke path { animation: none !important; stroke-dashoffset: 0 !important; }
        }
    </style>

    @php
        // Drifting paint dabs: [left, size(px), duration, delay, opacity, color]
        $dabs = [
            ['10%', 6, '10s', '0s', '0.5', 'rgba(239, 68, 68, 0.9)'],
            ['22%', 8, '12s', '2s', '0.45', 'rgba(20, 184, 166, 0.9)'],
            ['35%', 5, '9s', '3.4s', '0.4', 'rgba(217, 119, 6, 0.9)'],
            ['47%', 7, '11s', '1s', '0.5', 'rgba(20, 184, 166, 0.9)'],
            ['59%', 6, '10.5s', '2.6s', '0.45', 'rgba(239, 68, 68, 0.9)'],
            ['70%', 5, '9.5s', '4s', '0.4', 'rgba(217, 119, 6, 0.9)'],
            ['82%', 8, '12s', '1.3s', '0.5', 'rgba(20, 184, 166, 0.9)'],
            ['92%', 5, '9s', '3s', '0.4', 'rgba(239, 68, 68, 0.9)'],
        ];
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: your art, your audience                             -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 35%, rgba(20, 184, 166, 0.24), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 55% 55%, rgba(217, 119, 6, 0.14), rgba(217, 119, 6, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <!-- Canvas weave texture -->
            <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.06]">
                <svg aria-hidden="true" width="100%" height="100%">
                    <defs>
                        <pattern id="canvas-weave" x="0" y="0" width="8" height="8" patternUnits="userSpaceOnUse">
                            <path d="M0,4 L8,4 M4,0 L4,8" stroke="currentColor" stroke-width="0.5" class="text-sky-800 dark:text-sky-300" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#canvas-weave)" />
                </svg>
            </div>
            <div class="es-dab absolute inset-x-0 bottom-0 top-1/3">
                @foreach ($dabs as [$l, $s, $d, $dl, $op, $col])
                    <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; background: {{ $col }}; --dab-dur: {{ $d }}; --dab-delay: {{ $dl }}; --dab-op: {{ $op }};"></span>
                @endforeach
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 artist-frame-badge mb-8 inline-flex items-center gap-3 rounded-sm glass px-5 py-2.5 backdrop-blur-sm">
                <svg aria-hidden="true" class="h-5 w-5 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Visual Artists</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your art. Your audience.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-artist">No gallery gate.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-3 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                From open studios to art fairs. One link for all your exhibitions.
            </p>
            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-base text-gray-400 dark:text-gray-500">
                Build your collector base directly.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the tools
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl es-btn-artist px-8 py-4 text-lg font-semibold text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl">
                    Create your schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Medium marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Painting', 'Sculpture', 'Photography', 'Printmaking', 'Mixed Media', 'Digital Art', 'Ceramics', 'Textiles'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100/80 px-4 py-1.5 text-xs font-semibold text-sky-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-sky-400 to-cyan-400"></span>
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
                    <div class="mb-2 text-4xl font-black text-sky-500 dark:text-sky-400"><span data-count-to="80">80</span>%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">of artists rely on word-of-mouth alone</div>
                </div>
                <div data-reveal class="border-gray-200 p-6 dark:border-white/5 md:border-x">
                    <div class="mb-2 text-4xl font-black text-cyan-500 dark:text-cyan-400"><span data-count-to="47">47</span>%</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">of collectors discover shows too late</div>
                </div>
                <div data-reveal class="p-6">
                    <div class="mb-2 text-4xl font-black text-rose-500 dark:text-rose-400">$0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">platform fees forever</div>
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
                    Everything to grow your <span class="es-brush"><span class="text-gradient-artist">collector base</span><svg class="es-brush-stroke" viewBox="0 0 300 14" preserveAspectRatio="none" aria-hidden="true"><path pathLength="1" d="M5,9 C70,3 130,12 185,6 C235,1 275,9 296,5"></path></svg></span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Exhibition announcements (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    Exhibition Announcements
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Tell collectors when you're showing</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">New exhibition opening? Art fair booth? Send branded announcements directly to collectors and fans. No algorithm deciding who sees your work.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Solo shows</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Group exhibitions</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Art fairs</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="es-smear max-w-xs rounded-2xl border border-sky-300 bg-gradient-to-br from-sky-100 to-cyan-100 p-4 dark:border-sky-400/30 dark:from-sky-950 dark:to-cyan-950">
                                        <div class="mb-3 flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-cyan-500 text-sm font-semibold text-white">EM</div>
                                            <div><div class="text-sm font-semibold text-gray-900 dark:text-white">Elena Martinez</div><div class="text-xs text-sky-600 dark:text-sky-300">Upcoming Exhibition</div></div>
                                        </div>
                                        <div class="rounded-xl border border-sky-400/20 bg-gradient-to-br from-sky-600/30 to-cyan-600/30 p-3">
                                            <div class="text-center">
                                                <div class="mb-1 text-xs font-semibold text-gray-900 dark:text-white">OPENING RECEPTION</div>
                                                <div class="text-sm font-bold text-sky-700 dark:text-sky-300">Fragments of Light</div>
                                                <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Gallery Row / Sat 6-9 PM</div>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex gap-4 text-xs">
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-emerald-500 dark:text-emerald-400">68%</span> opened</div>
                                            <div class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-amber-500 dark:text-amber-400">24%</span> clicked</div>
                                        </div>
                                    </div>
                                </div>
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
                            Ticketing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Zero platform fees on tickets</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Opening receptions, workshops, studio visits. 100% of Stripe payments go to you.</p>
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

                <!-- One link -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            Share Link
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One link for your portfolio & shows</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Put it in your website, Instagram bio, or artist statement. All your exhibitions in one place.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-2 rounded-lg border border-sky-400/30 bg-sky-500/20 p-2">
                                <svg aria-hidden="true" class="h-4 w-4 shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                <span class="truncate font-mono text-xs text-gray-900 dark:text-white">yourart.eventschedule.com</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1 text-center">
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Website</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">Instagram</div>
                                <div class="rounded bg-gray-200 p-1.5 text-[10px] text-sky-600 dark:bg-white/5 dark:text-sky-300">CV</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Venue sync (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    Venue Sync
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Galleries add your show, it auto-appears</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">When a gallery adds your exhibition to their calendar, it automatically appears on yours. One listing, both schedules updated.</p>
                            </div>
                            <div class="flex flex-col items-center justify-center gap-3" aria-hidden="true">
                                <span class="artist-frame-badge es-frame-chip glass inline-flex items-center gap-1.5 rounded-sm px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider">
                                    <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    Now on view
                                </span>
                                <div class="flex items-center gap-4">
                                    <div class="w-32 rounded-xl border border-blue-400/30 bg-blue-500/15 p-4">
                                        <div class="mb-2 text-center text-xs font-semibold text-blue-600 dark:text-blue-300">Gallery</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-gray-300 dark:bg-white/20"></div>
                                            <div class="h-2 w-3/4 rounded bg-blue-400/40"></div>
                                            <div class="h-2 w-1/2 rounded bg-gray-200 dark:bg-white/10"></div>
                                        </div>
                                        <div class="mt-3 rounded-lg border border-blue-400/30 bg-blue-400/20 p-2">
                                            <div class="text-center text-[10px] font-medium text-blue-700 dark:text-white">+ Your Show</div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <svg aria-hidden="true" class="es-sync-dot h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                        <span class="text-[10px] text-blue-500 dark:text-blue-400">auto-sync</span>
                                    </div>
                                    <div class="w-32 rounded-xl border border-gray-300 bg-gray-200 p-4 dark:border-white/20 dark:bg-white/10">
                                        <div class="mb-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-300">You</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 rounded bg-gray-300 dark:bg-white/20"></div>
                                            <div class="h-2 w-3/4 rounded bg-sky-400/40"></div>
                                            <div class="h-2 w-1/2 rounded bg-gray-200 dark:bg-white/10"></div>
                                        </div>
                                        <div class="mt-3 rounded-lg border border-sky-400/30 bg-sky-400/20 p-2">
                                            <div class="text-center text-[10px] font-medium text-gray-900 dark:text-white">New show!</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Event graphics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Auto-generated promo images</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Share exhibition announcements on social media instantly. No design tools needed.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative">
                                <div class="es-canvas-frame flex h-32 w-32 flex-col items-center justify-center bg-gradient-to-br from-sky-800 to-cyan-900 p-3 text-center">
                                    <div class="mb-1 text-[8px] uppercase tracking-wider text-sky-300">Exhibition</div>
                                    <div class="text-xs font-semibold text-white">Fragments of Light</div>
                                    <div class="mt-1 text-[8px] text-sky-300">Gallery Row / Mar 15</div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/80">
                                    <svg aria-hidden="true" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                </div>
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
                            Calendar Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Google Calendar for shows & deadlines</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync. Exhibitions, install dates, openings, and submission deadlines all in one place.</p>
                        <div class="mt-auto flex items-center justify-center gap-3" aria-hidden="true">
                            <div class="w-20 rounded-xl border border-blue-400/30 bg-blue-500/15 p-3">
                                <div class="mb-1 text-center text-[10px] text-blue-600 dark:text-blue-300">Schedule</div>
                                <div class="space-y-1">
                                    <div class="es-sync-dot h-1.5 rounded bg-sky-400/50"></div>
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

                <!-- Analytics (bottom right) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Analytics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Know your audience</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">See who views your schedule, which shows get the most interest, and where your collectors are.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2" style="--i: 0;">
                                <div class="w-16 text-[10px] text-gray-500 dark:text-gray-400">Views</div>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full bg-cyan-400/60" style="width: 82%"></div></div>
                                <div class="text-[10px] font-semibold text-cyan-500 dark:text-cyan-400">1.2k</div>
                            </div>
                            <div class="es-ai-field flex items-center gap-2" style="--i: 1;">
                                <div class="w-16 text-[10px] text-gray-500 dark:text-gray-400">Follows</div>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full bg-teal-400/60" style="width: 45%"></div></div>
                                <div class="text-[10px] font-semibold text-teal-500 dark:text-teal-400">87</div>
                            </div>
                            <div class="es-ai-field flex items-center gap-2" style="--i: 2;">
                                <div class="w-16 text-[10px] text-gray-500 dark:text-gray-400">Tickets</div>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-white/10"><div class="h-full rounded-full bg-emerald-400/60" style="width: 30%"></div></div>
                                <div class="text-[10px] font-semibold text-emerald-500 dark:text-emerald-400">34</div>
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
    <!-- 4. Exhibition season wall (dark band)                        -->
    <!-- ============================================================ -->
    @php
        $seasons = [
            ['Spring', 'text-sky-400', 'Open Studio', 'Invite collectors into your creative space', 'from-sky-100 to-cyan-100 dark:from-sky-900/50 dark:to-cyan-900/40 border-sky-300 dark:border-sky-500/30', '-1deg'],
            ['Summer', 'text-cyan-400', 'Art Fair', 'Booth at a regional or national fair', 'from-cyan-100 to-rose-100 dark:from-cyan-900/50 dark:to-rose-900/40 border-cyan-300 dark:border-cyan-500/30', '1deg'],
            ['Fall', 'text-rose-400', 'Solo Exhibition', 'Your own gallery show with opening reception', 'from-rose-100 to-sky-100 dark:from-rose-900/50 dark:to-sky-900/40 border-rose-300 dark:border-rose-500/30', '-1deg'],
            ['Winter', 'text-blue-400', 'Group Show', 'Collaborative exhibition with fellow artists', 'from-blue-100 to-sky-100 dark:from-blue-900/50 dark:to-sky-900/40 border-blue-300 dark:border-blue-500/30', '1deg'],
        ];
    @endphp
    <section class="bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-24 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 25%, rgba(20, 184, 166, 0.22), rgba(20, 184, 166, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 65%, rgba(239, 68, 68, 0.16), rgba(239, 68, 68, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-dab absolute inset-0">
                    @foreach ($dabs as [$l, $s, $d, $dl, $op, $col])
                        <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; background: {{ $col }}; --dab-dur: {{ $d }}; --dab-delay: {{ $dl }}; --dab-op: {{ $op }};"></span>
                    @endforeach
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-16 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Plan your <span class="es-brush"><span class="text-gradient-artist">exhibition season</span><svg class="es-brush-stroke" viewBox="0 0 300 14" preserveAspectRatio="none" aria-hidden="true"><path pathLength="1" d="M5,9 C70,4 130,11 185,6 C235,2 275,8 296,5"></path></svg></span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        One schedule for every show throughout the year.
                    </p>
                </div>

                <div class="relative">
                    <!-- Picture rail -->
                    <div class="absolute left-0 right-0 top-0 hidden h-px bg-gradient-to-r from-transparent via-white/25 to-transparent md:block" aria-hidden="true"></div>
                    <div class="grid grid-cols-1 gap-6 pt-8 md:grid-cols-4" data-reveal-group="90">
                        @foreach ($seasons as [$season, $accent, $title, $desc, $card, $rot])
                            <div data-reveal class="gallery-wall-card relative rounded-2xl border bg-gradient-to-br p-6 text-center transition-transform hover:rotate-0 {{ $card }}" style="transform: rotate({{ $rot }});">
                                <div class="absolute -top-8 left-1/2 hidden h-8 w-px -translate-x-1/2 bg-white/25 md:block" aria-hidden="true"></div>
                                <div class="absolute -top-9 left-1/2 hidden h-3 w-3 -translate-x-1/2 rounded-full border border-white/40 md:block" aria-hidden="true"></div>
                                <div class="mb-2 text-xs font-semibold uppercase tracking-wider {{ $accent }}">{{ $season }}</div>
                                <div class="mb-1 text-lg font-bold text-gray-900 dark:text-white">{{ $title }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $desc }}</div>
                            </div>
                        @endforeach
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
                    Built for every <span class="text-gradient-artist">visual medium</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether you work in oil or pixels, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Painters & Illustrators"
                    description="Gallery openings, studio shows, and art walks. Share your exhibition calendar and build a collector following."
                    icon-color="fuchsia"
                    blog-slug="for-painters-illustrators"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Sculptors & Installation Artists"
                    description="Site-specific installations, gallery exhibitions, and public art unveilings. Show collectors where to experience your work."
                    icon-color="violet"
                    blog-slug="for-sculptors-installation-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Photographers"
                    description="Photo exhibitions, gallery talks, and portfolio reviews. One link for all your shows and events."
                    icon-color="blue"
                    blog-slug="for-photographers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Printmakers"
                    description="Print exhibitions, studio sales, and edition releases. Let collectors know when new work is available."
                    icon-color="rose"
                    blog-slug="for-printmakers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Mixed Media Artists"
                    description="Interdisciplinary exhibitions, pop-up shows, and collaborative projects. Manage your eclectic schedule."
                    icon-color="amber"
                    blog-slug="for-mixed-media-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Digital Artists"
                    description="Online exhibitions, NFT drops, and digital gallery shows. Share your virtual and IRL events in one place."
                    icon-color="cyan"
                    blog-slug="for-digital-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
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
            ['1', 'Add your exhibitions', 'Gallery shows, open studios, art fairs, pop-ups. Import from Google Calendar or add them manually.'],
            ['2', 'Share your link', 'Add it to your website, Instagram bio, artist CV, or email signature. One link, always current.'],
            ['3', 'Build your collector base', 'Collectors follow your schedule and get notified about new exhibitions. Grow your audience on your terms.'],
        ];
    @endphp
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Three steps to a bigger <span class="text-gradient-artist">collector base</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl es-btn-artist text-xl font-bold text-white shadow-lg">
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
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-art-galleries', 'Art Galleries'], ['/for-dance-groups', 'Dance Groups'], ['/for-circus-acrobatics', 'Circus & Acrobatics'], ['/for-musicians', 'Musicians']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-related-card group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="text-lg font-semibold text-gray-900 transition-colors es-related-title dark:text-white">{{ $relName }}</div>
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
                    Frequently asked <span class="text-gradient-artist">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything visual artists ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for visual artists?', 'Yes. Event Schedule is free forever for sharing your exhibition and event schedule, building a collector following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan.'],
                    ['Can I list exhibitions, open studios, and art fairs together?', 'Yes. Use sub-schedules to organize by event type - solo exhibitions, group shows, open studios, art fairs, and workshops. Each event can include descriptions, images, venue details, and ticketing options.'],
                    ['How do collectors and art lovers discover my events?', 'Followers can receive email notifications when you add new exhibitions or events. Share your schedule link in your artist bio, on social media, or embed it on your portfolio website.'],
                    ['Can I sell tickets to openings and workshops?', 'Yes. Connect your Stripe account and sell tickets directly from your schedule. Perfect for ticketed opening receptions, art workshops, or studio tours. Zero platform fees - you only pay Stripe\'s processing fees.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(20, 184, 166, 0.3), rgba(20, 184, 166, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-dab absolute inset-0">
                        @foreach ($dabs as [$l, $s, $d, $dl, $op, $col])
                            <span style="left: {{ $l }}; width: {{ $s }}px; height: {{ $s }}px; background: {{ $col }}; --dab-dur: {{ $d }}; --dab-delay: {{ $dl }}; --dab-op: {{ $op }};"></span>
                        @endforeach
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your art deserves an audience. <span class="text-gradient-artist">Not an algorithm.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Stop relying on social media reach. Build your collector base directly. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-art" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl es-btn-artist px-8 py-4 text-lg font-semibold text-white shadow-xl transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl">
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
