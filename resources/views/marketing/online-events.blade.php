<x-marketing-layout>
    <x-slot name="title">Virtual & Online Event Hosting | Streaming & Ticketing - Event Schedule</x-slot>
    <x-slot name="description">Host virtual events with ease. Toggle between in-person and online, add your streaming URL, and sell tickets to attendees worldwide.</x-slot>
    <x-slot name="keywords">online events, virtual events, streaming events, webinars, live streaming, zoom events, youtube live events, online ticketing</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">Online Events</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Online Events</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Go live,<br>
                <span class="text-gradient">anywhere</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Host virtual events with any streaming platform. Share your link with ticket holders and reach audiences worldwide.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-sky-600 to-blue-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-sky-500/25">
                    Get started free
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

                <!-- Easy Toggle (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                Easy Toggle
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Switch in one click</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Toggle any event between in-person and online with a single checkbox. Add your streaming URL and you're ready to go live.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">In-Person</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-sky-500/30 text-sky-700 dark:text-sky-300 text-sm border border-sky-400/30">Online</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Toggle mockup -->
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-6 w-64">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-gray-600 dark:text-gray-300 text-sm">Online Event</span>
                                        <div class="w-12 h-6 bg-sky-500 rounded-full relative">
                                            <div class="absolute right-1 top-1 w-4 h-4 bg-white rounded-full"></div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-gray-500 text-xs">Streaming URL</label>
                                        <div class="bg-gray-200 dark:bg-white/5 rounded-lg px-3 py-2 text-sky-400 text-sm truncate">
                                            zoom.us/j/123456789
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Any Streaming Platform -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Any Platform
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Your platform, your choice</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Works with any streaming service. Just paste your URL and attendees will see it on the event page and their ticket.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg">
                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Zoom</span>
                        </div>
                        <div class="flex items-center gap-3 px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">YouTube Live</span>
                        </div>
                        <div class="flex items-center gap-3 px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg">
                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Twitch</span>
                        </div>
                        <div class="flex items-center gap-3 px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg">
                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Google Meet</span>
                        </div>
                    </div>
                </div>

                <!-- Visible on Tickets -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        On Your Ticket
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Link on every ticket</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Ticket holders see the streaming URL when they view their ticket. No searching through emails for the link.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Your Ticket</span>
                        </div>
                        <div class="text-xs text-gray-500 mb-1">Join the stream:</div>
                        <div class="text-sky-400 text-sm">zoom.us/j/123...</div>
                    </div>
                </div>

                <!-- Sell Tickets (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Full Ticketing
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Sell tickets to virtual events</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">All ticketing features work for online events. Multiple ticket types, QR codes, attendee management, and secure payments with Stripe.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg class="w-8 h-8 text-blue-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                <div class="text-blue-400 text-sm">Multiple Tiers</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg class="w-8 h-8 text-blue-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <div class="text-blue-400 text-sm">QR Codes</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg class="w-8 h-8 text-blue-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <div class="text-blue-400 text-sm">Attendees</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg class="w-8 h-8 text-blue-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <div class="text-blue-400 text-sm">Secure Pay</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Use Cases Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for any virtual event
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From webinars to concerts, reach your audience wherever they are.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Webinars -->
                <a href="{{ marketing_url('/for-webinars') }}" class="block bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30 rounded-2xl p-6 border border-sky-200 dark:border-sky-500/20 shadow-sm text-center hover:shadow-lg hover:border-sky-300 dark:hover:border-sky-400/30 transition-all">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Webinars</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Host educational sessions, product demos, or training workshops for attendees worldwide.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium hover:gap-3 gap-2 transition-all mt-4 text-sm">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Live Concerts -->
                <a href="{{ marketing_url('/for-live-concerts') }}" class="block bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30 rounded-2xl p-6 border border-sky-200 dark:border-sky-500/20 shadow-sm text-center hover:shadow-lg hover:border-sky-300 dark:hover:border-sky-400/30 transition-all">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Live Concerts</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Stream live performances to fans who can't make it in person. Sell virtual tickets alongside venue tickets.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium hover:gap-3 gap-2 transition-all mt-4 text-sm">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Online Classes -->
                <a href="{{ marketing_url('/for-online-classes') }}" class="block bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30 rounded-2xl p-6 border border-sky-200 dark:border-sky-500/20 shadow-sm text-center hover:shadow-lg hover:border-sky-300 dark:hover:border-sky-400/30 transition-all">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Online Classes</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Yoga, cooking, art, or fitness classes. Schedule recurring sessions and manage enrollments.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium hover:gap-3 gap-2 transition-all mt-4 text-sm">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Virtual Conferences -->
                <a href="{{ marketing_url('/for-virtual-conferences') }}" class="block bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30 rounded-2xl p-6 border border-sky-200 dark:border-sky-500/20 shadow-sm text-center hover:shadow-lg hover:border-sky-300 dark:hover:border-sky-400/30 transition-all">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Virtual Conferences</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Host multi-day conferences with different ticket types for keynotes, workshops, and networking sessions.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium hover:gap-3 gap-2 transition-all mt-4 text-sm">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Live Q&A Sessions -->
                <a href="{{ marketing_url('/for-live-qa-sessions') }}" class="block bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30 rounded-2xl p-6 border border-sky-200 dark:border-sky-500/20 shadow-sm text-center hover:shadow-lg hover:border-sky-300 dark:hover:border-sky-400/30 transition-all">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Live Q&A Sessions</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Interactive sessions where speakers answer audience questions in real-time.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium hover:gap-3 gap-2 transition-all mt-4 text-sm">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <!-- Watch Parties -->
                <a href="{{ marketing_url('/for-watch-parties') }}" class="block bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30 rounded-2xl p-6 border border-sky-200 dark:border-sky-500/20 shadow-sm text-center hover:shadow-lg hover:border-sky-300 dark:hover:border-sky-400/30 transition-all">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-100 dark:bg-white/10 mb-6">
                        <svg class="w-8 h-8 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Watch Parties</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Premiere screenings, movie nights, or sports viewing events with your community.</p>
                    <span class="inline-flex items-center text-sky-600 dark:text-sky-400 font-medium hover:gap-3 gap-2 transition-all mt-4 text-sm">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background blobs matching Sub-schedules page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-rose-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-cyan-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/sub-schedules') }}" class="group block">
                <div class="bg-gradient-to-br from-rose-100 to-cyan-100 dark:from-rose-900 dark:to-cyan-900 rounded-3xl border border-rose-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <!-- Text content -->
                        <div class="flex-1 text-center lg:text-left">
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-rose-600 dark:group-hover:text-rose-300 transition-colors">Sub-schedules</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Organize events into categories. Perfect for venues with multiple rooms or event series.</p>
                            <span class="inline-flex items-center text-rose-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Mini mockup: Nested list with colored dots -->
                        <div class="flex-shrink-0">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4 w-48">
                                <div class="text-[10px] text-gray-500 mb-3">Sub-schedules</div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-rose-500/20 border border-rose-500/30">
                                        <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                                        <span class="text-gray-900 dark:text-white text-xs">Main Stage</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                        <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">Acoustic Room</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                        <div class="w-2 h-2 rounded-full bg-sky-400"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">Outdoor Patio</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                        <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">Jazz Lounge</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-sky-600 to-blue-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to go virtual?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start hosting online events today. Toggle any event to online and add your streaming URL.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-sky-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
