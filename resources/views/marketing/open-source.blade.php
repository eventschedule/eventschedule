<x-marketing-layout>
    <x-slot name="title">Open Source Event Calendar | Self-Host & REST API - Event Schedule</x-slot>
    <x-slot name="description">Event Schedule is 100% open source under the AAL license. Self-host on your own server or integrate with our REST API. Full control, no vendor lock-in.</x-slot>
    <x-slot name="keywords">open source event calendar, self hosted events, event API, REST API events, AAL license, selfhost calendar, event schedule API, developer API</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <style>
        /* Custom gray gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #6b7280 0%, #374151 50%, #1f2937 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* Page-specific styles */
        @keyframes typing {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .animate-typing { animation: typing 1s ease-in-out infinite; }
        .code-block {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-gray-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-slate-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear_gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">100% Open Source</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Open source<br>
                <span class="text-gradient">& API access</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Self-host on your own server or integrate with our REST API. Full control, no vendor lock-in.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-gray-700 to-gray-800 rounded-2xl hover:scale-105 transition-all shadow-lg border border-gray-600">
                    <svg class="mr-2 w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    View on GitHub
                </a>
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-gray-900 dark:text-white glass border border-gray-200 dark:border-white/10 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
                    Start for free
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Open Source (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-gray-900/50 to-slate-900/50 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-500/20 text-gray-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                                Open Source
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Fully transparent code</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Every line of code is available on GitHub under the AAL license. Inspect, modify, or contribute. No hidden functionality, no surprises.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-sm">AAL Licensed</span>
                                <span class="px-3 py-1 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-sm">Laravel + Vue.js</span>
                                <span class="px-3 py-1 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-sm">MIT-friendly</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="relative animate-float">
                                <div class="w-64 h-48 bg-gradient-to-br from-gray-100 dark:from-white/5 to-gray-200 dark:to-white/10 rounded-2xl border border-gray-300 dark:border-white/20 p-4 shadow-2xl">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-500/70"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
                                    </div>
                                    <div class="code-block text-xs space-y-1">
                                        <div class="text-gray-500">// Event Schedule</div>
                                        <div><span class="text-purple-400">git</span> <span class="text-gray-600 dark:text-gray-300">clone</span></div>
                                        <div class="text-cyan-400 text-[10px] break-all">github.com/eventschedule</div>
                                        <div class="mt-2"><span class="text-purple-400">composer</span> <span class="text-gray-600 dark:text-gray-300">install</span></div>
                                        <div><span class="text-purple-400">npm</span> <span class="text-gray-600 dark:text-gray-300">run build</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Self-Host Options -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900/50 to-teal-900/50 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                        </svg>
                        Selfhost
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Your server, your data</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Install via Softaculous (one-click) or Docker. Keep full control of your data.</p>

                    <div class="space-y-3">
                        <a href="https://www.softaculous.com/apps/calendars/Event_Schedule" target="_blank" class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10 hover:bg-gray-300 dark:hover:bg-white/15 transition-colors">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white font-medium text-sm">Softaculous</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">One-click install</div>
                            </div>
                        </a>
                        <a href="https://github.com/eventschedule/dockerfiles" target="_blank" class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10 hover:bg-gray-300 dark:hover:bg-white/15 transition-colors">
                            <div class="w-8 h-8 rounded-lg bg-cyan-500/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-cyan-300" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M13.983 11.078h2.119a.186.186 0 00.186-.185V9.006a.186.186 0 00-.186-.186h-2.119a.185.185 0 00-.185.185v1.888c0 .102.083.185.185.185m-2.954-5.43h2.118a.186.186 0 00.186-.186V3.574a.186.186 0 00-.186-.185h-2.118a.185.185 0 00-.185.185v1.888c0 .102.082.185.185.185"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white font-medium text-sm">Docker</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">Containerized deploy</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- REST API -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-900/50 to-indigo-900/50 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        REST API
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Build integrations</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Programmatically manage schedules and events. Create custom workflows and automations.</p>

                    <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl p-4 code-block text-xs">
                        <div class="text-gray-500 mb-2"># Create an event</div>
                        <div class="text-violet-400">POST <span class="text-gray-600 dark:text-gray-300">/api/events/myschedule</span></div>
                        <div class="text-gray-500 mt-2">X-API-Key: your_api_key</div>
                    </div>
                </div>

                <!-- API Endpoints (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900/50 to-blue-900/50 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-start">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                API Endpoints
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Full CRUD operations</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Manage your schedules, events, and ticket sales programmatically. API access requires a Pro subscription.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-[#0f0f14] border border-gray-100 dark:border-white/5">
                                <span class="px-2 py-1 rounded bg-emerald-500/30 text-emerald-300 text-xs font-mono">GET</span>
                                <span class="text-gray-600 dark:text-gray-300 text-sm font-mono">/api/schedules</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-[#0f0f14] border border-gray-100 dark:border-white/5">
                                <span class="px-2 py-1 rounded bg-emerald-500/30 text-emerald-300 text-xs font-mono">GET</span>
                                <span class="text-gray-600 dark:text-gray-300 text-sm font-mono">/api/events</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-[#0f0f14] border border-gray-100 dark:border-white/5">
                                <span class="px-2 py-1 rounded bg-blue-500/30 text-blue-300 text-xs font-mono">POST</span>
                                <span class="text-gray-600 dark:text-gray-300 text-sm font-mono">/api/events/{'{subdomain}'}</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-[#0f0f14] border border-gray-100 dark:border-white/5">
                                <span class="px-2 py-1 rounded bg-blue-500/30 text-blue-300 text-xs font-mono">POST</span>
                                <span class="text-gray-600 dark:text-gray-300 text-sm font-mono">/api/sales</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- API Features Section -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Developer-friendly API
                </h2>
                <p class="text-xl text-gray-500">
                    Built for reliability and ease of use.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- API Key Auth -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-100 mb-6">
                        <svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">API Key Authentication</h3>
                    <p class="text-gray-600 text-sm">Simple header-based authentication. Generate API keys from your account settings.</p>
                </div>

                <!-- Rate Limiting -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-100 mb-6">
                        <svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Rate Limiting</h3>
                    <p class="text-gray-600 text-sm">60 requests per minute per IP. Built-in brute force protection keeps your data safe.</p>
                </div>

                <!-- JSON Responses -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-100 mb-6">
                        <svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3zm5 1h6m-6 4h6m-6 4h4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">JSON Responses</h3>
                    <p class="text-gray-600 text-sm">Clean, paginated JSON responses with metadata. Easy to parse in any language.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Use Cases -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    What you can build
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Common use cases for the API.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Automated Imports -->
                <div class="bg-gray-100 dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-500/20 mb-4">
                        <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Automated Imports</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Pull events from other systems and create them automatically. Sync with your existing tools.</p>
                </div>

                <!-- Website Integration -->
                <div class="bg-gray-100 dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-500/20 mb-4">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Website Widgets</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Fetch events and display them on your own website with custom styling and layouts.</p>
                </div>

                <!-- Mobile Apps -->
                <div class="bg-gray-100 dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20 mb-4">
                        <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Mobile Apps</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Build custom mobile experiences. The API works with any frontend framework or native app.</p>
                </div>

                <!-- Ticket Sales -->
                <div class="bg-gray-100 dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/20 mb-4">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Custom Ticket Sales</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Create sales programmatically. Build your own checkout flow while leveraging our ticketing infrastructure.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Note -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-violet-100 mb-8">
                    <svg class="w-10 h-10 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    API access included with Pro
                </h2>
                <p class="text-xl text-gray-600 mb-6 max-w-2xl mx-auto">
                    API access requires a Pro subscription. The first year is free, then $5/month. Self-hosted installations have full API access.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('sign_up') }}" class="inline-flex items-center px-6 py-3 text-lg font-semibold text-white bg-gradient-to-r from-violet-600 to-indigo-600 rounded-xl hover:scale-105 transition-all shadow-lg shadow-violet-500/25">
                        Start Free Trial
                    </a>
                    <a href="{{ marketing_url('/selfhost') }}" class="inline-flex items-center px-6 py-3 text-lg font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Learn about selfhosting
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background blobs matching Ticketing page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-fuchsia-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-violet-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/ticketing') }}" class="group block">
                <div class="bg-gradient-to-br from-fuchsia-900/50 to-violet-900/50 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <!-- Text content -->
                        <div class="flex-1 text-center lg:text-left">
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-fuchsia-300 transition-colors">Ticketing</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Sell tickets directly from your schedule. QR codes for fast check-ins, Stripe payments built-in.</p>
                            <span class="inline-flex items-center text-fuchsia-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Mini mockup: QR code ticket -->
                        <div class="flex-shrink-0">
                            <div class="bg-white rounded-xl p-3 w-36 shadow-xl">
                                <!-- Ticket header -->
                                <div class="text-center mb-2">
                                    <div class="text-[10px] text-gray-500 font-medium">TICKET</div>
                                    <div class="text-xs text-gray-900 font-semibold">Jazz Night</div>
                                </div>
                                <!-- Mini QR code mockup -->
                                <div class="bg-gray-100 rounded-lg p-2 mb-2">
                                    <div class="grid grid-cols-5 gap-0.5">
                                        @for ($i = 0; $i < 25; $i++)
                                            <div class="aspect-square {{ rand(0, 1) ? 'bg-gray-800' : 'bg-gray-200' }} rounded-[1px]"></div>
                                        @endfor
                                    </div>
                                </div>
                                <!-- Ticket footer -->
                                <div class="flex justify-between items-center text-[8px] text-gray-500 border-t border-dashed border-gray-300 pt-1.5">
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

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-gray-700 to-gray-900 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Start building today
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Explore the codebase on GitHub or sign up to get your API key.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-gray-900 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                    <svg class="mr-2 w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    View on GitHub
                </a>
                <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white border-2 border-white/30 rounded-2xl hover:bg-white/10 transition-all">
                    Get Started Free
                </a>
            </div>
        </div>
    </section>
</x-marketing-layout>
