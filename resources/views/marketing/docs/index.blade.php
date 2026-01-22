<x-marketing-layout>
    <x-slot name="title">Developer Documentation - Event Schedule</x-slot>
    <x-slot name="description">Technical documentation for setting up and configuring Event Schedule. Learn how to deploy as SaaS, integrate Stripe payments, and sync with Google Calendar.</x-slot>
    <x-slot name="keywords">event schedule documentation, saas setup, stripe integration, google calendar sync, api documentation</x-slot>

    <style>
        .text-gradient {
            background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .text-gradient-api {
            background: linear-gradient(135deg, #10b981 0%, #06b6d4 50%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        @keyframes float-delayed {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        @keyframes code-scroll {
            0% { transform: translateY(0); }
            100% { transform: translateY(-50%); }
        }
        @keyframes cursor-blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float-delayed 5s ease-in-out infinite; animation-delay: 1s; }
        .animate-cursor { animation: cursor-blink 1s infinite; }
        .doc-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .doc-card:hover {
            transform: translateY(-8px);
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .api-banner {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .api-banner:hover {
            transform: scale(1.01);
        }
        .api-banner:hover .api-arrow {
            transform: translateX(8px);
        }
        .api-arrow {
            transition: transform 0.3s ease;
        }
        .code-block {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, monospace;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                <span class="text-sm text-gray-300">Developer Documentation</span>
            </div>

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                Technical <span class="text-gradient">Documentation</span>
            </h1>

            <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                Everything you need to deploy, configure, and integrate Event Schedule for your use case.
            </p>
        </div>
    </section>

    <!-- Documentation Cards -->
    <section class="bg-[#0a0a0f] py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($docs as $doc)
                    <a href="{{ route($doc['route']) }}" class="doc-card block">
                        <div class="relative overflow-hidden rounded-2xl border border-white/10 p-6 h-full
                            @if($doc['color'] === 'cyan') bg-gradient-to-br from-cyan-900/50 to-teal-900/50
                            @elseif($doc['color'] === 'blue') bg-gradient-to-br from-blue-900/50 to-cyan-900/50
                            @elseif($doc['color'] === 'indigo') bg-gradient-to-br from-indigo-900/50 to-blue-900/50
                            @elseif($doc['color'] === 'violet') bg-gradient-to-br from-violet-900/50 to-indigo-900/50
                            @endif
                        ">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl mb-4
                                @if($doc['color'] === 'cyan') bg-cyan-500/20
                                @elseif($doc['color'] === 'blue') bg-blue-500/20
                                @elseif($doc['color'] === 'indigo') bg-indigo-500/20
                                @elseif($doc['color'] === 'violet') bg-violet-500/20
                                @endif
                            ">
                                @if($doc['icon'] === 'download')
                                    <svg class="w-6 h-6 @if($doc['color'] === 'cyan') text-cyan-400 @elseif($doc['color'] === 'blue') text-blue-400 @elseif($doc['color'] === 'indigo') text-indigo-400 @elseif($doc['color'] === 'violet') text-violet-400 @endif" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                @elseif($doc['icon'] === 'server')
                                    <svg class="w-6 h-6 @if($doc['color'] === 'cyan') text-cyan-400 @elseif($doc['color'] === 'blue') text-blue-400 @elseif($doc['color'] === 'indigo') text-indigo-400 @elseif($doc['color'] === 'violet') text-violet-400 @endif" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                    </svg>
                                @elseif($doc['icon'] === 'credit-card')
                                    <svg class="w-6 h-6 @if($doc['color'] === 'cyan') text-cyan-400 @elseif($doc['color'] === 'blue') text-blue-400 @elseif($doc['color'] === 'indigo') text-indigo-400 @elseif($doc['color'] === 'violet') text-violet-400 @endif" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                @elseif($doc['icon'] === 'calendar')
                                    <svg class="w-6 h-6 @if($doc['color'] === 'cyan') text-cyan-400 @elseif($doc['color'] === 'blue') text-blue-400 @elseif($doc['color'] === 'indigo') text-indigo-400 @elseif($doc['color'] === 'violet') text-violet-400 @endif" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </div>

                            <h3 class="text-xl font-bold text-white mb-2">{{ $doc['title'] }}</h3>
                            <p class="text-gray-400 text-sm mb-4">{{ $doc['description'] }}</p>

                            <div class="inline-flex items-center text-sm font-medium
                                @if($doc['color'] === 'cyan') text-cyan-400
                                @elseif($doc['color'] === 'blue') text-blue-400
                                @elseif($doc['color'] === 'indigo') text-indigo-400
                                @elseif($doc['color'] === 'violet') text-violet-400
                                @endif
                            ">
                                Read Guide
                                <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- API Reference Banner -->
    <section class="bg-[#0a0a0f] py-12 pb-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('marketing.docs.api') }}" class="api-banner group block relative overflow-hidden rounded-3xl">
                <!-- Gradient background -->
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-900 via-teal-900 to-cyan-900"></div>

                <!-- Animated glow effects -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/30 rounded-full blur-[100px] animate-pulse-slow"></div>
                    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-cyan-500/30 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-teal-500/20 rounded-full blur-[80px] animate-pulse-slow" style="animation-delay: 0.75s;"></div>
                </div>

                <!-- Grid pattern overlay -->
                <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:24px_24px]"></div>

                <!-- Border glow -->
                <div class="absolute inset-0 rounded-3xl border border-emerald-400/20 group-hover:border-emerald-400/40 transition-colors duration-500"></div>

                <!-- Content -->
                <div class="relative z-10 p-8 md:p-12 flex flex-col lg:flex-row items-center gap-8">
                    <!-- Left side: Text content -->
                    <div class="flex-1 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/20 border border-emerald-400/30 mb-6">
                            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                            <span class="text-sm font-medium text-emerald-300">REST API</span>
                        </div>

                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                            API <span class="text-gradient-api">Reference</span>
                        </h2>

                        <p class="text-lg text-gray-300 mb-6 max-w-xl">
                            Build powerful integrations with our REST API. Create events, manage schedules, and automate your workflow programmatically.
                        </p>

                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 mb-6">
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                JSON responses
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                API key auth
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-400">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Full CRUD
                            </div>
                        </div>

                        <div class="inline-flex items-center gap-3 text-emerald-400 font-semibold text-lg group-hover:text-emerald-300 transition-colors">
                            Explore the API
                            <svg class="w-6 h-6 api-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </div>
                    </div>

                    <!-- Right side: Code mockups -->
                    <div class="flex-shrink-0 hidden md:block">
                        <div class="relative">
                            <!-- Main terminal window -->
                            <div class="animate-float bg-black/60 backdrop-blur-sm rounded-2xl border border-white/10 shadow-2xl shadow-emerald-500/10 overflow-hidden w-80">
                                <!-- Terminal header -->
                                <div class="flex items-center gap-2 px-4 py-3 bg-white/5 border-b border-white/10">
                                    <div class="flex gap-1.5">
                                        <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-2 code-block">api-request.sh</span>
                                </div>
                                <!-- Terminal content -->
                                <div class="p-4 code-block text-sm">
                                    <div class="text-gray-500 mb-2"># Create an event</div>
                                    <div class="mb-3">
                                        <span class="text-emerald-400">POST</span>
                                        <span class="text-gray-300"> /api/events/demo</span>
                                    </div>
                                    <div class="text-gray-500 mb-1"># Response</div>
                                    <div class="text-gray-400">{</div>
                                    <div class="text-gray-400 pl-4">"<span class="text-cyan-400">id</span>": <span class="text-amber-400">42</span>,</div>
                                    <div class="text-gray-400 pl-4">"<span class="text-cyan-400">name</span>": "<span class="text-emerald-300">Jazz Night</span>",</div>
                                    <div class="text-gray-400 pl-4">"<span class="text-cyan-400">status</span>": "<span class="text-emerald-300">created</span>"</div>
                                    <div class="text-gray-400">}</div>
                                    <div class="mt-2 flex items-center">
                                        <span class="text-emerald-400">$</span>
                                        <span class="w-2 h-4 bg-emerald-400 ml-1 animate-cursor"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating endpoint badges -->
                            <div class="absolute -top-4 -right-4 animate-float-delayed">
                                <div class="bg-emerald-500/20 backdrop-blur-sm rounded-lg px-3 py-1.5 border border-emerald-400/30 shadow-lg">
                                    <span class="code-block text-xs text-emerald-300">GET /schedules</span>
                                </div>
                            </div>

                            <div class="absolute -bottom-2 -left-4 animate-float" style="animation-delay: 2s;">
                                <div class="bg-cyan-500/20 backdrop-blur-sm rounded-lg px-3 py-1.5 border border-cyan-400/30 shadow-lg">
                                    <span class="code-block text-xs text-cyan-300">POST /sales</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- Open Source Section -->
    <section class="bg-[#0a0a0f] py-20 border-t border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 mb-6">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">
                    100% Open Source
                </h2>
                <p class="text-lg text-gray-400 mb-8 max-w-xl mx-auto">
                    Event Schedule is fully open source. Explore the code, report issues, or contribute on GitHub.
                </p>

                <div class="flex flex-wrap justify-center gap-4">
                    <a href="https://github.com/eventschedule/eventschedule" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        View on GitHub
                    </a>
                    <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        Discussions
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-marketing-layout>
