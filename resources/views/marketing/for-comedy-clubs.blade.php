<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Comedy Clubs | Book & Promote Shows</x-slot>
    <x-slot name="description">Be the stage where comedy careers are made. Manage lineups, sell tickets with zero platform fees, and email your audience directly. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Comedy Clubs</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Comedy Clubs",
        "description": "Manage lineups, sell tickets with zero fees, and build your audience with newsletters. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Comedy Clubs"
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
                "name": "Is Event Schedule free for comedy clubs?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your show calendar, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I manage different show types like headliners, open mics, and improv?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by show type - headliner shows, open mic nights, improv jams, comedy workshops, and special events. Each show can have its own lineup, description, and ticket options."
                }
            },
            {
                "@type": "Question",
                "name": "How do comedy fans discover our shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your club's schedule and receive email notifications when new shows are announced. Embed your calendar on your website, share on social media, or send newsletters with upcoming lineups and headliners."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell tickets with different pricing for different shows?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set unique ticket types and prices per show - premium seats for headliners, discounted early bird tickets, or free entry for open mics. Connect Stripe and sell directly with zero platform fees."
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
        "name": "Event Schedule for Comedy Clubs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Comedy Club Management Software",
        "operatingSystem": "Web",
        "description": "Be the stage where comedy careers are made. From open mic to headliner - manage lineups, sell tickets with zero fees, and build your audience. Built for stand-up clubs, improv theaters, and open mic venues.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Lineup builder with performer profiles",
            "Open mic sign-up management",
            "Accept booking requests from comedians with video clips",
            "Recurring show scheduling",
            "Multiple room/stage support",
            "QR code ticketing with zero fees",
            "Direct newsletter to comedy fans",
            "Event analytics"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "comedy club calendar, comedy show schedule, comedy club booking, comedy event management, free comedy club scheduling",
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
        /* For-comedy-clubs "The Room" styles. The shared es-* motion system
           lives in marketing.css; this holds only the amber->yellow spotlight
           gradient text. */
        .text-gradient-comedy {
            background: linear-gradient(135deg, #f59e0b, #eab308, #facc15);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the room                                            -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.42), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(234, 179, 8, 0.4), rgba(234, 179, 8, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="absolute left-0 top-0 h-full w-32 bg-gradient-to-r from-red-500/10 to-transparent dark:from-red-950/70 md:w-48"></div>
            <div class="absolute right-0 top-0 h-full w-32 bg-gradient-to-l from-red-500/10 to-transparent dark:from-red-950/70 md:w-48"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5 shadow-[0_0_15px_rgba(251,191,36,0.25)]">
                <span class="h-2 w-2 animate-pulse rounded-full bg-amber-500 dark:bg-amber-400"></span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Comedy Clubs & Improv Theaters</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.3rem] font-black leading-[1.08] tracking-tight text-gray-900 dark:text-white sm:text-5xl lg:text-6xl">
                <span class="es-mask"><span class="es-mask-line">Running a comedy club is hard.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-comedy es-gradient-anim">Your software shouldn't be the joke.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Two drink minimum. Zero booking hassle. From open mic to Netflix special.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the week
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-400 to-yellow-500 px-8 py-4 text-lg font-semibold text-black shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Create your club's calendar
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Genre marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Stand-up', 'Improv', 'Open Mic', 'Showcase', 'Headliner', 'Recording Night', 'Sketch', 'Roast'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-amber-300 bg-amber-100/80 px-4 py-1.5 text-xs font-semibold text-amber-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-amber-400 to-yellow-400"></span>
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
    <!-- 2. The comedy club week                                      -->
    <!-- ============================================================ -->
    @php
        $comedyWeek = [
            ['MON', 'Dark', '', 'bg-gray-400 dark:bg-gray-700', 'text-gray-500 dark:text-gray-400', 'bg-gray-200/50 dark:bg-gray-800/30 border-gray-300/30 dark:border-gray-700/30'],
            ['TUE', 'Open Mic', 'New comics cut their teeth', 'bg-lime-500', 'text-lime-600 dark:text-lime-400', 'bg-lime-100/40 dark:bg-lime-900/30 border-lime-300/40 dark:border-lime-700/30'],
            ['WED', 'Improv Jam', 'Ensemble magic', 'bg-blue-500', 'text-blue-600 dark:text-blue-400', 'bg-blue-100/40 dark:bg-blue-900/30 border-blue-300/40 dark:border-blue-700/30'],
            ['THU', 'New Talent', 'Rising stars', 'bg-amber-500', 'text-amber-600 dark:text-amber-400', 'bg-amber-100/40 dark:bg-amber-900/30 border-amber-300/40 dark:border-amber-700/30'],
            ['FRI', 'Headliner', 'The draw', 'bg-yellow-400 shadow-lg shadow-yellow-500/50', 'text-yellow-600 dark:text-yellow-400', 'bg-yellow-100/50 dark:bg-yellow-900/40 border-yellow-300/50 dark:border-yellow-600/40'],
            ['SAT', 'Headliner', 'Sold out shows', 'bg-yellow-400 shadow-lg shadow-yellow-500/50', 'text-yellow-600 dark:text-yellow-400', 'bg-yellow-100/50 dark:bg-yellow-900/40 border-yellow-300/50 dark:border-yellow-600/40'],
            ['SUN', 'Industry', 'Podcast / Special', 'bg-blue-500', 'text-blue-600 dark:text-blue-400', 'bg-blue-100/40 dark:bg-blue-900/30 border-blue-300/40 dark:border-blue-700/30'],
        ];
    @endphp
    <section class="border-t border-gray-200 bg-white py-16 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <h2 class="es-balance mb-3 text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>The Comedy Club <span class="text-gradient-comedy">Week</span></h2>
                <p class="text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">Your weekly rhythm, automated. Set it once, it runs forever.</p>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-gradient-to-br from-gray-100 to-gray-200 p-6 dark:border-white/10 dark:from-gray-900 dark:to-gray-800/30" data-reveal="panel">
                <div class="flex min-w-[700px] gap-3" data-reveal-group="50">
                    @foreach ($comedyWeek as [$day, $label, $detail, $dot, $text, $card])
                        <div data-reveal class="flex-1 rounded-xl border p-4 text-center {{ $card }}">
                            <div class="mb-2 text-xs font-semibold {{ $text }}">{{ $day }}</div>
                            <div class="mx-auto mb-2 h-3 w-3 rounded-full {{ $dot }}"></div>
                            <div class="text-xs font-medium {{ $label === 'Dark' ? 'text-gray-600 dark:text-gray-300' : $text }}">{{ $label }}</div>
                            @if ($detail)
                                <div class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">{{ $detail }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">Recurring events auto-populate your calendar. One setup, endless shows.</p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Bento features                                            -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Everything to run the <span class="text-gradient-comedy">room</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Lineup builder (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    Lineup Builder
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Announce your lineup, build the buzz</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Feature your headliner, middle act, and host. Add their videos, link their profiles. Fans see who's on stage before they buy.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Multiple performers</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Video embeds</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Profile links</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs rounded-2xl border border-amber-300 bg-gradient-to-br from-amber-50 to-yellow-50 p-4 dark:border-amber-400/30 dark:from-amber-950 dark:to-yellow-950">
                                        <div class="mb-3 text-[10px] font-semibold tracking-wide text-amber-600 dark:text-amber-300">FRIDAY NIGHT LINEUP</div>
                                        <div class="space-y-3">
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg border border-amber-400/30 bg-amber-500/15 p-2" style="--i: 0;">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-yellow-500 text-xs font-bold text-white">SC</div>
                                                <div class="flex-1"><div class="text-sm font-semibold text-gray-900 dark:text-white">Sarah Chen</div><div class="text-xs text-amber-600 dark:text-amber-300">Headliner - 45 min</div></div>
                                                <svg aria-hidden="true" class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-red-500 text-xs font-bold text-white">MR</div>
                                                <div class="flex-1"><div class="text-sm font-medium text-gray-600 dark:text-gray-300">Mike Rodriguez</div><div class="text-xs text-gray-500 dark:text-gray-400">Feature - 20 min</div></div>
                                            </div>
                                            <div class="es-ai-field flex items-center gap-3 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-gray-500 to-gray-600 text-xs font-bold text-white">TK</div>
                                                <div class="flex-1"><div class="text-sm font-medium text-gray-600 dark:text-gray-300">Tony Kim</div><div class="text-xs text-gray-500 dark:text-gray-400">MC / Host</div></div>
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

                <!-- Open mic -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-lime-200 bg-lime-100 px-3 py-1.5 text-sm font-medium text-lime-700 dark:border-lime-800/30 dark:bg-lime-900/40 dark:text-lime-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            Open Mic
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Tuesday is sacred</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Your open mic runs itself. Comics sign up, you approve the list, they get their 5 minutes.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-lime-500/30 bg-lime-500/15 p-2" style="--i: 0;">
                                <span class="flex-1 text-xs text-gray-600 dark:text-gray-300">Jamie Lee</span>
                                <div class="flex gap-1">
                                    <div class="flex h-5 w-5 items-center justify-center rounded bg-emerald-500/30"><svg aria-hidden="true" class="h-3 w-3 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                                    <div class="flex h-5 w-5 items-center justify-center rounded bg-red-500/30"><svg aria-hidden="true" class="h-3 w-3 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></div>
                                </div>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><span class="flex-1 text-xs text-gray-500 dark:text-gray-400">Alex Torres</span><span class="text-[10px] text-lime-500 dark:text-lime-400">Approved</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><span class="flex-1 text-xs text-gray-500 dark:text-gray-400">Sam Park</span><span class="text-[10px] text-lime-500 dark:text-lime-400">Approved</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Booking inbox -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            Booking Inbox
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Comics pitch themselves</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">They submit their clips, you watch and decide. No cold emails, no back-and-forth.</p>
                        <div class="mt-auto rounded-xl border border-teal-400/30 bg-teal-500/15 p-3" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 text-xs font-semibold text-white">JL</div>
                                <div class="flex-1"><div class="text-sm font-medium text-gray-900 dark:text-white">Jamie Lee</div><div class="text-xs text-teal-600 dark:text-teal-300">Wants a showcase spot</div></div>
                            </div>
                            <div class="relative mb-3 aspect-video overflow-hidden rounded-lg bg-gray-200 dark:bg-[#0f0f14]">
                                <div class="absolute inset-0 flex items-center justify-center"><div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20"><svg aria-hidden="true" class="ml-0.5 h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div></div>
                                <div class="absolute bottom-1 right-1 rounded bg-black/50 px-1 text-[10px] text-white/70">3:42</div>
                            </div>
                            <span class="block w-full rounded-lg border border-teal-500/30 bg-teal-500/20 py-1.5 text-center text-xs font-medium text-teal-600 dark:text-teal-300">Watch Clip</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Zero-fee ticketing (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700 dark:border-green-800/30 dark:bg-green-900/40 dark:text-green-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    Zero-Fee Ticketing
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Keep 100% of ticket sales</h3>
                                <p class="text-lg text-gray-500 dark:text-gray-400">GA, VIP front row, two-drink minimum - however you price it. Scan QR codes at the door. No platform taking a cut.</p>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Multiple ticket types</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">QR scanning</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Direct to Stripe</span>
                                </div>
                            </div>
                            <div class="flex justify-center" aria-hidden="true">
                                <div class="w-full max-w-xs space-y-3">
                                    <div class="es-ai-field rounded-xl border border-green-500/30 bg-gradient-to-r from-green-50 to-emerald-50 p-4 dark:from-green-950 dark:to-emerald-950" style="--i: 0;">
                                        <div class="mb-1 flex items-center justify-between"><span class="font-medium text-gray-900 dark:text-white">General Admission</span><span class="font-bold text-green-600 dark:text-green-300">$20</span></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Standard seating</div>
                                    </div>
                                    <div class="es-ai-field rounded-xl border border-amber-500/30 bg-gradient-to-r from-amber-50 to-yellow-50 p-4 dark:from-amber-950 dark:to-yellow-950" style="--i: 1;">
                                        <div class="mb-1 flex items-center justify-between"><span class="font-medium text-gray-900 dark:text-white">VIP Front Row</span><span class="font-bold text-amber-600 dark:text-amber-300">$35</span></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Best seats + drink voucher</div>
                                    </div>
                                    <div class="es-ai-field rounded-xl border border-blue-500/30 bg-gradient-to-r from-blue-50 to-cyan-50 p-4 dark:from-blue-950 dark:to-cyan-950" style="--i: 2;">
                                        <div class="mb-1 flex items-center justify-between"><span class="font-medium text-gray-900 dark:text-white">Recording Night</span><span class="font-bold text-blue-600 dark:text-blue-300">$40</span></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Special taping event</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-yellow-200 bg-yellow-100 px-3 py-1.5 text-sm font-medium text-yellow-700 dark:border-yellow-800/30 dark:bg-yellow-900/40 dark:text-yellow-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Newsletter
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One click, every fan knows</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Thursday afternoon: send this week's lineup to everyone who follows you. No algorithm decides who sees it.</p>
                        <div class="mt-auto rounded-xl border border-yellow-400/30 bg-yellow-500/15 p-3" aria-hidden="true">
                            <div class="mb-2 flex items-center gap-2">
                                <div class="flex h-6 w-6 items-center justify-center rounded bg-gradient-to-br from-yellow-500 to-amber-500"><svg aria-hidden="true" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8" /></svg></div>
                                <span class="text-xs font-medium text-gray-900 dark:text-white">This Week at The Comedy Cellar</span>
                            </div>
                            <div class="mb-2 text-[10px] text-yellow-600 dark:text-yellow-300">Sending to 2,341 followers...</div>
                            <div class="h-1 overflow-hidden rounded-full bg-yellow-200 dark:bg-yellow-900/50"><div class="h-full animate-pulse rounded-full bg-yellow-400" style="width: 75%"></div></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Analytics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Know what fills seats</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Headliners vs. showcases. Friday vs. Saturday. See which comics draw crowds.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            @foreach ([['Headliner Shows', '94'], ['Showcase Nights', '76'], ['Open Mic', '58']] as [$label, $pct])
                                <div>
                                    <div class="mb-1 flex items-center justify-between"><span class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span><span class="text-xs font-medium text-cyan-600 dark:text-cyan-300">{{ $pct }}%</span></div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-cyan-500/20"><div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-cyan-400" style="width: {{ $pct }}%"></div></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multiple rooms -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            Multiple Rooms
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Main stage. Back room. Podcast corner.</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Separate calendars for each space. One venue, many vibes.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg border border-emerald-500/30 bg-emerald-500/15 p-2" style="--i: 0;"><div class="h-2 w-2 rounded-full bg-emerald-400"></div><span class="text-sm text-gray-900 dark:text-white">Main Showroom</span><span class="ml-auto text-xs text-emerald-600 dark:text-emerald-300">8 shows</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 1;"><div class="h-2 w-2 rounded-full bg-teal-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">The Basement</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">5 shows</span></div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/5" style="--i: 2;"><div class="h-2 w-2 rounded-full bg-blue-400"></div><span class="text-sm text-gray-600 dark:text-gray-300">Podcast Studio</span><span class="ml-auto text-xs text-gray-500 dark:text-gray-400">3 shows</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. From open mic to headliner                                -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>From Open Mic to <span class="text-gradient-comedy">Headliner</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Your club is where careers start.</p>
            </div>

            <div class="grid grid-cols-1 items-end gap-6 md:grid-cols-3" data-reveal-group="100">
                <div data-reveal class="rounded-2xl border border-lime-500/30 bg-gradient-to-br from-lime-100/40 to-green-100/40 p-8 text-center dark:from-lime-900/30 dark:to-green-900/30">
                    <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-lime-500/20"><div class="h-6 w-6 rounded-full bg-lime-400/60"></div></div>
                    <h3 class="mb-2 text-xl font-bold text-lime-600 dark:text-lime-400">Open Mic</h3>
                    <p class="text-gray-500 dark:text-gray-400">5 minutes to prove yourself</p>
                </div>
                <div data-reveal class="rounded-2xl border border-amber-500/30 bg-gradient-to-br from-amber-100/40 to-orange-100/40 p-8 text-center dark:from-amber-900/30 dark:to-orange-900/30">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-amber-500/20"><div class="h-10 w-10 rounded-full bg-amber-400/70"></div></div>
                    <h3 class="mb-2 text-xl font-bold text-amber-600 dark:text-amber-400">Feature</h3>
                    <p class="text-gray-500 dark:text-gray-400">25 minutes. The crowd knows your name.</p>
                </div>
                <div data-reveal class="rounded-2xl border border-yellow-500/40 bg-gradient-to-br from-yellow-100/50 to-amber-100/50 p-8 text-center shadow-lg shadow-yellow-500/10 dark:from-yellow-900/40 dark:to-amber-900/40">
                    <div class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-yellow-500/30 shadow-lg shadow-yellow-400/30"><div class="h-14 w-14 rounded-full bg-gradient-to-br from-yellow-400 to-amber-400"></div></div>
                    <h3 class="mb-2 text-2xl font-bold text-yellow-600 dark:text-yellow-400">Headliner</h3>
                    <p class="text-gray-500 dark:text-gray-400">Your name on the marquee</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Recording nights (online events)                          -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" data-reveal="panel" class="es-bento group block">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 dark:border-red-800/30 dark:bg-red-900/40 dark:text-red-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                Recording Nights
                            </div>
                            <h2 class="mb-3 text-2xl font-black tracking-tight text-gray-900 transition-colors group-hover:text-red-600 dark:text-white dark:group-hover:text-red-400 lg:text-3xl">Album recordings. Netflix tapings. The big nights.</h2>
                            <p class="mb-4 text-lg text-gray-500 dark:text-gray-400">Sell tickets to the live audience AND stream to fans worldwide. Your club, your special.</p>
                            <div class="mb-4 flex flex-wrap justify-center gap-3 lg:justify-start">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Live + Virtual tickets</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Global streaming</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">No capacity limits</span>
                            </div>
                            <span class="inline-flex items-center gap-2 font-medium text-red-600 transition-all group-hover:gap-3 dark:text-red-400">
                                Learn more about online events
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="w-56 rounded-2xl border border-red-200 bg-gray-100 p-6 dark:border-red-500/30 dark:bg-[#0f0f14]">
                                <div class="mb-4 text-xs font-medium text-gray-600 dark:text-gray-300">RECORDING IN PROGRESS</div>
                                <div class="mb-4 flex items-center justify-center gap-2"><div class="h-3 w-3 animate-pulse rounded-full bg-red-500"></div><span class="text-sm font-bold tracking-wide text-red-500 dark:text-red-400">LIVE</span></div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-200 px-3 py-2 dark:bg-white/5"><svg aria-hidden="true" class="h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg><span class="text-xs text-gray-600 dark:text-gray-300">2,847 watching</span></div>
                                    <div class="flex items-center gap-2 rounded-lg bg-gray-200 px-3 py-2 dark:bg-white/5"><svg aria-hidden="true" class="h-4 w-4 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span class="text-xs text-gray-600 dark:text-gray-300">14 countries</span></div>
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
    <!-- 6. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Perfect for all <span class="text-gradient-comedy">comedy venues</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From intimate open mics to major comedy clubs, Event Schedule fits your stage.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Stand-up Comedy Clubs"
                    description="Headliners, features, and open mic nights. Build your audience of comedy fans."
                    icon-color="amber"
                    blog-slug="for-stand-up-comedy-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Improv Theaters"
                    description="Harold nights, improv jams, and sketch shows. Perfect for ensemble performances."
                    icon-color="lime"
                    blog-slug="for-improv-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-lime-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Open Mic Venues"
                    description="New comic nights and amateur showcases. Help rising talent find their stage."
                    icon-color="green"
                    blog-slug="for-open-mic-comedy-venues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Comedy Bars & Restaurants"
                    description="Dinner shows and drink-minimum events. Comedy plus cuisine."
                    icon-color="orange"
                    blog-slug="for-comedy-bars-restaurants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Sketch Comedy Venues"
                    description="Revues, variety shows, and ensemble performances. Where sketch troupes shine."
                    icon-color="emerald"
                    blog-slug="for-sketch-comedy-venues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Live Podcast Studios"
                    description="Live recordings and audience participation shows. Bring podcasts to life."
                    icon-color="purple"
                    blog-slug="for-live-podcast-studios"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. How it works (dark band)                                  -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.28), rgba(245, 158, 11, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 60%, rgba(234, 179, 8, 0.26), rgba(234, 179, 8, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Get your comedy club online in <span class="text-gradient-comedy">three steps</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    @foreach ([['1', 'Set up your room', 'Add your club, rooms (main stage, back room), and your open mic schedule. Set up recurring shows once.'], ['2', 'Build your lineup', 'Add headliners, approve open mic requests, link comedian profiles with their clips. Show fans who\'s on stage.'], ['3', 'Grow your audience', 'Fans follow your club, get the weekly lineup, and buy tickets directly from you. No middleman.']] as [$n, $title, $desc])
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-yellow-500 text-xl font-bold text-white shadow-lg shadow-amber-500/30">{{ $n }}</div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Key features                                              -->
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
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-Schedules" description="Organize events into categories and groups" :url="marketing_url('/features/sub-schedules')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></x-slot:icon>
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
    <!-- 9. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([['/for-venues', 'Venues'], ['/for-bars', 'Bars'], ['/for-comedians', 'Comedians'], ['/for-nightclubs', 'Nightclubs']] as [$relHref, $relName])
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
    <!-- 10. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-comedy">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything comedy clubs ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for comedy clubs?', 'Yes. Event Schedule is free forever for sharing your show calendar, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I manage different show types like headliners, open mics, and improv?', 'Yes. Use sub-schedules to organize by show type - headliner shows, open mic nights, improv jams, comedy workshops, and special events. Each show can have its own lineup, description, and ticket options.'],
                    ['How do comedy fans discover our shows?', 'Fans can follow your club\'s schedule and receive email notifications when new shows are announced. Embed your calendar on your website, share on social media, or send newsletters with upcoming lineups and headliners.'],
                    ['Can I sell tickets with different pricing for different shows?', 'Yes. Set unique ticket types and prices per show - premium seats for headliners, discounted early bird tickets, or free entry for open mics. Connect Stripe and sell directly with zero platform fees.'],
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
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-amber-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(245, 158, 11, 0.32), rgba(245, 158, 11, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Be the room where it <span class="text-gradient-comedy">happens</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Build your audience. Launch careers. Keep every dollar. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-club" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-400 to-yellow-500 px-8 py-4 text-lg font-semibold text-black shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-yellow-500/40">
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
