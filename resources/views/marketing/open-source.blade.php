<x-marketing-layout>
    <x-slot name="title">Open Source Event Calendar - Event Schedule</x-slot>
    <x-slot name="description">100% open source under the Attribution Assurance License. Selfhost on your own server or integrate via REST API. Full control, no vendor lock-in.</x-slot>
    <x-slot name="breadcrumbTitle">Open Source</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareSourceCode",
        "name": "Event Schedule",
        "description": "Event Schedule is 100% open source under the Attribution Assurance License (AAL). Selfhost on your own server or integrate with our REST API.",
        "codeRepository": "https://github.com/eventschedule/eventschedule",
        "programmingLanguage": ["PHP", "JavaScript", "Vue.js"],
        "runtimePlatform": "Laravel",
        "license": "https://opensource.org/licenses/AAL",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "url": "{{ url()->current() }}"
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (developer blue to cyan to sky) */
        .text-gradient-opensource {
            background: linear-gradient(135deg, #2563eb 0%, #06b6d4 50%, #0ea5e9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-opensource {
            background: linear-gradient(135deg, #60a5fa 0%, #22d3ee 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-opensource {
            background: linear-gradient(135deg, #60a5fa 0%, #22d3ee 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .code-block {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
        }

        /* Signature float: a live terminal window */
        .es-terminal-float { animation: es-terminal-bob 5.5s ease-in-out infinite; }
        @keyframes es-terminal-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a git branch line with commit nodes */
        .es-commits { position: relative; }
        .es-commits::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 2px;
            background: rgba(37, 99, 235, 0.22);
            transform: translateY(-50%);
        }
        .es-commit {
            position: relative;
            z-index: 1;
            flex: 0 0 auto;
            width: 12px;
            height: 12px;
            border-radius: 9999px;
            background: rgba(37, 99, 235, 0.85);
            animation: es-commit-pulse var(--cm-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--cm-delay, 0s);
        }
        @keyframes es-commit-pulse {
            0%, 100% { opacity: 0.3; transform: scale(0.78); }
            50% { opacity: 1; transform: scale(1.08); filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.55)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-terminal-float, .es-commit, .animate-pulse-slow, .animate-float { animation: none !important; }
            .es-commit { opacity: 0.6; transform: none; }
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
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(6, 182, 212, 0.26), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.14), rgba(14, 165, 233, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Git commit-graph motif along the bottom edge -->
            <div class="es-commits absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-10 px-8 opacity-60 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 14; $i++)
                    @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-commit" style="--cm-dur: {{ $dur }}s; --cm-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">100% Open Source</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Open source</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-opensource">&amp; API access</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Selfhost on your own server or integrate with our REST API. Full control, no vendor lock-in.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-700 bg-gradient-to-r from-gray-700 to-gray-800 px-8 py-4 text-lg font-semibold text-white shadow-lg transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl">
                    <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    View on GitHub
                </a>
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Start for free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <div class="es-fade-up es-d-3 pointer-events-auto mt-8">
                @include('marketing.partials.github-star-badge')
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento grid                                               -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- Open Source (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-gray-200 bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 dark:border-white/10 dark:bg-gray-500/20 dark:text-gray-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                    Open Source
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white lg:text-4xl">Fully transparent code</h2>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Every line of code is available on GitHub under the Attribution Assurance License (AAL). Inspect, modify, or contribute. No hidden functionality, no surprises.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Open Source (AAL)</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Laravel + Vue.js</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">MIT-friendly</span>
                                </div>
                            </div>
                            <div class="shrink-0" aria-hidden="true">
                                <div class="h-48 w-64 rounded-2xl border border-gray-300 bg-gradient-to-br from-gray-100 to-gray-200 p-4 shadow-2xl dark:border-white/20 dark:from-white/5 dark:to-white/10">
                                    <div class="mb-3 flex items-center gap-2">
                                        <div class="h-3 w-3 rounded-full bg-red-500/70"></div>
                                        <div class="h-3 w-3 rounded-full bg-yellow-500/70"></div>
                                        <div class="h-3 w-3 rounded-full bg-green-500/70"></div>
                                    </div>
                                    <div class="code-block space-y-1 text-xs">
                                        <div class="text-gray-500 dark:text-gray-400">// Event Schedule</div>
                                        <div><span class="text-blue-500 dark:text-blue-400">git</span> <span class="text-gray-600 dark:text-gray-300">clone</span></div>
                                        <div class="break-all text-[10px] text-cyan-600 dark:text-cyan-400">github.com/eventschedule</div>
                                        <div class="mt-2"><span class="text-blue-500 dark:text-blue-400">composer</span> <span class="text-gray-600 dark:text-gray-300">install</span></div>
                                        <div><span class="text-blue-500 dark:text-blue-400">npm</span> <span class="text-gray-600 dark:text-gray-300">run build</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Self-Host Options -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                            </svg>
                            Selfhost
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Your server, your data</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Install via Softaculous (one-click) or Docker. Keep full control of your data.</p>
                        <div class="mt-auto space-y-3">
                            <a href="https://www.softaculous.com/apps/calendars/Event_Schedule" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 transition-colors hover:bg-gray-200 dark:border-white/10 dark:bg-white/10 dark:hover:bg-white/15">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/30">
                                    <svg aria-hidden="true" class="h-4 w-4 text-blue-500 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Softaculous</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">One-click install</div>
                                </div>
                            </a>
                            <a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 transition-colors hover:bg-gray-200 dark:border-white/10 dark:bg-white/10 dark:hover:bg-white/15">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-500/30">
                                    <svg aria-hidden="true" class="h-4 w-4 text-cyan-500 dark:text-cyan-300" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13.983 11.078h2.119a.186.186 0 00.186-.185V9.006a.186.186 0 00-.186-.186h-2.119a.185.185 0 00-.185.185v1.888c0 .102.083.185.185.185m-2.954-5.43h2.118a.186.186 0 00.186-.186V3.574a.186.186 0 00-.186-.185h-2.118a.185.185 0 00-.185.185v1.888c0 .102.082.185.185.185"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Docker</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Containerized deploy</div>
                                </div>
                            </a>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- REST API -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                            REST API
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Build integrations</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Programmatically manage schedules and events. Create custom workflows and automations.</p>
                        <div class="code-block mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 text-xs dark:border-white/10 dark:bg-[#0f0f14]">
                            <div class="mb-2 text-gray-500 dark:text-gray-400"># Create an event</div>
                            <div class="text-blue-500 dark:text-blue-400">POST <span class="text-gray-600 dark:text-gray-300">/api/events/myschedule</span></div>
                            <div class="mt-2 text-gray-500 dark:text-gray-400">X-API-Key: your_api_key</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- API Endpoints (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-start gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    API Endpoints
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">Full CRUD operations</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Manage your schedules, events, and ticket sales programmatically. API access requires a Pro subscription.</p>
                            </div>
                            <div class="space-y-3" aria-hidden="true">
                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/5 dark:bg-[#0f0f14]">
                                    <span class="inline-flex items-center rounded bg-emerald-500/30 px-2 py-1 font-mono text-xs text-emerald-600 dark:text-emerald-300">GET</span>
                                    <span class="font-mono text-sm text-gray-600 dark:text-gray-300">/api/schedules</span>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/5 dark:bg-[#0f0f14]">
                                    <span class="inline-flex items-center rounded bg-emerald-500/30 px-2 py-1 font-mono text-xs text-emerald-600 dark:text-emerald-300">GET</span>
                                    <span class="font-mono text-sm text-gray-600 dark:text-gray-300">/api/events</span>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/5 dark:bg-[#0f0f14]">
                                    <span class="inline-flex items-center rounded bg-blue-500/30 px-2 py-1 font-mono text-xs text-blue-600 dark:text-blue-300">POST</span>
                                    <span class="font-mono text-sm text-gray-600 dark:text-gray-300">/api/events/{'{subdomain}'}</span>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/5 dark:bg-[#0f0f14]">
                                    <span class="inline-flex items-center rounded bg-blue-500/30 px-2 py-1 font-mono text-xs text-blue-600 dark:text-blue-300">POST</span>
                                    <span class="font-mono text-sm text-gray-600 dark:text-gray-300">/api/sales</span>
                                </div>
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
    <!-- 3. Developer-friendly API                                   -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Developer-friendly <span class="text-gradient-opensource">API</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Built for reliability and ease of use.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                @php
                    $apiFeatures = [
                        ['API Key Authentication', 'Simple header-based authentication. Generate API keys from your account settings.', 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
                        ['Rate Limiting', '60 requests per minute per IP. Built-in brute force protection keeps your data safe.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['JSON Responses', 'Clean, paginated JSON responses with metadata. Easy to parse in any language.', 'M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3zm5 1h6m-6 4h6m-6 4h4'],
                    ];
                @endphp
                @foreach ($apiFeatures as $f)
                    <div data-reveal class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mx-auto mb-6 inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-white/10">
                            <svg aria-hidden="true" class="h-8 w-8 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $f[2] }}" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">{{ $f[0] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $f[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. What you can build                                       -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>What you can <span class="text-gradient-opensource">build</span></h2>
                <p class="mt-4 text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common use cases for the API.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="80">
                @php
                    $useCases = [
                        ['bg-blue-500/20', 'text-blue-500 dark:text-blue-400', 'Automated Imports', 'Pull events from other systems and create them automatically. Sync with your existing tools.', 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                        ['bg-sky-500/20', 'text-sky-500 dark:text-sky-400', 'Website Widgets', 'Fetch events and display them on your own website with custom styling and layouts.', 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'],
                        ['bg-blue-500/20', 'text-blue-500 dark:text-blue-400', 'Mobile Apps', 'Build custom mobile experiences. The API works with any frontend framework or native app.', 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
                        ['bg-emerald-500/20', 'text-emerald-500 dark:text-emerald-400', 'Custom Ticket Sales', 'Create sales programmatically. Build your own checkout flow while leveraging our ticketing infrastructure.', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                    ];
                @endphp
                @foreach ($useCases as $u)
                    <div data-reveal class="rounded-2xl border border-gray-200 bg-gray-100 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-white/5">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl {{ $u[0] }}">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $u[1] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $u[4] }}" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">{{ $u[2] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $u[3] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Next Feature                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/ticketing') }}" data-reveal class="group block">
                <div class="rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-100 to-blue-100 p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-sky-900 dark:to-blue-900 lg:p-10">
                    <div class="flex flex-col items-center gap-8 lg:flex-row">
                        <div class="flex-1 text-center lg:text-left">
                            <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-300 lg:text-3xl">Ticketing</h3>
                            <p class="mb-4 text-lg text-gray-600 dark:text-white/80">Sell tickets directly from your schedule. QR codes for fast check-ins, Stripe payments built-in.</p>
                            <span class="inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                Learn more
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="shrink-0" aria-hidden="true">
                            <div class="w-36 rounded-xl bg-white p-3 shadow-xl">
                                <div class="mb-2 text-center">
                                    <div class="text-[10px] font-medium text-gray-500">TICKET</div>
                                    <div class="text-xs font-semibold text-gray-900">Jazz Night</div>
                                </div>
                                <div class="mb-2 rounded-lg bg-gray-100 p-2">
                                    <div class="grid grid-cols-5 gap-0.5">
                                        @for ($i = 0; $i < 25; $i++)
                                            <div class="aspect-square {{ rand(0, 1) ? 'bg-gray-800' : 'bg-gray-200' }} rounded-[1px]"></div>
                                        @endfor
                                    </div>
                                </div>
                                <div class="flex items-center justify-between border-t border-dashed border-gray-300 pt-1.5 text-[8px] text-gray-500">
                                    <span>General</span>
                                    <span>#4821</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-commits absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-10 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 10; $i++)
                            @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                            <span class="es-commit" style="--cm-dur: {{ $dur }}s; --cm-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Start building <span class="text-gradient-opensource">today</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Explore the codebase on GitHub or sign up to get your API key.
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

                    <div class="mt-6">
                        <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-medium text-gray-300 transition-colors hover:text-white">
                            <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            View on GitHub
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
