<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Curators | Multi-Venue Events</x-slot>
    <x-slot name="description">Build the ultimate local guide. Use AI import to aggregate events from multiple sources and grow your following. For bloggers, orgs, and event aggregators.</x-slot>
    <x-slot name="breadcrumbTitle">For Curators</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Curators",
        "description": "Build the ultimate local guide. Use AI-powered import, aggregate events from multiple sources, and grow your following. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Event Curators"
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
                "name": "Is Event Schedule free for event curators?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for aggregating events, building a following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan."
                }
            },
            {
                "@type": "Question",
                "name": "Can I aggregate events from multiple venues and performers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Follow other schedules on Event Schedule and their events automatically appear on your curated calendar. You can also manually add events from any source using AI-powered import - just paste a URL or image and the event details are extracted automatically."
                }
            },
            {
                "@type": "Question",
                "name": "How do people discover my curated schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Followers receive email notifications when you add new events. Share your schedule link on social media, embed it on your blog or website, or send newsletters to your audience with upcoming highlights."
                }
            },
            {
                "@type": "Question",
                "name": "Can I control which events appear on my schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Enable the approval workflow to review events before they appear on your public schedule. Accept or reject submissions from venues and performers, keeping your curation quality high."
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
        "name": "Event Schedule for Curators",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Curation Software",
        "operatingSystem": "Web",
        "description": "Build the ultimate local guide. Use AI-powered import, aggregate events from multiple sources, and grow your following.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "AI-powered event import",
            "City search discovery",
            "Approval workflow",
            "Event aggregation",
            "Schedule graphics",
            "Follower notifications"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "event curator schedule, event promoter calendar, multi-venue event management, curator booking platform, free curator scheduling",
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
        /* For-curators "The Guide" styles. The shared es-* motion system lives
           in marketing.css; this holds the saffron-to-coral guide gradient, the
           city-map pin-network motif (dotted routes that draw themselves, one
           pin pulsing), the city-search map grid, the APPROVED stamp, and the
           accent recolor for the See-all links and related-page cards. */
        .text-gradient-guide {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-guide {
            background: linear-gradient(135deg, #fbbf24, #fb923c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* City-map pin network: 5 pins joined by dotted routes that draw
           themselves, one pin pulsing. A network, deliberately unlike a single
           roaming ping. */
        .es-mapnet { width: 100%; height: auto; overflow: visible; opacity: 0.4; }
        .dark .es-mapnet { opacity: 0.55; }
        .es-net-route {
            fill: none;
            stroke: #f97316;
            stroke-width: 1.6;
            stroke-linecap: round;
            stroke-dasharray: 0.5 5;
        }
        .dark .es-net-route { stroke: #fb923c; }
        .es-net-body { fill: #ea580c; }
        .dark .es-net-body { fill: #fb923c; }
        .es-net-eye { fill: rgba(255, 248, 237, 0.92); }
        .es-net-ring { fill: none; stroke: #f97316; stroke-width: 1.4; }
        .dark .es-net-ring { stroke: #fdba74; }
        .es-net-pin { transform-box: fill-box; transform-origin: center; }
        .es-net-pulse { transform-box: fill-box; transform-origin: center; opacity: 0; }

        /* Motion on: routes stream in, pins pop, one pin pulses. Gated behind
           .es-anim so no-JS / crawlers / reduced-motion see the network drawn. */
        .es-anim .es-net-route {
            opacity: 0;
            stroke-dashoffset: 130;
            animation: es-net-draw 2.6s ease-out forwards;
        }
        .es-anim .es-net-route.r2 { animation-delay: 0.4s; }
        .es-anim .es-net-route.r3 { animation-delay: 0.8s; }
        .es-anim .es-net-route.r4 { animation-delay: 1.2s; }
        .es-anim .es-net-route.r5 { animation-delay: 1.5s; }
        .es-anim .es-net-pin { opacity: 0; animation: es-net-pop 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
        .es-anim .es-net-pin.p1 { animation-delay: 0.25s; }
        .es-anim .es-net-pin.p2 { animation-delay: 0.7s; }
        .es-anim .es-net-pin.p3 { animation-delay: 1s; }
        .es-anim .es-net-pin.p4 { animation-delay: 1.3s; }
        .es-anim .es-net-pin.p5 { animation-delay: 1.6s; }
        .es-anim .es-net-pulse { animation: es-net-pulse 2.6s ease-out infinite; animation-delay: 2s; }
        @keyframes es-net-draw {
            0% { opacity: 0; stroke-dashoffset: 130; }
            30% { opacity: 1; }
            100% { opacity: 1; stroke-dashoffset: 0; }
        }
        @keyframes es-net-pop {
            0% { opacity: 0; transform: translateY(-4px) scale(0.4); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes es-net-pulse {
            0% { transform: scale(0.6); opacity: 0.55; }
            100% { transform: scale(3); opacity: 0; }
        }

        /* Faint map grid behind the city-search card (sits behind content via
           the z-index:-1 within .es-tilt-inner's will-change stacking context) */
        .es-mapgrid {
            z-index: -1;
            background-image:
                linear-gradient(to right, rgba(245, 158, 11, 0.10) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(245, 158, 11, 0.10) 1px, transparent 1px);
            background-size: 22px 22px;
            -webkit-mask-image: radial-gradient(ellipse 85% 75% at 72% 28%, #000 22%, transparent 78%);
            mask-image: radial-gradient(ellipse 85% 75% at 72% 28%, #000 22%, transparent 78%);
        }
        .dark .es-mapgrid {
            background-image:
                linear-gradient(to right, rgba(251, 146, 60, 0.13) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(251, 146, 60, 0.13) 1px, transparent 1px);
        }

        /* APPROVED stamp on the approval mock */
        .es-stamp {
            position: absolute;
            top: -0.6rem;
            right: 0.25rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.15rem 0.45rem;
            border: 2px solid rgba(234, 88, 12, 0.55);
            border-radius: 0.4rem;
            background: rgba(245, 158, 11, 0.10);
            color: #ea580c;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            transform: rotate(-8deg);
        }
        .es-stamp svg { width: 0.7rem; height: 0.7rem; }
        .dark .es-stamp {
            border-color: rgba(251, 146, 60, 0.6);
            background: rgba(251, 146, 60, 0.12);
            color: #fdba74;
        }
        .es-anim .es-stamp-float {
            animation: es-stamp-in 0.6s cubic-bezier(0.2, 1.5, 0.4, 1) both;
            animation-delay: 0.5s;
        }
        @keyframes es-stamp-in {
            0% { opacity: 0; transform: rotate(10deg) scale(1.8); }
            60% { opacity: 1; }
            100% { opacity: 1; transform: rotate(-8deg) scale(1); }
        }

        /* Accent recolor for the hard-coded blue See-all links + related cards */
        .guide-link { color: #ea580c; }
        .dark .guide-link { color: #fb923c; }
        .guide-rel { transition: all 0.2s ease; }
        .guide-rel:hover { border-color: rgba(245, 158, 11, 0.45); background-color: rgba(245, 158, 11, 0.06); }
        .dark .guide-rel:hover { border-color: rgba(249, 115, 22, 0.35); background-color: rgba(249, 115, 22, 0.07); }
        .guide-rel:hover .guide-rel-ink { color: #ea580c; }
        .dark .guide-rel:hover .guide-rel-ink { color: #fb923c; }

        @media (prefers-reduced-motion: reduce) {
            .es-net-route, .es-net-pin, .es-net-pulse, .es-stamp-float { animation: none !important; }
            .es-net-route { opacity: 0.85 !important; stroke-dashoffset: 0 !important; }
            .es-net-pin { opacity: 1 !important; }
            .es-net-pulse { opacity: 0 !important; }
            .es-stamp-float { opacity: 1 !important; transform: rotate(-8deg) !important; }
        }
    </style>

    @php
        $esPinNetwork = <<<'SVG'
<svg class="es-mapnet" viewBox="0 0 220 150" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
<path class="es-net-route r1" d="M38,66 Q62,50 92,44" />
<path class="es-net-route r2" d="M92,44 Q124,48 150,62" />
<path class="es-net-route r3" d="M38,66 Q50,92 72,112" />
<path class="es-net-route r4" d="M72,112 Q126,122 176,110" />
<path class="es-net-route r5" d="M150,62 Q170,84 176,110" />
<g transform="translate(38,66)"><g class="es-net-pin p1"><path class="es-net-body" d="M0,0 C-2.2,-3.6 -6,-6.6 -6,-11 A6,6 0 1,1 6,-11 C6,-6.6 2.2,-3.6 0,0 Z" /><circle class="es-net-eye" cx="0" cy="-11" r="2.3" /></g></g>
<g transform="translate(92,44)"><circle class="es-net-pulse es-net-ring" cx="0" cy="-11" r="6" /><g class="es-net-pin p2"><path class="es-net-body" d="M0,0 C-2.2,-3.6 -6,-6.6 -6,-11 A6,6 0 1,1 6,-11 C6,-6.6 2.2,-3.6 0,0 Z" /><circle class="es-net-eye" cx="0" cy="-11" r="2.3" /></g></g>
<g transform="translate(150,62)"><g class="es-net-pin p3"><path class="es-net-body" d="M0,0 C-2.2,-3.6 -6,-6.6 -6,-11 A6,6 0 1,1 6,-11 C6,-6.6 2.2,-3.6 0,0 Z" /><circle class="es-net-eye" cx="0" cy="-11" r="2.3" /></g></g>
<g transform="translate(72,112)"><g class="es-net-pin p4"><path class="es-net-body" d="M0,0 C-2.2,-3.6 -6,-6.6 -6,-11 A6,6 0 1,1 6,-11 C6,-6.6 2.2,-3.6 0,0 Z" /><circle class="es-net-eye" cx="0" cy="-11" r="2.3" /></g></g>
<g transform="translate(176,110)"><g class="es-net-pin p5"><path class="es-net-body" d="M0,0 C-2.2,-3.6 -6,-6.6 -6,-11 A6,6 0 1,1 6,-11 C6,-6.6 2.2,-3.6 0,0 Z" /><circle class="es-net-eye" cx="0" cy="-11" r="2.3" /></g></g>
</svg>
SVG;
    @endphp

    <!-- ============================================================ -->
    <!-- 1. Hero: the feed                                            -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.42), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(249, 115, 22, 0.4), rgba(249, 115, 22, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="pointer-events-none absolute inset-0 flex items-center justify-center"><div class="w-full max-w-2xl">{!! $esPinNetwork !!}</div></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Bloggers, Community Orgs & Aggregators</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.75rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Build the ultimate</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-guide es-gradient-anim">local guide</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Aggregate events from everywhere. Curate what's happening in your city or niche.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it works
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=curator') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Start curating
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Source-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-4xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Venues', 'Performers', 'Blogs', 'Eventbrite', 'Flyers', 'WhatsApp', 'Telegram', 'Instagram', 'Agendas', 'Newsletters'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-gray-200/70 bg-gray-100/80 px-4 py-1.5 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-amber-400 to-orange-400"></span>
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
    <section id="features" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-1.5" data-reveal>
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-amber-400 to-orange-400" aria-hidden="true"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:text-gray-300">The curator's toolkit</span>
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Everything happening, <span class="text-gradient-guide">in one place</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- AI-Powered Import (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                                    AI-Powered
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Smart event import</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Paste a URL, drop an image of a flyer or agenda, or copy text from anywhere. AI extracts event details automatically, even multiple events from a single image. No more manual data entry.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">URL parsing</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Image recognition</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Agenda scanning</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Any language</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="mb-3 max-w-xs rounded-2xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
                                        <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Paste URL or text</div>
                                        <div class="font-mono text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                                            eventbrite.com/e/jazz...<br>
                                            <span class="text-amber-500 dark:text-amber-400">Parsing...</span>
                                        </div>
                                    </div>
                                    <div class="my-2 flex justify-center">
                                        <svg aria-hidden="true" class="h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                                    </div>
                                    <div class="max-w-xs rounded-2xl border border-amber-300 bg-gradient-to-br from-amber-50 to-orange-50 p-4 dark:border-amber-400/30 dark:from-amber-950 dark:to-orange-950">
                                        <div class="mb-2 text-xs text-amber-600 dark:text-amber-300">Extracted</div>
                                        <div class="space-y-2 text-sm">
                                            <div class="es-ai-field flex justify-between" style="--i: 0;"><span class="text-gray-500 dark:text-gray-400">Name:</span><span class="text-gray-900 dark:text-white">Jazz Night</span></div>
                                            <div class="es-ai-field flex justify-between" style="--i: 1;"><span class="text-gray-500 dark:text-gray-400">Date:</span><span class="text-gray-900 dark:text-white">Mar 15, 8:00 PM</span></div>
                                            <div class="es-ai-field flex justify-between" style="--i: 2;"><span class="text-gray-500 dark:text-gray-400">Venue:</span><span class="text-gray-900 dark:text-white">Blue Note</span></div>
                                            <div class="es-ai-field flex justify-between" style="--i: 3;"><span class="text-gray-500 dark:text-gray-400">Price:</span><span class="text-gray-900 dark:text-white">$25</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- City Search Import -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="es-mapgrid absolute inset-0" aria-hidden="true"></div>
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            City Search
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Discover local events</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Search for events by city. Import what you find directly to your schedule with one click.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="mb-3 rounded-xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-gray-900">
                                <div class="flex items-center gap-2">
                                    <svg aria-hidden="true" class="h-4 w-4 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                                    <span class="text-sm text-gray-900 dark:text-white">Austin, TX</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="es-ai-field flex items-center gap-2 rounded-lg border border-cyan-400/30 bg-cyan-500/15 p-2" style="--i: 0;">
                                    <div class="flex-1"><div class="text-sm text-gray-900 dark:text-white">SXSW Panel</div><div class="text-xs text-cyan-600 dark:text-cyan-300">Convention Center</div></div>
                                    <span class="rounded bg-cyan-500/30 px-2 py-1 text-xs text-cyan-700 dark:text-cyan-200">Add</span>
                                </div>
                                <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;">
                                    <div class="flex-1"><div class="text-sm text-gray-600 dark:text-gray-300">Live Music @ Stubbs</div><div class="text-xs text-gray-500 dark:text-gray-400">Red River District</div></div>
                                    <span class="rounded bg-gray-200 px-2 py-1 text-xs text-gray-600 dark:bg-white/10 dark:text-gray-300">Add</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Approval Workflow -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Approval
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Review before publishing</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Events from external sources go to your inbox. Review, edit, and approve before they appear on your schedule.</p>
                        <div class="relative mt-auto space-y-2" aria-hidden="true">
                            <div class="es-stamp es-stamp-float">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                Approved
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-sky-400/30 bg-sky-500/15 p-3" style="--i: 0;">
                                <div class="flex-1"><div class="text-sm font-medium text-gray-900 dark:text-white">Jazz Night @ Blue Note</div><div class="text-xs text-sky-600 dark:text-sky-300">Pending review</div></div>
                                <div class="flex gap-1">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500/20"><svg aria-hidden="true" class="h-3 w-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-red-500/20"><svg aria-hidden="true" class="h-3 w-3 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></div>
                                </div>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl bg-gray-100 p-3 dark:bg-white/5" style="--i: 1;">
                                <div class="flex-1"><div class="text-sm font-medium text-gray-600 dark:text-gray-300">Comedy Show @ The Roxy</div><div class="text-xs text-gray-500 dark:text-gray-400">Pending review</div></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Aggregate Multiple Sources (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-100 px-3 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-800/30 dark:bg-rose-900/40 dark:text-rose-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                    Aggregation
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Unified calendar</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Pull events from venues, performers, and other curators. Display everything in one clean, searchable calendar.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">Sources</div>
                                <div class="space-y-2">
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg border border-rose-500/30 bg-rose-500/15 p-2" style="--i: 0;">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-500/30"><svg aria-hidden="true" class="h-4 w-4 text-rose-600 dark:text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg></div>
                                        <div class="flex-1 text-sm text-gray-900 dark:text-white">Blue Note Jazz Club</div>
                                        <span class="text-xs text-rose-600 dark:text-rose-300">12 events</span>
                                    </div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-200 dark:bg-white/10"><svg aria-hidden="true" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13" /></svg></div>
                                        <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Sarah Johnson Trio</div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">8 events</span>
                                    </div>
                                    <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-200 dark:bg-white/10"><svg aria-hidden="true" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2" /></svg></div>
                                        <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Austin Music Blog</div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">24 events</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Email Graphics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Email schedule graphics</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Generate shareable graphics of your weekly or monthly schedule. Perfect for newsletters and social media.</p>
                        <div class="mt-auto rounded-xl border border-amber-300 bg-gradient-to-br from-amber-50 to-orange-50 p-3 dark:border-amber-400/30 dark:from-amber-950 dark:to-orange-950" aria-hidden="true">
                            <div class="mb-2 text-center text-xs text-amber-600 dark:text-amber-300">This Week in Austin</div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-xs"><span class="w-8 text-gray-500 dark:text-gray-400">Fri</span><span class="text-gray-900 dark:text-white">Jazz @ Blue Note</span></div>
                                <div class="flex items-center gap-2 text-xs"><span class="w-8 text-gray-500 dark:text-gray-400">Sat</span><span class="text-gray-900 dark:text-white">Comedy @ Roxy</span></div>
                                <div class="flex items-center gap-2 text-xs"><span class="w-8 text-gray-500 dark:text-gray-400">Sun</span><span class="text-gray-900 dark:text-white">Art Walk</span></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Build Your Following -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Followers
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Build your audience</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Visitors can follow your schedule and get notified when you add new events. Engage followers with polls and interactive content.</p>
                        <div class="mt-auto text-center" aria-hidden="true">
                            <div class="mb-1 text-4xl font-black text-emerald-500 dark:text-emerald-400" data-count-to="2,847">2,847</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">followers</div>
                            <div class="mt-3 flex justify-center -space-x-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-emerald-500 to-teal-500 text-xs text-white dark:border-[#0a0a0f]">A</div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-teal-500 to-cyan-500 text-xs text-white dark:border-[#0a0a0f]">B</div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-cyan-500 to-blue-500 text-xs text-white dark:border-[#0a0a0f]">C</div>
                                <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-blue-500 to-sky-500 text-xs text-white dark:border-[#0a0a0f]">+</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Calendar Sync
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Google Calendar sync</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync keeps your curated calendar and Google Calendar in perfect harmony.</p>
                        <div class="relative mt-auto flex items-center justify-center gap-8 py-2" aria-hidden="true">
                            <div class="w-20 rounded-lg border border-blue-400/30 bg-blue-500/15 p-2">
                                <div class="mb-1 text-center text-[10px] font-medium text-blue-600 dark:text-blue-300">Guide</div>
                                <div class="mb-1 h-1.5 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                <div class="h-1.5 w-3/4 rounded bg-blue-400/40"></div>
                            </div>
                            <div class="absolute left-1/2 top-1/2 h-px w-10 -translate-x-1/2 -translate-y-1/2 border-t border-dashed border-blue-300 dark:border-blue-500/40"></div>
                            <div class="es-sync-dot" style="left: calc(50% - 24px);"></div>
                            <div class="w-20 rounded-lg border border-gray-300 bg-gray-100 p-2 dark:border-white/20 dark:bg-white/10">
                                <div class="mb-1 text-center text-[10px] font-medium text-gray-600 dark:text-gray-300">Google</div>
                                <div class="mb-1 h-1.5 rounded bg-blue-400/50"></div>
                                <div class="h-1.5 w-3/4 rounded bg-green-400/50"></div>
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
    <!-- 3. Already have a community?                                 -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    Already have a <span class="text-gradient-guide">community</span>?
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    If you're sharing events in a WhatsApp group, Facebook group, or anywhere else, you're already a curator.
                </p>
            </div>

            <div class="es-bento" data-reveal="panel">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-gray-50 p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <!-- Social platforms side -->
                        <div class="flex-1">
                            <div class="mb-6 flex flex-wrap justify-center gap-4 lg:justify-start" data-reveal-group="60">
                                <div data-reveal class="flex h-14 w-14 items-center justify-center rounded-2xl border border-[#25D366]/30 bg-[#25D366]/20">
                                    <svg aria-hidden="true" class="h-7 w-7 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </div>
                                <div data-reveal class="flex h-14 w-14 items-center justify-center rounded-2xl border border-[#1877F2]/30 bg-[#1877F2]/20">
                                    <svg aria-hidden="true" class="h-7 w-7 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </div>
                                <div data-reveal class="flex h-14 w-14 items-center justify-center rounded-2xl border border-[#0088cc]/30 bg-[#0088cc]/20">
                                    <svg aria-hidden="true" class="h-7 w-7 text-[#0088cc]" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                </div>
                                <div data-reveal class="flex h-14 w-14 items-center justify-center rounded-2xl border border-[#E4405F]/30 bg-[#E4405F]/20">
                                    <svg aria-hidden="true" class="h-7 w-7 text-[#E4405F]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.757-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
                                </div>
                                <div data-reveal class="flex h-14 w-14 items-center justify-center rounded-2xl border border-emerald-500/30 bg-emerald-500/20">
                                    <svg aria-hidden="true" class="h-7 w-7 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                            </div>
                            <p class="text-center text-gray-500 dark:text-gray-400 lg:text-left">
                                Your group is great for community, but posts get buried and new members miss old events.
                            </p>
                        </div>

                        <!-- Arrow/Plus -->
                        <div class="flex items-center justify-center">
                            <div class="hidden lg:block">
                                <svg aria-hidden="true" class="h-8 w-8 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            </div>
                            <div class="lg:hidden">
                                <svg aria-hidden="true" class="h-8 w-8 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                            </div>
                        </div>

                        <!-- Event Schedule side -->
                        <div class="flex-1">
                            <div class="mb-6 flex justify-center lg:justify-end">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/25">
                                    <svg aria-hidden="true" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            </div>
                            <p class="text-center text-gray-500 dark:text-gray-400 lg:text-right">
                                A dedicated event website is a permanent, searchable home anyone can discover.
                            </p>
                        </div>
                    </div>

                    <p class="mt-6 text-center font-semibold text-amber-600 dark:text-amber-400">
                        Your community + a professional event website
                    </p>

                    <div class="mt-10 border-t border-gray-200 pt-8 dark:border-white/10">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="80">
                            <div class="text-center" data-reveal>
                                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/20">
                                    <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                </div>
                                <h4 class="mb-1 font-semibold text-gray-900 dark:text-white">One link to share</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Share a single link instead of posting events repeatedly</p>
                            </div>
                            <div class="text-center" data-reveal>
                                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/20">
                                    <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                </div>
                                <h4 class="mb-1 font-semibold text-gray-900 dark:text-white">Searchable archive</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Followers can browse past and upcoming events anytime</p>
                            </div>
                            <div class="text-center" data-reveal>
                                <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/20">
                                    <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <h4 class="mb-1 font-semibold text-gray-900 dark:text-white">Reach new people</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Anyone can discover your events, even outside your group</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 text-center">
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-amber-500/25 transition-all hover:-translate-y-0.5 hover:shadow-xl">
                            Get Started Free
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                    <div class="es-glare" aria-hidden="true"></div>
                    <div class="es-ring-glow" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Stream to the world                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" data-reveal="panel" class="es-bento group block">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                Online Events
                            </div>
                            <h3 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400 lg:text-3xl">Stream to the world</h3>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Share live performances with fans worldwide. Add your streaming URL and sell tickets to viewers anywhere. No venue required.</p>
                            <div class="mb-4 flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Live streaming</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Global ticket sales</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Any platform</span>
                            </div>
                            <span class="inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                Learn more
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="w-52 rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="mb-4 flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-300">Online Event</span>
                                    <div class="relative h-5 w-10 rounded-full bg-sky-500"><div class="absolute right-0.5 top-0.5 h-4 w-4 rounded-full bg-white"></div></div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Zoom</span></div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-red-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">YouTube Live</span></div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-100 px-2 py-1.5 dark:bg-white/5"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-xs text-gray-600 dark:text-gray-300">Twitch</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="es-glare" aria-hidden="true"></div>
                    <div class="es-ring-glow" aria-hidden="true"></div>
                </div>
            </a>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Perfect for (curator types)                                  -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for every kind of <span class="text-gradient-guide">curator</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    However you gather what's happening, Event Schedule gives your guide a permanent home.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- City Guides -->
                <x-sub-audience-card
                    name="City Guides"
                    description="Aggregate every gig, market, and pop-up in your city into one guide locals actually check."
                    icon-color="amber"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Festival Programmers -->
                <x-sub-audience-card
                    name="Festival Programmers"
                    description="Publish your full multi-stage lineup and let attendees follow set times as they firm up."
                    icon-color="rose"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Scene Blogs -->
                <x-sub-audience-card
                    name="Scene Blogs"
                    description="Turn your niche coverage into a living calendar. Import events straight from posts and flyers."
                    icon-color="sky"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Campus Calendars -->
                <x-sub-audience-card
                    name="Campus Calendars"
                    description="Pull together club nights, lectures, and games so students never miss what's on."
                    icon-color="emerald"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Tourism & Visitor Boards -->
                <x-sub-audience-card
                    name="Tourism & Visitor Boards"
                    description="Show visitors everything happening this week, updated automatically as venues post."
                    icon-color="cyan"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18 15 15 0 010-18z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Community Newsletters -->
                <x-sub-audience-card
                    name="Community Newsletters"
                    description="Curate a weekly roundup and send the highlights straight to your subscribers' inboxes."
                    icon-color="orange"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. How it works (dark band)                                  -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.3), rgba(245, 158, 11, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 60%, rgba(249, 115, 22, 0.26), rgba(249, 115, 22, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center"><div class="w-full max-w-2xl">{!! $esPinNetwork !!}</div></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.07] px-4 py-1.5" data-reveal>
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400" aria-hidden="true"></span>
                        <span class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-300">Quick setup</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Start curating events in <span class="text-gradient-guide">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 text-xl font-bold text-white shadow-lg shadow-amber-500/30">1</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Create Your Guide</h3>
                        <p class="text-sm text-gray-400">Sign up and set up your local guide. Choose your city, niche, or theme.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 text-xl font-bold text-white shadow-lg shadow-amber-500/30">2</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Import Events</h3>
                        <p class="text-sm text-gray-400">Use AI import, city search, or aggregate from venues and performers.</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 text-xl font-bold text-white shadow-lg shadow-amber-500/30">3</div>
                        <h3 class="mb-2 text-lg font-semibold text-white">Grow Your Audience</h3>
                        <p class="text-sm text-gray-400">Share your guide. Followers get notified when you add new events.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-gray-50 py-20 dark:border-white/5 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-Schedules" description="Organize events into categories and groups" :url="marketing_url('/features/sub-schedules')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send event updates directly to followers' inboxes" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="guide-link inline-flex items-center font-medium hover:underline">
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
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-talent', 'Talent'], ['/for-venues', 'Venues'], ['/for-community-centers', 'Community Centers'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="group guide-rel flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 p-5 transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                            <div class="guide-rel-ink text-lg font-semibold text-gray-900 transition-colors dark:text-white">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="guide-rel-ink w-5 h-5 text-gray-400 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="guide-link inline-flex items-center font-medium hover:underline">
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
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-guide">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything curators ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for event curators?', 'Yes. Event Schedule is free forever for aggregating events, building a following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan.'],
                    ['Can I aggregate events from multiple venues and performers?', 'Yes. Follow other schedules on Event Schedule and their events automatically appear on your curated calendar. You can also manually add events from any source using AI-powered import - just paste a URL or image and the event details are extracted automatically.'],
                    ['How do people discover my curated schedule?', 'Followers receive email notifications when you add new events. Share your schedule link on social media, embed it on your blog or website, or send newsletters to your audience with upcoming highlights.'],
                    ['Can I control which events appear on my schedule?', 'Yes. Enable the approval workflow to review events before they appear on your public schedule. Accept or reject submissions from venues and performers, keeping your curation quality high.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-amber-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(245, 158, 11, 0.32), rgba(245, 158, 11, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center"><div class="w-full max-w-2xl">{!! $esPinNetwork !!}</div></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Become the go-to <span class="text-gradient-guide">local guide</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Start curating in minutes. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-city" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
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
