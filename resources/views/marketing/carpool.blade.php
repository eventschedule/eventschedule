<x-marketing-layout>
    <x-slot name="title">Event Carpool Matching - Event Schedule</x-slot>
    <x-slot name="description">Let attendees coordinate rides to your events. Drivers offer seats, riders request spots, with approvals, reminders, and reviews. Pro feature.</x-slot>
    <x-slot name="breadcrumbTitle">Carpool</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How does carpool matching work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Drivers create ride offers specifying their city, direction (to event, from event, or both), departure time, meeting point, and available spots. Riders browse offers and request a spot with an optional message. Drivers then approve or decline each request."
                }
            },
            {
                "@type": "Question",
                "name": "Who can offer or request rides?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any authenticated guest can offer or request rides for events that have carpool enabled. Organizers enable carpool matching per event in the admin portal."
                }
            },
            {
                "@type": "Question",
                "name": "How does carpool handle safety?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "After a ride, passengers can leave 1 to 5 star ratings and written reviews for drivers. Users can report problematic behavior, and organizers can moderate all carpool activity from the admin portal."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (emerald to green) */
        .text-gradient-carpool {
            background: linear-gradient(135deg, #059669 0%, #16a34a 50%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-carpool {
            background: linear-gradient(135deg, #34d399 0%, #86efac 50%, #a7f3d0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* On always-dark surfaces, keep the accent bright regardless of page mode */
        .es-finale-panel .text-gradient-carpool {
            background: linear-gradient(135deg, #34d399 0%, #86efac 50%, #a7f3d0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live carpool ride card */
        .es-carpool-float { animation: es-carpool-bob 5.5s ease-in-out infinite; }
        @keyframes es-carpool-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a road of flowing lane markings */
        .es-dash {
            flex: 0 0 auto;
            width: 34px;
            height: 6px;
            border-radius: 9999px;
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.7), rgba(34, 197, 94, 0.3));
            animation: es-dash-flow var(--rd-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--rd-delay, 0s);
        }
        @keyframes es-dash-flow {
            0%, 100% { opacity: 0.2; transform: translateX(-5px); }
            50% { opacity: 0.9; transform: translateX(5px); filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-carpool-float, .es-dash, .animate-pulse-slow { animation: none !important; }
            .es-dash { opacity: 0.5; transform: none; }
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
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(34, 197, 94, 0.26), rgba(34, 197, 94, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(52, 211, 153, 0.14), rgba(52, 211, 153, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Road-lane motif along the bottom edge -->
            <div class="es-road absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-4 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 16; $i++)
                    @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 8) * 0.24; @endphp
                    <span class="es-dash" style="--rd-dur: {{ $dur }}s; --rd-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg class="h-5 w-5 text-emerald-500 dark:text-emerald-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 17h2m10 0h2M5 17a2 2 0 01-2-2v-4a1 1 0 011-1h1l2-4h10l2 4h1a1 1 0 011 1v4a2 2 0 01-2 2M5 17a2 2 0 002 2h10a2 2 0 002-2M7.5 14h.01M16.5 14h.01" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Ride Together</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Coordinate rides</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-carpool">to your events</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Let your attendees share rides. Drivers offer seats, riders request spots, and everyone gets to the event together.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-green-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
                    Get started free
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-carpool" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Carpool guide
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
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Smarter commutes for <span class="text-gradient-carpool">every event</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Help your community show up together, ride after ride.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- 1: Ride Offers -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 17h2m10 0h2M5 17a2 2 0 01-2-2v-4a1 1 0 011-1h1l2-4h10l2 4h1a1 1 0 011 1v4a2 2 0 01-2 2M5 17a2 2 0 002 2h10a2 2 0 002-2M7.5 14h.01M16.5 14h.01" />
                            </svg>
                            Offers
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Ride offers</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Drivers post offers with their city, direction, departure time, meeting point, and available spots from 1 to 10.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">Downtown</span>
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">To event</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                    <span>3 spots</span>
                                    <span>Departs 6:30 PM</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2: Request and Approval -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Approval
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Request and approval</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Riders request spots with a message. Drivers review each request and approve or decline.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <div class="h-5 w-5 shrink-0 rounded-full bg-emerald-300 dark:bg-emerald-500/40"></div>
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Sarah</span>
                                        <span class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Approved</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-5 w-5 shrink-0 rounded-full bg-green-300 dark:bg-green-500/40"></div>
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Mike</span>
                                        <span class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-[11px] text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3: To, From, or Both -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            Flexible
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">To, from, or both</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Offers support to-event, from-event, or round trip directions. Riders find exactly what they need.</p>
                        <div class="mt-auto flex flex-wrap gap-2" aria-hidden="true">
                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 dark:border-white/10 dark:bg-white/10 dark:text-gray-300">
                                <svg class="mr-1.5 h-3 w-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                To event
                            </span>
                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 dark:border-white/10 dark:bg-white/10 dark:text-gray-300">
                                <svg class="mr-1.5 h-3 w-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
                                From event
                            </span>
                            <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 dark:border-white/10 dark:bg-white/10 dark:text-gray-300">
                                <svg class="mr-1.5 h-3 w-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                                Round trip
                            </span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4: Notifications and Reminders -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            Alerts
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Notifications and reminders</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Email notifications for requests, approvals, and cancellations. Automatic 24-hour reminders before departure.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"></div>
                                        <span class="text-xs text-gray-600 dark:text-gray-300">New ride request from Sarah</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></div>
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Your request was approved</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-2 shrink-0 rounded-full bg-amber-500"></div>
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Ride reminder: departing tomorrow</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5: Reviews and Safety -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                            Trust
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Reviews and safety</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Post-ride 1 to 5 star ratings. Report problematic behavior. Admin moderation tools keep rides safe.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-2 flex items-center gap-1">
                                    @for ($s = 0; $s < 5; $s++)
                                        <svg class="h-4 w-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <div class="text-xs italic text-gray-600 dark:text-gray-300">"Smooth ride, great conversation!"</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6: Pro Feature -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Pro
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Pro feature</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Carpool matching is available on Pro and Enterprise plans. Enable it for any event in the admin portal.</p>
                        <div class="mt-auto flex flex-wrap gap-2" aria-hidden="true">
                            @foreach (['Pro plan', 'Enterprise plan', 'Unlimited rides'] as $tag)
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
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-carpool">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about carpool matching.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['How does carpool matching work?', 'Drivers create ride offers specifying their city, direction (to event, from event, or both), departure time, meeting point, and available spots. Riders browse offers and request a spot with an optional message. Drivers then approve or decline each request.'],
                        ['Who can offer or request rides?', 'Any authenticated guest can offer or request rides for events that have carpool enabled. Organizers enable carpool matching per event in the admin portal.'],
                        ['How does carpool handle safety?', 'After a ride, passengers can leave 1 to 5 star ratings and written reviews for drivers. Users can report problematic behavior, and organizers can moderate all carpool activity from the admin portal.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-emerald-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(16, 185, 129, 0.3), rgba(16, 185, 129, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-road absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-4 px-8 opacity-45" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 12; $i++)
                            @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 8) * 0.24; @endphp
                            <span class="es-dash" style="--rd-dur: {{ $dur }}s; --rd-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Make events easier <span class="text-gradient-carpool">to reach</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Enable carpool matching and let your community share rides. No credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 to-green-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-emerald-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/40">
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
        "name": "Event Schedule Carpool Matching",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Ride Sharing Coordination Software",
        "operatingSystem": "Web",
        "description": "Let attendees coordinate rides to your events. Drivers offer seats, riders request spots, with approvals, reminders, and reviews. Pro feature.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free trial, Pro plan feature"
        },
        "featureList": [
            "Driver ride offers with city and direction",
            "Rider request and driver approval workflow",
            "To-event, from-event, and round trip support",
            "Email notifications and 24-hour reminders",
            "Post-ride star ratings and reviews",
            "Admin moderation and reporting tools"
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
