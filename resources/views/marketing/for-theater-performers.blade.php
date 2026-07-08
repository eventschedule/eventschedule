<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Theater Performers | Share Your Shows</x-slot>
    <x-slot name="description">Share your theater productions with audiences everywhere. Email your fans directly - no social media algorithms. Sell tickets to your shows with zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Theater Performers</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Theater Performers",
        "description": "Share your theater productions with audiences everywhere. Email your fans directly. Sell tickets to your shows with zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Theater Performers"
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
                "name": "Is Event Schedule free for theater performers?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your performance schedule, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales."
                }
            },
            {
                "@type": "Question",
                "name": "Can I list auditions and performances together?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Use sub-schedules to organize by type - performances, auditions, workshops, and rehearsals. Keep public shows visible to audiences while managing audition calls and rehearsal schedules separately."
                }
            },
            {
                "@type": "Question",
                "name": "How do audiences discover my performances?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can follow your schedule and receive email notifications when you add new shows. Share your schedule link in your actor bio, on social media, or embed it on your personal website."
                }
            },
            {
                "@type": "Question",
                "name": "What happens when a theater casts me in a production?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When a theater adds you to their production on Event Schedule, it automatically appears on your personal schedule too. Both calendars stay in sync without duplicate data entry."
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
        "name": "Event Schedule for Theater Performers",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Theater Production Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your theater productions with audiences everywhere. Email your fans directly - no algorithm. Sell tickets to your shows with zero platform fees.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Show run scheduling for multi-night productions",
            "Zero-fee ticket sales for performances",
            "Direct newsletter communication with audiences",
            "Virtual performance and livestream support",
            "Two-way Google Calendar sync",
            "Cast and crew coordination",
            "Auto-generated promotional graphics",
            "Multiple ticket types per performance",
            "Season announcement support"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "theater schedule, theater performance calendar, actor audition management, theater booking, free theater scheduling",
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
        /* For-theater-performers "In Lights" styles. The shared es-* motion
           system lives in marketing.css; this holds only the gold marquee
           gradient text and the chasing marquee bulbs along the top edge. */
        .text-gradient-marquee {
            background: linear-gradient(90deg, #d97706, #ca8a04, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-marquee {
            background: linear-gradient(90deg, #fcd34d, #facc15, #fcd34d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes es-marquee-bulb {
            0%, 100% { opacity: 0.3; box-shadow: 0 0 2px rgba(251, 191, 36, 0.3); }
            50% { opacity: 1; box-shadow: 0 0 8px rgba(251, 191, 36, 0.8), 0 0 12px rgba(251, 191, 36, 0.4); }
        }
        .es-bulb {
            animation: es-marquee-bulb 1.5s ease-in-out infinite;
            animation-delay: var(--d, 0s);
        }
        @media (prefers-reduced-motion: reduce) {
            .es-bulb { animation: none !important; opacity: 0.7 !important; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: the marquee                                         -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <!-- Marquee bulbs along the top edge -->
        <div class="absolute left-0 right-0 top-0 z-20 flex justify-center overflow-hidden" aria-hidden="true">
            <div class="flex gap-4 px-8 py-1.5">
                @for ($i = 0; $i < 30; $i++)
                    <span class="es-bulb h-2 w-2 shrink-0 rounded-full bg-amber-400" style="--d: {{ $i * 0.1 }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(220, 38, 38, 0.32), rgba(220, 38, 38, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(245, 158, 11, 0.42), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <div class="absolute left-0 top-0 h-full w-32 bg-gradient-to-r from-red-500/10 to-transparent dark:from-red-950/60 md:w-48"></div>
            <div class="absolute right-0 top-0 h-full w-32 bg-gradient-to-l from-red-500/10 to-transparent dark:from-red-950/60 md:w-48"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="h-2 w-2 animate-pulse rounded-full bg-amber-500 dark:bg-amber-400"></span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Actors, Companies & Productions</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.75rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your name</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-marquee es-gradient-anim">in lights</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Share your productions, sell tickets, and let audiences know when the curtain rises.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#features" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See what's included
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Create your schedule
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
                                @foreach (['Musical', 'Drama', 'Comedy', 'Improv', 'Fringe', 'Community Theater', 'Cabaret', 'Devised'] as $tag)
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
    <!-- 2. Production timeline                                       -->
    <!-- ============================================================ -->
    @php
        $timeline = [
            ['Auditions', 'Open call', 'bg-red-100 dark:bg-red-900/40', 'border-red-300 dark:border-red-700/50', 'text-red-600 dark:text-red-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />', false],
            ['Callbacks', 'Final selection', 'bg-orange-100 dark:bg-orange-900/40', 'border-orange-300 dark:border-orange-700/50', 'text-orange-600 dark:text-orange-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />', false],
            ['Rehearsals', 'Build the show', 'bg-yellow-100 dark:bg-yellow-900/40', 'border-yellow-300 dark:border-yellow-700/50', 'text-yellow-600 dark:text-yellow-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />', false],
            ['Tech Week', 'Lights & sound', 'bg-lime-100 dark:bg-lime-900/40', 'border-lime-300 dark:border-lime-700/50', 'text-lime-600 dark:text-lime-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />', false],
            ['Preview', 'First audience', 'bg-emerald-100 dark:bg-emerald-900/40', 'border-emerald-300 dark:border-emerald-700/50', 'text-emerald-600 dark:text-emerald-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />', false],
            ['Opening Night', 'Break a leg!', 'bg-amber-200 dark:bg-amber-700/60', 'border-amber-400 dark:border-amber-500', 'text-amber-600 dark:text-amber-300', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />', true],
            ['The Run', 'Fill every seat', 'bg-blue-100 dark:bg-blue-900/40', 'border-blue-300 dark:border-blue-700/50', 'text-blue-600 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />', false],
            ['Closing', 'Take a bow', 'bg-rose-100 dark:bg-rose-900/40', 'border-rose-300 dark:border-rose-700/50', 'text-rose-600 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />', false],
        ];
    @endphp
    <section class="border-t border-gray-200 bg-white py-20 dark:border-white/5 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    From auditions to <span class="text-gradient-marquee">closing night</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">Track every stage of your production journey</p>
            </div>

            <div class="relative">
                <div class="absolute left-0 right-0 top-8 hidden h-0.5 bg-gradient-to-r from-transparent via-amber-500/50 to-transparent lg:block" aria-hidden="true"></div>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-8" data-reveal-group="60">
                    @foreach ($timeline as [$tName, $tSub, $tChip, $tBorder, $tText, $tIcon, $tHi])
                        <div data-reveal class="group relative text-center">
                            <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full border-2 {{ $tChip }} {{ $tBorder }} transition-transform group-hover:scale-110 {{ $tHi ? 'shadow-lg shadow-amber-500/30' : '' }}">
                                <svg aria-hidden="true" class="h-7 w-7 {{ $tText }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $tIcon !!}</svg>
                            </div>
                            <div class="text-sm font-medium {{ $tHi ? 'text-amber-600 dark:text-amber-300' : 'text-gray-900 dark:text-white' }}">{{ $tName }}</div>
                            <div class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $tSub }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Now playing (show run)                                    -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-100 px-4 py-1.5 dark:border-red-700/30 dark:bg-red-900/30" data-reveal>
                    <span class="h-2 w-2 animate-pulse rounded-full bg-red-500"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-red-700 dark:text-red-300">Now Playing</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal style="--reveal-delay: 0.08s;">
                    Your entire run, <span class="text-gradient-marquee">one schedule</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.14s;">
                    Preview to closing night. Matinees and evening shows. Audiences see availability at a glance.
                </p>
            </div>

            <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                <div class="es-tilt-inner relative overflow-hidden rounded-2xl border border-amber-800/30 bg-gradient-to-b from-[#1a1515] to-[#0f0c0c] shadow-2xl" aria-hidden="true">
                    <div class="border-b border-amber-900/30 bg-gradient-to-r from-red-950 via-red-900 to-red-950 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="mb-1 text-xs uppercase tracking-widest text-amber-200">Riverside Players presents</div>
                                <div class="text-2xl font-semibold text-white">A Midsummer Night's Dream</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-stone-400">March 7 - 22</div>
                                <div class="text-sm text-amber-400">8 Performances</div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3 p-6">
                        <div class="es-ai-field flex items-center gap-4 rounded-xl border border-blue-500/20 bg-blue-500/10 p-4" style="--i: 0;">
                            <div class="w-14 shrink-0 text-center"><div class="text-xs uppercase text-blue-300">Fri</div><div class="text-xl font-semibold text-white">Mar 7</div></div>
                            <div class="flex-1"><div class="flex items-center gap-2"><span class="font-medium text-white">Preview Night</span><span class="inline-flex items-center rounded-full bg-blue-500/20 px-2 py-0.5 text-xs text-blue-300">Discounted</span></div><div class="text-sm text-stone-500">7:30 PM</div></div>
                            <div class="text-right"><div class="font-medium text-blue-300">$15</div><div class="text-xs text-stone-500">42 left</div></div>
                        </div>
                        <div class="es-ai-field relative flex items-center gap-4 overflow-hidden rounded-xl border border-amber-500/30 bg-amber-500/10 p-4" style="--i: 1;">
                            <div class="absolute right-0 top-0 rounded-bl bg-amber-500 px-2 py-0.5 text-xs font-semibold text-black">OPENING</div>
                            <div class="w-14 shrink-0 text-center"><div class="text-xs uppercase text-amber-300">Sat</div><div class="text-xl font-semibold text-white">Mar 8</div></div>
                            <div class="flex-1"><div class="font-medium text-white">Opening Night</div><div class="text-sm text-stone-500">7:30 PM - Post-show reception</div></div>
                            <div class="text-right"><div class="font-medium text-amber-300">$35</div><div class="text-xs text-amber-500">12 left</div></div>
                        </div>
                        <div class="es-ai-field flex items-center gap-4 rounded-xl border border-white/10 bg-white/5 p-4" style="--i: 2;">
                            <div class="w-14 shrink-0 text-center"><div class="text-xs uppercase text-stone-400">Sun</div><div class="text-xl font-semibold text-white">Mar 9</div></div>
                            <div class="flex-1"><div class="flex items-center gap-2"><span class="font-medium text-white">Matinee</span><span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2 py-0.5 text-xs text-emerald-300">Family-friendly</span></div><div class="text-sm text-stone-500">2:00 PM</div></div>
                            <div class="text-right"><div class="font-medium text-white">$25</div><div class="text-xs text-stone-500">68 left</div></div>
                        </div>
                        <div class="es-ai-field flex items-center gap-4 rounded-xl border border-white/10 bg-white/5 p-4" style="--i: 3;">
                            <div class="w-14 shrink-0 text-center"><div class="text-xs uppercase text-stone-400">Thu-Sat</div><div class="text-lg font-semibold text-white">Mar 13-15</div></div>
                            <div class="flex-1"><div class="font-medium text-white">Evening Performances</div><div class="text-sm text-stone-500">7:30 PM nightly</div></div>
                            <div class="text-right"><div class="font-medium text-white">$25</div><div class="text-xs text-stone-500">Available</div></div>
                        </div>
                        <div class="es-ai-field relative flex items-center gap-4 overflow-hidden rounded-xl border border-rose-500/20 bg-rose-500/10 p-4" style="--i: 4;">
                            <div class="absolute right-0 top-0 rounded-bl bg-rose-600 px-2 py-0.5 text-xs font-semibold text-white">FINAL</div>
                            <div class="w-14 shrink-0 text-center"><div class="text-xs uppercase text-rose-300">Sat</div><div class="text-xl font-semibold text-white">Mar 22</div></div>
                            <div class="flex-1"><div class="font-medium text-white">Closing Night</div><div class="text-sm text-stone-500">7:30 PM - Cast celebration</div></div>
                            <div class="text-right"><div class="font-medium text-rose-300">SOLD OUT</div></div>
                        </div>
                    </div>
                    <div class="es-glare" aria-hidden="true"></div>
                    <div class="es-ring-glow" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Season announcement                                       -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
                        Season Announcements
                    </div>
                    <h2 class="es-balance mb-6 text-3xl font-black leading-tight tracking-tight text-gray-900 dark:text-white md:text-5xl">
                        Announce your entire <span class="text-gradient-marquee">season at once</span>
                    </h2>
                    <p class="mb-8 text-lg leading-relaxed text-gray-500 dark:text-gray-400">
                        Theater companies plan seasons in advance. Add all your upcoming productions to one schedule. Subscribers get notified of your full season - and can plan which shows to see.
                    </p>
                    <ul class="space-y-3">
                        @foreach (['Multiple productions on one schedule', 'Season subscribers get first notice', 'Build anticipation months ahead'] as $benefit)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 shrink-0 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-gray-600 dark:text-gray-300">{{ $benefit }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative overflow-hidden rounded-2xl border border-amber-900/30 bg-gradient-to-br from-[#1a1515] to-[#0f0c0c] p-6 shadow-2xl" aria-hidden="true">
                        <div class="mb-6 border-b border-amber-900/20 pb-4 text-center">
                            <div class="mb-1 text-xs uppercase tracking-widest text-amber-400">Riverside Players</div>
                            <div class="text-2xl font-light text-white">2025-26 Season</div>
                        </div>
                        <div class="space-y-4">
                            <div class="es-ai-field rounded-xl border-l-2 border-blue-500 bg-gradient-to-r from-blue-900/30 to-transparent p-4" style="--i: 0;">
                                <div class="mb-1 text-xs uppercase tracking-wide text-blue-300">October 2025</div>
                                <div class="font-medium text-white">The Crucible</div>
                                <div class="text-sm text-stone-500">Arthur Miller</div>
                            </div>
                            <div class="es-ai-field rounded-xl border-l-2 border-emerald-500 bg-gradient-to-r from-emerald-900/30 to-transparent p-4" style="--i: 1;">
                                <div class="mb-1 text-xs uppercase tracking-wide text-emerald-300">December 2025</div>
                                <div class="font-medium text-white">A Christmas Carol</div>
                                <div class="text-sm text-stone-500">Charles Dickens, adapted</div>
                            </div>
                            <div class="es-ai-field rounded-xl border-l-2 border-amber-500 bg-gradient-to-r from-amber-900/30 to-transparent p-4" style="--i: 2;">
                                <div class="mb-1 text-xs uppercase tracking-wide text-amber-300">March 2026</div>
                                <div class="font-medium text-white">A Midsummer Night's Dream</div>
                                <div class="text-sm text-stone-500">William Shakespeare</div>
                            </div>
                            <div class="es-ai-field rounded-xl border-l-2 border-rose-500 bg-gradient-to-r from-rose-900/30 to-transparent p-4" style="--i: 3;">
                                <div class="mb-1 text-xs uppercase tracking-wide text-rose-300">May 2026</div>
                                <div class="font-medium text-white">Sweeney Todd</div>
                                <div class="text-sm text-stone-500">Stephen Sondheim</div>
                            </div>
                        </div>
                        <div class="mt-6 border-t border-amber-900/20 pt-4 text-center"><span class="text-sm text-amber-400">Season subscriptions available</span></div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Bento features                                            -->
    <!-- ============================================================ -->
    <section id="features" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Everything you need to <span class="text-gradient-marquee">fill the house</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">

                <!-- Zero-fee ticketing (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 dark:border-red-800/30 dark:bg-red-900/40 dark:text-red-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                                    Zero-Fee Ticketing
                                </div>
                                <h3 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Fill every seat, keep every dollar</h3>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Sell tickets directly through your schedule. Connect Stripe and you're ready to go. No platform fees - just payment processing.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Preview discounts</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Opening night premium</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Student rush</span>
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="max-w-xs rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="space-y-3">
                                        <div class="es-ai-field flex items-center justify-between rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 0;">
                                            <div><div class="font-medium text-gray-900 dark:text-white">Orchestra</div><div class="text-xs text-red-500 dark:text-red-400">24 remaining</div></div>
                                            <div class="text-xl font-semibold text-gray-900 dark:text-white">$35</div>
                                        </div>
                                        <div class="es-ai-field flex items-center justify-between rounded-xl border border-red-400/30 bg-red-500/15 p-3" style="--i: 1;">
                                            <div><div class="font-medium text-gray-900 dark:text-white">Balcony</div><div class="text-xs text-red-600 dark:text-red-300">48 remaining</div></div>
                                            <div class="text-xl font-semibold text-gray-900 dark:text-white">$25</div>
                                        </div>
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
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Newsletter
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Build your audience</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Fans subscribe directly. New production? Send an email. No algorithm decides who sees it.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative">
                                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-amber-600">
                                    <svg aria-hidden="true" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-amber-400 text-[10px] font-bold text-black">!</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Cast & crew -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Company
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Coordinate your ensemble</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Invite cast, crew, and stage management. Everyone sees the schedule. Changes sync instantly.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-100 p-2 dark:bg-white/10" style="--i: 0;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-500 text-xs font-semibold text-white">SM</div>
                                <div class="flex-1 text-sm text-gray-900 dark:text-white">Stage Manager</div>
                                <span class="inline-flex items-center rounded bg-blue-100 px-1.5 py-0.5 text-[10px] text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Admin</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5" style="--i: 1;">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-xs font-semibold text-white">JK</div>
                                <div class="flex-1 text-sm text-gray-600 dark:text-gray-300">Cast Member</div>
                                <span class="inline-flex items-center rounded bg-amber-100 px-1.5 py-0.5 text-[10px] text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">View</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Google Calendar sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Calendar
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sync with Google Calendar</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Two-way sync keeps rehearsals, tech week, and performances updated everywhere.</p>
                        <div class="relative mt-auto flex items-center justify-center gap-8 py-2" aria-hidden="true">
                            <div class="w-20 rounded-lg border border-emerald-400/30 bg-emerald-500/15 p-2">
                                <div class="mb-1 text-center text-[10px] font-medium text-emerald-600 dark:text-emerald-300">Schedule</div>
                                <div class="mb-1 h-1.5 rounded bg-gray-300/60 dark:bg-white/20"></div>
                                <div class="h-1.5 w-3/4 rounded bg-emerald-400/40"></div>
                            </div>
                            <div class="absolute left-1/2 top-1/2 h-px w-10 -translate-x-1/2 -translate-y-1/2 border-t border-dashed border-emerald-300 dark:border-emerald-500/40"></div>
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

                <!-- Virtual performances (link) -->
                <a href="{{ marketing_url('/features/online-events') }}" class="es-bento group relative block" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            Livestream
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-400">Stream performances worldwide</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Reach audiences who can't make it to the theater. Sell tickets to your stream.</p>
                        <span class="mt-auto inline-flex items-center gap-1 text-sm font-medium text-sky-600 transition-all group-hover:gap-2 dark:text-sky-400">
                            Learn more
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </span>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </a>

                <!-- Promo graphics -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Share-ready images</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate promotional graphics for social media. Announce your production instantly.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-28 w-28 rounded-xl border border-orange-400/30 bg-gradient-to-br from-orange-500/25 to-red-500/25 p-2">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br from-red-900/60 to-amber-900/60">
                                    <div class="mb-0.5 text-[8px] font-medium text-white">NOW PLAYING</div>
                                    <div class="text-center text-xs font-semibold text-amber-300">A Midsummer Night's Dream</div>
                                    <div class="mt-1 text-[8px] text-white/70">Mar 7-22</div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-orange-500">
                                    <svg aria-hidden="true" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
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
    <!-- 6. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Built for <span class="text-gradient-marquee">every stage</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From Broadway-style productions to black box experiments.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Musical Theater -->
                <x-sub-audience-card
                    name="Musical Theater"
                    description="Broadway-style musicals, revivals, original works, cabaret. Song, dance, and spectacle."
                    icon-color="amber"
                    blog-slug="for-musical-theater-performers"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🎵</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Drama & Straight Plays -->
                <x-sub-audience-card
                    name="Drama & Straight Plays"
                    description="Classic and contemporary dramas, tragedies, period pieces. The power of the spoken word."
                    icon-color="red"
                    blog-slug="for-drama-actors"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🎭</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Community Theater -->
                <x-sub-audience-card
                    name="Community Theater"
                    description="Local productions, volunteer casts, neighborhood playhouses. Theater for everyone."
                    icon-color="emerald"
                    blog-slug="for-community-theater-performers"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🏠</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Improv & Sketch -->
                <x-sub-audience-card
                    name="Improv & Sketch"
                    description="Comedy troupes, improv nights, sketch shows, variety acts. Yes, and..."
                    icon-color="violet"
                    blog-slug="for-improv-sketch-performers"
                >
                    <x-slot:icon>
                        <span class="text-2xl">😂</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Experimental & Fringe -->
                <x-sub-audience-card
                    name="Experimental & Fringe"
                    description="Avant-garde, site-specific, immersive theater, devised work. Pushing boundaries."
                    icon-color="cyan"
                    blog-slug="for-experimental-fringe-theater"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🔮</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Children's & Youth Theater -->
                <x-sub-audience-card
                    name="Children's & Youth Theater"
                    description="Family-friendly shows, school productions, youth ensembles. Inspiring the next generation."
                    icon-color="pink"
                    blog-slug="for-childrens-youth-theater"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🌟</span>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. How it works (dark band, playbill acts)                   -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(220, 38, 38, 0.26), rgba(220, 38, 38, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 60%, rgba(245, 158, 11, 0.26), rgba(245, 158, 11, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        From sign-up to <span class="text-gradient-marquee">sold-out</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="120">
                    @foreach ([['1', 'Act One', 'Add Your Production', 'Create your show run with all performance dates. Preview to closing night.'], ['2', 'Act Two', 'Share Your Schedule', 'One link for your website, programs, and social bios. Always current.'], ['3', 'Act Three', 'Fill Every Seat', 'Audiences follow you and get notified about new productions.']] as [$n, $act, $title, $desc])
                        <div class="relative rounded-2xl border border-white/10 bg-white/[0.05] p-7 text-center backdrop-blur-sm" data-reveal="panel">
                            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-xl font-bold text-white shadow-lg shadow-amber-500/30">{{ $n }}</div>
                            <div class="mb-2 text-xs uppercase tracking-wider text-amber-400">{{ $act }}</div>
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
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google Calendar" :url="marketing_url('/features/calendar-sync')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
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
                @foreach ([['/for-comedians', 'Comedians'], ['/for-dance-groups', 'Dance Groups'], ['/for-spoken-word', 'Spoken Word Artists'], ['/for-theaters', 'Theaters']] as [$relHref, $relName])
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
                    Frequently asked <span class="text-gradient-marquee">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything theater performers ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for theater performers?', 'Yes. Event Schedule is free forever for sharing your performance schedule, building an audience following, and syncing with Google Calendar. Ticketing and newsletters are available on the Pro plan, with zero platform fees on ticket sales.'],
                    ['Can I list auditions and performances together?', 'Yes. Use sub-schedules to organize by type - performances, auditions, workshops, and rehearsals. Keep public shows visible to audiences while managing audition calls and rehearsal schedules separately.'],
                    ['How do audiences discover my performances?', 'Fans can follow your schedule and receive email notifications when you add new shows. Share your schedule link in your actor bio, on social media, or embed it on your personal website.'],
                    ['What happens when a theater casts me in a production?', 'When a theater adds you to their production on Event Schedule, it automatically appears on your personal schedule too. Both calendars stay in sync without duplicate data entry.'],
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
                <div class="absolute left-0 right-0 top-0 z-20 flex justify-center overflow-hidden" aria-hidden="true">
                    <div class="flex gap-4 px-8 py-1.5">
                        @for ($i = 0; $i < 24; $i++)
                            <span class="es-bulb h-2 w-2 shrink-0 rounded-full bg-amber-400" style="--d: {{ $i * 0.1 }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <p class="mb-6 text-sm uppercase tracking-[0.2em] text-gray-400">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        The show must go on <span class="text-gradient-marquee">and audiences need to find it</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Join theater companies and performers who've simplified sharing their schedule.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-company" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Take the Stage
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
