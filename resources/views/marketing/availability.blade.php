<x-marketing-layout>
    <x-slot name="title">Availability Management - Event Schedule</x-slot>
    <x-slot name="description">Set availability windows for your schedule. Define when you're open for bookings and let guests see your available times. Enterprise feature. No credit card required.</x-slot>
    <x-slot name="breadcrumbTitle">Availability</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How does availability management work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Schedule owners set their available days and time slots. Guests can see when the schedule is available and plan accordingly. Available times are displayed on the schedule's public page."
                }
            },
            {
                "@type": "Question",
                "name": "Who can set availability?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Schedule admins and owners can configure availability from the schedule settings. Availability management is an Enterprise plan feature."
                }
            },
            {
                "@type": "Question",
                "name": "Can I set different availability for different days?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You can configure unique time slots for each day of the week, set days as unavailable, and adjust your schedule as needed."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (teal to cyan) */
        .text-gradient-availability {
            background: linear-gradient(135deg, #0d9488 0%, #06b6d4 50%, #2dd4bf 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-availability {
            background: linear-gradient(135deg, #2dd4bf 0%, #67e8f9 50%, #99f6e4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* On always-dark surfaces, keep the accent bright regardless of page mode */
        .es-finale-panel .text-gradient-availability {
            background: linear-gradient(135deg, #2dd4bf 0%, #67e8f9 50%, #99f6e4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live weekly availability card */
        .es-avail-float { animation: es-avail-bob 5.5s ease-in-out infinite; }
        @keyframes es-avail-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: an availability grid of cells toggling open */
        .es-cell {
            flex: 0 0 auto;
            width: 24px;
            height: 24px;
            border-radius: 7px;
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.7), rgba(6, 182, 212, 0.35));
            animation: es-cell-pulse var(--cl-dur, 3s) ease-in-out infinite;
            animation-delay: var(--cl-delay, 0s);
        }
        @keyframes es-cell-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.88); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(20, 184, 166, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-avail-float, .es-cell, .animate-pulse-slow { animation: none !important; }
            .es-cell { opacity: 0.5; transform: none; }
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
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(20, 184, 166, 0.3), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(6, 182, 212, 0.26), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(45, 212, 191, 0.14), rgba(45, 212, 191, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Availability-grid motif along the bottom edge -->
            <div class="es-cells absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-3 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 20; $i++)
                    @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 8) * 0.26; @endphp
                    <span class="es-cell" style="--cl-dur: {{ $dur }}s; --cl-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg class="h-5 w-5 text-teal-500 dark:text-teal-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Schedule Management</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Share your</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-availability">availability</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Set your available days and times so guests always know when to reach you. Display your availability right on your schedule page.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-teal-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
                    Get started free
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.managing_schedules') }}#availability" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Availability guide
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento grid                                               -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Simple availability, <span class="text-gradient-availability">clear communication</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Let everyone know when you're open, right on your schedule.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- 1: Weekly Schedule -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            Weekly
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Weekly schedule</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Define your availability for each day of the week. Set different hours for different days to match your routine.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="space-y-1.5">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-300">Monday</span>
                                        <span class="text-teal-600 dark:text-teal-400">9:00 AM - 5:00 PM</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-300">Tuesday</span>
                                        <span class="text-teal-600 dark:text-teal-400">9:00 AM - 5:00 PM</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-300">Wednesday</span>
                                        <span class="text-gray-400 dark:text-gray-500">Unavailable</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-300">Thursday</span>
                                        <span class="text-teal-600 dark:text-teal-400">10:00 AM - 6:00 PM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2: Public Display -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Visibility
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Public display</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Your availability is shown directly on your schedule page. Guests see your open hours without needing to ask.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-2 text-xs font-medium text-gray-900 dark:text-white">Available times</div>
                                <div class="grid grid-cols-2 gap-1">
                                    <div class="rounded bg-teal-50 px-2 py-1 text-center text-xs text-teal-700 dark:bg-teal-500/10 dark:text-teal-300">Mon 9-5</div>
                                    <div class="rounded bg-teal-50 px-2 py-1 text-center text-xs text-teal-700 dark:bg-teal-500/10 dark:text-teal-300">Tue 9-5</div>
                                    <div class="rounded bg-teal-50 px-2 py-1 text-center text-xs text-teal-700 dark:bg-teal-500/10 dark:text-teal-300">Thu 10-6</div>
                                    <div class="rounded bg-teal-50 px-2 py-1 text-center text-xs text-teal-700 dark:bg-teal-500/10 dark:text-teal-300">Fri 9-5</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3: Easy Configuration -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Setup
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Easy configuration</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Set up your availability in the schedule settings. Toggle days on or off and set time ranges with a few clicks.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded bg-teal-500"></div>
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Monday: On</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded bg-teal-500"></div>
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Tuesday: On</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded bg-gray-300 dark:bg-gray-600"></div>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Wednesday: Off</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4: Talent Schedules -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            Talent
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Perfect for talent</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Musicians, DJs, speakers, and performers can share when they're available for bookings directly on their schedule.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-2 text-xs font-medium text-gray-900 dark:text-white">DJ Alex - Available</div>
                                <div class="space-y-1">
                                    <div class="text-xs text-teal-600 dark:text-teal-400">Fri & Sat evenings</div>
                                    <div class="text-xs text-teal-600 dark:text-teal-400">Sun afternoons</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5: Venue Schedules -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                            </svg>
                            Venues
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Great for venues</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Venues can display their operating hours and available booking slots. Visitors see open times at a glance.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-2 text-xs font-medium text-gray-900 dark:text-white">The Blue Note</div>
                                <div class="space-y-1">
                                    <div class="text-xs text-teal-600 dark:text-teal-400">Wed-Sun: 7 PM - 2 AM</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Mon-Tue: Closed</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6: Enterprise Feature -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Enterprise
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Enterprise feature</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Availability management is available on Enterprise plans. Display your open hours on any schedule type.</p>
                        <div class="mt-auto flex flex-wrap gap-2" aria-hidden="true">
                            @foreach (['Enterprise plan', 'All schedule types', 'Weekly hours'] as $tag)
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-black/30">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-availability">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about availability.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['How does availability management work?', 'Schedule owners set their available days and time slots. Guests can see when the schedule is available and plan accordingly. Available times are displayed on the schedule\'s public page.'],
                        ['Who can set availability?', 'Schedule admins and owners can configure availability from the schedule settings. Availability management is an Enterprise plan feature.'],
                        ['Can I set different availability for different days?', 'Yes. You can configure unique time slots for each day of the week, set days as unavailable, and adjust your schedule as needed.'],
                    ];
                @endphp
                @foreach ($faqs as [$q, $a])
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

    <!-- ============================================================ -->
    <!-- 4. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-teal-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(20, 184, 166, 0.3), rgba(20, 184, 166, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-cells absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-3 px-8 opacity-45" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 14; $i++)
                            @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 8) * 0.26; @endphp
                            <span class="es-cell" style="--cl-dur: {{ $dur }}s; --cl-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Let guests see your <span class="text-gradient-availability">availability</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Set your hours and start sharing your schedule today. No credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-teal-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Availability Management",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Scheduling Software",
        "operatingSystem": "Web",
        "description": "Set availability windows for your schedule. Define when you're open for bookings and let guests see your available times. Enterprise feature. No credit card required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free trial, Enterprise plan feature"
        },
        "featureList": [
            "Weekly availability schedule",
            "Per-day time slot configuration",
            "Public availability display",
            "Works with all schedule types",
            "Easy toggle on/off per day",
            "Enterprise plan feature"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
