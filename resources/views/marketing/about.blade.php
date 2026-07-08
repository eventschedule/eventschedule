<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.about_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.about_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">About</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Event Schedule",
        "url": "{{ config('app.url') }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ config('app.url') }}/images/dark_logo.png",
            "width": 712,
            "height": 140
        },
        "foundingDate": "2024",
        "description": "Event Schedule helps talent, venues, and organizers share events and sell tickets. Open source, privacy-focused, and community-driven.",
        "knowsAbout": ["Event Management", "Ticketing", "Calendar Synchronization", "Open Source Software"],
        "sameAs": [
            "https://github.com/eventschedule/eventschedule",
            "https://www.facebook.com/appeventschedule",
            "https://www.instagram.com/eventschedule/",
            "https://youtube.com/@EventSchedule",
            "https://x.com/ScheduleEvent",
            "https://www.linkedin.com/company/eventschedule/"
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        .text-gradient-about {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-about {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-about {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- 1. Hero                                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(66svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Our story</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">About</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-about">Event Schedule</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                The simple and free way to share your event schedule with the world.
            </p>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Mission                                                  -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div data-reveal="panel" class="relative overflow-hidden rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-100 to-sky-100 p-8 dark:border-white/10 dark:from-blue-900/30 dark:to-sky-900/30 lg:p-12">
                <div class="pointer-events-none absolute right-0 top-0 h-64 w-64 rounded-full bg-blue-500/10 blur-[80px]" aria-hidden="true"></div>
                <div class="relative">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        Our Mission
                    </div>
                    <h2 class="mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">Making event sharing effortless</h2>
                    <div class="space-y-6 text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                        <p>Event Schedule was created to solve a simple problem: making it easy for anyone with events to share them with their audience.</p>
                        <p>Whether you're a musician with upcoming shows, a venue with a packed calendar, an event curator aggregating local happenings, or a food truck appearing at different spots each day, you deserve a simple, professional way to let people know where you'll be.</p>
                        <p>We believe sharing your schedule shouldn't require expensive software or technical expertise. That's why we built Event Schedule to be free, fast, and easy to use.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Who It's For                                             -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Built for everyone <span class="text-gradient-about">with events</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">However you share your events, Event Schedule is built to work for you.</p>
            </div>

            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-4 md:grid-cols-3" data-reveal-group="80">
                @php
                    $personas = [
                        ['/for-talent', 'For Talent', 'Share your upcoming shows, appearances, and locations with fans. Perfect for musicians, DJs, artists, food trucks, and anyone who wants their audience to know where to find them.', 'from-blue-500 to-sky-500 shadow-blue-500/25', 'text-blue-600 dark:text-blue-400', 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
                        ['/for-venues', 'For Venues', 'Keep your event calendar updated and easily accessible. Let visitors see what\'s coming up and sell tickets directly through your schedule.', 'from-sky-500 to-cyan-500 shadow-sky-500/25', 'text-sky-600 dark:text-sky-400', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ['/for-curators', 'For Curators', 'Aggregate events from multiple sources and create a comprehensive guide to what\'s happening in your area or niche.', 'from-amber-500 to-orange-500 shadow-amber-500/25', 'text-amber-600 dark:text-amber-400', 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    ];
                @endphp
                @foreach ($personas as $p)
                    <a href="{{ marketing_url($p[0]) }}" class="es-bento group relative block" data-tilt="5" data-reveal="panel">
                        <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br shadow-lg {{ $p[3] }}">
                                <svg aria-hidden="true" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $p[5] }}" />
                                </svg>
                            </div>
                            <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ $p[1] }}</h3>
                            <p class="leading-relaxed text-gray-600 dark:text-gray-400">{{ $p[2] }}</p>
                            <span class="mt-auto inline-flex items-center pt-4 text-sm font-medium opacity-0 transition-opacity group-hover:opacity-100 {{ $p[4] }}">
                                Learn more
                                <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Open Source                                              -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <div class="mx-auto mb-8 inline-flex h-20 w-20 items-center justify-center rounded-3xl border border-gray-300 bg-gray-200 dark:border-white/20 dark:bg-white/10" data-reveal>
                <svg aria-hidden="true" class="h-10 w-10 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
            </div>
            <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Free and <span class="text-gradient-about">open source</span></h2>
            <p class="mx-auto mb-8 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Event Schedule is open source under the Attribution Assurance License (AAL). Selfhost on your own server, contribute to the codebase, or just use it free forever.</p>

            <div data-reveal>
                @include('marketing.partials.github-star-badge')
            </div>

            <div class="flex flex-wrap justify-center gap-4" data-reveal>
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-all hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                    <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    View on GitHub
                </a>
                <a href="{{ marketing_url('/features') }}" class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-3 font-medium text-white shadow-lg shadow-blue-500/25 transition-all hover:from-blue-500 hover:to-sky-500">
                    Explore Features
                    <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Values                                                   -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>What we <span class="text-gradient-about">believe in</span></h2>
            </div>

            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                @php
                    $values = [
                        ['from-blue-100 to-sky-100 dark:from-blue-900/30 dark:to-sky-900/30', 'border-blue-200 dark:border-blue-500/20', 'from-blue-500/20 to-sky-500/20 dark:from-blue-500/30 dark:to-sky-500/30', 'text-blue-600 dark:text-blue-400', 'Free Forever', 'Core features will always be free. No tricks, no bait-and-switch. If you can selfhost, it\'s 100% free.', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['from-sky-100 to-cyan-100 dark:from-sky-900/30 dark:to-cyan-900/30', 'border-sky-200 dark:border-sky-500/20', 'from-sky-500/20 to-cyan-500/20 dark:from-sky-500/30 dark:to-cyan-500/30', 'text-sky-600 dark:text-sky-400', 'Privacy First', 'Your data is yours. We don\'t sell it, share it, or use it for advertising. Selfhost for complete control.', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                        ['from-emerald-100 to-teal-100 dark:from-emerald-900/30 dark:to-teal-900/30', 'border-emerald-200 dark:border-emerald-500/20', 'from-emerald-500/20 to-teal-500/20 dark:from-emerald-500/30 dark:to-teal-500/30', 'text-emerald-600 dark:text-emerald-400', 'Simple by Design', 'No bloat, no complexity. We focus on the features that matter and make them work well.', 'M13 10V3L4 14h7v7l9-11h-7z'],
                    ];
                @endphp
                @foreach ($values as $v)
                    <div data-reveal class="rounded-3xl border bg-gradient-to-br p-8 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-lg {{ $v[0] }} {{ $v[1] }}">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br {{ $v[2] }}">
                            <svg aria-hidden="true" class="h-8 w-8 {{ $v[3] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $v[6] }}" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ $v[4] }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $v[5] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Built By                                                 -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div data-reveal="panel" class="relative overflow-hidden rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-100 to-sky-100 p-8 dark:border-white/10 dark:from-blue-900/30 dark:to-sky-900/30 lg:p-12">
                <div class="pointer-events-none absolute right-0 top-0 h-64 w-64 rounded-full bg-blue-500/10 blur-[80px]" aria-hidden="true"></div>
                <div class="relative text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        The Team
                    </div>
                    <h2 class="mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">Built by the team behind Invoice Ninja</h2>
                    <p class="mx-auto mb-8 max-w-2xl text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                        Event Schedule is created by the same team that built Invoice Ninja, a popular source-available invoicing platform trusted by hundreds of thousands of businesses worldwide. We bring the same commitment to quality, transparency, and user-focused design to Event Schedule.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="https://invoiceninja.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-all hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            invoiceninja.com
                        </a>
                        <a href="https://github.com/invoiceninja/invoiceninja" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-all hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            Invoice Ninja on GitHub
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-about">get started?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your free schedule today. No credit card required.
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
