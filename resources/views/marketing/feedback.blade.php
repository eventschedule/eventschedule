<x-marketing-layout>
    <x-slot name="title">Event Feedback & Reviews - Event Schedule</x-slot>
    <x-slot name="description">Collect feedback and reviews from event attendees. Guests leave star ratings and written reviews after your events, so you can learn what worked.</x-slot>
    <x-slot name="breadcrumbTitle">Event Feedback</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How does event feedback work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "After an event ends, guests can leave a star rating and a written review. Organizers see all feedback in the admin portal and can use it to improve future events."
                }
            },
            {
                "@type": "Question",
                "name": "Who can leave feedback?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any authenticated guest who attended or followed the event can leave feedback. Each guest can submit one review per event."
                }
            },
            {
                "@type": "Question",
                "name": "Can organizers moderate reviews?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Organizers can view all reviews in the admin portal and manage them. Reviews help organizers understand what worked and what to improve."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (amber to orange) */
        .text-gradient-feedback {
            background: linear-gradient(135deg, #d97706 0%, #f97316 50%, #fbbf24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-feedback {
            background: linear-gradient(135deg, #fbbf24 0%, #fdba74 50%, #fde68a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* On always-dark surfaces, keep the accent bright regardless of page mode */
        .es-finale-panel .text-gradient-feedback {
            background: linear-gradient(135deg, #fbbf24 0%, #fdba74 50%, #fde68a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live review card */
        .es-review-float { animation: es-review-bob 5.5s ease-in-out infinite; }
        @keyframes es-review-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a field of twinkling stars (the rating) */
        .es-star {
            display: inline-flex;
            color: rgba(245, 158, 11, 0.85);
            animation: es-star-twinkle var(--st-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--st-delay, 0s);
        }
        @keyframes es-star-twinkle {
            0%, 100% { opacity: 0.25; transform: scale(0.82); }
            50% { opacity: 1; transform: scale(1); filter: drop-shadow(0 0 6px rgba(245, 158, 11, 0.55)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-review-float, .es-star, .animate-pulse-slow { animation: none !important; }
            .es-star { opacity: 0.6; transform: none; }
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
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(245, 158, 11, 0.3), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(249, 115, 22, 0.26), rgba(249, 115, 22, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(251, 191, 36, 0.14), rgba(251, 191, 36, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Star-rating motif along the bottom edge -->
            <div class="es-stars absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-5 px-8 opacity-60 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 14; $i++)
                    @php $sz = [16, 24, 14, 20, 28][$i % 5]; $dur = 2.4 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.28; @endphp
                    <span class="es-star" style="--st-dur: {{ $dur }}s; --st-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg class="h-5 w-5 text-amber-500 dark:text-amber-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Guest Insights</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Event feedback</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-feedback">and reviews</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Collect star ratings and written reviews from your attendees. Understand what worked, what to improve, and build on every event.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-600 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Get started free
                    <svg class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_events') }}#feedback" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Feedback guide
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
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Learn from <span class="text-gradient-feedback">every event</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Turn attendee impressions into insight you can act on.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- 1: Star Ratings -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                            Ratings
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Star ratings</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Guests rate your event on a 5-star scale. Quick to submit and easy to compare across events.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-2 flex items-center gap-1">
                                    @for ($s = 0; $s < 5; $s++)
                                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">4.8 average from 24 reviews</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2: Written Reviews -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                            Reviews
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Written reviews</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Guests share detailed feedback in their own words. Understand what resonated and what to improve.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="text-xs italic text-gray-600 dark:text-gray-300">"Great energy and lineup. Would love an earlier start time next week."</div>
                                <div class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">- Sarah M.</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3: Post-Event Collection -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            Timing
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Post-event collection</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">The feedback form appears after events end so impressions are fresh and reviews are genuine.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-300">Jazz Night</span>
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Ended</span>
                                </div>
                                <div class="mt-1 text-xs text-amber-500">Leave your review</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4: Admin Dashboard -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            Management
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Admin dashboard</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">View all reviews in one place. See ratings, read comments, and track feedback trends across events.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-900 dark:text-white">Recent feedback</span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">12 reviews</span>
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <div class="flex gap-0.5">
                                            @for ($s = 0; $s < 5; $s++)
                                                <svg class="h-3 w-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                        </div>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Jazz Night</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex gap-0.5">
                                            @for ($s = 0; $s < 4; $s++)
                                                <svg class="h-3 w-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            @endfor
                                            <svg class="h-3 w-3 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        </div>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">Open Mic</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5: One Review Per Guest -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Integrity
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One review per guest</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Authenticated reviews prevent duplicates. Each signed-in guest can submit one review per event.</p>
                        <div class="mt-auto" aria-hidden="true">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-2 flex items-center gap-2">
                                    <div class="h-6 w-6 shrink-0 rounded-full bg-amber-300 dark:bg-amber-500/40"></div>
                                    <span class="text-xs text-gray-600 dark:text-gray-300">Sarah reviewed</span>
                                    <span class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Submitted</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 shrink-0 rounded-full bg-orange-300 dark:bg-orange-500/40"></div>
                                    <span class="text-xs text-gray-600 dark:text-gray-300">Mike reviewed</span>
                                    <span class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Submitted</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6: Pro Feature -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg class="h-4 w-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Pro
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Pro feature</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Event feedback is available on Pro and Enterprise plans. Collect unlimited reviews across all your events.</p>
                        <div class="mt-auto flex flex-wrap gap-2" aria-hidden="true">
                            @foreach (['Pro plan', 'Enterprise plan', 'Unlimited reviews'] as $tag)
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
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-feedback">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about event feedback.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['How does event feedback work?', 'After an event ends, guests can leave a star rating and a written review. Organizers see all feedback in the admin portal and can use it to improve future events.'],
                        ['Who can leave feedback?', 'Any authenticated guest who attended or followed the event can leave feedback. Each guest can submit one review per event.'],
                        ['Can organizers moderate reviews?', 'Yes. Organizers can view all reviews in the admin portal and manage them. Reviews help organizers understand what worked and what to improve.'],
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
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-amber-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(245, 158, 11, 0.3), rgba(245, 158, 11, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-stars absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-5 px-8 opacity-50" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 10; $i++)
                            @php $sz = [16, 24, 14, 20, 28][$i % 5]; $dur = 2.4 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.28; @endphp
                            <span class="es-star" style="--st-dur: {{ $dur }}s; --st-delay: {{ $delay }}s;">
                                <svg width="{{ $sz }}" height="{{ $sz }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Improve with <span class="text-gradient-feedback">every event</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Start collecting feedback from your guests today. No credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
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
        "name": "Event Schedule Event Feedback",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Feedback Collection Software",
        "operatingSystem": "Web",
        "description": "Collect feedback and reviews from event attendees. Guests leave star ratings and written reviews after events. Pro feature to understand your audience. No credit card required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free trial, Pro plan feature"
        },
        "featureList": [
            "5-star rating system",
            "Written guest reviews",
            "Post-event feedback collection",
            "Admin review dashboard",
            "One review per authenticated user",
            "Unlimited reviews across events"
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
