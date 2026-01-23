<x-marketing-layout>
    <x-slot name="title">AI-Powered Event Import | Text & Image to Calendar - Event Schedule</x-slot>
    <x-slot name="description">Smart event import with AI. Paste text or drop an image and AI extracts all the details automatically. Instant translation included.</x-slot>
    <x-slot name="keywords">AI event import, smart event parsing, image to event, text to event, event translation, automatic event creation, AI calendar, event extraction</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <style>
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
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
        @keyframes typing {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-typing { animation: typing 1s ease-in-out infinite; }
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
    </style>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-fuchsia-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span class="text-sm text-gray-300">AI-Powered</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 leading-tight">
                Smart event<br>
                <span class="text-gradient">import</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12">
                Paste text or drop an image. AI extracts all the details automatically.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-violet-600 to-fuchsia-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-violet-500/25">
                    Try it free
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Smart Event Parsing (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-900/50 to-indigo-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Smart Parsing
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-white mb-4">Parse any format</h3>
                            <p class="text-gray-400 text-lg mb-6">Flyers, emails, social posts, screenshots. Paste the text or drop an image and AI extracts event name, date, time, venue, and description.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Text parsing</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Image recognition</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Any language</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Input side -->
                                <div class="bg-black/40 rounded-2xl border border-white/10 p-4 mb-4 max-w-xs">
                                    <div class="text-xs text-gray-500 mb-2">Paste or drop</div>
                                    <div class="text-sm text-gray-300 font-mono leading-relaxed">
                                        Jazz Night at Blue Note<br>
                                        Friday, March 15 at 8pm<br>
                                        Featuring the Sarah Johnson Trio<br>
                                        Tickets: $25 at the door
                                    </div>
                                </div>
                                <!-- Arrow -->
                                <div class="flex justify-center my-2">
                                    <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <!-- Output side -->
                                <div class="bg-gradient-to-br from-violet-500/20 to-indigo-500/20 rounded-2xl border border-violet-400/30 p-4 max-w-xs">
                                    <div class="text-xs text-violet-300 mb-2">Extracted</div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-gray-400">Name:</span><span class="text-white">Jazz Night</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">Date:</span><span class="text-white">Mar 15, 8:00 PM</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">Venue:</span><span class="text-white">Blue Note</span></div>
                                        <div class="flex justify-between"><span class="text-gray-400">Talent:</span><span class="text-white">Sarah Johnson Trio</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instant Translation -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-900/50 to-pink-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-500/20 text-fuchsia-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                        Translation
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Instant translation</h3>
                    <p class="text-gray-400 mb-6">Translate your entire schedule to English automatically. Perfect for international audiences.</p>

                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm font-medium">EN</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm font-medium">ES</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm font-medium">FR</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm font-medium">DE</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm font-medium">IT</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm font-medium">PT</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm font-medium">NL</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm font-medium">HE</span>
                        <span class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-sm font-medium">AR</span>
                    </div>
                </div>

                <!-- Venue & Talent Matching -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900/50 to-teal-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Smart Linking
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Auto-link venues & talent</h3>
                    <p class="text-gray-400 mb-6">AI matches parsed events to existing venues and performers in your schedule using fuzzy matching.</p>

                    <div class="bg-black/30 rounded-xl p-4 border border-white/10">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-white text-sm font-medium">Blue Note Jazz Club</div>
                                <div class="text-emerald-400 text-xs">Matched to existing venue</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-white text-sm font-medium">Sarah Johnson Trio</div>
                                <div class="text-emerald-400 text-xs">Matched to existing talent</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Formats (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900/50 to-blue-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Multiple Formats
                            </div>
                            <h3 class="text-3xl font-bold text-white mb-4">Any input works</h3>
                            <p class="text-gray-400 text-lg">Text, images, screenshots, flyers, social media posts. Drop it in and AI figures out the rest.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-black/30 rounded-xl p-4 border border-white/10 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="text-white text-sm font-medium">Text</div>
                            </div>
                            <div class="bg-black/30 rounded-xl p-4 border border-white/10 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="text-white text-sm font-medium">Images</div>
                            </div>
                            <div class="bg-black/30 rounded-xl p-4 border border-white/10 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                <div class="text-white text-sm font-medium">Flyers</div>
                            </div>
                            <div class="bg-black/30 rounded-xl p-4 border border-white/10 text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                <div class="text-white text-sm font-medium">Social Posts</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500">
                    Three steps to import any event.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-violet-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Paste or Drop</h3>
                    <p class="text-gray-600 text-sm">
                        Copy text from anywhere or drop an image of an event flyer or screenshot.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-violet-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">AI Extracts</h3>
                    <p class="text-gray-600 text-sm">
                        AI identifies event name, date, time, venue, performer, and description.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-violet-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Review & Save</h3>
                    <p class="text-gray-600 text-sm">
                        Verify the extracted details, make any edits, and add to your schedule.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Formats Section -->
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-violet-500/20 to-fuchsia-500/20 border border-violet-500/30 mb-8">
                    <svg class="w-10 h-10 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Supported image formats
                </h2>
                <p class="text-xl text-gray-400 mb-8 max-w-2xl mx-auto">
                    Upload event flyers, screenshots, or any image containing event details.
                </p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-2xl mx-auto">
                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                        <div class="text-2xl font-bold text-white mb-1">JPEG</div>
                        <p class="text-gray-400 text-sm">Photos & flyers</p>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                        <div class="text-2xl font-bold text-white mb-1">PNG</div>
                        <p class="text-gray-400 text-sm">Screenshots</p>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                        <div class="text-2xl font-bold text-white mb-1">GIF</div>
                        <p class="text-gray-400 text-sm">Animated graphics</p>
                    </div>
                    <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                        <div class="text-2xl font-bold text-white mb-1">WebP</div>
                        <p class="text-gray-400 text-sm">Modern format</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background blobs matching Calendar Sync page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-cyan-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/calendar-sync') }}" class="group block">
                <div class="bg-gradient-to-br from-blue-900/50 to-indigo-900/50 rounded-3xl border border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <!-- Text content -->
                        <div class="flex-1 text-center lg:text-left">
                            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-3 group-hover:text-blue-300 transition-colors">Calendar Sync</h3>
                            <p class="text-gray-400 text-lg mb-4">Two-way sync with Google Calendar. Changes flow in both directions automatically.</p>
                            <span class="inline-flex items-center text-blue-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Mini mockup: Bidirectional sync arrows -->
                        <div class="flex-shrink-0">
                            <div class="flex items-center gap-3">
                                <!-- Event Schedule box -->
                                <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-24">
                                    <div class="text-[10px] text-blue-300 mb-2 text-center">Event Schedule</div>
                                    <div class="space-y-1">
                                        <div class="h-1.5 bg-white/20 rounded"></div>
                                        <div class="h-1.5 bg-white/20 rounded w-3/4"></div>
                                        <div class="h-1.5 bg-white/20 rounded w-1/2"></div>
                                    </div>
                                </div>
                                <!-- Sync arrows -->
                                <div class="flex flex-col items-center gap-1">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                    <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                </div>
                                <!-- Google Calendar box -->
                                <div class="bg-white/10 rounded-xl border border-white/20 p-3 w-24">
                                    <div class="text-[10px] text-gray-300 mb-2 text-center">Google Calendar</div>
                                    <div class="space-y-1">
                                        <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                        <div class="h-1.5 bg-green-400/40 rounded w-3/4"></div>
                                        <div class="h-1.5 bg-yellow-400/40 rounded w-1/2"></div>
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
    <section class="relative bg-gradient-to-br from-violet-600 to-fuchsia-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Import events in seconds
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Stop typing event details manually. Let AI do the work for you.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-violet-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
