<x-marketing-layout>
    <x-slot name="title">Features - Event Schedule</x-slot>
    <x-slot name="description">Discover all the features that make Event Schedule the best way to manage events, sell tickets, and engage your audience.</x-slot>
    <x-slot name="keywords">event management features, online ticketing, QR code check-in, Google Calendar sync, AI event parsing, custom domain events, recurring events, event API</x-slot>

    <style>
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out infinite; animation-delay: 2s; }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
        .feature-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .bento-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bento-card:hover {
            transform: scale(1.02);
        }
        .ticket-stub {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            position: relative;
        }
        .ticket-stub::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
        }
        .ticket-stub::after {
            content: '';
            position: absolute;
            right: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-fuchsia-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-600/10 rounded-full blur-[150px]"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-sm text-gray-300">20+ powerful features</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 leading-tight">
                Built for people who<br>
                <span class="text-gradient">run events</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12">
                Everything you need to create calendars, sell tickets, and check in attendees. No bloat. No complexity.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-violet-500/25">
                    Start for free
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#features" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white glass border border-white/10 rounded-2xl hover:bg-white/10 transition-all">
                    Explore features
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section id="features" class="bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Hero Card - Calendars (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-900/50 to-indigo-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Event Calendars
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-white mb-4">Your schedule, your way</h3>
                            <p class="text-gray-400 text-lg mb-6">Create beautiful, shareable event calendars. Get your own subdomain or connect a custom domain. Organize with sub-schedules for different rooms or categories.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Custom domains</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Sub-schedules</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Recurring events</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 animate-float">
                            <div class="w-64 h-80 bg-gradient-to-br from-white/10 to-white/5 rounded-2xl border border-white/20 p-4 shadow-2xl">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-500"></div>
                                    <div class="text-white font-semibold">Blue Room</div>
                                </div>
                                <div class="space-y-2">
                                    <div class="p-3 rounded-xl bg-violet-500/30 border border-violet-400/30">
                                        <div class="text-white text-sm font-medium">Jazz Night</div>
                                        <div class="text-violet-200 text-xs">8:00 PM</div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-fuchsia-500/30 border border-fuchsia-400/30">
                                        <div class="text-white text-sm font-medium">Open Mic</div>
                                        <div class="text-fuchsia-200 text-xs">9:30 PM</div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-indigo-500/30 border border-indigo-400/30">
                                        <div class="text-white text-sm font-medium">DJ Set</div>
                                        <div class="text-indigo-200 text-xs">11:00 PM</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticketing Card -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-900/50 to-pink-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-500/20 text-fuchsia-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Sell tickets online</h3>
                    <p class="text-gray-400 mb-6">Multiple ticket types, limits, reservations. Accept payments with Stripe. Zero platform fees.</p>

                    <div class="space-y-3 animate-float-delayed">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white/10 border border-white/10">
                            <div>
                                <div class="text-white font-medium">GA</div>
                                <div class="text-gray-400 text-xs">142 sold</div>
                            </div>
                            <div class="text-xl font-bold text-white">$25</div>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-fuchsia-500/20 border border-fuchsia-400/30">
                            <div>
                                <div class="text-white font-medium">VIP</div>
                                <div class="text-fuchsia-300 text-xs">38 sold</div>
                            </div>
                            <div class="text-xl font-bold text-white">$75</div>
                        </div>
                    </div>
                </div>

                <!-- QR Check-ins -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900/50 to-teal-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        Check-ins
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">QR code scanning</h3>
                    <p class="text-gray-400 mb-6">Fast, contactless entry. Scan with any smartphone. Real-time attendance tracking.</p>

                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="w-32 h-32 bg-white rounded-2xl p-2 animate-float">
                                <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Features (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-900/50 to-orange-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        AI-Powered
                    </div>
                    <h3 class="text-3xl font-bold text-white mb-4">Let AI do the heavy lifting</h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-black/30 rounded-2xl p-6 border border-white/10">
                            <h4 class="text-lg font-semibold text-white mb-3">Smart Event Parsing</h4>
                            <p class="text-gray-400 text-sm mb-4">Paste event details in any format. AI extracts title, date, time, location automatically.</p>
                            <div class="font-mono text-sm">
                                <div class="text-gray-500 mb-1">Input:</div>
                                <div class="text-amber-300 mb-3">"Jazz @ Blue Room Mar 15 8pm $20"</div>
                                <div class="text-gray-500 mb-1">Output:</div>
                                <div class="text-emerald-400">Title: Jazz</div>
                                <div class="text-emerald-400">Venue: Blue Room</div>
                                <div class="text-emerald-400">Date: March 15, 8:00 PM</div>
                            </div>
                        </div>
                        <div class="bg-black/30 rounded-2xl p-6 border border-white/10">
                            <h4 class="text-lg font-semibold text-white mb-3">Instant Translation</h4>
                            <p class="text-gray-400 text-sm mb-4">Translate your entire schedule into 30+ languages with one click.</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-3 py-1.5 bg-white/10 rounded-lg text-sm text-white">English</span>
                                <span class="px-3 py-1.5 bg-white/10 rounded-lg text-sm text-white">Espa&ntilde;ol</span>
                                <span class="px-3 py-1.5 bg-white/10 rounded-lg text-sm text-white">Fran&ccedil;ais</span>
                                <span class="px-3 py-1.5 bg-white/10 rounded-lg text-sm text-white">&#20013;&#25991;</span>
                                <span class="px-3 py-1.5 bg-amber-500/30 rounded-lg text-sm text-amber-300">+26 more</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Embeddable -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-900/50 to-pink-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        Embed
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Embed anywhere</h3>
                    <p class="text-gray-400 mb-6">Add your schedule to any website with a simple embed code or iframe.</p>

                    <div class="bg-black/40 rounded-xl p-4 font-mono text-sm">
                        <div class="text-gray-500 mb-2">&lt;!-- Your Schedule --&gt;</div>
                        <div class="text-rose-300">&lt;iframe src="..."&gt;</div>
                        <div class="text-rose-300">&lt;/iframe&gt;</div>
                    </div>
                </div>

                <!-- Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-900/50 to-cyan-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Sync
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Google Calendar sync</h3>
                    <p class="text-gray-400 mb-6">Two-way sync keeps everything up to date. Changes appear instantly.</p>

                    <div class="flex items-center justify-center gap-4">
                        <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-8 h-8" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse"></div>
                            <div class="w-8 h-0.5 bg-blue-400/50"></div>
                            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
                            <div class="w-8 h-0.5 bg-blue-400/50"></div>
                            <div class="w-2 h-2 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-violet-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <span class="text-white font-bold text-lg">ES</span>
                        </div>
                    </div>
                </div>

                <!-- Recurring Events -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900/50 to-purple-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Repeat
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Recurring events</h3>
                    <p class="text-gray-400 mb-6">Set it once, runs forever. Daily, weekly, monthly, or custom patterns.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/30 flex items-center justify-center text-indigo-300 font-medium">M</div>
                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-gray-500 font-medium">T</div>
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/30 flex items-center justify-center text-indigo-300 font-medium">W</div>
                            <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-gray-500 font-medium">T</div>
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/30 flex items-center justify-center text-indigo-300 font-medium">F</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- More Features - Light Section -->
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    And that's just the start
                </h2>
                <p class="text-xl text-gray-500 max-w-2xl mx-auto">
                    Every tool you need to run events like a pro.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="feature-card bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Import Events</h3>
                    <p class="text-gray-600 text-sm">Pull events from external websites automatically.</p>
                </div>

                <div class="feature-card bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-fuchsia-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-fuchsia-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Event Graphics</h3>
                    <p class="text-gray-600 text-sm">Auto-generate flyers and social media graphics.</p>
                </div>

                <div class="feature-card bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Team Access</h3>
                    <p class="text-gray-600 text-sm">Invite team members with custom permissions.</p>
                </div>

                <div class="feature-card bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Online Events</h3>
                    <p class="text-gray-600 text-sm">Sell tickets to virtual events with streaming links.</p>
                </div>

                <div class="feature-card bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Recurring Events</h3>
                    <p class="text-gray-600 text-sm">Set up events that repeat daily, weekly, or monthly.</p>
                </div>

                <div class="feature-card bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Embeddable</h3>
                    <p class="text-gray-600 text-sm">Embed your schedule on any website.</p>
                </div>

                <div class="feature-card bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">REST API</h3>
                    <p class="text-gray-600 text-sm">Full API for custom integrations.</p>
                </div>

                <div class="feature-card bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-cyan-100 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Self-Hosted</h3>
                    <p class="text-gray-600 text-sm">Run on your own server. AAL licensed.</p>
                </div>

            </div>
        </div>
    </section>

    <!-- Open Source Section -->
    <section class="bg-[#0a0a0f] py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 mb-6">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Free and open source
                </h2>
                <p class="text-xl text-gray-400 mb-8 max-w-2xl mx-auto">
                    Event Schedule is AAL licensed. Self-host on your own server, contribute to the codebase, or just use it free forever.
                </p>
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl text-white font-medium transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    View on GitHub
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-violet-600 to-indigo-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to get started?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Create your free event schedule in seconds. No credit card required.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-violet-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Start for free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
