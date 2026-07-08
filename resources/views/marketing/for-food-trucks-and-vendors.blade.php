<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Food Trucks & Vendors | Share Your Route</x-slot>
    <x-slot name="description">Tell hungry customers where to find you. Share daily locations, build a following, and take catering bookings. Zero platform fees. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Food Trucks & Vendors</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Food Trucks & Vendors",
        "description": "Tell hungry customers where to find you. Share daily locations, build a following, and take catering bookings. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Food Trucks, Vendors & Mobile Kitchens"
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
                "name": "Is Event Schedule free for food trucks?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free forever for sharing your location schedule, building a customer following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan, with zero platform fees."
                }
            },
            {
                "@type": "Question",
                "name": "Can I show where my truck will be each day?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Create events with location details for each stop on your route. Customers can see your full weekly schedule at a glance and know exactly where to find you. Add venue names, addresses, and hours for every location."
                }
            },
            {
                "@type": "Question",
                "name": "How do customers find out where I'll be?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Customers can follow your schedule and receive email notifications when you add new stops. Share your schedule link on social media, embed it on your website, or send newsletters with your weekly route."
                }
            },
            {
                "@type": "Question",
                "name": "Does it work for farmers market and festival schedules?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. List all your market appearances, festivals, and pop-up locations in one schedule. Use sub-schedules to organize by type. Customers always see your complete calendar of upcoming appearances."
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
        "name": "Event Schedule for Food Trucks & Vendors",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Food Truck Location and Schedule Software",
        "operatingSystem": "Web",
        "description": "Tell hungry customers where to find you. Share daily locations, build a following, and take catering bookings. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Weekly route and daily location scheduling",
            "One-click location newsletters",
            "Instant follower notifications on new stops",
            "QR code for your truck window",
            "Auto-generated find-us social graphics",
            "Catering and private event bookings",
            "Google Calendar two-way sync"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "food truck schedule, food truck location tracker, mobile vendor calendar, food truck finder, free food truck scheduling",
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
        /* For-food-trucks-and-vendors "On the Route" styles. The shared es-*
           motion system lives in marketing.css; this holds the food gradient
           text, the drifting location card, and the map-pin ping motif. */
        .text-gradient-food {
            background: linear-gradient(135deg, #f97316, #eab308);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-food {
            background: linear-gradient(135deg, #fb923c, #facc15);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes es-truck-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-truck-float { animation: es-truck-float 6s ease-in-out infinite; }

        /* Map-pin ping - concentric rings from a "you are here" pin */
        .es-ping span {
            position: absolute;
            left: 50%;
            top: 50%;
            height: 18px;
            width: 18px;
            margin: -9px 0 0 -9px;
            border-radius: 9999px;
            border: 2px solid rgba(16, 185, 129, 0.5);
            transform: scale(0.3);
            opacity: 0;
            animation: es-ping var(--ping-dur, 3s) ease-out infinite;
            animation-delay: var(--ping-delay, 0s);
        }
        @keyframes es-ping {
            0% { transform: scale(0.3); opacity: 0.7; }
            100% { transform: scale(4.5); opacity: 0; }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-truck-float, .es-ping span { animation: none !important; }
            .es-ping span { opacity: 0; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: where to find you                                   -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(88svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 30%, rgba(249, 115, 22, 0.3), rgba(249, 115, 22, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 42%, rgba(234, 179, 8, 0.28), rgba(234, 179, 8, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 55% 75%, rgba(16, 185, 129, 0.14), rgba(16, 185, 129, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-orange-500 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">For Food Trucks, Vendors & Mobile Kitchens</span>
            </div>

            <h1 class="es-balance mb-8 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Tell hungry customers</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-food es-gradient-anim">where to find you</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Share your daily locations, build a following, and let regulars know where you're parking next.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#route" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See the route
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
                <a href="{{ app_url('/sign_up?type=talent') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-orange-600 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-orange-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
                    Create your schedule
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Vendor-type marquee -->
            <div class="es-fade-up es-d-4 pointer-events-auto mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1" aria-hidden="true">
                        <div class="es-marquee-track">
                            @for ($tc = 0; $tc < 2; $tc++)
                                @foreach (['Food Trucks', 'Taco Trucks', 'Coffee Carts', 'BBQ', 'Pop-ups', 'Catering', 'Ice Cream', 'Festival Vendors'] as $tag)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-orange-200 bg-orange-100/80 px-4 py-1.5 text-xs font-semibold text-orange-800 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-r from-orange-400 to-amber-400"></span>
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
    <!-- 2. Sound familiar? (problem)                                 -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <h2 class="es-balance text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Sound familiar?</h2>
            </div>

            <div class="mx-auto mb-8 max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04]" data-reveal="panel">
                <div class="mb-4 flex items-center gap-3 border-b border-gray-200 pb-4 dark:border-white/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-amber-500 text-sm font-bold text-white">TT</div>
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">Taco Truck Tony</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Posted a photo</div>
                    </div>
                </div>
                <div class="space-y-3" data-reveal-group="80">
                    @foreach ([['bg-blue-500/30', 'Where are you today?'], ['bg-cyan-500/30', 'Where are you guys today??'], ['bg-green-500/30', 'Location???'], ['bg-blue-500/30', 'Where will you be tomorrow?'], ['bg-yellow-500/30', 'Are you at the food park today?']] as [$dot, $q])
                        <div data-reveal class="flex items-start gap-2">
                            <div class="h-6 w-6 shrink-0 rounded-full {{ $dot }}"></div>
                            <div class="rounded-xl bg-gray-100 px-3 py-2 dark:bg-white/10">
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $q }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center" data-reveal>
                <p class="mb-2 text-lg text-gray-500 dark:text-gray-400">You post once. Get asked <span data-count-to="47" class="font-semibold text-orange-600 dark:text-orange-400">47</span> times.</p>
                <p class="text-gray-500 dark:text-gray-400">Facebook shows your posts to 3% of followers. Your regulars can't find you.</p>
                <p class="mt-6 text-lg font-medium text-orange-600 dark:text-orange-400">There's a better way.</p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Your weekly route (dark band)                             -->
    <!-- ============================================================ -->
    @php
        $route = [
            ['Mon', 'Downtown Food Park', '11am - 2pm', 'past', ''],
            ['TODAY', 'Tech Campus - Building A', '11am - 2pm', 'now', 'Now serving'],
            ['Wed', 'Farmers Market', '8am - 1pm', 'next', ''],
            ['Thu', 'Private event (not shown publicly)', '', 'private', ''],
            ['Fri', 'Brewery District Food Truck Rally', '5pm - 10pm', 'next', ''],
        ];
    @endphp
    <section id="route" class="scroll-mt-24 bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 30%, rgba(249, 115, 22, 0.24), rgba(249, 115, 22, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 60%, rgba(16, 185, 129, 0.18), rgba(16, 185, 129, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-2xl">
                <div class="mx-auto mb-12 max-w-xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                        Your weekly route, <span class="text-gradient-food">always up to date</span>
                    </h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                        Post your rotation once. Customers see where you are now and where you'll be next.
                    </p>
                </div>

                <div class="space-y-4" data-reveal-group="90">
                    @foreach ($route as [$day, $place, $time, $state, $badge])
                        <div data-reveal class="flex items-center gap-4">
                            <div class="w-16 shrink-0 text-right">
                                @if ($state === 'now')
                                    <span class="text-sm font-bold text-emerald-400">{{ $day }}</span>
                                @else
                                    <span class="text-sm font-medium text-gray-400">{{ $day }}</span>
                                @endif
                            </div>
                            <div class="relative flex w-4 shrink-0 justify-center">
                                @if ($state === 'now')
                                    <span class="es-ping relative flex h-4 w-4 items-center justify-center">
                                        <span style="--ping-dur: 3s; --ping-delay: 0s;"></span>
                                        <span style="--ping-dur: 3s; --ping-delay: 1.5s;"></span>
                                        <span class="relative h-4 w-4 rounded-full border-2 border-emerald-400 bg-emerald-500"></span>
                                    </span>
                                @elseif ($state === 'past')
                                    <span class="h-4 w-4 rounded-full border-2 border-white/30 bg-white/20"></span>
                                @elseif ($state === 'private')
                                    <span class="h-4 w-4 rounded-full border-2 border-amber-400/40 bg-amber-500/30"></span>
                                @else
                                    <span class="h-4 w-4 rounded-full border-2 border-orange-400/50 bg-orange-500/50"></span>
                                @endif
                            </div>
                            <div class="flex-1">
                                @if ($state === 'now')
                                    <div class="flex items-center justify-between rounded-xl border border-emerald-400/30 bg-emerald-500/15 p-3">
                                        <div>
                                            <div class="text-sm font-medium text-emerald-200">{{ $place }}</div>
                                            <div class="text-xs text-emerald-300/70">{{ $time }}</div>
                                        </div>
                                        <div class="flex items-center gap-1 rounded-full bg-emerald-500/30 px-2 py-1">
                                            <div class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></div>
                                            <span class="text-xs font-medium text-emerald-200">{{ $badge }}</span>
                                        </div>
                                    </div>
                                @elseif ($state === 'private')
                                    <div class="rounded-xl border border-dashed border-white/15 bg-white/[0.04] p-3">
                                        <span class="text-sm italic text-gray-400">{{ $place }}</span>
                                    </div>
                                @else
                                    <div class="rounded-xl bg-white/[0.05] p-3">
                                        <div class="text-sm text-gray-300">{{ $place }}</div>
                                        @if ($time)
                                            <div class="text-xs text-gray-500">{{ $time }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Put this on your truck (QR)                               -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="es-bento group relative" data-reveal="panel">
                <div class="es-tilt-inner relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04] lg:p-10">
                    <div class="grid items-center gap-12 md:grid-cols-2">
                        <div>
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                                QR Code
                            </div>
                            <h2 class="mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">Put this on your truck</h2>
                            <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Print the QR code and stick it on your window. Customers scan once, follow your truck forever. No app download needed.</p>
                            <ul class="space-y-3 text-gray-500 dark:text-gray-400">
                                @foreach (['They follow once, never miss you again', "Build an audience that's YOURS, not Facebook's", 'Get notified when they book catering'] as $point)
                                    <li class="flex items-center gap-3">
                                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span>{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="flex justify-center" aria-hidden="true">
                            <div class="relative">
                                <div class="relative h-80 w-64 overflow-hidden rounded-t-3xl border-4 border-gray-600 bg-gradient-to-b from-gray-700 to-gray-800">
                                    <div class="absolute inset-0 bg-gradient-to-br from-white/10 via-transparent to-transparent"></div>
                                    <div class="absolute left-1/2 top-8 -translate-x-1/2 rounded-xl bg-white p-3 shadow-2xl">
                                        <div class="h-28 w-28 bg-contain bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%23c2410c%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')]"></div>
                                        <div class="mt-2 text-center">
                                            <div class="text-xs font-bold text-gray-800">SCAN TO FOLLOW</div>
                                            <div class="text-[10px] font-medium text-orange-600">tacotrucktony.eventschedule.com</div>
                                        </div>
                                    </div>
                                    <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-gray-900/80 to-transparent"></div>
                                </div>
                                <div class="-mt-1 mx-auto h-4 w-72 rounded-b-sm bg-gradient-to-b from-orange-600 to-orange-700"></div>
                            </div>
                        </div>
                    </div>
                    <div class="es-glare" aria-hidden="true"></div>
                    <div class="es-ring-glow" aria-hidden="true"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Features grid                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Everything to feed your <span class="text-gradient-food">following</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="110">

                <!-- Newsletter -->
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Newsletter
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Announce your week's spots in one click</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">No algorithm. 100% of your followers see it. Send your Monday-Friday locations every Sunday night.</p>
                        <div class="mt-auto rounded-xl border border-orange-500/20 bg-gray-100 p-4 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-amber-500"><svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div>
                                <div><div class="font-medium text-gray-900 dark:text-white">This Week's Spots</div><div class="text-sm text-orange-600 dark:text-orange-300">Sending to 1,247 hungry fans...</div></div>
                            </div>
                            <div class="space-y-1 text-sm">
                                <div class="es-ai-field flex gap-2 text-gray-500 dark:text-gray-400" style="--i: 0;"><span class="text-orange-500 dark:text-orange-400">Mon:</span> Downtown Food Park</div>
                                <div class="es-ai-field flex gap-2 text-gray-500 dark:text-gray-400" style="--i: 1;"><span class="text-orange-500 dark:text-orange-400">Tue:</span> Tech Campus</div>
                                <div class="es-ai-field flex gap-2 text-gray-500 dark:text-gray-400" style="--i: 2;"><span class="text-orange-500 dark:text-orange-400">Wed:</span> Farmers Market</div>
                                <div class="es-ai-field flex gap-2 text-gray-500 dark:text-gray-400" style="--i: 3;"><span class="text-orange-500 dark:text-orange-400">Fri:</span> Brewery District</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Instant notifications -->
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            Notifications
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Customers get pinged instantly</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Post a new location, your followers get notified. Last-minute spot change? They know immediately.</p>
                        <div class="mx-auto mt-auto w-full max-w-xs rounded-2xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-amber-500"><span class="text-xs font-bold text-white">TT</span></div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Taco Truck Tony</div>
                                    <div class="text-xs text-emerald-500 dark:text-emerald-400">New location posted!</div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tech Campus - Building A</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Promo graphics -->
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Promo Graphics
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">"Find us today" posts, ready to share</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Auto-generate Instagram-ready graphics with your location. One click to download and post.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="relative h-32 w-32 rounded-xl border border-sky-400/30 bg-sky-500/20 p-2">
                                <div class="flex h-full w-full flex-col items-center justify-center rounded-lg bg-gradient-to-br from-orange-600/40 to-amber-600/40">
                                    <svg aria-hidden="true" class="mb-1 h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                                    <div class="text-xs font-semibold text-white">FIND US TODAY</div>
                                    <div class="mt-0.5 text-[10px] text-sky-200">Downtown Food Park</div>
                                </div>
                                <div class="absolute -bottom-2 -right-2 flex h-8 w-8 items-center justify-center rounded-full bg-sky-500 shadow-lg">
                                    <svg aria-hidden="true" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Catering & private events -->
                <div class="es-bento group relative" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            Bookings
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Take catering inquiries</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Corporate lunches, weddings, private parties. Customers can request bookings right from your page. Zero platform fees.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field flex items-center justify-between rounded-lg bg-gray-100 p-3 dark:bg-white/10" style="--i: 0;">
                                <div><span class="text-sm text-gray-900 dark:text-white">Corporate Lunch</span><span class="block text-xs text-gray-500 dark:text-gray-400">50 people, March 15</span></div>
                                <span class="text-sm font-medium text-amber-600 dark:text-amber-400">$650</span>
                            </div>
                            <div class="es-ai-field flex items-center justify-between rounded-lg border border-amber-400/30 bg-amber-500/20 p-3" style="--i: 1;">
                                <div><span class="text-sm text-gray-900 dark:text-white">Wedding Catering</span><span class="block text-xs text-amber-700/70 dark:text-amber-300/70">150 people, June 22</span></div>
                                <span class="text-sm font-medium text-amber-600 dark:text-amber-300">$3,200</span>
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
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Built for every <span class="text-gradient-food">kitchen on wheels</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    From taco trucks to coffee carts, Event Schedule helps mobile vendors connect with customers.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Taco Trucks"
                    description="Authentic tacos, burritos, and Mexican street food - let fans track your daily location and specials."
                    icon-color="orange"
                    blog-slug="for-taco-trucks"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Coffee & Beverage Carts"
                    description="Mobile espresso, smoothies, juice bars - let caffeine seekers find their morning fix."
                    icon-color="amber"
                    blog-slug="for-coffee-beverage-carts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="BBQ & Smoker Trucks"
                    description="Low and slow on the go. Share when the brisket's ready and where fans can find it."
                    icon-color="red"
                    blog-slug="for-bbq-smoker-trucks"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Catering Businesses"
                    description="Private events and corporate lunches. Take bookings and manage your catering calendar in one place."
                    icon-color="emerald"
                    blog-slug="for-mobile-catering-businesses"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Pop-up Kitchens"
                    description="Temporary restaurant experiences. Announce your next pop-up and build anticipation."
                    icon-color="violet"
                    blog-slug="for-popup-kitchens"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Festival Vendors"
                    description="Music festivals, county fairs, and outdoor events - let fans know which festivals you'll be serving at."
                    icon-color="teal"
                    blog-slug="for-festival-vendors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. How it works                                              -->
    <!-- ============================================================ -->
    @php
        $steps = [
            ['1', 'Add Your Spots', 'Enter your weekly rotation or daily locations. Add addresses so customers can find you.'],
            ['2', 'Share Your Link', 'One URL for all your locations. Put it in your bio, on your truck, everywhere.'],
            ['3', 'Feed Your Fans', 'Customers follow and get notified of new locations. They find you, you feed them.'],
        ];
    @endphp
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl" data-reveal>
                    How it <span class="text-gradient-food">works</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Get your schedule online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 text-2xl font-bold text-white shadow-lg shadow-orange-500/25">
                            {{ $num }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $desc }}</p>
                    </div>
                @endforeach
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
                    <x-feature-link-card name="Sub-Schedules" description="Organize events into categories and groups" :url="marketing_url('/features/sub-schedules')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set events to repeat weekly on chosen days" :url="marketing_url('/features/recurring-events')" icon-color="lime">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
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
                @foreach ([['/for-farmers-markets', 'Farmers Markets'], ['/for-breweries-and-wineries', 'Breweries & Wineries'], ['/for-restaurants', 'Restaurants'], ['/for-bars', 'Bars']] as [$relHref, $relName])
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
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-food">questions</span>
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything food truck owners ask about Event Schedule.
                </p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Is Event Schedule free for food trucks?', 'Yes. Event Schedule is free forever for sharing your location schedule, building a customer following, and syncing with Google Calendar. Newsletters and advanced features are available on the Pro plan, with zero platform fees.'],
                    ['Can I show where my truck will be each day?', 'Yes. Create events with location details for each stop on your route. Customers can see your full weekly schedule at a glance and know exactly where to find you. Add venue names, addresses, and hours for every location.'],
                    ['How do customers find out where I\'ll be?', 'Customers can follow your schedule and receive email notifications when you add new stops. Share your schedule link on social media, embed it on your website, or send newsletters with your weekly route.'],
                    ['Does it work for farmers market and festival schedules?', 'Yes. List all your market appearances, festivals, and pop-up locations in one schedule. Use sub-schedules to organize by type. Customers always see your complete calendar of upcoming appearances.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-orange-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(249, 115, 22, 0.32), rgba(249, 115, 22, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Stop answering <span class="text-gradient-food">"Where are you today?"</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Let your regulars find you. Free forever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-truck" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-orange-600 to-amber-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-orange-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
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
