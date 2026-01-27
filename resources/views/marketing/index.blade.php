<x-marketing-layout>
    <x-slot name="title">Event Schedule - Plan, Promote & Share Event Calendars</x-slot>
    <x-slot name="description">Create beautiful event calendars, sell tickets with no platform fees, and check in attendees with QR codes. Free for venues, performers, and communities.</x-slot>
    <x-slot name="keywords">event schedule, event calendar, free event management, ticketing platform, QR check-in, venue calendar, performer schedule, sell tickets online, event organizer software</x-slot>
    <x-slot name="socialImage">social/home.png</x-slot>

    <style>
        /* Smooth scrolling for anchor links */
        html {
            scroll-behavior: smooth;
        }

        /* Animations */
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes float-gentle {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        @keyframes scroll-step {
            /* 7 images, each shows for ~1.5s with quick slide */
            0%, 12% { transform: translateX(0); }
            14%, 26% { transform: translateX(-100%); }
            28%, 40% { transform: translateX(-200%); }
            42%, 54% { transform: translateX(-300%); }
            56%, 68% { transform: translateX(-400%); }
            70%, 82% { transform: translateX(-500%); }
            84%, 96% { transform: translateX(-600%); }
            100% { transform: translateX(-700%); }
        }
        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        @keyframes reveal-up {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        @keyframes bounce-subtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }

        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient-shift 8s ease infinite;
        }
        .animate-float { animation: float-gentle 6s ease-in-out infinite; }
        .animate-marquee { animation: marquee 30s linear infinite; }
        .animate-scroll-step { animation: scroll-step 21s ease-in-out infinite; }
        .screenshot-carousel:hover .animate-scroll-step {
            animation-play-state: paused;
        }
        .animate-reveal { animation: reveal-up 0.8s ease-out forwards; }
        .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        .animate-bounce-subtle { animation: bounce-subtle 2s ease-in-out infinite; }

        /* Delays */
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }

        /* Glass effect */
        .glass {
            background: rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        .dark .glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Gradient text */
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
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

        /* Glow effects */
        .glow-blue { box-shadow: 0 0 60px rgba(102, 126, 234, 0.4); }
        .glow-purple { box-shadow: 0 0 60px rgba(118, 75, 162, 0.4); }
        .glow-pink { box-shadow: 0 0 60px rgba(240, 147, 251, 0.3); }

        /* Interactive card */
        .bento-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bento-card:hover {
            transform: translateY(-4px) scale(1.01);
        }

        /* Marquee pause on hover */
        .marquee-container:hover .animate-marquee {
            animation-play-state: paused;
        }

        /* Cursor glow - optional fancy effect */
        .cursor-glow {
            position: fixed;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.15) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
            transform: translate(-50%, -50%);
            transition: opacity 0.3s;
        }
    </style>

    <!-- Hero Section - Side by side with carousel -->
    <section class="relative min-h-screen flex items-center overflow-hidden bg-white dark:bg-[#0a0a0f] noise">
        <!-- Animated gradient orbs -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-gradient-to-r from-violet-600/30 to-indigo-600/30 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] bg-gradient-to-r from-fuchsia-600/20 to-pink-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-r from-blue-600/10 to-cyan-600/10 rounded-full blur-[120px]"></div>
        </div>

        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.02)_1px,transparent_1px)] bg-[size:60px_60px]"></div>

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
                        Event calendars, ticketing, and check-ins for venues, performers, and communities.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-reveal delay-300">
                        <a href="{{ route('sign_up') }}" class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl overflow-hidden transition-all hover:scale-105 hover:shadow-2xl hover:shadow-violet-500/25">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <div class="absolute inset-0 animate-shimmer"></div>
                        </a>
                        @if (!Auth::check() || \App\Services\DemoService::isDemoUser(Auth::user()))
                        <a href="https://demo.eventschedule.com" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-gray-900 dark:text-white glass rounded-2xl hover:bg-gray-200 dark:hover:bg-white/10 transition-all">
                            View demo
                        </a>
                        @endif
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
                        @foreach($screenshots as $screenshot)
                        <div class="flex-shrink-0 w-full overflow-hidden">
                            <img src="{{ url($screenshot[0]) }}" alt="{{ $screenshot[1] }}" class="block w-full h-auto scale-[1.02]" loading="lazy" />
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Marquee Section - Social proof -->
    <section class="relative bg-gray-50 border-y border-gray-200 py-4 overflow-hidden marquee-container">
        <div class="flex animate-marquee whitespace-nowrap">
            @for($i = 0; $i < 2; $i++)
            <span class="mx-8 text-2xl font-semibold text-gray-400 flex items-center gap-3">
                <span class="text-gray-900">Musicians</span>
                <svg class="w-2 h-2 text-violet-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-400 flex items-center gap-3">
                <span class="text-gray-900">Venues</span>
                <svg class="w-2 h-2 text-fuchsia-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-400 flex items-center gap-3">
                <span class="text-gray-900">DJs</span>
                <svg class="w-2 h-2 text-pink-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-400 flex items-center gap-3">
                <span class="text-gray-900">Promoters</span>
                <svg class="w-2 h-2 text-indigo-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-400 flex items-center gap-3">
                <span class="text-gray-900">Food Trucks</span>
                <svg class="w-2 h-2 text-cyan-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-400 flex items-center gap-3">
                <span class="text-gray-900">Theaters</span>
                <svg class="w-2 h-2 text-amber-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-400 flex items-center gap-3">
                <span class="text-gray-900">Bands</span>
                <svg class="w-2 h-2 text-emerald-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            <span class="mx-8 text-2xl font-semibold text-gray-400 flex items-center gap-3">
                <span class="text-gray-900">Festivals</span>
                <svg class="w-2 h-2 text-rose-500" fill="currentColor" viewBox="0 0 8 8" aria-hidden="true"><circle cx="4" cy="4" r="4"/></svg>
            </span>
            @endfor
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="relative bg-gray-50 py-24 scroll-mt-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Everything you need to run events
                </h2>
                <p class="text-xl text-gray-500 max-w-2xl mx-auto">
                    From scheduling to check-ins, we've got you covered.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Ticketing & Check-ins -->
                <a href="{{ marketing_url('/ticketing') }}" class="feature-card group block bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-violet-200 transition-all" aria-label="Learn more about ticketing and QR check-ins">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-100 mb-4">
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-violet-600 transition-colors">Ticketing & QR Check-ins</h3>
                    <p class="text-gray-600 text-sm">Sell tickets online with multiple types (GA, VIP), set limits, and scan QR codes for fast check-ins.</p>
                    <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Secure payments by Stripe</span>
                    </div>
                    <span class="inline-flex items-center mt-3 text-sm font-medium text-violet-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- AI Features -->
                <a href="{{ marketing_url('/ai') }}" class="feature-card group block bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-fuchsia-200 transition-all" aria-label="Learn more about AI-powered features">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-fuchsia-100 mb-4">
                        <svg class="w-6 h-6 text-fuchsia-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-fuchsia-600 transition-colors">AI-Powered</h3>
                    <p class="text-gray-600 text-sm">Auto-extract event details with AI parsing. Translate your entire schedule instantly.</p>
                    <span class="inline-flex items-center mt-3 text-sm font-medium text-fuchsia-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Calendar Sync -->
                <a href="{{ marketing_url('/calendar-sync') }}" class="feature-card group block bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-blue-200 transition-all" aria-label="Learn more about calendar sync">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">Calendar Sync</h3>
                    <p class="text-gray-600 text-sm">Two-way sync with Google Calendar. Let attendees add events to Apple, Google, or Outlook calendars.</p>
                    <span class="inline-flex items-center mt-3 text-sm font-medium text-blue-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Built-in Analytics -->
                <a href="{{ marketing_url('/analytics') }}" class="feature-card group block bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-emerald-200 transition-all" aria-label="Learn more about built-in analytics">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 mb-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-emerald-600 transition-colors">Built-in Analytics</h3>
                    <p class="text-gray-600 text-sm">Track page views, device breakdown, top events, and traffic sources. No external services required.</p>
                    <span class="inline-flex items-center mt-3 text-sm font-medium text-emerald-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Custom Fields -->
                <a href="{{ marketing_url('/custom-fields') }}" class="feature-card group block bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-amber-200 transition-all" aria-label="Learn more about custom fields">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-amber-600 transition-colors">Custom Fields</h3>
                    <p class="text-gray-600 text-sm">Collect additional info from ticket buyers with text, dropdown, date, and yes/no fields.</p>
                    <span class="inline-flex items-center mt-3 text-sm font-medium text-amber-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Team & Permissions -->
                <a href="{{ marketing_url('/team-scheduling') }}" class="feature-card group block bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-cyan-200 transition-all" aria-label="Learn more about team scheduling">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 mb-4">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-cyan-600 transition-colors">Team Scheduling</h3>
                    <p class="text-gray-600 text-sm">Invite team members, manage permissions, and coordinate schedules together.</p>
                    <span class="inline-flex items-center mt-3 text-sm font-medium text-cyan-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Sub-schedules -->
                <a href="{{ marketing_url('/sub-schedules') }}" class="feature-card group block bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-rose-200 transition-all" aria-label="Learn more about sub-schedules">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 mb-4">
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-rose-600 transition-colors">Sub-schedules</h3>
                    <p class="text-gray-600 text-sm">Organize events into categories. Perfect for venues with multiple rooms or event series.</p>
                    <span class="inline-flex items-center mt-3 text-sm font-medium text-rose-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- Online Events -->
                <a href="{{ marketing_url('/online-events') }}" class="feature-card group block bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-indigo-200 transition-all" aria-label="Learn more about online events">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-100 mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">Online Events</h3>
                    <p class="text-gray-600 text-sm">Sell tickets to virtual events. Share streaming links with ticket holders automatically.</p>
                    <span class="inline-flex items-center mt-3 text-sm font-medium text-indigo-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <!-- API & Open Source -->
                <a href="{{ marketing_url('/open-source') }}" class="feature-card group block bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-gray-300 transition-all" aria-label="Learn more about open source and API">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 mb-4">
                        <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-gray-700 transition-colors">Open Source & API</h3>
                    <p class="text-gray-600 text-sm">Self-host on your own server. Full REST API for custom integrations. AAL licensed.</p>
                    <span class="inline-flex items-center mt-3 text-sm font-medium text-gray-600 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
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
                <div class="bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-500/20 mb-4">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Free forever</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Unlimited events and schedules on our free plan</p>
                </div>

                <!-- No Platform Fees -->
                <div class="bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-violet-500/20 mb-4">
                        <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">0% platform fees</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">We don't take a cut of your ticket sales</p>
                </div>

                <!-- Open Source -->
                <div class="bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-fuchsia-500/20 mb-4">
                        <svg class="w-6 h-6 text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Open source</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Self-host on your own server. AAL licensed.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials Section - Hidden for now
    <section class="relative bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-1.5 rounded-full text-sm font-medium bg-violet-100 text-violet-600 border border-violet-200 mb-6">
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
                        <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "Event Schedule has completely transformed how we manage our venue calendar. Our patrons love being able to see what's coming up."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white font-semibold text-sm">
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
                        <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        "Finally, a simple way to share my gig schedule with fans. The QR check-in feature is a game changer for my shows."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-fuchsia-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
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
                        <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
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

    <!-- How it works - Visual steps -->
    <section class="relative bg-gray-50 py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <span class="inline-block px-4 py-1.5 rounded-full text-sm font-medium bg-emerald-100 text-emerald-600 border border-emerald-200 mb-6">
                    Quick setup
                </span>
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Live in minutes
                </h2>
                <p class="text-xl text-gray-500 max-w-2xl mx-auto">
                    Three steps. That's all it takes.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-violet-400 to-indigo-400 rounded-3xl opacity-0 group-hover:opacity-20 blur-xl transition-opacity"></div>
                    <div class="relative bg-white border border-gray-200 rounded-3xl p-8 h-full shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white font-bold text-xl">1</div>
                            <div class="h-px flex-1 bg-gradient-to-r from-violet-300 to-transparent"></div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Create your schedule</h3>
                        <p class="text-gray-600">Sign up free. Add your events manually or import from Google Calendar.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-fuchsia-400 to-pink-400 rounded-3xl opacity-0 group-hover:opacity-20 blur-xl transition-opacity"></div>
                    <div class="relative bg-white border border-gray-200 rounded-3xl p-8 h-full shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-fuchsia-500 to-pink-500 flex items-center justify-center text-white font-bold text-xl">2</div>
                            <div class="h-px flex-1 bg-gradient-to-r from-fuchsia-300 to-transparent"></div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Share your link</h3>
                        <p class="text-gray-600">Get your custom URL. Put it in your bio, website, or anywhere you want.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-3xl opacity-0 group-hover:opacity-20 blur-xl transition-opacity"></div>
                    <div class="relative bg-white border border-gray-200 rounded-3xl p-8 h-full shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white font-bold text-xl">3</div>
                            <div class="h-px flex-1 bg-gradient-to-r from-emerald-300 to-transparent"></div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Grow your audience</h3>
                        <p class="text-gray-600">Fans follow your schedule. They get notified when you add new events.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA - Full impact -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Background effects -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-violet-600/20 to-transparent rounded-full blur-[100px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8">
                Ready to share<br>
                <span class="text-gradient">your schedule?</span>
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-12 max-w-2xl mx-auto">
                Join musicians, venues, and organizers who use Event Schedule to connect with their audience.
            </p>
            <a href="{{ route('sign_up') }}" class="group relative inline-flex items-center justify-center px-12 py-6 text-xl font-semibold text-white bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl overflow-hidden transition-all hover:scale-105 hover:shadow-2xl hover:shadow-violet-500/30">
                <span class="relative z-10 flex items-center gap-3">
                    Get started free
                    <svg class="w-6 h-6 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </span>
                <div class="absolute inset-0 animate-shimmer"></div>
            </a>
            <p class="mt-6 text-gray-500 dark:text-gray-500 text-sm">No credit card required. Free forever.</p>
        </div>
    </section>
</x-marketing-layout>
