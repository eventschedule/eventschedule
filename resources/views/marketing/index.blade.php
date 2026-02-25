<x-marketing-layout>
    <x-slot name="title">Event Schedule - Plan, Promote & Share Event Calendars</x-slot>
    <x-slot name="description">Create professional event calendars, sell tickets with no platform fees, and check in attendees with QR codes. Free for venues, performers, and communities.</x-slot>
    <x-slot name="breadcrumbTitle">Home</x-slot>
    <x-slot name="preload">
        <link rel="preload" as="image" type="image/webp" href="{{ url(webp_path('/images/screenshots/marketing_1_800w.jpg')) }}">
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Homepage-specific animations (not shared across pages) */
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        @keyframes scroll-step {
            0%, 12% { transform: translateX(0); }
            14%, 26% { transform: translateX(-100%); }
            28%, 40% { transform: translateX(-200%); }
            42%, 54% { transform: translateX(-300%); }
            56%, 68% { transform: translateX(-400%); }
            70%, 82% { transform: translateX(-500%); }
            84%, 96% { transform: translateX(-600%); }
            100% { transform: translateX(-700%); }
        }
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient-shift 8s ease infinite;
        }
        .animate-marquee { animation: marquee 30s linear infinite; }
        .animate-scroll-step { animation: scroll-step 21s ease-in-out infinite; }
        .screenshot-carousel:hover .animate-scroll-step {
            animation-play-state: paused;
        }
        /* Noise overlay */
        .noise::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
            opacity: 0.03;
            pointer-events: none;
        }

        /* Marquee pause on hover */
        .marquee-container:hover .animate-marquee {
            animation-play-state: paused;
        }

    </style>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule",
        "description": "The simple and free way to share your event schedule. Perfect for musicians, venues, event organizers, and vendors.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "screenshot": "{{ config('app.url') }}/images/social/home.png",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to share your event schedule",
        "description": "Get your event schedule live and shared with your audience in three simple steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Create your schedule",
                "text": "Sign up free. Add your events manually or import from Google Calendar."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share your link",
                "text": "Get your custom URL. Put it in your bio, website, or anywhere you want."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Grow your audience",
                "text": "Fans follow your schedule. Send them newsletters and notify them when you add new events."
            }
        ]
    }
    </script>
    </x-slot>

    <!-- Hero Section - Side by side with carousel -->
    <section class="relative min-h-screen flex items-center overflow-hidden bg-white dark:bg-[#0a0a0f] noise">
        <!-- Animated gradient orbs -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-gradient-to-r from-blue-600/30 to-blue-500/30 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] bg-gradient-to-r from-sky-600/20 to-cyan-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-blue-600/10 to-cyan-600/10 rounded-full blur-[120px]"></div>
        </div>

        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 grid-pattern bg-[size:60px_60px]"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-12 lg:gap-16 items-center">
                <!-- Left side - Text content -->
                <div class="text-center lg:text-left lg:col-span-3">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass mb-8 animate-reveal">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-sm text-gray-600 dark:text-gray-300">Free forever. No credit card.</span>
                    </div>

                    <!-- Main headline -->
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight mb-6 animate-reveal delay-100">
                        <span class="block text-gray-900 dark:text-white">Plan, Promote &</span>
                        <span class="block text-gradient">Share Event Calendars</span>
                    </h1>

                    <!-- Subheadline -->
                    <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-xl mb-12 animate-reveal delay-200">
                        Share events, sell tickets, and grow your audience. Built for venues, performers, and communities.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-reveal delay-300">
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl overflow-hidden transition-all hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <div class="absolute inset-0 animate-shimmer"></div>
                        </a>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 sm:mt-0 sm:self-center">Set up in under 2 minutes</p>
                    </div>
                </div>

                <!-- Right side - Screenshot carousel -->
                <div class="relative screenshot-carousel overflow-hidden rounded-2xl shadow-2xl shadow-black/50 ring-1 ring-gray-200 dark:ring-white/10 lg:col-span-2">
                    <div class="flex animate-scroll-step">
                        @php
                        $screenshots = [
                            ['/images/screenshots/marketing_1.jpg', 'Event Schedule calendar view showing upcoming events in a clean grid layout'],
                            ['/images/screenshots/marketing_2.jpg', 'Event details page with ticket options and event information'],
                            ['/images/screenshots/marketing_3.jpg', 'Mobile-friendly event schedule on a smartphone display'],
                            ['/images/screenshots/marketing_4.jpg', 'Event management dashboard for organizing your schedule'],
                            ['/images/screenshots/marketing_5.jpg', 'Ticket sales and check-in management interface'],
                            ['/images/screenshots/marketing_6.jpg', 'Event analytics showing page views and visitor statistics'],
                            ['/images/screenshots/marketing_7.jpg', 'Schedule customization options with branding settings'],
                            ['/images/screenshots/marketing_1.jpg', 'Event Schedule calendar view showing upcoming events in a clean grid layout'], // duplicate for seamless loop
                        ];
                        @endphp
                        @foreach($screenshots as $index => $screenshot)
                        @php
                            $smallPath = str_replace('.jpg', '_800w.jpg', $screenshot[0]);
                        @endphp
                        <div class="flex-shrink-0 w-full overflow-hidden">
                            <picture>
                                <source srcset="{{ url(webp_path($smallPath)) }}" type="image/webp">
                                <img src="{{ url($smallPath) }}" alt="{{ $screenshot[1] }}" width="800" height="1094" class="block w-full h-auto scale-[1.05]" @if($index === 0) fetchpriority="high" @else loading="lazy" decoding="async" @endif />
                            </picture>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Marquee Section - Social proof -->
    <section class="relative bg-gray-50 dark:bg-[#0f0f14] border-y border-gray-200 dark:border-white/10 py-4 overflow-hidden marquee-container">
        <div class="flex animate-marquee whitespace-nowrap">
            @for($i = 0; $i < 2; $i++)
            <span class="mx-8 text-2xl font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-3">
                <span class="text-gray-900 dark:text-white">Musicians</span>
                <svg class="w-2 h-2 text-blue-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-3">
                <span class="text-gray-900 dark:text-white">Venues</span>
                <svg class="w-2 h-2 text-sky-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-3">
                <span class="text-gray-900 dark:text-white">DJs</span>
                <svg class="w-2 h-2 text-cyan-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-3">
                <span class="text-gray-900 dark:text-white">Promoters</span>
                <svg class="w-2 h-2 text-blue-400" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-3">
                <span class="text-gray-900 dark:text-white">Food Trucks</span>
                <svg class="w-2 h-2 text-cyan-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-3">
                <span class="text-gray-900 dark:text-white">Theaters</span>
                <svg class="w-2 h-2 text-amber-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-3">
                <span class="text-gray-900 dark:text-white">Bands</span>
                <svg class="w-2 h-2 text-emerald-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-500 dark:text-gray-400 flex items-center gap-3">
                <span class="text-gray-900 dark:text-white">Festivals</span>
                <svg class="w-2 h-2 text-rose-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            @endfor
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="relative bg-gray-50 dark:bg-[#0f0f14] py-24 scroll-mt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Everything you need to fill seats
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    One platform for scheduling, ticketing, newsletters, and check-ins.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Ticketing & Check-ins -->
                <a href="{{ marketing_url('/features/ticketing') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-500/30 transition-all flex flex-col" aria-label="Learn more about ticketing and QR check-ins">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 mb-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Ticketing & QR Check-ins</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Sell tickets online with multiple types (GA, VIP), set limits, and scan QR codes for fast check-ins.</p>
                    <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Secure payments by Stripe</span>
                    </div>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-blue-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- AI Features -->
                <a href="{{ marketing_url('/features/ai') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-500/30 transition-all flex flex-col" aria-label="Learn more about AI-powered features">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 mb-4">
                        <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">AI-Powered</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Paste a flyer or scan a printed agenda and let AI extract the details. Translate your events into 11 languages instantly.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-sky-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Newsletters -->
                <a href="{{ route('marketing.newsletters') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-cyan-200 dark:hover:border-cyan-500/30 transition-all flex flex-col" aria-label="Learn more about newsletters">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-500/20 mb-4">
                        <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">Newsletters</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Send branded emails to followers and ticket buyers with a drag-and-drop editor and A/B testing.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-cyan-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Calendar Sync -->
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-teal-200 dark:hover:border-teal-500/30 transition-all flex flex-col" aria-label="Learn more about calendar sync">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-500/20 mb-4">
                        <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">Calendar Sync</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Two-way sync with Google Calendar. Let attendees add events to Apple, Google, or Outlook calendars.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-teal-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Built-in Analytics -->
                <a href="{{ route('marketing.analytics') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-emerald-200 dark:hover:border-emerald-500/30 transition-all flex flex-col" aria-label="Learn more about built-in analytics">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 mb-4">
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Built-in Analytics</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Track page views, device breakdown, top events, and traffic sources. No external services required.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-emerald-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Recurring Events -->
                <a href="{{ marketing_url('/features/recurring-events') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-green-200 dark:hover:border-green-500/30 transition-all flex flex-col" aria-label="Learn more about recurring events">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-green-100 dark:bg-green-500/20 mb-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">Recurring Events</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Set events to repeat weekly on chosen days with flexible end conditions and per-occurrence tickets.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-green-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Team Scheduling -->
                <a href="{{ marketing_url('/features/team-scheduling') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-lime-200 dark:hover:border-lime-500/30 transition-all flex flex-col" aria-label="Learn more about team scheduling">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-lime-100 dark:bg-lime-500/20 mb-4">
                        <svg class="w-6 h-6 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-lime-600 dark:group-hover:text-lime-400 transition-colors">Team Scheduling</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Invite your team so everyone can add events and manage tickets without sharing a login.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-lime-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Private Events -->
                <a href="{{ route('marketing.private_events') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-yellow-200 dark:hover:border-yellow-500/30 transition-all flex flex-col" aria-label="Learn more about private events">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-yellow-100 dark:bg-yellow-500/20 mb-4">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition-colors">Private Events</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Password-protect events for VIP audiences or invite-only gatherings. Control who sees what.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-yellow-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Custom Fields -->
                <a href="{{ marketing_url('/features/custom-fields') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-500/30 transition-all flex flex-col" aria-label="Learn more about custom fields">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 mb-4">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Custom Fields</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Collect additional info from ticket buyers with text, dropdown, date, and yes/no fields.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-amber-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Event Graphics -->
                <a href="{{ marketing_url('/features/event-graphics') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-orange-200 dark:hover:border-orange-500/30 transition-all flex flex-col" aria-label="Learn more about event graphics">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-500/20 mb-4">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">Event Graphics</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Auto-generate shareable images and formatted text for your upcoming events. Ready for Instagram, WhatsApp, email, and more.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-orange-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Boost -->
                <a href="{{ route('marketing.boost') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-red-200 dark:hover:border-red-500/30 transition-all flex flex-col" aria-label="Learn more about Boost">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-red-100 dark:bg-red-500/20 mb-4">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Boost</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Turn any event into a Facebook or Instagram ad in minutes. Set your budget, pick your audience, and launch with no ad experience needed.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-red-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Fan Videos & Comments -->
                <a href="{{ marketing_url('/features/fan-videos') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-rose-200 dark:hover:border-rose-500/30 transition-all flex flex-col" aria-label="Learn more about fan videos and comments">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-500/20 mb-4">
                        <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">Fan Videos & Comments</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Fans add YouTube videos and comments to your events, with your approval before anything goes live. Build community around your shows.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-rose-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Polls -->
                <a href="{{ marketing_url('/features/polls') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-sky-200 dark:hover:border-sky-500/30 transition-all flex flex-col" aria-label="Learn more about event polls">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 mb-4">
                        <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">Event Polls</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Add multiple choice polls. Guests vote and see real-time results.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-sky-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- White-label Branding -->
                <a href="{{ route('marketing.white_label') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-500/30 transition-all flex flex-col" aria-label="Learn more about white-label branding">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 mb-4">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">White-label Branding</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Remove branding, add custom CSS, and match your brand.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-amber-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Embed Calendar -->
                <a href="{{ route('marketing.embed_calendar') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-cyan-200 dark:hover:border-cyan-500/30 transition-all flex flex-col" aria-label="Learn more about embed calendar">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-500/20 mb-4">
                        <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">Embed Calendar</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Embed your schedule on any website with a simple iframe.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-cyan-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Online Events -->
                <a href="{{ marketing_url('/features/online-events') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-500/30 transition-all flex flex-col" aria-label="Learn more about online events">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 mb-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Online Events</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Host virtual events with any streaming platform. Easy toggle between in-person and online, with the link on every ticket.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-blue-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Sub-schedules -->
                <a href="{{ marketing_url('/features/sub-schedules') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-teal-200 dark:hover:border-teal-500/30 transition-all flex flex-col" aria-label="Learn more about sub-schedules">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-500/20 mb-4">
                        <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">Sub-schedules</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Organize events into categories. Perfect for venues with multiple rooms or event series.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-teal-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- API & Open Source -->
                <a href="{{ marketing_url('/open-source') }}" class="feature-card group block bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-500/30 transition-all flex flex-col" aria-label="Learn more about open source and API">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-500/20 mb-4">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Open Source & API</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm flex-grow">Selfhost for full control over your data. Integrate with your existing tools through our REST API.</p>
                    <span class="inline-flex items-center mt-auto text-sm font-medium text-gray-600 dark:text-gray-300 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="h-16 section-fade-to-white"></div>

    <!-- Integrates With Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-10">Integrates with</p>
            <div class="flex flex-wrap justify-center items-center gap-x-10 gap-y-8">
                <!-- Google Calendar -->
                <a href="{{ marketing_url('/google-calendar') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-50 group-hover:opacity-100 transition-all duration-300" viewBox="0 0 24 24" fill="none">
                        <path d="M18.316 5.684H5.684v12.632h12.632V5.684z" class="fill-gray-300 group-hover:fill-white dark:fill-gray-600 dark:group-hover:fill-white/20"/>
                        <path d="M21.053 22H5.684l-2.631-2.632V5.684L5.684 3h12.632L21.053 5.684V22z" class="fill-gray-400 group-hover:fill-[#4285F4] dark:fill-gray-500 dark:group-hover:fill-[#4285F4]"/>
                        <path d="M18.316 22l2.737-2.632V22h-2.737z" class="fill-gray-500 group-hover:fill-[#1A73E8] dark:fill-gray-600 dark:group-hover:fill-[#1A73E8]"/>
                        <path d="M5.684 18.316L3.053 22V19.368l2.631-1.053z" class="fill-gray-500 group-hover:fill-[#1A73E8] dark:fill-gray-600 dark:group-hover:fill-[#1A73E8]"/>
                        <path d="M21.053 5.684L18.316 3v2.684h2.737z" class="fill-gray-500 group-hover:fill-[#1A73E8] dark:fill-gray-600 dark:group-hover:fill-[#1A73E8]"/>
                        <path d="M5.684 3L3.053 5.684h2.631V3z" class="fill-gray-500 group-hover:fill-[#EA4335] dark:fill-gray-600 dark:group-hover:fill-[#EA4335]"/>
                        <path d="M18.316 5.684V3l2.737 2.684h-2.737z" class="fill-gray-500 group-hover:fill-[#34A853] dark:fill-gray-600 dark:group-hover:fill-[#34A853]"/>
                        <rect x="7" y="9" width="10" height="1" rx="0.5" class="fill-gray-400 group-hover:fill-[#4285F4] dark:fill-gray-500 dark:group-hover:fill-[#4285F4]"/>
                        <rect x="7" y="12" width="7" height="1" rx="0.5" class="fill-gray-400 group-hover:fill-[#4285F4] dark:fill-gray-500 dark:group-hover:fill-[#4285F4]"/>
                        <rect x="7" y="15" width="5" height="1" rx="0.5" class="fill-gray-400 group-hover:fill-[#4285F4] dark:fill-gray-500 dark:group-hover:fill-[#4285F4]"/>
                    </svg>
                    <span class="text-xs text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Google Calendar</span>
                </a>
                <!-- Stripe -->
                <a href="{{ marketing_url('/stripe') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-50 group-hover:opacity-100 transition-all duration-300" viewBox="0 0 24 24" fill="none">
                        <rect x="1" y="4" width="22" height="16" rx="3" class="fill-gray-400 group-hover:fill-[#635BFF] dark:fill-gray-500 dark:group-hover:fill-[#635BFF]"/>
                        <path d="M11.2 10.3c0-.66.6-1.12 1.45-1.12.95 0 1.95.45 2.55 1.05l.8-1.85c-.7-.55-1.7-.95-3.05-.95-2.2 0-3.6 1.15-3.6 3.05 0 3 4.1 2.5 4.1 3.8 0 .55-.5.95-1.35.95-1.1 0-2.3-.55-3-1.15l-.85 1.85c.85.7 2.1 1.15 3.55 1.15 2.25 0 3.75-1.1 3.75-3.05 0-3.25-4.35-2.7-4.35-3.73z" class="fill-white dark:fill-gray-300"/>
                    </svg>
                    <span class="text-xs text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Stripe</span>
                </a>
                <!-- Invoice Ninja -->
                <a href="{{ marketing_url('/invoiceninja') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-50 group-hover:opacity-100 transition-all duration-300" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="5" width="18" height="16" rx="2" class="fill-gray-400 group-hover:fill-[#000] dark:fill-gray-500 dark:group-hover:fill-white"/>
                        <path d="M7.5 10h9M7.5 13h6M7.5 16h3" class="stroke-white group-hover:stroke-white dark:stroke-gray-800 dark:group-hover:stroke-gray-900" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M12 2L9 5h6l-3-3z" class="fill-gray-500 group-hover:fill-[#2E7D32] dark:fill-gray-600 dark:group-hover:fill-[#4CAF50]"/>
                    </svg>
                    <span class="text-xs text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Invoice Ninja</span>
                </a>
                <!-- CalDAV -->
                <a href="{{ marketing_url('/caldav') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-50 group-hover:opacity-100 transition-all duration-300" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="5" width="18" height="17" rx="2" class="fill-gray-400 group-hover:fill-[#F57C00] dark:fill-gray-500 dark:group-hover:fill-[#F57C00]"/>
                        <rect x="3" y="5" width="18" height="5" rx="2" class="fill-gray-500 group-hover:fill-[#E65100] dark:fill-gray-600 dark:group-hover:fill-[#E65100]"/>
                        <circle cx="7" cy="7.5" r="1" class="fill-white/80"/>
                        <circle cx="17" cy="7.5" r="1" class="fill-white/80"/>
                        <path d="M9 15l2 2 4-4" class="stroke-white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                    <span class="text-xs text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">CalDAV</span>
                </a>
                <!-- Apple Calendar -->
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-50 group-hover:opacity-100 transition-all duration-300" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="5" width="18" height="17" rx="3" class="fill-gray-400 group-hover:fill-white dark:fill-gray-500 dark:group-hover:fill-gray-800"/>
                        <rect x="3" y="5" width="18" height="6" rx="3" class="fill-gray-500 group-hover:fill-[#EF4444] dark:fill-gray-600 dark:group-hover:fill-[#EF4444]"/>
                        <text x="7.5" y="9.5" class="fill-white" font-size="5" font-weight="bold" font-family="system-ui">31</text>
                        <circle cx="8" cy="16" r="1.5" class="fill-gray-500 group-hover:fill-[#EF4444] dark:fill-gray-600 dark:group-hover:fill-[#EF4444]"/>
                        <rect x="11" y="14" width="6" height="1" rx="0.5" class="fill-gray-300 group-hover:fill-gray-400 dark:fill-gray-700 dark:group-hover:fill-gray-600"/>
                        <rect x="11" y="16.5" width="4" height="1" rx="0.5" class="fill-gray-300 group-hover:fill-gray-400 dark:fill-gray-700 dark:group-hover:fill-gray-600"/>
                    </svg>
                    <span class="text-xs text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Apple Calendar</span>
                </a>
                <!-- Outlook -->
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="group flex flex-col items-center gap-2">
                    <svg aria-hidden="true" class="h-10 w-10 opacity-50 group-hover:opacity-100 transition-all duration-300" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="4" width="18" height="17" rx="2" class="fill-gray-400 group-hover:fill-[#0078D4] dark:fill-gray-500 dark:group-hover:fill-[#0078D4]"/>
                        <rect x="12" y="4" width="9" height="8.5" rx="1" class="fill-gray-500 group-hover:fill-[#0063B1] dark:fill-gray-600 dark:group-hover:fill-[#0063B1]"/>
                        <path d="M13 5.5l4 3-4 3" class="stroke-white" stroke-width="1" fill="none" stroke-linejoin="round"/>
                        <ellipse cx="8" cy="14.5" rx="3.5" ry="4" class="fill-gray-500 group-hover:fill-[#0063B1] dark:fill-gray-600 dark:group-hover:fill-[#0063B1]"/>
                        <ellipse cx="8" cy="14.5" rx="2" ry="2.5" class="fill-white/30"/>
                    </svg>
                    <span class="text-xs text-gray-400 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Outlook</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Why Free Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Free and open source. Forever.
                </h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    No hidden fees. No per-ticket charges. Keep 100% of your ticket sales.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Free Plan -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-500/20 mb-4">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Free forever</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Unlimited events and schedules on our free plan</p>
                </div>

                <!-- No Platform Fees -->
                <div class="bg-gradient-to-br from-blue-50 to-sky-50 dark:from-blue-900/30 dark:to-sky-900/30 border border-blue-200 dark:border-blue-500/20 rounded-2xl p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/20 mb-4">
                        <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">0% platform fees</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">We don't take a cut of your ticket sales</p>
                </div>

                <!-- Open Source -->
                <div class="bg-gradient-to-br from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30 border border-sky-200 dark:border-sky-500/20 rounded-2xl p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-sky-500/20 mb-4">
                        <svg class="w-6 h-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Open source</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Selfhost on your own server. Your data, your rules.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Highlight Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden">
        <!-- Background accent -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/2 right-0 -translate-y-1/2 w-[500px] h-[500px] bg-gradient-to-l from-sky-600/10 to-cyan-600/10 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Left - Text content -->
                <div>
                    <span class="inline-block px-4 py-1.5 rounded-full text-sm font-medium bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/30 mb-6">
                        Built-in newsletters
                    </span>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                        Reach your audience<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-500 to-cyan-500">directly</span>
                    </h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 mb-8 max-w-lg">
                        Send branded newsletters to your followers and ticket buyers. No third-party email tools needed.
                    </p>

                    <!-- Feature badges -->
                    <div class="flex flex-wrap gap-3">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                            </svg>
                            Drag-and-drop builder
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                            </svg>
                            Templates
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            A/B testing
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Audience segments
                        </span>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('marketing.newsletters') }}" class="inline-flex items-center gap-2 text-sky-600 dark:text-sky-400 font-semibold hover:gap-3 transition-all">
                            Learn more about newsletters
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- Right - Newsletter mockup -->
                <div class="relative">
                    <div class="relative bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 shadow-2xl shadow-sky-500/10 overflow-hidden animate-float">
                        <!-- Email header mockup -->
                        <div class="bg-gradient-to-r from-sky-500 to-cyan-500 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-white font-semibold text-sm">This Week's Events</div>
                                    <div class="text-white/70 text-xs">Your weekly newsletter</div>
                                </div>
                            </div>
                        </div>
                        <!-- Email body mockup -->
                        <div class="px-6 py-5 space-y-4">
                            <!-- Banner placeholder -->
                            <div class="h-28 rounded-xl bg-gradient-to-r from-sky-100 to-cyan-100 dark:from-sky-900/30 dark:to-cyan-900/30 flex items-center justify-center">
                                <span class="text-sky-600 dark:text-sky-400 font-bold text-lg">Featured Event</span>
                            </div>
                            <!-- Content lines -->
                            <div class="space-y-2">
                                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded-full w-full"></div>
                                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded-full w-4/5"></div>
                                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded-full w-3/5"></div>
                            </div>
                            <!-- Event cards -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 p-3">
                                    <div class="h-16 rounded-lg bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900/30 dark:to-sky-900/30 mb-2"></div>
                                    <div class="h-2.5 bg-gray-200 dark:bg-white/10 rounded-full w-3/4 mb-1.5"></div>
                                    <div class="h-2 bg-gray-100 dark:bg-white/5 rounded-full w-1/2"></div>
                                </div>
                                <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 p-3">
                                    <div class="h-16 rounded-lg bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900/30 dark:to-cyan-900/30 mb-2"></div>
                                    <div class="h-2.5 bg-gray-200 dark:bg-white/10 rounded-full w-3/4 mb-1.5"></div>
                                    <div class="h-2 bg-gray-100 dark:bg-white/5 rounded-full w-1/2"></div>
                                </div>
                            </div>
                            <!-- CTA button -->
                            <div class="flex justify-center pt-2">
                                <div class="px-6 py-2 rounded-lg bg-gradient-to-r from-sky-500 to-cyan-500 text-white text-sm font-semibold">View All Events</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Boost Highlight Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden">
        <!-- Background accent -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/2 left-0 -translate-y-1/2 w-[500px] h-[500px] bg-gradient-to-r from-orange-600/10 to-amber-600/10 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Left - Phone mockup -->
                <div class="relative order-2 lg:order-1">
                    <div class="relative mx-auto w-[280px]">
                        <!-- Phone frame -->
                        <div class="relative bg-white dark:bg-white/5 rounded-[2.5rem] border-[3px] border-gray-200 dark:border-white/10 shadow-2xl shadow-orange-500/10 overflow-hidden animate-float">
                            <!-- Notch -->
                            <div class="flex justify-center pt-2 pb-1 bg-gray-50 dark:bg-white/5">
                                <div class="w-20 h-5 bg-gray-200 dark:bg-white/10 rounded-full"></div>
                            </div>
                            <!-- Sponsored post -->
                            <div class="px-4 py-3">
                                <!-- Post header -->
                                <div class="flex items-center gap-2.5 mb-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-400 to-amber-500 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">The Blue Note</div>
                                        <div class="text-[10px] text-gray-400">Sponsored</div>
                                    </div>
                                </div>
                                <!-- Event image placeholder -->
                                <div class="h-40 rounded-xl bg-gradient-to-br from-orange-200 to-amber-200 dark:from-orange-800/60 dark:to-amber-800/60 flex items-center justify-center mb-3">
                                    <div class="text-center">
                                        <svg class="w-8 h-8 text-orange-500 dark:text-orange-300 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                        </svg>
                                        <span class="text-orange-600 dark:text-orange-300 font-bold text-sm">Jazz Night</span>
                                        <div class="text-orange-500/70 dark:text-orange-400/70 text-[10px]">Sat, Mar 15 - 8 PM</div>
                                    </div>
                                </div>
                                <!-- CTA card -->
                                <div class="bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 p-3 mb-3">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">eventschedule.com</div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Jazz Night at The Blue Note</div>
                                    <div class="inline-block px-4 py-1.5 rounded-lg bg-gradient-to-r from-orange-500 to-amber-500 text-white text-xs font-semibold">Learn More</div>
                                </div>
                                <!-- Engagement row -->
                                <div class="flex items-center justify-between text-gray-400 dark:text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" /></svg>
                                        <span class="text-[10px]">142</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                        <span class="text-[10px]">28</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                                        <span class="text-[10px]">Share</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right - Text content -->
                <div class="order-1 lg:order-2">
                    <span class="inline-block px-4 py-1.5 rounded-full text-sm font-medium bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 border border-orange-200 dark:border-orange-500/30 mb-6">
                        Event Boost
                    </span>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                        Grow your audience<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-amber-500">automatically</span>
                    </h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 mb-8 max-w-lg">
                        Turn your event details into Facebook and Instagram ads. Set a budget, pick your audience, and launch in minutes.
                    </p>

                    <!-- Feature badges -->
                    <div class="flex flex-wrap gap-3">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Smart targeting
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Budget control
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                            Real-time analytics
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            Facebook & Instagram
                        </span>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('marketing.boost') }}" class="inline-flex items-center gap-2 text-orange-600 dark:text-orange-400 font-semibold hover:gap-3 transition-all">
                            Learn more about Boost
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials Section - Hidden for now
    <section class="relative bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-600 border border-blue-200 mb-6">
                    Trusted by event organizers
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    What our users say
                </h2>
                <p class="text-xl text-gray-500 max-w-2xl mx-auto">
                    Join thousands of venues, musicians, and organizers who trust Event Schedule.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Testimonial 1 -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-1 mb-4">
                        @for ($i = 0; $i < 5; $i++)
                        <svg aria-hidden="true" class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "Event Schedule has completely transformed how we manage our venue calendar. Our patrons love being able to see what's coming up."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-400 flex items-center justify-center text-white font-semibold text-sm">
                            MJ
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Mike Johnson</div>
                            <div class="text-sm text-gray-500">Venue Manager</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-1 mb-4">
                        @for ($i = 0; $i < 5; $i++)
                        <svg aria-hidden="true" class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "Finally, a simple way to share my gig schedule with fans. The QR check-in feature is a game changer for my shows."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-500 to-cyan-500 flex items-center justify-center text-white font-semibold text-sm">
                            SR
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">Sarah Rodriguez</div>
                            <div class="text-sm text-gray-500">Independent Musician</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-1 mb-4">
                        @for ($i = 0; $i < 5; $i++)
                        <svg aria-hidden="true" class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "Zero platform fees on ticket sales? I thought it was too good to be true, but it's real. Switched from Eventbrite and never looked back."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white font-semibold text-sm">
                            DT
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">David Thompson</div>
                            <div class="text-sm text-gray-500">Event Organizer</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    --}}

    <!-- Transition -->
    <div class="h-16 section-fade-to-gray"></div>

    <!-- How it works - Visual steps -->
    <section class="relative bg-gray-50 dark:bg-[#0f0f14] py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <span class="inline-block px-4 py-1.5 rounded-full text-sm font-medium bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 mb-6">
                    Quick setup
                </span>
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                    Live in minutes
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Three steps. That's all it takes.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-400 to-blue-300 rounded-3xl opacity-0 group-hover:opacity-20 blur-xl transition-opacity duration-300 transform-gpu"></div>
                    <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-3xl p-8 h-full shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-400 flex items-center justify-center text-white font-bold text-xl">1</div>
                            <div class="h-px flex-1 bg-gradient-to-r from-blue-300 dark:from-blue-500/50 to-transparent"></div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Create your schedule</h3>
                        <p class="text-gray-600 dark:text-gray-400">Sign up free. Add your events manually or import from Google Calendar.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-sky-400 to-cyan-400 rounded-3xl opacity-0 group-hover:opacity-20 blur-xl transition-opacity duration-300 transform-gpu"></div>
                    <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-3xl p-8 h-full shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 flex items-center justify-center text-white font-bold text-xl">2</div>
                            <div class="h-px flex-1 bg-gradient-to-r from-sky-300 dark:from-sky-500/50 to-transparent"></div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Share your link</h3>
                        <p class="text-gray-600 dark:text-gray-400">Get your custom URL. Put it in your bio, website, or anywhere you want.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-3xl opacity-0 group-hover:opacity-20 blur-xl transition-opacity duration-300 transform-gpu"></div>
                    <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-3xl p-8 h-full shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white font-bold text-xl">3</div>
                            <div class="h-px flex-1 bg-gradient-to-r from-emerald-300 dark:from-emerald-500/50 to-transparent"></div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Grow your audience</h3>
                        <p class="text-gray-600 dark:text-gray-400">Fans follow your schedule. Send them newsletters and notify them when you add new events.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA - Full impact -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Background effects -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-blue-600/20 to-transparent rounded-full blur-[100px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8">
                Ready to share<br>
                <span class="text-gradient">your schedule?</span>
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-12 max-w-2xl mx-auto">
                Start sharing your schedule with your audience today.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex items-center justify-center px-12 py-6 text-xl font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl overflow-hidden transition-all hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/30">
                <span class="relative z-10 flex items-center gap-3">
                    Get started free
                    <svg class="w-6 h-6 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </span>
                <div class="absolute inset-0 animate-shimmer"></div>
            </a>
            <p class="mt-6 text-gray-600 dark:text-gray-400 text-sm">No credit card required. Free forever.</p>
        </div>
    </section>
</x-marketing-layout>
