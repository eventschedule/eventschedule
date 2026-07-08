<x-marketing-layout>
    <x-slot name="title">Fan Videos & Comments for Events - Event Schedule</x-slot>
    <x-slot name="description">Let fans add YouTube videos, photos, and comments to your event pages for free, with organizer approval before anything goes live.</x-slot>
    <x-slot name="breadcrumbTitle">Fan Videos, Photos & Comments</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Are fan videos moderated before appearing?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. All fan-submitted videos, photos, and comments require your approval before they appear on your schedule. You have full control over what gets published."
                }
            },
            {
                "@type": "Question",
                "name": "What video platforms are supported?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Fans can share videos from YouTube, Vimeo, and other major platforms by pasting a link. The video is embedded directly on your event page."
                }
            },
            {
                "@type": "Question",
                "name": "Where do fan videos appear?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Approved videos, photos, and comments appear on the individual event page where they were submitted. They help build social proof and community engagement around your events."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Fan Videos, Photos & Comments",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Community Engagement Software",
        "operatingSystem": "Web",
        "description": "Let fans share YouTube videos, photos, and comments on your event pages for free. Organizer approval workflow, agenda integration, and recurring event support. No credit card required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "YouTube video submissions",
            "Photo uploads",
            "Text comments",
            "Organizer approval workflow",
            "Agenda segment integration",
            "Recurring event support",
            "Community building"
        ],
        "url": "{{ url()->current() }}",
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
        /* For fan-videos "The Reel" styles. The shared es-* motion system lives in
           marketing.css; this holds the orange glow gradient, the drifting fan-clip
           card, and the play-button motif (fan videos playing across the page). */
        .text-gradient-fan {
            background: linear-gradient(135deg, #ea580c, #f97316, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(234, 88, 12, 0.3);
        }
        .dark .text-gradient-fan {
            background: linear-gradient(135deg, #fb923c, #fdba74, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(251, 146, 60, 0.3);
        }
        @keyframes es-fan-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-fan-float { animation: es-fan-float 6s ease-in-out infinite; }

        /* Play-button motif: play triangles blink in a wave, like fan video clips
           playing across every event page. */
        .es-plays { display: flex; align-items: center; }
        .es-play {
            flex: 0 0 auto; width: 0; height: 0;
            border-top: 8px solid transparent; border-bottom: 8px solid transparent;
            border-left: 13px solid rgba(249, 115, 22, 0.85);
            animation: es-play-blink var(--pl-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--pl-delay, 0s);
        }
        @keyframes es-play-blink {
            0%, 100% { opacity: 0.22; }
            50% { opacity: 1; filter: drop-shadow(0 0 8px rgba(249, 115, 22, 0.55)); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-fan-float, .es-play, .animate-pulse-slow { animation: none !important; }
            .es-play { opacity: 0.55; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: fan videos                                          -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(234, 88, 12, 0.3), rgba(234, 88, 12, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(245, 158, 11, 0.28), rgba(245, 158, 11, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(220, 38, 38, 0.12), rgba(220, 38, 38, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Play-button motif along the bottom edge -->
            <div class="es-plays absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-7 px-8 opacity-45 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 18; $i++)
                    @php $dur = 2.4 + ($i % 5) * 0.32; $delay = ($i % 9) * 0.26; @endphp
                    <span class="es-play" style="--pl-dur: {{ $dur }}s; --pl-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Fan Engagement</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Fan videos, photos &amp;</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-fan">comments</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Let fans and attendees sign in and share YouTube videos, photos, and comments on your events. All submissions need your approval. Turn every show into a shared memory.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-orange-600 to-red-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-orange-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_events') }}#fan-content" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Fan Content guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento features                                           -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Community-powered <span class="text-gradient-fan">event pages</span></h2>
            </div>

            <!-- Fan photo gallery (full width lead card) -->
            <div class="es-bento group relative mb-4" data-tilt="3" data-reveal="panel">
                <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1">
                            <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Photo Uploads
                            </div>
                            <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-4xl">Fan photo gallery</h3>
                            <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Fans upload photos directly to your event pages. Photos appear in a scrollable gallery with a lightbox viewer. Supports JPG, PNG, GIF, and WebP up to 5MB.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Direct upload</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Photo gallery</span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Lightbox viewer</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0" aria-hidden="true">
                            <div class="w-56 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Concert Photos</div>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <div class="aspect-square rounded-lg bg-orange-200 dark:bg-orange-800/40"></div>
                                    <div class="aspect-square rounded-lg bg-amber-200 dark:bg-amber-800/40"></div>
                                    <div class="aspect-square rounded-lg bg-red-200 dark:bg-red-800/40"></div>
                                    <div class="aspect-square rounded-lg bg-orange-300 dark:bg-orange-700/40"></div>
                                </div>
                                <div class="mt-2 text-[10px] text-gray-600 dark:text-gray-300">4 photos by fans</div>
                            </div>
                        </div>
                    </div>
                    <div class="es-glare" aria-hidden="true"></div>
                    <div class="es-ring-glow" aria-hidden="true"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Fan video clips (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    YouTube Videos
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Fan video clips</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Fans share their favorite moments by adding YouTube video links to your event pages. Videos appear as embedded players, creating a rich media gallery of every show.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">YouTube embeds</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Fan submissions</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Duplicate prevention</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-56" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">Jazz Night</div>
                                    <div class="mb-3 text-[10px] text-gray-400 dark:text-gray-500">Fri, Mar 15 at 8 PM</div>
                                    <div class="mb-2 flex aspect-video items-center justify-center rounded-lg bg-gray-800">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-600">
                                            <svg aria-hidden="true" class="ml-0.5 h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-600 dark:text-gray-300">Uploaded by Sarah M.</div>
                                    <div class="mb-2 mt-2 flex aspect-video items-center justify-center rounded-lg bg-gray-800 opacity-60">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-600">
                                            <svg aria-hidden="true" class="ml-0.5 h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-600 dark:text-gray-300">Uploaded by Mike T.</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Attendee feedback (comments) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            Comments
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Attendee feedback</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Attendees leave text comments (up to 1,000 characters) on events to share their experience and connect with other fans.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="es-ai-field flex items-start gap-2" style="--i: 0;">
                                <div class="h-7 w-7 flex-shrink-0 rounded-full bg-orange-300 dark:bg-orange-500/40"></div>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:border-white/10 dark:bg-white/10 dark:text-gray-300">Amazing show tonight! The energy was incredible.</div>
                            </div>
                            <div class="es-ai-field flex items-start gap-2" style="--i: 1;">
                                <div class="h-7 w-7 flex-shrink-0 rounded-full bg-amber-300 dark:bg-amber-500/40"></div>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:border-white/10 dark:bg-white/10 dark:text-gray-300">Best night ever! Can't wait for the next one.</div>
                            </div>
                            <div class="es-ai-field flex items-start gap-2" style="--i: 2;">
                                <div class="h-7 w-7 flex-shrink-0 rounded-full bg-red-300 dark:bg-red-500/40"></div>
                                <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:border-white/10 dark:bg-white/10 dark:text-gray-300">Loved every minute of it!</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Approval workflow -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Moderation
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Approval workflow</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">All submissions default to pending. You approve or reject every video, photo, and comment from the event edit page before it goes live.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 0;">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-300">New video from Sarah</span>
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Pending</span>
                                </div>
                                <div class="mt-2 flex gap-2">
                                    <div class="flex-1 rounded-lg bg-emerald-500 py-1 text-center text-[10px] text-white">Approve</div>
                                    <div class="flex-1 rounded-lg bg-gray-200 py-1 text-center text-[10px] text-gray-600 dark:bg-white/10 dark:text-gray-300">Reject</div>
                                </div>
                            </div>
                            <div class="es-ai-field rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-400/30 dark:bg-emerald-500/10" style="--i: 1;">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600 dark:text-gray-300">Comment from Mike</span>
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Approved</span>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Agenda integration (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                    Per-Segment
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Agenda integration</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">Attach videos, photos, and comments to individual agenda segments. Fans can share feedback on specific parts of your event, not just the event as a whole.</p>
                            </div>
                            <div class="space-y-3 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                @php
                                    $agenda = [
                                        ['Opening Act', '8:00 PM - 8:30 PM', '2 videos', '5 comments'],
                                        ['Main Performance', '8:30 PM - 10:00 PM', '7 videos', '12 comments'],
                                        ['Encore', '10:00 PM - 10:30 PM', '3 videos', '8 comments'],
                                    ];
                                @endphp
                                @foreach ($agenda as $ai => $seg)
                                    <div class="es-ai-field rounded-xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/10" style="--i: {{ $ai }};">
                                        <div class="mb-1 text-xs font-medium text-gray-900 dark:text-white">{{ $seg[0] }}</div>
                                        <div class="mb-2 text-[10px] text-gray-500 dark:text-gray-400">{{ $seg[1] }}</div>
                                        <div class="flex gap-2">
                                            <span class="inline-flex items-center gap-1 text-[10px] text-red-500 dark:text-red-400">
                                                <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                {{ $seg[2] }}
                                            </span>
                                            <span class="inline-flex items-center gap-1 text-[10px] text-orange-500 dark:text-orange-400">
                                                <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                                {{ $seg[3] }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Per-occurrence content (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Recurring
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Per-occurrence content</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">For recurring events, fan videos, photos, and comments are tied to specific occurrence dates. Each week's show gets its own collection of fan content, keeping memories organized.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Date-specific</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Organized history</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Browse by date</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-56" aria-hidden="true">
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-3 flex gap-1">
                                        <div class="flex-1 rounded-lg bg-gray-200 py-1 text-center text-[10px] text-gray-500 dark:bg-white/10 dark:text-gray-400">Mar 1</div>
                                        <div class="flex-1 rounded-lg bg-orange-500 py-1 text-center text-[10px] font-medium text-white">Mar 8</div>
                                        <div class="flex-1 rounded-lg bg-gray-200 py-1 text-center text-[10px] text-gray-500 dark:bg-white/10 dark:text-gray-400">Mar 15</div>
                                    </div>
                                    <div class="mb-2 text-[10px] text-gray-500 dark:text-gray-400">Mar 8 content</div>
                                    <div class="space-y-2">
                                        <div class="rounded-lg border border-gray-200 bg-white p-2 dark:border-white/10 dark:bg-white/10">
                                            <div class="flex items-center gap-1.5">
                                                <div class="flex h-4 w-4 items-center justify-center rounded bg-red-600">
                                                    <svg aria-hidden="true" class="h-2.5 w-2.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                                </div>
                                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Front row view</span>
                                            </div>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 bg-white p-2 dark:border-white/10 dark:bg-white/10">
                                            <div class="text-[10px] text-gray-600 dark:text-gray-300">"Best set this week!"</div>
                                        </div>
                                        <div class="rounded-lg border border-gray-200 bg-white p-2 dark:border-white/10 dark:bg-white/10">
                                            <div class="flex items-center gap-1.5">
                                                <div class="flex h-4 w-4 items-center justify-center rounded bg-red-600">
                                                    <svg aria-hidden="true" class="h-2.5 w-2.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                                </div>
                                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Drum solo clip</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Shared memories (community) -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            Community
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Shared memories</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Turn shows into shared experiences. Every event page becomes a living archive of fan content that grows over time.</p>
                        <div class="mt-auto rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 text-[10px] text-gray-500 dark:text-gray-400">Fan engagement over time</div>
                            <div class="flex h-16 items-end justify-between gap-1">
                                @foreach ([30, 50, 70, 90, 100, 85, 95] as $bi => $bh)
                                    <div class="flex-1 rounded-t bg-orange-500" style="height: {{ $bh }}%; opacity: {{ 0.3 + $bi * 0.08 }};"></div>
                                @endforeach
                            </div>
                            <div class="mt-2 flex justify-between">
                                <span class="text-[9px] text-gray-400 dark:text-gray-500">Week 1</span>
                                <span class="text-[9px] text-gray-400 dark:text-gray-500">Week 7</span>
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
    <!-- 3. Keep exploring (dark band)                               -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(234, 88, 12, 0.24), rgba(234, 88, 12, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-plays absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-7 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    @for ($i = 0; $i < 16; $i++)
                        @php $dur = 2.4 + ($i % 5) * 0.32; $delay = ($i % 9) * 0.26; @endphp
                        <span class="es-play" style="--pl-dur: {{ $dur }}s; --pl-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Keep <span class="text-gradient-fan">exploring</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">See what else powers your event pages.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                    <!-- Open Source & API -->
                    <a href="{{ marketing_url('/open-source') }}" data-reveal class="group flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-8 transition-all duration-300 hover:-translate-y-1 hover:border-orange-500/30 hover:bg-white/[0.06]">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-white transition-colors group-hover:text-orange-300">Open Source &amp; API</h3>
                        <p class="mb-4 text-lg text-gray-300">100% open source. Selfhost on your own server or integrate with our REST API.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-orange-400 transition-all group-hover:gap-3">
                            Learn more
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>

                    <!-- View all features -->
                    <a href="{{ marketing_url('/features') }}" data-reveal class="group flex flex-col rounded-2xl border border-orange-500/20 bg-gradient-to-br from-orange-500/10 to-red-500/10 p-8 transition-all duration-300 hover:-translate-y-1 hover:border-orange-500/40">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-orange-500/20 bg-orange-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-white transition-colors group-hover:text-orange-300">View all features</h3>
                        <p class="mb-4 text-lg text-gray-300">Explore ticketing, calendar sync, embed widgets, and everything else Event Schedule offers.</p>
                        <span class="mt-auto inline-flex items-center gap-2 font-medium text-orange-400 transition-all group-hover:gap-3">
                            See features
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>

                    <!-- Popular with -->
                    <div data-reveal class="flex flex-col rounded-2xl border border-white/10 bg-white/[0.04] p-8">
                        <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-orange-500/20 bg-orange-500/10">
                            <svg aria-hidden="true" class="h-6 w-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="mb-4 text-xl font-bold text-white">Popular with</h3>
                        <div class="space-y-3">
                            <a href="{{ marketing_url('/for-musicians') }}" class="group/link flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition-all hover:border-orange-500/30 hover:bg-white/10">
                                <span class="font-medium text-white">Musicians</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-orange-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="{{ marketing_url('/for-comedians') }}" class="group/link flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition-all hover:border-orange-500/30 hover:bg-white/10">
                                <span class="font-medium text-white">Comedians</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-orange-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="{{ marketing_url('/for-dance-groups') }}" class="group/link flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-3 transition-all hover:border-orange-500/30 hover:bg-white/10">
                                <span class="font-medium text-white">Dance Groups</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-orange-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-fan">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about fan videos, photos, and comments.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['Are fan videos moderated before appearing?', 'Yes. All fan-submitted videos, photos, and comments require your approval before they appear on your schedule. You have full control over what gets published.'],
                    ['What video platforms are supported?', 'Fans can share videos from YouTube, Vimeo, and other major platforms by pasting a link. The video is embedded directly on your event page.'],
                    ['Where do fan videos appear?', 'Approved videos, photos, and comments appear on the individual event page where they were submitted. They help build social proof and community engagement around your events.'],
                ] as [$q, $a])
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
    <!-- 5. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Newsletters"
                        description="Send branded newsletters to followers and ticket buyers"
                        :url="marketing_url('/features/newsletters')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Online Events"
                        description="Host virtual events with Zoom, Google Meet, and more"
                        :url="marketing_url('/features/online-events')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Embed your event schedule on any website"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-orange-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(234, 88, 12, 0.3), rgba(234, 88, 12, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-plays absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-7 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 13; $i++)
                            @php $dur = 2.4 + ($i % 5) * 0.32; $delay = ($i % 9) * 0.26; @endphp
                            <span class="es-play" style="--pl-dur: {{ $dur }}s; --pl-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Build community around <span class="text-gradient-fan">your events</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Let fans share their favorite moments. No credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-orange-600 to-red-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-orange-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
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
