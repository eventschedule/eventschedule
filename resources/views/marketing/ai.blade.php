<x-marketing-layout>
    <x-slot name="title">AI-Powered Event Import | Text & Image to Calendar - Event Schedule</x-slot>
    <x-slot name="description">Smart event import with AI. Paste text or drop an image and AI extracts all the details automatically. Instant translation included.</x-slot>
    <x-slot name="keywords">AI event import, smart event parsing, image to event, text to event, event translation, automatic event creation, AI calendar, event extraction</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">AI Import</x-slot>

    <style>
        /* Page-specific typing animation */
        @keyframes typing {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        .animate-typing { animation: typing 1s ease-in-out infinite; }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">AI-Powered</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Smart event<br>
                <span class="text-gradient">import</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Paste text or drop an image, even from multi-event agendas and setlists. AI extracts all the details automatically.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    Try it free
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
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">AI-powered features</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Smart Event Parsing (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Smart Parsing
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Parse any format</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-6">Flyers, emails, agendas, and setlists. Paste the text or drop an image and AI extracts event name, date, time, venue, and description. Multi-event images are parsed into individual events automatically.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Text parsing</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Image recognition</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Any language</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Agendas & setlists</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Input side -->
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-4 max-w-xs">
                                    <div class="text-xs text-gray-400 dark:text-gray-400 mb-2">Paste or drop</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300 font-mono leading-relaxed">
                                        Jazz Night at Blue Note<br>
                                        Friday, March 15 at 8pm<br>
                                        Featuring the Sarah Johnson Trio<br>
                                        Tickets: $25 at the door
                                    </div>
                                </div>
                                <!-- Arrow -->
                                <div class="flex justify-center my-2">
                                    <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <!-- Output side -->
                                <div class="bg-gradient-to-br from-blue-500/20 to-sky-500/20 rounded-2xl border border-blue-400/30 p-4 max-w-xs">
                                    <div class="text-xs text-blue-700 dark:text-blue-300 mb-2">Extracted</div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-blue-700 dark:text-white/60">Name:</span><span class="text-blue-900 dark:text-white">Jazz Night</span></div>
                                        <div class="flex justify-between"><span class="text-blue-700 dark:text-white/60">Date:</span><span class="text-blue-900 dark:text-white">Mar 15, 8:00 PM</span></div>
                                        <div class="flex justify-between"><span class="text-blue-700 dark:text-white/60">Venue:</span><span class="text-blue-900 dark:text-white">Blue Note</span></div>
                                        <div class="flex justify-between"><span class="text-blue-700 dark:text-white/60">Talent:</span><span class="text-blue-900 dark:text-white">Sarah Johnson Trio</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instant Translation -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                        Translation
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Instant translation</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Translate your entire schedule automatically. Support for 9 languages including English, Spanish, French, German, and more.</p>

                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">EN</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">ES</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">FR</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">DE</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">IT</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">PT</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">NL</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">HE</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">AR</span>
                    </div>
                </div>

                <!-- Venue & Talent Matching -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Smart Linking
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Auto-link venues & talent</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">AI matches parsed events to existing venues and performers in your schedule using fuzzy matching.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Blue Note Jazz Club</div>
                                <div class="text-emerald-600 dark:text-emerald-400 text-xs">Matched to existing venue</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Sarah Johnson Trio</div>
                                <div class="text-emerald-600 dark:text-emerald-400 text-xs">Matched to existing talent</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Formats (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Multiple Formats
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Any input works</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg">Text, images, screenshots, and flyers. Drop it in and AI figures out the rest.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Text</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Images</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Flyers</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Agendas</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Three steps to import any event.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Paste or Drop</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Copy text from anywhere or drop an image of an event flyer, agenda, setlist, or screenshot.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">AI Extracts</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        AI identifies event name, date, time, venue, performer, and description.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Review & Save</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Verify the extracted details, make any edits, and add to your schedule.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Formats Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-500/20 to-sky-500/20 border border-blue-500/30 mb-8">
                    <svg class="w-10 h-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Supported image formats
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
                    Upload event flyers, screenshots, or any image containing event details.
                </p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-2xl mx-auto">
                    <div class="bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 rounded-2xl p-6 border border-amber-200 dark:border-white/10">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">JPEG</div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Photos & flyers</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 rounded-2xl p-6 border border-blue-200 dark:border-white/10">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">PNG</div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Screenshots</p>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl p-6 border border-emerald-200 dark:border-white/10">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">GIF</div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Animated graphics</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 rounded-2xl p-6 border border-blue-200 dark:border-white/10">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">WebP</div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Modern format</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Guide & Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-sky-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-cyan-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.creating_events') }}" class="group block">
                    <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 mb-6">
                            <svg class="w-6 h-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Read the guide</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Learn how to get the most out of AI-powered event import.</p>
                        <span class="inline-flex items-center text-blue-500 dark:text-blue-400 font-medium group-hover:gap-3 gap-2 transition-all">
                            Read guide
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Next feature -->
                <a href="{{ route('marketing.newsletters') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 rounded-3xl border border-sky-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition-colors">Newsletters</h3>
                        <p class="text-gray-500 dark:text-white/80 text-lg mb-4">Send beautiful newsletters to followers and ticket buyers with drag-and-drop building and A/B testing.</p>
                        <span class="inline-flex items-center text-sky-400 font-medium group-hover:gap-3 gap-2 transition-all">
                            Learn more
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Import events in seconds
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Stop typing event details manually. Let AI do the work for you.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule AI Import",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "AI Event Management Software",
        "operatingSystem": "Web",
        "description": "Smart event import with AI. Paste text or drop an image and AI extracts all the details automatically. Includes instant translation.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "AI-powered text parsing",
            "Image and flyer import",
            "Agenda and setlist parsing",
            "Multi-event image extraction",
            "Automatic event extraction",
            "Instant translation",
            "Bulk import support",
            "Google Gemini powered"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
