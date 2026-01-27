<x-marketing-layout>
    <x-slot name="title">Event Schedule for Circus & Acrobatics | Share Your Performances</x-slot>
    <x-slot name="description">Share your circus performances, sell tickets directly, and reach your audience with newsletters. No social media algorithms. Zero platform fees. Built for aerialists, acrobats, and circus performers.</x-slot>
    <x-slot name="keywords">circus schedule, aerialist calendar, acrobat booking, circus performer schedule, aerial show schedule, circus troupe management, sell circus tickets, performer newsletter</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-pink-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-rose-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg class="w-4 h-4 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                </svg>
                <span class="text-sm text-gray-300">For Circus Performers & Acrobats</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 leading-tight">
                Your performances. Your audience.<br>
                <span class="text-gradient-pink">No gatekeepers.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12">
                From training studios to international tours. One link for all your shows. Reach fans directly - no algorithm burying your content.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-pink-600 to-rose-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-pink-500/25">
                    Create your schedule
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Performance tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="px-3 py-1 rounded-full bg-pink-500/20 text-pink-300 text-xs font-medium border border-pink-500/30">Aerial</span>
                <span class="px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-medium border border-rose-500/30">Fire</span>
                <span class="px-3 py-1 rounded-full bg-fuchsia-500/20 text-fuchsia-300 text-xs font-medium border border-fuchsia-500/30">Acrobatics</span>
                <span class="px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 text-xs font-medium border border-purple-500/30">Juggling</span>
                <span class="px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-xs font-medium border border-violet-500/30">Stilt Walking</span>
                <span class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-medium border border-indigo-500/30">Contortion</span>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Newsletter (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-pink-900/50 to-rose-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/20 text-pink-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Newsletter
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-white mb-4">Announce shows directly to your fans</h3>
                            <p class="text-gray-400 text-lg mb-6">New show opening? Going on tour? Send beautiful promotional graphics directly to your fans' inbox. No algorithm gatekeeping. Unlike Facebook where you have to pay to reach your own followers, your message reaches everyone.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Tour announcements</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Show openings</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Special events</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-pink-500/20 to-rose-500/20 rounded-2xl border border-pink-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center text-white text-sm font-semibold">CA</div>
                                        <div>
                                            <div class="text-white font-semibold text-sm">Cirque Aerial</div>
                                            <div class="text-pink-300 text-xs">Summer Tour 2025!</div>
                                        </div>
                                    </div>
                                    <div class="bg-gradient-to-br from-pink-600/30 to-rose-600/30 rounded-xl p-3 border border-pink-400/20">
                                        <div class="text-center">
                                            <div class="text-white text-xs font-semibold mb-1">THIS WEEKEND</div>
                                            <div class="text-pink-300 text-sm font-bold">Aerial Dreams Show</div>
                                            <div class="text-gray-400 text-[10px] mt-1">Grand Theater - 8 PM</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticketing -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900/50 to-green-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Sell tickets. Keep everything.</h3>
                    <p class="text-gray-400 mb-6">Zero platform fees. Stripe goes directly to your account. QR check-in at the door.</p>

                    <div class="flex justify-center">
                        <div class="bg-emerald-500/20 rounded-xl border border-emerald-400/30 p-4 w-full max-w-[200px]">
                            <div class="text-center mb-3">
                                <div class="text-emerald-300 text-xs">You keep</div>
                                <div class="text-white text-3xl font-bold">100%</div>
                                <div class="text-gray-400 text-xs">of ticket sales</div>
                            </div>
                            <div class="border-t border-emerald-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Platform fee</span>
                                    <span class="text-emerald-400 font-semibold">$0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Troupe Management -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-900/50 to-orange-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Troupe
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Coordinate your ensemble</h3>
                    <p class="text-gray-400 mb-6">Invite performers, stage managers, and your booking agent. Everyone sees the schedule and can add performances.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/10">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-xs font-semibold">AL</div>
                            <div class="flex-1">
                                <div class="text-white text-sm">Alex</div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 text-[10px]">Lead</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white text-xs font-semibold">SM</div>
                            <div class="flex-1">
                                <div class="text-gray-300 text-sm">Sam</div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-300 text-[10px]">Agent</span>
                        </div>
                    </div>
                </div>

                <!-- Share Link (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900/50 to-blue-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                Share Link
                            </div>
                            <h3 class="text-3xl font-bold text-white mb-4">One link for all your performances</h3>
                            <p class="text-gray-400 text-lg">Add it to your website, social bios, or booking portfolio. Fans and venues see all your upcoming shows in one place.</p>
                        </div>
                        <div class="bg-black/30 rounded-2xl p-5 border border-white/10">
                            <div class="text-xs text-gray-500 mb-2">Your schedule link</div>
                            <div class="flex items-center gap-2 p-3 rounded-xl bg-indigo-500/20 border border-indigo-400/30">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <span class="text-white text-sm font-mono">eventschedule.com/yourtroupe</span>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <div class="flex-1 text-center p-2 rounded-lg bg-white/5">
                                    <div class="text-indigo-300 text-xs">Website</div>
                                </div>
                                <div class="flex-1 text-center p-2 rounded-lg bg-white/5">
                                    <div class="text-indigo-300 text-xs">Instagram</div>
                                </div>
                                <div class="flex-1 text-center p-2 rounded-lg bg-white/5">
                                    <div class="text-indigo-300 text-xs">Booking</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-900/50 to-indigo-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Rehearsals, shows & workshops</h3>
                    <p class="text-gray-400 mb-6">Two-way Google sync. Add a show or training session and it appears everywhere. Real-time webhook sync.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-white/20 rounded"></div>
                                <div class="h-1.5 bg-white/20 rounded w-3/4"></div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <div class="bg-white/10 rounded-xl border border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-300 mb-1 text-center">Google</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                <div class="h-1.5 bg-green-400/40 rounded w-3/4"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Venue Linking -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-900/50 to-purple-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Venue Sync
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Theaters, festivals & events</h3>
                    <p class="text-gray-400 mb-6">When venues add your performance to their calendar, it automatically appears on yours. One booking, both schedules.</p>

                    <div class="flex items-center justify-center gap-2">
                        <div class="bg-violet-500/20 rounded-lg border border-violet-400/30 p-2 w-16">
                            <div class="text-[10px] text-violet-300 text-center mb-1">Venue</div>
                            <div class="h-1.5 bg-white/20 rounded mb-1"></div>
                            <div class="h-1.5 bg-violet-400/40 rounded w-3/4"></div>
                        </div>
                        <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <div class="bg-white/10 rounded-lg border border-white/20 p-2 w-16">
                            <div class="text-[10px] text-gray-300 text-center mb-1">You</div>
                            <div class="h-1.5 bg-white/20 rounded mb-1"></div>
                            <div class="h-1.5 bg-pink-400/40 rounded w-3/4"></div>
                        </div>
                    </div>
                </div>

                <!-- Event Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-900/50 to-pink-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-500/20 text-fuchsia-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Promotional images</h3>
                    <p class="text-gray-400 mb-6">Auto-generate shareable graphics for social media. Perfect for Instagram and Facebook posts announcing your shows.</p>

                    <div class="flex justify-center">
                        <div class="relative w-32 h-32 bg-gradient-to-br from-fuchsia-500/30 to-pink-500/30 rounded-xl border border-fuchsia-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-pink-600/40 to-rose-600/40 rounded-lg flex flex-col items-center justify-center">
                                <div class="text-white text-[10px] font-semibold mb-1">TONIGHT</div>
                                <div class="text-fuchsia-300 text-xs font-bold">Aerial Show</div>
                                <div class="text-gray-400 text-[8px] mt-1">Grand Theater - 8PM</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-fuchsia-500 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Career Journey Section -->
    <section class="bg-[#0a0a0f] py-24 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    From training to touring
                </h2>
                <p class="text-xl text-gray-500">
                    Event Schedule grows with your circus career
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Training & Workshops -->
                <div class="bg-gradient-to-br from-[#1a0f17] to-[#0a0a0f] rounded-2xl p-6 border border-pink-900/20 hover:border-pink-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-pink-900/30 mb-4">
                        <svg class="w-6 h-6 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Training & workshops</h3>
                    <p class="text-gray-400 text-sm">Learning new skills and teaching workshops. Share your class schedule with students.</p>
                </div>

                <!-- Local Performances -->
                <div class="bg-gradient-to-br from-[#170f1a] to-[#0a0a0f] rounded-2xl p-6 border border-rose-900/20 hover:border-rose-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-900/30 mb-4">
                        <svg class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Local performances</h3>
                    <p class="text-gray-400 text-sm">Regular gigs at local venues and events. Build a following in your community.</p>
                </div>

                <!-- Festival Circuits -->
                <div class="bg-gradient-to-br from-[#15101a] to-[#0a0a0f] rounded-2xl p-6 border border-fuchsia-900/20 hover:border-fuchsia-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-fuchsia-900/30 mb-4">
                        <svg class="w-6 h-6 text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Festival circuits</h3>
                    <p class="text-gray-400 text-sm">Weekend festivals and special events. Fans follow to know when you're performing nearby.</p>
                </div>

                <!-- Touring Shows -->
                <div class="bg-gradient-to-br from-[#12101a] to-[#0a0a0f] rounded-2xl p-6 border border-violet-900/20 hover:border-violet-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-900/30 mb-4">
                        <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Touring shows</h3>
                    <p class="text-gray-400 text-sm">Multi-city tours and theatrical runs. One link shows fans everywhere when you're coming to their city.</p>
                </div>

                <!-- Corporate & Private Events -->
                <div class="bg-gradient-to-br from-[#1a1510] to-[#0a0a0f] rounded-2xl p-6 border border-amber-900/20 hover:border-amber-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-900/30 mb-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Corporate & private events</h3>
                    <p class="text-gray-400 text-sm">High-end private bookings. Show your availability to event planners and agencies.</p>
                </div>

                <!-- International Tours -->
                <div class="bg-gradient-to-br from-[#0f1520] to-[#0a0a0f] rounded-2xl p-6 border border-indigo-900/20 hover:border-indigo-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-900/30 mb-4">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">International tours</h3>
                    <p class="text-gray-400 text-sm">Global performances and residencies. Manage your schedule across time zones with ease.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Perfect for all types of circus performers
                </h2>
                <p class="text-xl text-gray-500">
                    Whether you're a solo aerialist or a touring troupe, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Aerialists -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-pink-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-pink-100 mb-4">
                        <svg class="w-6 h-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aerialists</h3>
                    <p class="text-gray-600 text-sm">Share your aerial silk, trapeze, and hoop performances. Let fans know where to catch your next show.</p>
                </div>

                <!-- Circus Troupes -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-rose-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 mb-4">
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Circus Troupes</h3>
                    <p class="text-gray-600 text-sm">Coordinate your ensemble's schedule and let audiences follow your collective performances.</p>
                </div>

                <!-- Fire Performers -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-orange-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-orange-100 mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Fire Performers</h3>
                    <p class="text-gray-600 text-sm">Promote your fire dancing, breathing, and spinning shows at festivals and events.</p>
                </div>

                <!-- Contortionists -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-fuchsia-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-fuchsia-100 mb-4">
                        <svg class="w-6 h-6 text-fuchsia-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Contortionists</h3>
                    <p class="text-gray-600 text-sm">Showcase your flexibility performances and build a dedicated following.</p>
                </div>

                <!-- Jugglers & Prop Artists -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-purple-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-100 mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Jugglers & Prop Artists</h3>
                    <p class="text-gray-600 text-sm">List your juggling, poi, and object manipulation shows and workshops.</p>
                </div>

                <!-- Stilt Walkers -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-violet-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-100 mb-4">
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Stilt Walkers</h3>
                    <p class="text-gray-600 text-sm">Share your larger-than-life performances at parades, festivals, and corporate events.</p>
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
                    Get your performance schedule online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Add Your Performances</h3>
                    <p class="text-gray-600 text-sm">
                        Import from Google Calendar or add shows manually. Set up ticket sales if you want.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Share Your Link</h3>
                    <p class="text-gray-600 text-sm">
                        Add your schedule to your website, Instagram bio, or booking portfolio.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Build Your Audience</h3>
                    <p class="text-gray-600 text-sm">
                        Fans follow your schedule and get notified about performances near them.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-pink-600 to-rose-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Your art. Your audience. No middlemen.
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Stop posting into the void. Fill your shows. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-pink-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Circus & Acrobatics",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Circus Performer Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your circus performances, sell tickets directly, and reach your audience with newsletters. Built for aerialists, acrobats, and circus performers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Direct newsletter announcements to fans",
            "Custom schedule URL for websites and booking portfolios",
            "Zero-fee ticket sales with door check-in",
            "Google Calendar sync for rehearsals, shows, workshops",
            "Venue auto-linking for theaters and festivals",
            "Troupe and ensemble collaboration",
            "Fan notifications for nearby performances"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-pink {
            background: linear-gradient(135deg, #ec4899, #f43f5e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</x-marketing-layout>
