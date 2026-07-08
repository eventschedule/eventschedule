<x-marketing-layout>
    <x-slot name="title">Event Polls & Voting - Event Schedule</x-slot>
    <x-slot name="description">Add interactive polls to your events. Guests vote on multiple choice questions with real-time results. Pro feature with one vote per user. No credit card required.</x-slot>
    <x-slot name="breadcrumbTitle">Event Polls</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How do event polls work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Admins create polls with a question and multiple choice options on any event. Authenticated guests can vote once per poll. Results are shown immediately after voting with animated progress bars."
                }
            },
            {
                "@type": "Question",
                "name": "Who can create polls?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event organizers and admins can create up to 5 polls per event from the event edit page. Polls are a Pro plan feature."
                }
            },
            {
                "@type": "Question",
                "name": "Can guests see results before voting?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Results are hidden until a guest votes. Once they vote, they see the results with vote counts and percentages. Admins can always see results."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Event Polls",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Audience Engagement Software",
        "operatingSystem": "Web",
        "description": "Add interactive polls to your events. Guests vote on multiple choice questions with real-time results. Pro feature with one vote per user. No credit card required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free trial, Pro plan feature"
        },
        "featureList": [
            "Interactive multiple choice polls",
            "Real-time results with progress bars",
            "One vote per authenticated user",
            "Open and close polls on demand",
            "Mobile-friendly voting interface",
            "Up to 5 polls per event"
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
        /* For polls "The Vote" styles. The shared es-* motion system lives in
           marketing.css; this holds the blue glow gradient, the drifting poll card,
           and the result-bar motif (poll bars filling as votes come in). */
        .text-gradient-polls {
            background: linear-gradient(135deg, #2563eb, #3b82f6, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(37, 99, 235, 0.3);
        }
        .dark .text-gradient-polls {
            background: linear-gradient(135deg, #60a5fa, #3b82f6, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(96, 165, 250, 0.3);
        }
        @keyframes es-polls-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-polls-float { animation: es-polls-float 6s ease-in-out infinite; }

        /* Result-bar motif: poll bars fill left-to-right in a wave, like votes
           tallying up in real time. */
        .es-fills { display: flex; align-items: center; }
        .es-fill {
            position: relative; flex: 0 0 auto; height: 8px; border-radius: 9999px;
            overflow: hidden; background: rgba(59, 130, 246, 0.15);
        }
        .es-fill::after {
            content: ""; position: absolute; inset: 0; border-radius: 9999px;
            background: linear-gradient(to right, rgba(59, 130, 246, 0.9), rgba(14, 165, 233, 0.9));
            transform-origin: left;
            animation: es-fill-grow var(--fl-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--fl-delay, 0s);
        }
        @keyframes es-fill-grow {
            0%, 100% { transform: scaleX(0.15); }
            50% { transform: scaleX(1); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-polls-float, .animate-pulse-slow { animation: none !important; }
            .es-fill::after { animation: none !important; transform: scaleX(0.6); }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: event polls                                         -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.28), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.14), rgba(59, 130, 246, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Result-bar motif along the bottom edge -->
            <div class="es-fills absolute bottom-10 left-0 right-0 mx-auto hidden h-16 max-w-3xl flex-col items-stretch justify-center gap-2.5 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 5; $i++)
                    @php $w = [70, 45, 88, 60, 35][$i % 5]; $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 5) * 0.35; @endphp
                    <span class="es-fill" style="width: {{ $w }}%; --fl-dur: {{ $dur }}s; --fl-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Audience Engagement</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Interactive event</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-polls">polls</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Add multiple choice polls to any event. Guests vote with one tap and see real-time results with animated progress bars. A simple way to engage your audience.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_events') }}#polls" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Polls guide
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
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Simple polls, <span class="text-gradient-polls">real engagement</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- 1: Interactive multiple choice -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Multiple Choice
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Interactive multiple choice</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Create polls with 2 to 10 options. Each poll has a question and a set of choices for guests to pick from.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 text-xs font-medium text-gray-900 dark:text-white">What genre next week?</div>
                            <div class="space-y-1.5">
                                @foreach (['Jazz', 'Rock', 'Blues'] as $oi => $opt)
                                    <div class="es-ai-field rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-[10px] text-gray-600 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-gray-300" style="--i: {{ $oi }};">{{ $opt }}</div>
                                @endforeach
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2: Real-time results -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            Results
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Real-time results</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Animated progress bars with vote counts and percentages appear instantly after voting.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 text-xs font-medium text-gray-900 dark:text-white">Results</div>
                            <div class="space-y-2">
                                @foreach ([['Jazz', 45, 'bg-blue-500'], ['Rock', 35, 'bg-sky-500'], ['Blues', 20, 'bg-sky-400']] as [$lbl, $pct, $bar])
                                    <div>
                                        <div class="mb-1 flex justify-between text-[10px] text-gray-600 dark:text-gray-300"><span>{{ $lbl }}</span><span>{{ $pct }}%</span></div>
                                        <div class="h-2 rounded-full bg-gray-200 dark:bg-white/10"><div class="h-2 rounded-full {{ $bar }}" style="width: {{ $pct }}%"></div></div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2 text-[10px] text-gray-400 dark:text-gray-500">20 votes</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3: One vote per user -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Integrity
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">One vote per user</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Authenticated voting prevents duplicates. Each signed-in guest can vote once per poll for fair results.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="es-ai-field mb-2 flex items-center gap-2" style="--i: 0;">
                                <div class="h-6 w-6 flex-shrink-0 rounded-full bg-blue-300 dark:bg-blue-500/40"></div>
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Sarah voted</span>
                                <span class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Recorded</span>
                            </div>
                            <div class="es-ai-field flex items-center gap-2" style="--i: 1;">
                                <div class="h-6 w-6 flex-shrink-0 rounded-full bg-sky-300 dark:bg-sky-500/40"></div>
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Mike voted</span>
                                <span class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Recorded</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4: Open and close polls -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Control
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Open and close polls</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Toggle polls open or closed at any time. Close voting when you have enough responses and keep results visible.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Best opener?</span>
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Open</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Favorite song?</span>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-500 dark:bg-white/10 dark:text-gray-400">Closed</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5: Mobile-friendly -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Mobile
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Mobile-friendly</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Full-width buttons with generous touch targets make voting easy on any device. Guests can vote right from their phones.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="w-36 rounded-2xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-[#0f0f14]">
                                <div class="mb-2 text-[10px] text-gray-500 dark:text-gray-400">Vote now</div>
                                <div class="space-y-1.5">
                                    <div class="rounded-lg bg-blue-500 py-2 text-center text-[10px] font-medium text-white">Option A</div>
                                    <div class="rounded-lg border border-blue-200 bg-blue-50 py-2 text-center text-[10px] text-gray-600 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-gray-300">Option B</div>
                                    <div class="rounded-lg border border-blue-200 bg-blue-50 py-2 text-center text-[10px] text-gray-600 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-gray-300">Option C</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6: Pro feature -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                            Pro
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Pro feature</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Event polls are available on Pro and Enterprise plans. Up to 5 polls per event with unlimited votes.</p>
                        <div class="mt-auto flex flex-wrap gap-3">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Pro plan</span>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Enterprise plan</span>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">5 polls per event</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. See it in action (dark band)                             -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(37, 99, 235, 0.24), rgba(37, 99, 235, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(14, 165, 233, 0.2), rgba(14, 165, 233, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-fills absolute bottom-8 left-0 right-0 mx-auto flex h-14 w-full max-w-2xl flex-col items-stretch justify-center gap-2.5 px-8 opacity-35" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    @for ($i = 0; $i < 4; $i++)
                        @php $w = [70, 45, 88, 60][$i % 4]; $dur = 2.4 + ($i % 4) * 0.3; $delay = ($i % 4) * 0.35; @endphp
                        <span class="es-fill" style="width: {{ $w }}%; --fl-dur: {{ $dur }}s; --fl-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-2xl">
                <div class="mb-12 text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>See it <span class="text-gradient-polls">in action</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Guests vote with one tap, then watch the results roll in.</p>
                </div>

                <div data-reveal class="mx-auto max-w-md rounded-3xl border border-white/10 bg-white/[0.04] p-6 shadow-2xl shadow-blue-500/20 sm:p-8">
                    <div class="mb-1 text-xs font-medium uppercase tracking-wider text-blue-400">Live poll</div>
                    <div class="mb-5 text-xl font-bold text-white">What genre next week?</div>
                    <div class="space-y-4" aria-hidden="true">
                        @foreach ([['Jazz', 45], ['Rock', 35], ['Blues', 20]] as [$lbl, $pct])
                            <div>
                                <div class="mb-1.5 flex justify-between text-sm text-gray-200"><span>{{ $lbl }}</span><span class="font-semibold">{{ $pct }}%</span></div>
                                <div class="h-3 overflow-hidden rounded-full bg-white/10"><div class="h-3 rounded-full bg-gradient-to-r from-blue-500 to-sky-400" style="width: {{ $pct }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-5 flex items-center justify-between text-xs text-gray-400">
                        <span>20 votes</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Open</span>
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
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-polls">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about event polls.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['How do event polls work?', 'Admins create polls with a question and multiple choice options on any event. Authenticated guests can vote once per poll. Results are shown immediately after voting with animated progress bars.'],
                    ['Who can create polls?', 'Event organizers and admins can create up to 5 polls per event from the event edit page. Polls are a Pro plan feature.'],
                    ['Can guests see results before voting?', 'No. Results are hidden until a guest votes. Once they vote, they see the results with vote counts and percentages. Admins can always see results.'],
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
    <!-- 5. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-fills absolute bottom-8 left-0 right-0 mx-auto flex h-14 w-full max-w-lg flex-col items-stretch justify-center gap-2.5 px-8 opacity-35" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 4; $i++)
                            @php $w = [60, 88, 42, 72][$i % 4]; $dur = 2.4 + ($i % 4) * 0.3; $delay = ($i % 4) * 0.35; @endphp
                            <span class="es-fill" style="width: {{ $w }}%; --fl-dur: {{ $dur }}s; --fl-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Engage your audience <span class="text-gradient-polls">with polls</span>
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
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
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
