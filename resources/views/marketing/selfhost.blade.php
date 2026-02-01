<x-marketing-layout>
    <x-slot name="title">Selfhost Event Schedule - Run on Your Own Server</x-slot>
    <x-slot name="description">Self-host Event Schedule on your own server. 100% open source with one-click Docker installation, automatic updates, and exclusive AI-powered features.</x-slot>
    <x-slot name="keywords">selfhosted event management, open source calendar, selfhost events, docker event schedule, softaculous calendar, auto import events, AI blog SEO</x-slot>
    <x-slot name="socialImage">social/selfhost.png</x-slot>

    <style>
        /* Custom emerald gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-green-600/10 rounded-full blur-[150px]"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-300">100% Open Source</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Your server,<br>
                <span class="text-gradient">your rules</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Run Event Schedule on your own infrastructure. Full control, exclusive features, and zero platform fees.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('marketing.docs.selfhost.installation') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-emerald-500/25">
                    Installation Guide
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-gray-900 dark:text-white glass border border-gray-200 dark:border-white/10 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
                    <svg class="mr-2 w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    View on GitHub
                </a>
                <a href="{{ demo_url() }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-gray-900 dark:text-white glass border border-gray-200 dark:border-white/10 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
                    Admin Portal Demo
                </a>
                <a href="https://simpsons.eventschedule.com" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-gray-900 dark:text-white glass border border-gray-200 dark:border-white/10 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
                    Client Portal Demo
                </a>
            </div>
        </div>
    </section>

    <!-- Installation Options -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Easy Installation
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Get up and running in minutes</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Choose your preferred installation method. Both options provide automated setup with minimal configuration.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Softaculous -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 border border-gray-200 dark:border-white/10 p-8 flex flex-col h-full">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Softaculous</h3>
                            <p class="text-gray-600 dark:text-white">One-click installer</p>
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-white/80 mb-6">
                        Available on most cPanel hosting providers. Install Event Schedule with a single click - no command line required.
                    </p>
                    <ul class="space-y-3 mb-8 flex-grow">
                        <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Automatic database setup
                        </li>
                        <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Auto-configured environment
                        </li>
                        <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Available on 1000+ hosts
                        </li>
                    </ul>
                    <a href="https://www.softaculous.com/apps/calendars/Event_Schedule" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 rounded-xl text-white font-medium transition-colors mt-auto">
                        Install with Softaculous
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

                <!-- Docker -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8 flex flex-col h-full">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M13.98 11.08h2.12a.19.19 0 0 0 .19-.19V9.01a.19.19 0 0 0-.19-.19h-2.12a.18.18 0 0 0-.18.19v1.88c0 .1.08.19.18.19m-2.95-5.43h2.12a.19.19 0 0 0 .18-.19V3.58a.19.19 0 0 0-.18-.19h-2.12a.18.18 0 0 0-.19.19v1.88c0 .1.09.19.19.19m0 2.71h2.12a.19.19 0 0 0 .18-.18V6.29a.19.19 0 0 0-.18-.18h-2.12a.18.18 0 0 0-.19.18v1.89c0 .1.09.18.19.18m-2.93 0h2.12a.19.19 0 0 0 .18-.18V6.29a.18.18 0 0 0-.18-.18H8.1a.18.18 0 0 0-.19.18v1.89c0 .1.09.18.19.18m-2.96 0h2.11a.19.19 0 0 0 .19-.18V6.29a.18.18 0 0 0-.19-.18H5.14a.19.19 0 0 0-.19.18v1.89c0 .1.09.18.19.18m5.89 2.72h2.12a.19.19 0 0 0 .18-.19V9.01a.19.19 0 0 0-.18-.19h-2.12a.18.18 0 0 0-.19.19v1.88c0 .1.09.19.19.19m-2.93 0h2.12a.18.18 0 0 0 .18-.19V9.01a.18.18 0 0 0-.18-.19H8.1a.18.18 0 0 0-.19.19v1.88c0 .1.09.19.19.19m-2.96 0h2.11a.18.18 0 0 0 .19-.19V9.01a.18.18 0 0 0-.19-.19H5.14a.18.18 0 0 0-.19.19v1.88c0 .1.09.19.19.19m-2.92 0h2.12a.18.18 0 0 0 .18-.19V9.01a.18.18 0 0 0-.18-.19H2.22a.19.19 0 0 0-.19.19v1.88c0 .1.09.19.19.19m21.54-1.19c-.06-.03-.12-.06-.19-.08a1.58 1.58 0 0 0-.47-.15 3.04 3.04 0 0 0-.65-.05c-.14 0-.28 0-.42.02-.13.01-.26.03-.38.06l-.12.03c.01-.04.01-.07.01-.11a1.78 1.78 0 0 0-.63-1.46 2.04 2.04 0 0 0-.83-.43l-.18-.04.1.17c.22.37.33.75.33 1.13 0 .15-.02.31-.05.46a3.3 3.3 0 0 1-.19.57c-.35.23-.95.55-1.49.63-.54.08-1.08.08-1.63.08H2.87c-.17 0-.31.13-.32.3-.04.71.05 1.41.25 2.08a4.54 4.54 0 0 0 1.23 2.07c.87.83 2 1.35 3.28 1.54a12.8 12.8 0 0 0 2.15.14c1.67-.05 3.31-.37 4.74-1.09a7.77 7.77 0 0 0 2.75-2.3 8.67 8.67 0 0 0 1.37-2.77c.33.01.66-.02.97-.1.59-.15 1.08-.48 1.39-.96l.13-.21-.24-.07Z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Docker</h3>
                            <p class="text-gray-600 dark:text-white">Containerized deployment</p>
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-white/80 mb-6">
                        Deploy with Docker Compose for a consistent, isolated environment. Perfect for VPS, cloud servers, or local development.
                    </p>
                    <ul class="space-y-3 mb-8 flex-grow">
                        <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Isolated environment
                        </li>
                        <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Easy scaling and backups
                        </li>
                        <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Pre-configured compose file
                        </li>
                    </ul>
                    <a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-cyan-600 to-teal-600 hover:from-cyan-500 hover:to-teal-500 rounded-xl text-white font-medium transition-colors mt-auto">
                        View Docker Setup
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- One-Click Updates -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-700 dark:text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Automatic Updates
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">One-click updates</h2>
                    <p class="text-xl text-gray-500 dark:text-gray-400 mb-8">
                        Keep your installation up to date with a single click. When a new version is available, just click the update button in your admin panel - no terminal access needed.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-violet-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">Database migrations included</h4>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Schema changes are applied automatically</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-violet-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">No downtime required</h4>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Updates complete in seconds with minimal disruption</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-lg bg-violet-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">Version notifications</h4>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">Get notified in your admin panel when updates are available</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="relative">
                    <div class="animate-float bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-gray-200 dark:border-white/10 rounded-2xl p-6 shadow-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                                    <span class="text-white font-bold">ES</span>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white font-semibold">Event Schedule</div>
                                    <div class="text-gray-500 dark:text-white/70 text-sm">v2.4.1 installed</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-xs font-medium">
                                Update Available
                            </span>
                        </div>
                        <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl p-4 mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-600 dark:text-gray-300">New version:</span>
                                <span class="text-gray-900 dark:text-white font-medium">v2.5.0</span>
                            </div>
                            <div class="text-gray-600 dark:text-white/70 text-sm">
                                New features, bug fixes, and security updates
                            </div>
                        </div>
                        <button class="w-full py-3 bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-500 hover:to-purple-500 rounded-xl text-white font-medium transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Update Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Selfhost Exclusive Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    Exclusive Features
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Only available when selfhosting</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Unlock powerful features that are exclusive to selfhosted installations.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Auto Import -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Auto Import
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Automatic event importing</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">
                        Automatically pull events from external websites into your schedule. Our intelligent system extracts event details from any webpage using AI-powered parsing.
                    </p>
                    <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl p-4 mb-6">
                        <div class="flex items-center gap-3 mb-3">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span class="text-gray-900 dark:text-white font-medium">Respects robots.txt</span>
                        </div>
                        <p class="text-gray-600 dark:text-white/70 text-sm">
                            We check each website's robots.txt file before importing to ensure we only access content that site owners have permitted for automated access.
                        </p>
                    </div>
                    <ul class="space-y-2 text-gray-700 dark:text-white/90 text-sm">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Schedule automatic imports
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            AI extracts dates, times, venues
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Keep your schedule synced automatically
                        </li>
                    </ul>
                </div>

                <!-- AI Blog -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-100 to-pink-100 dark:from-fuchsia-900 dark:to-pink-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/20 dark:text-fuchsia-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        AI-Powered Blog
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Automated content for SEO</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">
                        Generate relevant blog content automatically to drive organic traffic to your event schedule. Our AI creates engaging posts tailored to your events and audience.
                    </p>
                    <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl p-4 mb-6">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-fuchsia-500/30 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-fuchsia-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">Best Jazz Venues in Your City</div>
                                    <div class="text-gray-500 dark:text-white/60 text-xs">Generated 2 hours ago</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-fuchsia-500/30 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-fuchsia-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">This Week's Must-See Events</div>
                                    <div class="text-gray-500 dark:text-white/60 text-xs">Scheduled for tomorrow</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="space-y-2 text-gray-700 dark:text-white/90 text-sm">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            SEO-optimized content
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Increase organic traffic
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Powered by Google Gemini
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Open Source Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gray-200 dark:bg-white/10 mb-8">
                    <svg class="w-10 h-10 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                    100% Open Source
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                    Event Schedule is fully open source under the AAL license. Inspect the code, contribute improvements, or fork it for your own needs.
                </p>

                <div class="grid sm:grid-cols-3 gap-6 mb-10">
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-xl p-6 border border-emerald-200 dark:border-emerald-500/20">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">100%</div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Open Source</div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-xl p-6 border border-emerald-200 dark:border-emerald-500/20">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Free</div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Forever</div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-xl p-6 border border-emerald-200 dark:border-emerald-500/20">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">AAL</div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">Licensed</div>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center gap-4">
                    <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 border border-gray-300 dark:border-white/20 rounded-xl text-gray-900 dark:text-white font-medium transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        Main Repository
                    </a>
                    <a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 border border-gray-300 dark:border-white/20 rounded-xl text-gray-900 dark:text-white font-medium transition-colors">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M13.983 11.078h2.119a.186.186 0 00.186-.185V9.006a.186.186 0 00-.186-.186h-2.119a.185.185 0 00-.185.185v1.888c0 .102.083.185.185.185m-2.954-5.43h2.118a.186.186 0 00.186-.186V3.574a.186.186 0 00-.186-.185h-2.118a.185.185 0 00-.185.185v1.888c0 .102.082.185.185.185m0 2.716h2.118a.187.187 0 00.186-.186V6.29a.186.186 0 00-.186-.185h-2.118a.185.185 0 00-.185.185v1.887c0 .102.082.186.185.186m-2.93 0h2.12a.186.186 0 00.184-.186V6.29a.185.185 0 00-.185-.185H8.1a.185.185 0 00-.185.185v1.887c0 .102.083.186.185.186m-2.964 0h2.119a.186.186 0 00.185-.186V6.29a.185.185 0 00-.185-.185H5.136a.186.186 0 00-.186.185v1.887c0 .102.084.186.186.186m5.893 2.715h2.118a.186.186 0 00.186-.185V9.006a.186.186 0 00-.186-.186h-2.118a.185.185 0 00-.185.185v1.888c0 .102.082.185.185.185m-2.93 0h2.12a.185.185 0 00.184-.185V9.006a.185.185 0 00-.184-.186h-2.12a.185.185 0 00-.184.185v1.888c0 .102.083.185.185.185m-2.964 0h2.119a.185.185 0 00.185-.185V9.006a.185.185 0 00-.185-.186h-2.119a.185.185 0 00-.186.185v1.888c0 .102.084.185.186.185m-2.92 0h2.12a.185.185 0 00.184-.185V9.006a.185.185 0 00-.184-.186h-2.12a.186.186 0 00-.186.185v1.888c0 .102.084.185.186.185m-.165 2.715h2.119a.186.186 0 00.185-.185v-1.888a.185.185 0 00-.185-.185H2.136a.186.186 0 00-.186.185v1.888c0 .102.084.185.186.185"/>
                        </svg>
                        Docker Files
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- SaaS White-Label Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 via-violet-100 to-purple-100 dark:from-indigo-900 dark:via-violet-900 dark:to-purple-900 border border-gray-200 dark:border-white/10 p-8 md:p-12">
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-violet-500/20 rounded-full blur-[100px]"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-500/20 rounded-full blur-[100px]"></div>

                <div class="relative z-10 grid lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Business Opportunity
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                            Build your own SaaS business
                        </h2>
                        <p class="text-xl text-gray-600 dark:text-white/80 mb-6">
                            Turn Event Schedule into your own white-label SaaS platform. Offer event scheduling as a service to your customers under your own brand.
                        </p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                                <svg class="w-5 h-5 text-violet-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Multi-tenant architecture built-in
                            </li>
                            <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                                <svg class="w-5 h-5 text-violet-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Stripe integration for subscriptions
                            </li>
                            <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                                <svg class="w-5 h-5 text-violet-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Full white-label customization
                            </li>
                            <li class="flex items-center gap-3 text-gray-700 dark:text-white/90">
                                <svg class="w-5 h-5 text-violet-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Keep 100% of your revenue
                            </li>
                        </ul>
                        <a href="{{ marketing_url('/saas') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 rounded-xl text-white font-medium transition-all shadow-lg shadow-violet-500/25">
                            Learn about SaaS setup
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <!-- Visual mockup -->
                    <div class="relative">
                        <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">YB</span>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white font-semibold">Your Brand</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-sm">yourbrand.com</div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600 dark:text-gray-300 text-sm">Monthly subscribers</span>
                                        <span class="text-emerald-400 text-sm">+12%</span>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">247</div>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600 dark:text-gray-300 text-sm">Monthly revenue</span>
                                        <span class="text-emerald-400 text-sm">+8%</span>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">$4,940</div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="bg-violet-500/20 rounded-lg p-3 text-center">
                                        <div class="text-gray-900 dark:text-white font-semibold">Free</div>
                                        <div class="text-violet-700 dark:text-violet-300 text-xs">142 users</div>
                                    </div>
                                    <div class="bg-indigo-500/20 rounded-lg p-3 text-center">
                                        <div class="text-gray-900 dark:text-white font-semibold">Pro</div>
                                        <div class="text-indigo-700 dark:text-indigo-300 text-xs">89 users</div>
                                    </div>
                                    <div class="bg-purple-500/20 rounded-lg p-3 text-center">
                                        <div class="text-gray-900 dark:text-white font-semibold">Team</div>
                                        <div class="text-purple-700 dark:text-purple-300 text-xs">16 users</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-600 dark:to-teal-700 py-24 overflow-hidden">
        <div class="hidden dark:block absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Ready to selfhost?
            </h2>
            <p class="text-xl text-gray-600 dark:text-white/80 mb-10 max-w-2xl mx-auto">
                Get started with the installation guide. Have questions? Check out our GitHub discussions.
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('marketing.docs.selfhost.installation') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 dark:text-emerald-600 dark:bg-white dark:bg-none rounded-2xl hover:scale-105 transition-all shadow-xl">
                    Read Installation Guide
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
                <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-gray-900 border-2 border-gray-300 dark:text-white dark:border-white/30 rounded-2xl hover:bg-gray-100 dark:hover:bg-white/10 transition-all">
                    Or try the hosted version
                </a>
            </div>
        </div>
    </section>
</x-marketing-layout>
