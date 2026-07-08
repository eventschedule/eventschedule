<x-marketing-layout>
    {{-- SEO Slots --}}
    <x-slot name="title">Event Schedule Examples | Live Demo Schedules to Explore</x-slot>
    <x-slot name="description">Explore {{ $scheduleCount }} live demo schedules showcasing Event Schedule. See real examples for fitness studios, music venues, yoga retreats, community groups, and more.</x-slot>
    <x-slot name="breadcrumbTitle">Examples</x-slot>

    {{-- Structured Data for Rich Results --}}
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "Event Schedule Examples",
        "description": "A gallery of {{ $scheduleCount }} live demo schedules showcasing Event Schedule features for various industries",
        "url": "{{ url('/examples') }}",
        "numberOfItems": {{ $scheduleCount }},
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "Live Event Schedule Demos",
        "description": "Explore real examples of Event Schedule in action",
        "numberOfItems": {{ $scheduleCount }},
        "itemListElement": [
            @foreach($allSchedules as $index => $schedule)
            {
                "@type": "ListItem",
                "position": {{ $index + 1 }},
                "name": "{{ $schedule['name'] }}",
                "url": "{{ $schedule['url'] }}"
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Can I create a schedule like these?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes! Event Schedule is free to use. Sign up and you can create a schedule just like these examples in minutes. All the features you see are available on the free plan."
                }
            },
            {
                "@type": "Question",
                "name": "Are these real schedules?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "These are demo schedules we've created to showcase different use cases and features. They demonstrate what's possible with Event Schedule for various industries including fitness, music, community groups, and more."
                }
            },
            {
                "@type": "Question",
                "name": "How long does it take to set up?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Most users have their schedule live within 5 minutes. Simply sign up, add your events (or import from Google Calendar), customize your colors, and share your link. No technical skills required."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (blue to sky to cyan) */
        .text-gradient-demos {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-demos {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-demos {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature motif: a filmstrip of gallery card chips */
        .es-card-chip {
            flex: 0 0 auto;
            height: 22px;
            border-radius: 6px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.6), rgba(14, 165, 233, 0.3));
            animation: es-chip-pulse var(--cp-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--cp-delay, 0s);
        }
        @keyframes es-chip-pulse {
            0%, 100% { opacity: 0.2; transform: translateY(2px) scale(0.94); }
            50% { opacity: 0.85; transform: translateY(-2px) scale(1); filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.4)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-card-chip, .animate-pulse-slow { animation: none !important; }
            .es-card-chip { opacity: 0.55; transform: none; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    {{-- Hero Section --}}
    <section class="es-hero relative flex min-h-[calc(66svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            {{-- Gallery-chip motif along the bottom edge --}}
            <div class="absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-4 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 14; $i++)
                    @php $w = [56, 84, 44, 72, 100][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.28; @endphp
                    <span class="es-card-chip" style="width: {{ $w }}px; --cp-dur: {{ $dur }}s; --cp-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">{{ $scheduleCount }} Live Examples</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">See Event Schedule</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-demos">in action</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-600 dark:text-gray-400 sm:text-xl">
                Explore demo schedules for fitness studios, music venues, community groups, and more. Click any example to see Event Schedule live.
            </p>

            <div class="es-fade-up es-d-3 flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Create your schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Demo Grid Section with Category Grouping --}}
    <section class="bg-gray-50 py-24 dark:bg-[#0d0d14]" aria-labelledby="demos-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 id="demos-heading" class="sr-only">Demo Schedules Gallery</h2>

            @php
                $categoryIcons = [
                    'Fitness & Wellness' => '<svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>',
                    'Music & Entertainment' => '<svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>',
                    'Community & Recreation' => '<svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
                    'Creative & Workshops' => '<svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>',
                    'Springfield' => '<svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
                ];
                $categoryColors = [
                    'Fitness & Wellness' => 'bg-teal-100 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400',
                    'Music & Entertainment' => 'bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400',
                    'Community & Recreation' => 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400',
                    'Creative & Workshops' => 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400',
                    'Springfield' => 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-600 dark:text-yellow-400',
                ];
                $categoryHoverColors = [
                    'Fitness & Wellness' => 'hover:shadow-teal-500/20 hover:border-teal-400/50 dark:hover:border-teal-400/30',
                    'Music & Entertainment' => 'hover:shadow-sky-500/20 hover:border-sky-400/50 dark:hover:border-sky-400/30',
                    'Community & Recreation' => 'hover:shadow-emerald-500/20 hover:border-emerald-400/50 dark:hover:border-emerald-400/30',
                    'Creative & Workshops' => 'hover:shadow-amber-500/20 hover:border-amber-400/50 dark:hover:border-amber-400/30',
                    'Springfield' => 'hover:shadow-yellow-500/20 hover:border-yellow-400/50 dark:hover:border-yellow-400/30',
                ];
                $scheduleLabels = [
                    // Fitness & Wellness
                    'meditationclasses' => 'Mindfulness',
                    'weekendyogaretreat' => 'Retreat',
                    'hikingclub' => 'Outdoors',
                    // Music & Entertainment
                    'battleofthebands' => 'Competition',
                    'sufficientgroundscoffeemusic' => 'Cafe',
                    'villageidiot' => 'Pub',
                    // Community & Recreation
                    'communityyouthgroup' => 'Youth',
                    'karateclub' => 'Dojo',
                    'countyfairgrounds' => 'Events',
                    // Creative & Workshops
                    'nateswoodworkingshop' => 'Crafts',
                    'painting' => 'Art Studio',
                    'pagesbooknookshop' => 'Bookshop',
                    // Springfield
                    'simpsons' => 'Curator',
                    'demo-moestavern' => 'Bar',
                    'demo-amphitheater' => 'Venue',
                    'demo-bowlarama' => 'Bowling',
                    'demo-aztectheater' => 'Cinema',
                    'demo-lardlad' => 'Donuts',
                ];
            @endphp
            @foreach($categories as $categoryName => $schedules)
            @if($categoryName === 'Springfield')
                @continue
            @endif
            <div class="mb-16 border-t border-gray-200 pt-8 first:border-t-0 first:pt-0 last:mb-0 dark:border-white/10">
                <div class="mb-8 flex items-center gap-3" data-reveal>
                    <div class="rounded-xl p-2.5 {{ $categoryColors[$categoryName] ?? 'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400' }}">
                        {!! $categoryIcons[$categoryName] ?? '<svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>' !!}
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $categoryName }}</h3>
                </div>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3" role="list" data-reveal-group="70">
                @foreach($schedules as $schedule)
                <article role="listitem" data-reveal>
                    <a href="{{ $schedule['url'] }}"
                       target="_blank"
                       rel="noopener"
                       aria-label="View {{ $schedule['name'] }} schedule"
                       class="group relative block h-full overflow-hidden rounded-3xl border border-gray-200 shadow-md transition-all duration-300 ease-out hover:scale-[1.02] hover:shadow-2xl dark:border-white/10 {{ $categoryHoverColors[$categoryName] ?? 'hover:shadow-blue-500/20 hover:border-blue-400/50 dark:hover:border-blue-400/30' }}">

                        {{-- Category badge --}}
                        <div class="absolute left-4 top-4 z-10">
                            <span class="inline-flex items-center rounded-full bg-white/95 px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm backdrop-blur-md dark:bg-gray-900/95 dark:text-gray-200">
                                {{ $scheduleLabels[$schedule['subdomain']] ?? 'Demo' }}
                            </span>
                        </div>

                        @if($schedule['header_image_url'] ?? false)
                            {{-- Header image background --}}
                            <div class="absolute inset-0 overflow-hidden">
                                <picture class="absolute inset-0">
                                    <source srcset="{{ url(webp_path($schedule['header_image_url'])) }}" type="image/webp">
                                    <img src="{{ url($schedule['header_image_url']) }}"
                                         alt="{{ $schedule['name'] }} header image"
                                         class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                         loading="lazy"
                                         decoding="async"
                                         width="400"
                                         height="280">
                                </picture>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
                            </div>

                            {{-- Content overlay --}}
                            <div class="relative flex min-h-[280px] flex-col justify-end p-8">
                                @if($schedule['profile_image_url'] ?? false)
                                    <picture class="mb-4 h-16 w-16 overflow-hidden rounded-full shadow-lg ring-4 ring-white/30 transition-transform duration-300 group-hover:-translate-y-1">
                                        <source srcset="{{ url(webp_path($schedule['profile_image_url'])) }}" type="image/webp">
                                        <img src="{{ url($schedule['profile_image_url']) }}"
                                             alt="{{ $schedule['name'] }} logo"
                                             class="h-full w-full object-cover"
                                             loading="lazy"
                                             decoding="async"
                                             width="64"
                                             height="64">
                                    </picture>
                                @else
                                    {{-- Initial letter fallback for schedules without profile image --}}
                                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#4E81FA] text-xl font-bold text-white shadow-lg ring-4 ring-white/30"
                                         aria-hidden="true">
                                        {{ strtoupper(substr($schedule['name'], 0, 1)) }}
                                    </div>
                                @endif
                                <h4 class="mb-2 text-2xl font-bold text-white">{{ $schedule['name'] }}</h4>
                                @if($schedule['description'] ?? false)
                                    <p class="text-sm text-white/70">{{ $schedule['description'] }}</p>
                                @endif
                            </div>
                        @else
                            {{-- Fallback gradient background --}}
                            <div class="h-full bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900/50 dark:to-sky-900/50">
                                <div class="flex min-h-[280px] flex-col justify-end p-8">
                                    @if($schedule['profile_image_url'] ?? false)
                                        <picture class="mb-4 h-16 w-16 overflow-hidden rounded-full shadow-lg ring-4 ring-gray-200 transition-transform duration-300 group-hover:-translate-y-1 dark:ring-white/20">
                                            <source srcset="{{ url(webp_path($schedule['profile_image_url'])) }}" type="image/webp">
                                            <img src="{{ url($schedule['profile_image_url']) }}"
                                                 alt="{{ $schedule['name'] }} logo"
                                                 class="h-full w-full object-cover"
                                                 loading="lazy"
                                                 decoding="async"
                                                 width="64"
                                                 height="64">
                                        </picture>
                                    @else
                                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#4E81FA] text-xl font-bold text-white shadow-lg ring-4 ring-gray-200 dark:ring-white/20"
                                             aria-hidden="true">
                                            {{ strtoupper(substr($schedule['name'], 0, 1)) }}
                                        </div>
                                    @endif
                                    <h4 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $schedule['name'] }}</h4>
                                    @if($schedule['description'] ?? false)
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schedule['description'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- External link indicator --}}
                        <div class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100">
                            <svg class="h-5 w-5 drop-shadow-lg {{ ($schedule['header_image_url'] ?? false) ? 'text-white' : 'text-gray-600 dark:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </div>
                    </a>
                </article>
                @endforeach
                </div>
            </div>
            @endforeach

            @if(count($categories) === 0)
                <div class="py-12 text-center">
                    <p class="text-gray-600 dark:text-gray-400">Demo schedules coming soon.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Springfield Demo Town Section --}}
    @if(isset($categories['Springfield']))
    <section class="bg-gray-50 pb-24 dark:bg-[#0d0d14]" aria-labelledby="springfield-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="border-t border-gray-200 pt-12 dark:border-white/10">
                <div class="mb-3 flex items-center gap-3" data-reveal>
                    <div class="rounded-xl bg-yellow-100 p-2.5 text-yellow-600 dark:bg-yellow-500/20 dark:text-yellow-400">
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h2 id="springfield-heading" class="text-2xl font-bold text-gray-900 dark:text-white">Springfield Demo Town</h2>
                </div>
                <p class="mb-8 max-w-2xl text-gray-500 dark:text-gray-400" data-reveal>Simpsons-themed demo schedules showing how the platform works for a fictional town's venues and businesses.</p>

                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3" role="list" data-reveal-group="70">
                @foreach($categories['Springfield'] as $schedule)
                <article role="listitem" data-reveal>
                    <a href="{{ $schedule['url'] }}"
                       target="_blank"
                       rel="noopener"
                       aria-label="View {{ $schedule['name'] }} schedule"
                       class="group relative block h-full overflow-hidden rounded-3xl border border-gray-200 shadow-md transition-all duration-300 ease-out hover:scale-[1.02] hover:shadow-2xl hover:border-yellow-400/50 hover:shadow-yellow-500/20 dark:border-white/10 dark:hover:border-yellow-400/30">

                        {{-- Category badge --}}
                        <div class="absolute left-4 top-4 z-10">
                            <span class="inline-flex items-center rounded-full bg-white/95 px-2.5 py-1 text-xs font-medium text-gray-700 shadow-sm backdrop-blur-md dark:bg-gray-900/95 dark:text-gray-200">
                                {{ $scheduleLabels[$schedule['subdomain']] ?? 'Demo' }}
                            </span>
                        </div>

                        @if($schedule['header_image_url'] ?? false)
                            {{-- Header image background --}}
                            <div class="absolute inset-0 overflow-hidden">
                                <picture class="absolute inset-0">
                                    <source srcset="{{ url(webp_path($schedule['header_image_url'])) }}" type="image/webp">
                                    <img src="{{ url($schedule['header_image_url']) }}"
                                         alt="{{ $schedule['name'] }} header image"
                                         class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                         loading="lazy"
                                         decoding="async"
                                         width="400"
                                         height="280">
                                </picture>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
                            </div>

                            {{-- Content overlay --}}
                            <div class="relative flex min-h-[280px] flex-col justify-end p-8">
                                @if($schedule['profile_image_url'] ?? false)
                                    <picture class="mb-4 h-16 w-16 overflow-hidden rounded-full shadow-lg ring-4 ring-white/30 transition-transform duration-300 group-hover:-translate-y-1">
                                        <source srcset="{{ url(webp_path($schedule['profile_image_url'])) }}" type="image/webp">
                                        <img src="{{ url($schedule['profile_image_url']) }}"
                                             alt="{{ $schedule['name'] }} logo"
                                             class="h-full w-full object-cover"
                                             loading="lazy"
                                             decoding="async"
                                             width="64"
                                             height="64">
                                    </picture>
                                @else
                                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#4E81FA] text-xl font-bold text-white shadow-lg ring-4 ring-white/30"
                                         aria-hidden="true">
                                        {{ strtoupper(substr($schedule['name'], 0, 1)) }}
                                    </div>
                                @endif
                                <h4 class="mb-2 text-2xl font-bold text-white">{{ $schedule['name'] }}</h4>
                                @if($schedule['description'] ?? false)
                                    <p class="text-sm text-white/70">{{ $schedule['description'] }}</p>
                                @endif
                            </div>
                        @else
                            {{-- Fallback gradient background --}}
                            <div class="h-full bg-gradient-to-br from-yellow-100 to-amber-100 dark:from-yellow-900/50 dark:to-amber-900/50">
                                <div class="flex min-h-[280px] flex-col justify-end p-8">
                                    @if($schedule['profile_image_url'] ?? false)
                                        <picture class="mb-4 h-16 w-16 overflow-hidden rounded-full shadow-lg ring-4 ring-gray-200 transition-transform duration-300 group-hover:-translate-y-1 dark:ring-white/20">
                                            <source srcset="{{ url(webp_path($schedule['profile_image_url'])) }}" type="image/webp">
                                            <img src="{{ url($schedule['profile_image_url']) }}"
                                                 alt="{{ $schedule['name'] }} logo"
                                                 class="h-full w-full object-cover"
                                                 loading="lazy"
                                                 decoding="async"
                                                 width="64"
                                                 height="64">
                                        </picture>
                                    @else
                                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#4E81FA] text-xl font-bold text-white shadow-lg ring-4 ring-gray-200 dark:ring-white/20"
                                             aria-hidden="true">
                                            {{ strtoupper(substr($schedule['name'], 0, 1)) }}
                                        </div>
                                    @endif
                                    <h4 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $schedule['name'] }}</h4>
                                    @if($schedule['description'] ?? false)
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schedule['description'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- External link indicator --}}
                        <div class="absolute right-4 top-4 opacity-0 transition-opacity group-hover:opacity-100">
                            <svg class="h-5 w-5 drop-shadow-lg {{ ($schedule['header_image_url'] ?? false) ? 'text-white' : 'text-gray-600 dark:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </div>
                    </a>
                </article>
                @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- FAQ Section for Rich Snippets --}}
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-demos">questions</span></h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $demoFaqs = [
                        ['Can I create a schedule like these?', 'Yes! Event Schedule is free to use. Sign up and you can create a schedule just like these examples in minutes. All the features you see are available on the free plan.'],
                        ['Are these real schedules?', 'These are demo schedules we\'ve created to showcase different use cases and features. They demonstrate what\'s possible with Event Schedule for various industries including fitness, music, community groups, and more.'],
                        ['How long does it take to set up?', 'Most users have their schedule live within 5 minutes. Simply sign up, add your events (or import from Google Calendar), customize your colors, and share your link. No technical skills required.'],
                    ];
                @endphp
                @foreach ($demoFaqs as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Use Cases Section (internal linking for SEO) --}}
    <section class="bg-gray-50 py-24 dark:bg-[#0d0d14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Built for <span class="text-gradient-demos">every industry</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">See how different organizations use Event Schedule</p>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="80">
                <a href="{{ marketing_url('/for-fitness-and-yoga') }}" data-reveal class="group rounded-xl border border-transparent bg-white p-6 text-center transition-all hover:-translate-y-1 hover:border-teal-200 hover:bg-teal-50 dark:bg-white/5 dark:hover:border-teal-500/20 dark:hover:bg-teal-500/10">
                    <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 text-teal-600 transition-transform group-hover:scale-110 dark:bg-teal-500/20 dark:text-teal-400">
                        <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                    </div>
                    <span class="block font-medium text-gray-700 dark:text-gray-300">Fitness & Yoga</span>
                </a>
                <a href="{{ marketing_url('/for-music-venues') }}" data-reveal class="group rounded-xl border border-transparent bg-white p-6 text-center transition-all hover:-translate-y-1 hover:border-sky-200 hover:bg-sky-50 dark:bg-white/5 dark:hover:border-sky-500/20 dark:hover:bg-sky-500/10">
                    <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-sky-100 text-sky-600 transition-transform group-hover:scale-110 dark:bg-sky-500/20 dark:text-sky-400">
                        <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>
                    </div>
                    <span class="block font-medium text-gray-700 dark:text-gray-300">Music Venues</span>
                </a>
                <a href="{{ marketing_url('/for-workshop-instructors') }}" data-reveal class="group rounded-xl border border-transparent bg-white p-6 text-center transition-all hover:-translate-y-1 hover:border-amber-200 hover:bg-amber-50 dark:bg-white/5 dark:hover:border-amber-500/20 dark:hover:bg-amber-500/10">
                    <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600 transition-transform group-hover:scale-110 dark:bg-amber-500/20 dark:text-amber-400">
                        <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                    </div>
                    <span class="block font-medium text-gray-700 dark:text-gray-300">Workshops</span>
                </a>
                <a href="{{ marketing_url('/for-community-centers') }}" data-reveal class="group rounded-xl border border-transparent bg-white p-6 text-center transition-all hover:-translate-y-1 hover:border-emerald-200 hover:bg-emerald-50 dark:bg-white/5 dark:hover:border-emerald-500/20 dark:hover:bg-emerald-500/10">
                    <div class="mb-3 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 transition-transform group-hover:scale-110 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <span class="block font-medium text-gray-700 dark:text-gray-300">Community Centers</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Finale --}}
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to create <span class="text-gradient-demos">your own?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Start sharing your events in minutes. Free forever, no credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
