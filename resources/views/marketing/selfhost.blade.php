<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.selfhost_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.selfhost_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Selfhost</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Selfhosted",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Management Software",
        "operatingSystem": "Linux",
        "description": "Selfhost Event Schedule on your own server. 100% open source with one-click Docker installation, automatic updates, and exclusive AI-powered features.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free and open source"
        },
        "featureList": [
            "One-click Softaculous installation",
            "Docker deployment",
            "Automatic updates",
            "AI-powered auto import",
            "AI blog generation for SEO",
            "Full data ownership",
            "White-label SaaS capability"
        ],
        "url": "{{ url()->current() }}",
        "downloadUrl": "https://github.com/eventschedule/eventschedule",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <x-seo.faq-schema :items="[
        ['q' => 'Is Event Schedule really free to selfhost?', 'a' => 'Yes. Event Schedule is fully open source under the Attribution Assurance License (AAL). You can inspect the code, contribute improvements, or fork it for your own needs at no cost.'],
        ['q' => 'How do I install Event Schedule on my server?', 'a' => 'You can install Event Schedule with a one-click Softaculous installer on most shared hosts, or deploy it with Docker Compose on a VPS, cloud server, or locally. Both paths are documented in the install guide.'],
        ['q' => 'How do updates work for selfhosted installations?', 'a' => 'When a new version is released, a notice appears in your admin panel. One click applies the update in seconds with minimal disruption. No terminal access is required.'],
        ['q' => 'Does the selfhosted version support ticket sales and AI features?', 'a' => 'Yes. Selfhosted installations include ticketing, QR check-ins, AI-powered event auto-import, and AI blog generation for SEO. You connect your own Stripe and Gemini API keys.'],
        ['q' => 'Can I run Event Schedule as a white-label SaaS for my customers?', 'a' => 'Yes. The selfhosted version supports white-label multi-tenant use, so you can run it as a branded SaaS product for your own customers.'],
    ]" />
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (emerald to teal) */
        .text-gradient-selfhost {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #0d9488 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-selfhost {
            background: linear-gradient(135deg, #34d399 0%, #10b981 50%, #14b8a6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-selfhost {
            background: linear-gradient(135deg, #34d399 0%, #10b981 50%, #14b8a6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live deploy terminal */
        .es-server-float { animation: es-server-bob 5.5s ease-in-out infinite; }
        @keyframes es-server-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a row of terminal cursors blinking (command line) */
        .es-cursor {
            flex: 0 0 auto;
            width: 12px;
            border-radius: 2px;
            background: linear-gradient(180deg, rgba(16, 185, 129, 0.85), rgba(20, 184, 166, 0.4));
            animation: es-cursor-blink var(--cu-dur, 2.6s) steps(1, end) infinite;
            animation-delay: var(--cu-delay, 0s);
        }
        @keyframes es-cursor-blink {
            0%, 49% { opacity: 0.9; filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.5)); }
            50%, 100% { opacity: 0.12; }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-server-float, .es-cursor, .animate-pulse-slow, .animate-float, .animate-ping { animation: none !important; }
            .es-cursor { opacity: 0.5; }
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
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(20, 184, 166, 0.26), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(5, 150, 105, 0.14), rgba(5, 150, 105, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Terminal-cursor motif along the bottom edge -->
            <div class="es-cursors absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-5 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 16; $i++)
                    @php $ht = [22, 28, 18, 24, 16][$i % 5]; $dur = 2.2 + ($i % 5) * 0.4; $delay = ($i % 8) * 0.22; @endphp
                    <span class="es-cursor" style="height: {{ $ht }}px; --cu-dur: {{ $dur }}s; --cu-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">100% Open Source</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Your server,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-selfhost">your rules</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Run Event Schedule on your own infrastructure. Full control, exclusive features, and zero platform fees.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('marketing.docs.selfhost.installation') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                    Installation Guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    View on GitHub
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Try the Demo                                             -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-1 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Live Demo
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>See it <span class="text-gradient-selfhost">in action</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Explore both sides of Event Schedule - the admin portal where you manage everything, and the public calendar your attendees see.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2" data-reveal-group="80">
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg">
                                <svg aria-hidden="true" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Portal</h3>
                        </div>
                        <p class="mb-6 flex-grow text-gray-600 dark:text-gray-400">Where schedule owners manage events, tickets, settings, and more. Create events, track sales, and customize your calendar.</p>
                        <a href="{{ demo_url() }}" target="_blank" rel="noopener noreferrer" class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3 font-medium text-white transition-colors hover:from-emerald-500 hover:to-teal-500">
                            Open Admin Demo
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-teal-600 shadow-lg">
                                <svg aria-hidden="true" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Guest Portal</h3>
                        </div>
                        <p class="mb-6 flex-grow text-gray-600 dark:text-gray-400">The public-facing calendar your attendees see. Browse events, purchase tickets, and subscribe to updates.</p>
                        <a href="https://simpsons.eventschedule.com" target="_blank" rel="noopener noreferrer" class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-teal-600 px-6 py-3 font-medium text-white transition-colors hover:from-cyan-500 hover:to-teal-500">
                            Open Guest Demo
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Installation Options                                     -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-1 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Easy Installation
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Get up and running <span class="text-gradient-selfhost">in minutes</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Choose your preferred installation method. Both options provide automated setup with minimal configuration.</p>
            </div>

            <div class="mx-auto grid max-w-5xl gap-4 md:grid-cols-2" data-reveal-group="80">
                <!-- Softaculous -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-6 flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-600 shadow-lg">
                                <svg aria-hidden="true" class="h-9 w-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Softaculous</h3>
                                <p class="text-gray-500 dark:text-gray-400">One-click installer</p>
                            </div>
                        </div>
                        <p class="mb-6 text-gray-600 dark:text-gray-400">Available on most cPanel hosting providers. Install Event Schedule with a single click - no command line required.</p>
                        <ul class="mb-8 flex-grow space-y-3">
                            @foreach (['Automatic database setup', 'Auto-configured environment', 'Available on 1000+ hosts'] as $item)
                                <li class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="https://www.softaculous.com/apps/calendars/Event_Schedule" target="_blank" rel="noopener noreferrer" class="mt-auto inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3 font-medium text-white transition-colors hover:from-emerald-500 hover:to-teal-500">
                            Install with Softaculous
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Docker -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-6 flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-500 shadow-lg">
                                <svg aria-hidden="true" class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M13.98 11.08h2.12a.19.19 0 0 0 .19-.19V9.01a.19.19 0 0 0-.19-.19h-2.12a.18.18 0 0 0-.18.19v1.88c0 .1.08.19.18.19m-2.95-5.43h2.12a.19.19 0 0 0 .18-.19V3.58a.19.19 0 0 0-.18-.19h-2.12a.18.18 0 0 0-.19.19v1.88c0 .1.09.19.19.19m0 2.71h2.12a.19.19 0 0 0 .18-.18V6.29a.19.19 0 0 0-.18-.18h-2.12a.18.18 0 0 0-.19.18v1.89c0 .1.09.18.19.18m-2.93 0h2.12a.19.19 0 0 0 .18-.18V6.29a.18.18 0 0 0-.18-.18H8.1a.18.18 0 0 0-.19.18v1.89c0 .1.09.18.19.18m-2.96 0h2.11a.19.19 0 0 0 .19-.18V6.29a.18.18 0 0 0-.19-.18H5.14a.19.19 0 0 0-.19.18v1.89c0 .1.09.18.19.18m5.89 2.72h2.12a.19.19 0 0 0 .18-.19V9.01a.19.19 0 0 0-.18-.19h-2.12a.18.18 0 0 0-.19.19v1.88c0 .1.09.19.19.19m-2.93 0h2.12a.18.18 0 0 0 .18-.19V9.01a.18.18 0 0 0-.18-.19H8.1a.18.18 0 0 0-.19.19v1.88c0 .1.09.19.19.19m-2.96 0h2.11a.18.18 0 0 0 .19-.19V9.01a.18.18 0 0 0-.19-.19H5.14a.18.18 0 0 0-.19.19v1.88c0 .1.09.19.19.19m-2.92 0h2.12a.18.18 0 0 0 .18-.19V9.01a.18.18 0 0 0-.18-.19H2.22a.19.19 0 0 0-.19.19v1.88c0 .1.09.19.19.19m21.54-1.19c-.06-.03-.12-.06-.19-.08a1.58 1.58 0 0 0-.47-.15 3.04 3.04 0 0 0-.65-.05c-.14 0-.28 0-.42.02-.13.01-.26.03-.38.06l-.12.03c.01-.04.01-.07.01-.11a1.78 1.78 0 0 0-.63-1.46 2.04 2.04 0 0 0-.83-.43l-.18-.04.1.17c.22.37.33.75.33 1.13 0 .15-.02.31-.05.46a3.3 3.3 0 0 1-.19.57c-.35.23-.95.55-1.49.63-.54.08-1.08.08-1.63.08H2.87c-.17 0-.31.13-.32.3-.04.71.05 1.41.25 2.08a4.54 4.54 0 0 0 1.23 2.07c.87.83 2 1.35 3.28 1.54a12.8 12.8 0 0 0 2.15.14c1.67-.05 3.31-.37 4.74-1.09a7.77 7.77 0 0 0 2.75-2.3 8.67 8.67 0 0 0 1.37-2.77c.33.01.66-.02.97-.1.59-.15 1.08-.48 1.39-.96l.13-.21-.24-.07Z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Docker</h3>
                                <p class="text-gray-500 dark:text-gray-400">Containerized deployment</p>
                            </div>
                        </div>
                        <p class="mb-6 text-gray-600 dark:text-gray-400">Deploy with Docker Compose for a consistent, isolated environment. Perfect for VPS, cloud servers, or local development.</p>
                        <ul class="mb-8 flex-grow space-y-3">
                            @foreach (['Isolated environment', 'Easy scaling and backups', 'Pre-configured compose file'] as $item)
                                <li class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="mt-auto inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-600 to-teal-600 px-6 py-3 font-medium text-white transition-colors hover:from-cyan-500 hover:to-teal-500">
                            View Docker Setup
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. One-Click Updates                                        -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div data-reveal>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Automatic Updates
                    </div>
                    <h2 class="mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">One-click updates</h2>
                    <p class="mb-8 text-xl text-gray-500 dark:text-gray-400">Keep your installation up to date with a single click. When a new version is available, just click the update button in your admin panel - no terminal access needed.</p>
                    <ul class="space-y-4">
                        @php
                            $updateFeatures = [
                                ['Database migrations included', 'Schema changes are applied automatically'],
                                ['No downtime required', 'Updates complete in seconds with minimal disruption'],
                                ['Version notifications', 'Get notified in your admin panel when updates are available'],
                            ];
                        @endphp
                        @foreach ($updateFeatures as $uf)
                            <li class="flex items-start gap-4">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-500/20">
                                    <svg aria-hidden="true" class="h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ $uf[0] }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $uf[1] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="relative" data-reveal style="--reveal-delay: 0.1s;" aria-hidden="true">
                    <div class="animate-float rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500">
                                    <span class="font-bold text-white">ES</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">Event Schedule</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">v2.4.1 installed</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Update Available</span>
                        </div>
                        <div class="mb-4 rounded-xl bg-gray-100 p-4 dark:bg-[#0f0f14]">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-300">New version:</span>
                                <span class="font-medium text-gray-900 dark:text-white">v2.5.0</span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">New features, bug fixes, and security updates</div>
                        </div>
                        <div class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 py-3 font-medium text-white">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Update Now
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Selfhost Exclusive Features                              -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-amber-500/20 px-3 py-1 text-sm font-medium text-amber-700 dark:text-amber-300">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Exclusive Features
                </div>
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Only available <span class="text-gradient-selfhost">when selfhosting</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Unlock powerful features that are exclusive to selfhosted installations.</p>
            </div>

            <div class="mx-auto grid max-w-5xl gap-4 md:grid-cols-2" data-reveal-group="80">
                <!-- Auto Import -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Auto Import
                        </div>
                        <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Automatic event importing</h3>
                        <p class="mb-6 text-gray-600 dark:text-gray-400">Automatically pull events from external websites into your schedule. Our intelligent system extracts event details from any webpage using AI-powered parsing.</p>
                        <div class="mb-6 rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                            <div class="mb-3 flex items-center gap-3">
                                <svg aria-hidden="true" class="h-5 w-5 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span class="font-medium text-gray-900 dark:text-white">Respects robots.txt</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">We check each website's robots.txt file before importing to ensure we only access content that site owners have permitted for automated access.</p>
                        </div>
                        <ul class="mt-auto space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            @foreach (['Schedule automatic imports', 'AI extracts dates, times, venues', 'Keep your schedule synced automatically'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg aria-hidden="true" class="h-4 w-4 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- AI Blog -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-4 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            AI-Powered Blog
                        </div>
                        <h3 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Automated content for SEO</h3>
                        <p class="mb-6 text-gray-600 dark:text-gray-400">Generate relevant blog content automatically to drive organic traffic to your event schedule. Our AI creates engaging posts tailored to your events and audience.</p>
                        <div class="mb-6 rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                            <div class="space-y-3">
                                @php
                                    $blogPosts = [
                                        ['Best Jazz Venues in Your City', 'Generated 2 hours ago'],
                                        ["This Week's Must-See Events", 'Scheduled for tomorrow'],
                                    ];
                                @endphp
                                @foreach ($blogPosts as $bp)
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/30">
                                            <svg aria-hidden="true" class="h-4 w-4 text-sky-500 dark:text-sky-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $bp[0] }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $bp[1] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <ul class="mt-auto space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            @foreach (['SEO-optimized content', 'Increase organic traffic', 'Powered by Google Gemini & OpenAI'] as $item)
                                <li class="flex items-center gap-2">
                                    <svg aria-hidden="true" class="h-4 w-4 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Open Source                                              -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <div class="mx-auto mb-8 inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gray-200 dark:bg-white/10" data-reveal>
                <svg aria-hidden="true" class="h-10 w-10 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
            </div>
            <h2 class="es-balance mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>100% <span class="text-gradient-selfhost">Open Source</span></h2>
            <p class="mx-auto mb-8 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Event Schedule is fully open source under the Attribution Assurance License (AAL). Inspect the code, contribute improvements, or fork it for your own needs.</p>

            <div data-reveal>
                @include('marketing.partials.github-star-badge')
            </div>

            <div class="mb-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @php
                    $osStats = [['100%', 'Open Source'], ['Free', 'Forever'], ['AAL', 'Open Source License']];
                @endphp
                @foreach ($osStats as $stat)
                    <div data-reveal class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-6 dark:border-emerald-500/20 dark:from-emerald-900/30 dark:to-teal-900/30">
                        <div class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stat[0] }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $stat[1] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap justify-center gap-4" data-reveal>
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-colors hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                    <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    Main Repository
                </a>
                <a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-colors hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13.983 11.078h2.119a.186.186 0 00.186-.185V9.006a.186.186 0 00-.186-.186h-2.119a.185.185 0 00-.185.185v1.888c0 .102.083.185.185.185m-2.954-5.43h2.118a.186.186 0 00.186-.186V3.574a.186.186 0 00-.186-.185h-2.118a.185.185 0 00-.185.185v1.888c0 .102.082.185.185.185m0 2.716h2.118a.187.187 0 00.186-.186V6.29a.186.186 0 00-.186-.185h-2.118a.185.185 0 00-.185.185v1.887c0 .102.082.186.185.186m-2.93 0h2.12a.186.186 0 00.184-.186V6.29a.185.185 0 00-.185-.185H8.1a.185.185 0 00-.185.185v1.887c0 .102.083.186.185.186m-2.964 0h2.119a.186.186 0 00.185-.186V6.29a.185.185 0 00-.185-.185H5.136a.186.186 0 00-.186.185v1.887c0 .102.084.186.186.186m5.893 2.715h2.118a.186.186 0 00.186-.185V9.006a.186.186 0 00-.186-.186h-2.118a.185.185 0 00-.185.185v1.888c0 .102.082.185.185.185m-2.93 0h2.12a.185.185 0 00.184-.185V9.006a.185.185 0 00-.184-.186h-2.12a.185.185 0 00-.184.185v1.888c0 .102.083.185.185.185m-2.964 0h2.119a.185.185 0 00.185-.185V9.006a.185.185 0 00-.185-.186h-2.119a.185.185 0 00-.186.185v1.888c0 .102.084.185.186.185m-2.92 0h2.12a.185.185 0 00.184-.185V9.006a.185.185 0 00-.184-.186h-2.12a.186.186 0 00-.186.185v1.888c0 .102.084.185.186.185m-.165 2.715h2.119a.186.186 0 00.185-.185v-1.888a.185.185 0 00-.185-.185H2.136a.186.186 0 00-.186.185v1.888c0 .102.084.185.186.185"/>
                    </svg>
                    Docker Files
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. SaaS White-Label                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div data-reveal="panel" class="relative overflow-hidden rounded-3xl border border-gray-200 bg-gradient-to-br from-sky-100 via-blue-100 to-blue-100 p-8 dark:border-white/10 dark:from-sky-900 dark:via-blue-900 dark:to-blue-900 md:p-12">
                <div class="pointer-events-none absolute right-0 top-0 h-64 w-64 rounded-full bg-blue-500/20 blur-[100px]" aria-hidden="true"></div>
                <div class="pointer-events-none absolute bottom-0 left-0 h-64 w-64 rounded-full bg-sky-500/20 blur-[100px]" aria-hidden="true"></div>

                <div class="relative z-10 grid items-center gap-8 lg:grid-cols-2">
                    <div>
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Business Opportunity
                        </div>
                        <h2 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">Build your own SaaS business</h2>
                        <p class="mb-6 text-xl text-gray-600 dark:text-white/80">Turn Event Schedule into your own white-label SaaS platform. Offer event scheduling as a service to your customers under your own brand.</p>
                        <ul class="mb-8 space-y-3">
                            @foreach (['Multi-tenant architecture built-in', 'Stripe integration for subscriptions', 'Full white-label customization', 'Keep 100% of your revenue'] as $item)
                                <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                                    <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ marketing_url('/saas') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-sky-600 px-6 py-3 font-medium text-white shadow-lg shadow-blue-500/25 transition-all hover:from-blue-500 hover:to-sky-500">
                            Learn about SaaS setup
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <div class="relative" aria-hidden="true">
                        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-[#0f0f14]">
                            <div class="mb-6 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-sky-600">
                                    <span class="text-sm font-bold text-white">YB</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">Your Brand</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">yourbrand.com</div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="rounded-xl border border-gray-100 bg-gray-100 p-4 dark:border-white/5 dark:bg-white/5">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Monthly subscribers</span>
                                        <span class="text-sm text-emerald-500 dark:text-emerald-400">+12%</span>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">247</div>
                                </div>
                                <div class="rounded-xl border border-gray-100 bg-gray-100 p-4 dark:border-white/5 dark:bg-white/5">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Monthly revenue</span>
                                        <span class="text-sm text-emerald-500 dark:text-emerald-400">+8%</span>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">$4,940</div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="rounded-lg bg-blue-500/20 p-3 text-center">
                                        <div class="font-semibold text-gray-900 dark:text-white">Free</div>
                                        <div class="text-xs text-blue-700 dark:text-blue-300">142 users</div>
                                    </div>
                                    <div class="rounded-lg bg-sky-500/20 p-3 text-center">
                                        <div class="font-semibold text-gray-900 dark:text-white">Pro</div>
                                        <div class="text-xs text-sky-700 dark:text-sky-300">89 users</div>
                                    </div>
                                    <div class="rounded-lg bg-blue-500/20 p-3 text-center">
                                        <div class="font-semibold text-gray-900 dark:text-white">Team</div>
                                        <div class="text-xs text-blue-700 dark:text-blue-300">16 users</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Finale                                                   -->
    <!-- ============================================================ -->
    <section class="relative bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-emerald-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-cursors absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-5 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 12; $i++)
                            @php $ht = [22, 28, 18, 24, 16][$i % 5]; $dur = 2.2 + ($i % 5) * 0.4; $delay = ($i % 8) * 0.22; @endphp
                            <span class="es-cursor" style="height: {{ $ht }}px; --cu-dur: {{ $dur }}s; --cu-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-selfhost">selfhost?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Get started with the installation guide. Have questions? Check out our GitHub discussions.
                    </p>
                    <div class="flex flex-col flex-wrap items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('marketing.docs.selfhost.installation') }}" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Read Installation Guide
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center rounded-2xl border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white transition-all hover:bg-white/10">
                            Or try the hosted version
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
