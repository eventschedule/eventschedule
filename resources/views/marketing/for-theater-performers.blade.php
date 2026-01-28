<x-marketing-layout>
    <x-slot name="title">Event Schedule for Theater Performers | Share Show Runs & Sell Tickets</x-slot>
    <x-slot name="description">Share your theater productions with audiences everywhere. Build your email list to reach fans directly - no social media algorithms. Sell tickets to your shows with zero platform fees.</x-slot>
    <x-slot name="keywords">theater schedule, show run calendar, theater tickets, play performance schedule, acting schedule app, theater company calendar, stage production tickets, drama performance scheduling</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section - The Marquee -->
    <section class="relative bg-white dark:bg-[#0a0808] py-32 overflow-hidden">
        <!-- Marquee light bulbs effect - top border -->
        <div class="absolute top-0 left-0 right-0 h-2 flex justify-center">
            <div class="flex gap-4 px-8">
                @for ($i = 0; $i < 30; $i++)
                    <div class="w-2 h-2 rounded-full bg-amber-400 animate-marquee-bulb" style="animation-delay: {{ $i * 0.1 }}s;"></div>
                @endfor
            </div>
        </div>

        <!-- Curtain swoops on sides -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-0 left-0 w-48 h-full bg-gradient-to-r from-red-950/80 via-red-900/30 to-transparent"></div>
            <div class="absolute top-0 right-0 w-48 h-full bg-gradient-to-l from-red-950/80 via-red-900/30 to-transparent"></div>
            <!-- Subtle spotlight from above -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[500px] bg-gradient-to-b from-amber-500/10 via-amber-400/5 to-transparent rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 backdrop-blur-sm">
                <span class="text-sm text-gray-600 dark:text-gray-300">For Actors, Companies & Productions</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-light text-gray-900 dark:text-white mb-6 leading-tight tracking-tight">
                Your name<br>
                <span class="font-semibold bg-gradient-to-r from-amber-300 via-yellow-400 to-amber-300 bg-clip-text text-transparent">in lights</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-stone-400 max-w-2xl mx-auto mb-12 font-light">
                Share your productions, sell tickets, and let audiences know when the curtain rises.
            </p>

            <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-medium text-white bg-gradient-to-r from-amber-600 to-amber-500 rounded-full hover:scale-105 transition-all shadow-lg shadow-amber-900/30 hover:shadow-amber-700/40 border border-amber-400/30">
                Create your schedule
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>

            <!-- Genre tags -->
            <div class="flex flex-wrap justify-center gap-3 mt-12">
                <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300/80 text-sm border border-amber-300 dark:border-amber-700/30">Musical</span>
                <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300/80 text-sm border border-amber-300 dark:border-amber-700/30">Drama</span>
                <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300/80 text-sm border border-amber-300 dark:border-amber-700/30">Comedy</span>
                <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300/80 text-sm border border-amber-300 dark:border-amber-700/30">Improv</span>
                <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300/80 text-sm border border-amber-300 dark:border-amber-700/30">Fringe</span>
                <span class="px-3 py-1.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300/80 text-sm border border-amber-300 dark:border-amber-700/30">Community Theater</span>
            </div>
        </div>
    </section>

    <!-- Production Timeline Section (UNIQUE TO THEATER) -->
    <section class="bg-white dark:bg-[#0a0808] py-20 border-t border-amber-200 dark:border-amber-900/20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-light text-gray-900 dark:text-white mb-4">
                    From auditions to <span class="text-amber-400">closing night</span>
                </h2>
                <p class="text-gray-500 dark:text-stone-400 text-lg">Track every stage of your production journey</p>
            </div>

            <!-- Horizontal Timeline -->
            <div class="relative">
                <!-- Timeline line -->
                <div class="hidden lg:block absolute top-8 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-amber-600/50 to-transparent"></div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                    <!-- Auditions -->
                    <div class="relative text-center group">
                        <div class="w-16 h-16 mx-auto rounded-full bg-red-900/40 border-2 border-red-700/50 flex items-center justify-center mb-3 group-hover:border-red-500 transition-colors">
                            <svg class="w-7 h-7 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </div>
                        <div class="text-gray-900 dark:text-white text-sm font-medium">Auditions</div>
                        <div class="text-stone-500 text-xs mt-1">Open call</div>
                    </div>

                    <!-- Callbacks -->
                    <div class="relative text-center group">
                        <div class="w-16 h-16 mx-auto rounded-full bg-orange-900/40 border-2 border-orange-700/50 flex items-center justify-center mb-3 group-hover:border-orange-500 transition-colors">
                            <svg class="w-7 h-7 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-gray-900 dark:text-white text-sm font-medium">Callbacks</div>
                        <div class="text-stone-500 text-xs mt-1">Final selection</div>
                    </div>

                    <!-- Rehearsals -->
                    <div class="relative text-center group">
                        <div class="w-16 h-16 mx-auto rounded-full bg-yellow-900/40 border-2 border-yellow-700/50 flex items-center justify-center mb-3 group-hover:border-yellow-500 transition-colors">
                            <svg class="w-7 h-7 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="text-gray-900 dark:text-white text-sm font-medium">Rehearsals</div>
                        <div class="text-stone-500 text-xs mt-1">Build the show</div>
                    </div>

                    <!-- Tech Week -->
                    <div class="relative text-center group">
                        <div class="w-16 h-16 mx-auto rounded-full bg-lime-900/40 border-2 border-lime-700/50 flex items-center justify-center mb-3 group-hover:border-lime-500 transition-colors">
                            <svg class="w-7 h-7 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <div class="text-gray-900 dark:text-white text-sm font-medium">Tech Week</div>
                        <div class="text-stone-500 text-xs mt-1">Lights & sound</div>
                    </div>

                    <!-- Preview -->
                    <div class="relative text-center group">
                        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-900/40 border-2 border-emerald-700/50 flex items-center justify-center mb-3 group-hover:border-emerald-500 transition-colors">
                            <svg class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <div class="text-gray-900 dark:text-white text-sm font-medium">Preview</div>
                        <div class="text-stone-500 text-xs mt-1">First audience</div>
                    </div>

                    <!-- Opening Night -->
                    <div class="relative text-center group">
                        <div class="w-16 h-16 mx-auto rounded-full bg-amber-700/60 border-2 border-amber-500 flex items-center justify-center mb-3 shadow-lg shadow-amber-500/30 group-hover:shadow-amber-500/50 transition-all">
                            <svg class="w-7 h-7 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div class="text-amber-300 text-sm font-semibold">Opening Night</div>
                        <div class="text-amber-500/70 text-xs mt-1">Break a leg!</div>
                    </div>

                    <!-- The Run -->
                    <div class="relative text-center group">
                        <div class="w-16 h-16 mx-auto rounded-full bg-violet-900/40 border-2 border-violet-700/50 flex items-center justify-center mb-3 group-hover:border-violet-500 transition-colors">
                            <svg class="w-7 h-7 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="text-gray-900 dark:text-white text-sm font-medium">The Run</div>
                        <div class="text-stone-500 text-xs mt-1">Fill every seat</div>
                    </div>

                    <!-- Closing -->
                    <div class="relative text-center group">
                        <div class="w-16 h-16 mx-auto rounded-full bg-rose-900/40 border-2 border-rose-700/50 flex items-center justify-center mb-3 group-hover:border-rose-500 transition-colors">
                            <svg class="w-7 h-7 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <div class="text-gray-900 dark:text-white text-sm font-medium">Closing</div>
                        <div class="text-stone-500 text-xs mt-1">Take a bow</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- "Now Playing" Feature - Show Runs -->
    <section class="bg-gray-50 dark:bg-[#0c0a0a] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-900/30 border border-red-700/30 mb-6">
                    <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                    <span class="text-red-300 text-sm font-medium uppercase tracking-wider">Now Playing</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-light text-gray-900 dark:text-white mb-4">
                    Your entire run, <span class="text-red-400">one schedule</span>
                </h2>
                <p class="text-gray-500 dark:text-stone-400 text-lg max-w-2xl mx-auto">
                    Preview to closing night. Matinees and evening shows. Audiences see availability at a glance.
                </p>
            </div>

            <!-- Now Playing Card - Theater Lobby Style -->
            <div class="relative">
                <!-- Decorative frame -->
                <div class="absolute -inset-1 bg-gradient-to-b from-amber-600/20 via-red-900/20 to-amber-600/20 rounded-3xl blur-sm"></div>

                <div class="relative bg-gradient-to-b from-[#1a1515] to-[#0f0c0c] rounded-2xl border border-amber-800/30 overflow-hidden shadow-2xl">
                    <!-- Header bar like a theater marquee -->
                    <div class="bg-gradient-to-r from-red-950 via-red-900 to-red-950 px-6 py-4 border-b border-amber-900/30">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-amber-200 text-xs uppercase tracking-widest mb-1">Riverside Players presents</div>
                                <div class="text-white text-2xl font-semibold">A Midsummer Night's Dream</div>
                            </div>
                            <div class="text-right">
                                <div class="text-stone-400 text-xs">March 7 - 22</div>
                                <div class="text-amber-400 text-sm">8 Performances</div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance list -->
                    <div class="p-6 space-y-3">
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-violet-500/10 border border-violet-500/20 hover:bg-violet-500/15 transition-colors">
                            <div class="text-center w-14 flex-shrink-0">
                                <div class="text-violet-300 text-xs uppercase">Fri</div>
                                <div class="text-white text-xl font-semibold">Mar 7</div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-white font-medium">Preview Night</span>
                                    <span class="px-2 py-0.5 rounded-full bg-violet-500/20 text-violet-300 text-xs">Discounted</span>
                                </div>
                                <div class="text-stone-500 text-sm">7:30 PM</div>
                            </div>
                            <div class="text-right">
                                <div class="text-violet-300 font-medium">$15</div>
                                <div class="text-stone-500 text-xs">42 left</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500/15 transition-colors relative overflow-hidden">
                            <div class="absolute top-0 right-0 px-2 py-0.5 bg-amber-500 text-black text-xs font-semibold rounded-bl">OPENING</div>
                            <div class="text-center w-14 flex-shrink-0">
                                <div class="text-amber-300 text-xs uppercase">Sat</div>
                                <div class="text-white text-xl font-semibold">Mar 8</div>
                            </div>
                            <div class="flex-1">
                                <div class="text-white font-medium">Opening Night</div>
                                <div class="text-stone-500 text-sm">7:30 PM - Post-show reception</div>
                            </div>
                            <div class="text-right">
                                <div class="text-amber-300 font-medium">$35</div>
                                <div class="text-amber-500 text-xs">12 left</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors">
                            <div class="text-center w-14 flex-shrink-0">
                                <div class="text-stone-400 text-xs uppercase">Sun</div>
                                <div class="text-white text-xl font-semibold">Mar 9</div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-white font-medium">Matinee</span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs">Family-friendly</span>
                                </div>
                                <div class="text-stone-500 text-sm">2:00 PM</div>
                            </div>
                            <div class="text-right">
                                <div class="text-white font-medium">$25</div>
                                <div class="text-stone-500 text-xs">68 left</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors">
                            <div class="text-center w-14 flex-shrink-0">
                                <div class="text-stone-400 text-xs uppercase">Thu-Sat</div>
                                <div class="text-white text-lg font-semibold">Mar 13-15</div>
                            </div>
                            <div class="flex-1">
                                <div class="text-white font-medium">Evening Performances</div>
                                <div class="text-stone-500 text-sm">7:30 PM nightly</div>
                            </div>
                            <div class="text-right">
                                <div class="text-white font-medium">$25</div>
                                <div class="text-stone-500 text-xs">Available</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/15 transition-colors relative overflow-hidden">
                            <div class="absolute top-0 right-0 px-2 py-0.5 bg-rose-600 text-white text-xs font-semibold rounded-bl">FINAL</div>
                            <div class="text-center w-14 flex-shrink-0">
                                <div class="text-rose-300 text-xs uppercase">Sat</div>
                                <div class="text-white text-xl font-semibold">Mar 22</div>
                            </div>
                            <div class="flex-1">
                                <div class="text-white font-medium">Closing Night</div>
                                <div class="text-stone-500 text-sm">7:30 PM - Cast celebration</div>
                            </div>
                            <div class="text-right">
                                <div class="text-rose-300 font-medium">SOLD OUT</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Season Announcement Section (UNIQUE TO THEATER) -->
    <section class="bg-white dark:bg-[#0a0808] py-24 border-t border-amber-200 dark:border-amber-900/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/10 text-amber-300 text-sm font-medium mb-6 border border-amber-500/20">
                        Season Announcements
                    </div>
                    <h2 class="text-4xl md:text-5xl font-light text-gray-900 dark:text-white mb-6 leading-tight">
                        Announce your entire<br>
                        <span class="text-amber-300">season at once</span>
                    </h2>
                    <p class="text-lg text-gray-500 dark:text-stone-400 mb-8 leading-relaxed">
                        Theater companies plan seasons in advance. Add all your upcoming productions to one schedule. Subscribers get notified of your full season - and can plan which shows to see.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-stone-300">Multiple productions on one schedule</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-stone-300">Season subscribers get first notice</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-stone-300">Build anticipation months ahead</span>
                        </li>
                    </ul>
                </div>

                <!-- Season mockup -->
                <div class="relative">
                    <div class="bg-gradient-to-br from-[#1a1515] to-[#0f0c0c] rounded-2xl border border-amber-900/30 p-6 shadow-2xl">
                        <div class="text-center mb-6 pb-4 border-b border-amber-900/20">
                            <div class="text-amber-400 text-xs uppercase tracking-widest mb-1">Riverside Players</div>
                            <div class="text-white text-2xl font-light">2025-26 Season</div>
                        </div>

                        <div class="space-y-4">
                            <div class="p-4 rounded-xl bg-gradient-to-r from-violet-900/30 to-transparent border-l-2 border-violet-500">
                                <div class="text-violet-300 text-xs uppercase tracking-wide mb-1">October 2025</div>
                                <div class="text-white font-medium">The Crucible</div>
                                <div class="text-stone-500 text-sm">Arthur Miller</div>
                            </div>

                            <div class="p-4 rounded-xl bg-gradient-to-r from-emerald-900/30 to-transparent border-l-2 border-emerald-500">
                                <div class="text-emerald-300 text-xs uppercase tracking-wide mb-1">December 2025</div>
                                <div class="text-white font-medium">A Christmas Carol</div>
                                <div class="text-stone-500 text-sm">Charles Dickens, adapted</div>
                            </div>

                            <div class="p-4 rounded-xl bg-gradient-to-r from-amber-900/30 to-transparent border-l-2 border-amber-500">
                                <div class="text-amber-300 text-xs uppercase tracking-wide mb-1">March 2026</div>
                                <div class="text-white font-medium">A Midsummer Night's Dream</div>
                                <div class="text-stone-500 text-sm">William Shakespeare</div>
                            </div>

                            <div class="p-4 rounded-xl bg-gradient-to-r from-rose-900/30 to-transparent border-l-2 border-rose-500">
                                <div class="text-rose-300 text-xs uppercase tracking-wide mb-1">May 2026</div>
                                <div class="text-white font-medium">Sweeney Todd</div>
                                <div class="text-stone-500 text-sm">Stephen Sondheim</div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-amber-900/20 text-center">
                            <span class="text-amber-400 text-sm">Season subscriptions available</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid for Features -->
    <section class="bg-gray-50 dark:bg-[#0c0a0a] py-24 border-t border-amber-200 dark:border-amber-900/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-light text-gray-900 dark:text-white mb-4">
                    Everything you need to <span class="text-amber-400">fill the house</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Zero-Fee Ticketing (spans 2 cols) -->
                <div class="lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-100 to-red-100 dark:from-red-950/50 dark:to-red-900/30 border border-red-200 dark:border-red-800/30 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Zero-Fee Ticketing
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-light text-gray-900 dark:text-white mb-4">Fill every seat, keep every dollar</h3>
                            <p class="text-gray-500 dark:text-stone-400 text-lg mb-6">Sell tickets directly through your schedule. Connect Stripe and you're ready to go. No platform fees - just payment processing.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-stone-300 text-sm">Preview discounts</span>
                                <span class="px-3 py-1 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-stone-300 text-sm">Opening night premium</span>
                                <span class="px-3 py-1 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-stone-300 text-sm">Student rush</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-5 max-w-xs">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-300 dark:border-white/10">
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-medium">Orchestra</div>
                                            <div class="text-red-400 text-xs">24 remaining</div>
                                        </div>
                                        <div class="text-xl font-semibold text-gray-900 dark:text-white">$35</div>
                                    </div>
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-red-500/20 border border-red-400/30">
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-medium">Balcony</div>
                                            <div class="text-red-300 text-xs">48 remaining</div>
                                        </div>
                                        <div class="text-xl font-semibold text-gray-900 dark:text-white">$25</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email List -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-amber-100 dark:from-amber-950/50 dark:to-amber-900/30 border border-amber-200 dark:border-amber-800/30 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Newsletter
                    </div>
                    <h3 class="text-2xl font-light text-gray-900 dark:text-white mb-3">Build your audience</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-6">Fans subscribe directly. New production? Send an email. No algorithm decides who sees it.</p>

                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="absolute -top-1 -right-1 w-5 h-5 bg-amber-400 rounded-full flex items-center justify-center">
                                <span class="text-black text-[10px] font-bold">!</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cast & Crew -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-950/50 dark:to-violet-900/30 border border-violet-200 dark:border-violet-800/30 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Company
                    </div>
                    <h3 class="text-2xl font-light text-gray-900 dark:text-white mb-3">Coordinate your ensemble</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-6">Invite cast, crew, and stage management. Everyone sees the schedule. Changes sync instantly.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center text-white text-xs font-semibold">SM</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm">Stage Manager</div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded bg-violet-500/20 text-violet-300 text-[10px]">Admin</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-xs font-semibold">JK</div>
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-stone-300 text-sm">Cast Member</div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 text-[10px]">View</span>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-950/50 dark:to-emerald-900/30 border border-emerald-200 dark:border-emerald-800/30 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar
                    </div>
                    <h3 class="text-2xl font-light text-gray-900 dark:text-white mb-3">Sync with Google Calendar</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-6">Two-way sync keeps rehearsals, tech week, and performances updated everywhere.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-emerald-500/20 rounded-xl border border-emerald-400/30 p-3 w-20">
                            <div class="text-[10px] text-emerald-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-white/20 rounded"></div>
                                <div class="h-1.5 bg-white/20 rounded w-3/4"></div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-600 dark:text-stone-300 mb-1 text-center">Google</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                <div class="h-1.5 bg-green-400/40 rounded w-3/4"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Virtual Performances -->
                <a href="{{ marketing_url('/online-events') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-blue-100 dark:from-indigo-950/50 dark:to-indigo-900/30 border border-indigo-200 dark:border-indigo-800/30 p-8 hover:scale-[1.02] transition-all">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Livestream
                    </div>
                    <h3 class="text-2xl font-light text-gray-900 dark:text-white mb-3 group-hover:text-indigo-300 transition-colors">Stream performances worldwide</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-6">Reach audiences who can't make it to the theater. Sell tickets to your stream.</p>

                    <div class="flex justify-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500/30 to-blue-500/30 rounded-xl flex items-center justify-center border border-indigo-400/30">
                            <svg class="w-6 h-6 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <span class="inline-flex items-center text-indigo-400 text-sm font-medium mt-4 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Promo Graphics -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-950/50 dark:to-orange-900/30 border border-orange-200 dark:border-orange-800/30 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-light text-gray-900 dark:text-white mb-3">Share-ready images</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-6">Auto-generate promotional graphics for social media. Announce your production instantly.</p>

                    <div class="flex justify-center">
                        <div class="relative w-28 h-28 bg-gradient-to-br from-orange-500/30 to-red-500/30 rounded-xl border border-orange-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-red-900/60 to-amber-900/60 rounded-lg flex flex-col items-center justify-center">
                                <div class="text-gray-900 dark:text-white text-[8px] font-medium mb-0.5">NOW PLAYING</div>
                                <div class="text-amber-300 text-xs font-semibold text-center">A Midsummer Night's Dream</div>
                                <div class="text-gray-500 dark:text-stone-400 text-[8px] mt-1">Mar 7-22</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-orange-500 rounded-full flex items-center justify-center">
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

    <!-- Perfect For Section - 6 Theater Sub-types -->
    <section class="bg-stone-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-light text-stone-900 dark:text-white mb-4">
                    Built for every stage
                </h2>
                <p class="text-lg text-stone-500 dark:text-gray-400">
                    From Broadway-style productions to black box experiments.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Musical Theater -->
                <x-sub-audience-card
                    name="Musical Theater"
                    description="Broadway-style musicals, revivals, original works, cabaret. Song, dance, and spectacle."
                    icon-color="amber"
                    blog-slug="for-musical-theater-performers"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🎵</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Drama & Straight Plays -->
                <x-sub-audience-card
                    name="Drama & Straight Plays"
                    description="Classic and contemporary dramas, tragedies, period pieces. The power of the spoken word."
                    icon-color="red"
                    blog-slug="for-drama-actors"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🎭</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Community Theater -->
                <x-sub-audience-card
                    name="Community Theater"
                    description="Local productions, volunteer casts, neighborhood playhouses. Theater for everyone."
                    icon-color="emerald"
                    blog-slug="for-community-theater-performers"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🏠</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Improv & Sketch -->
                <x-sub-audience-card
                    name="Improv & Sketch"
                    description="Comedy troupes, improv nights, sketch shows, variety acts. Yes, and..."
                    icon-color="violet"
                    blog-slug="for-improv-sketch-performers"
                >
                    <x-slot:icon>
                        <span class="text-2xl">😂</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Experimental & Fringe -->
                <x-sub-audience-card
                    name="Experimental & Fringe"
                    description="Avant-garde, site-specific, immersive theater, devised work. Pushing boundaries."
                    icon-color="cyan"
                    blog-slug="for-experimental-fringe-theater"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🔮</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Children's & Youth Theater -->
                <x-sub-audience-card
                    name="Children's & Youth Theater"
                    description="Family-friendly shows, school productions, youth ensembles. Inspiring the next generation."
                    icon-color="pink"
                    blog-slug="for-childrens-youth-theater"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🌟</span>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works - Playbill Style -->
    <section class="bg-stone-100 dark:bg-[#0a0a0f] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-light text-stone-900 dark:text-white mb-4">
                    From sign-up to sold-out
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="relative bg-white dark:bg-[#12121a] rounded-2xl p-8 border border-stone-200 dark:border-white/10 shadow-sm text-center">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-amber-600 text-white text-sm font-bold rounded-full flex items-center justify-center shadow-lg">
                            1
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2">Act One</div>
                        <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-3">Add Your Production</h3>
                        <p class="text-stone-500 dark:text-gray-400 text-sm">
                            Create your show run with all performance dates. Preview to closing night.
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative bg-white dark:bg-[#12121a] rounded-2xl p-8 border border-stone-200 dark:border-white/10 shadow-sm text-center">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-amber-600 text-white text-sm font-bold rounded-full flex items-center justify-center shadow-lg">
                            2
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2">Act Two</div>
                        <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-3">Share Your Schedule</h3>
                        <p class="text-stone-500 dark:text-gray-400 text-sm">
                            One link for your website, programs, and social bios. Always current.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative bg-white dark:bg-[#12121a] rounded-2xl p-8 border border-stone-200 dark:border-white/10 shadow-sm text-center">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-amber-600 text-white text-sm font-bold rounded-full flex items-center justify-center shadow-lg">
                            3
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2">Act Three</div>
                        <h3 class="text-lg font-semibold text-stone-900 dark:text-white mb-3">Fill Every Seat</h3>
                        <p class="text-stone-500 dark:text-gray-400 text-sm">
                            Audiences follow you and get notified about new productions.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section - "Take a Bow" -->
    <section class="relative bg-white dark:bg-[#0a0808] py-24 overflow-hidden">
        <!-- Spotlight effect -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[500px] h-[400px] bg-gradient-to-b from-amber-500/15 via-amber-400/5 to-transparent rounded-full blur-3xl"></div>
        </div>

        <!-- Curtain sides -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-32 h-full bg-gradient-to-r from-red-950/60 to-transparent"></div>
            <div class="absolute top-0 right-0 w-32 h-full bg-gradient-to-l from-red-950/60 to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-stone-500 text-sm uppercase tracking-wider mb-6">Free forever</p>

            <h2 class="text-4xl md:text-5xl font-light text-gray-900 dark:text-white mb-6 leading-tight">
                The show must go on<br>
                <span class="bg-gradient-to-r from-amber-300 via-yellow-400 to-amber-300 bg-clip-text text-transparent">and audiences need to find it</span>
            </h2>

            <p class="text-lg text-gray-500 dark:text-stone-400 mb-10 max-w-xl mx-auto">
                Join theater companies and performers who've simplified sharing their schedule.
            </p>

            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-medium text-white bg-gradient-to-r from-amber-600 to-amber-500 rounded-full hover:scale-105 transition-all shadow-lg shadow-amber-900/30 hover:shadow-amber-700/40 border border-amber-400/30">
                Take the Stage
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
        "name": "Event Schedule for Theater Performers",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Theater Production Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your theater productions with audiences everywhere. Build your email list to reach fans directly. Sell tickets to your shows with zero platform fees.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Show run scheduling for multi-night productions",
            "Zero-fee ticket sales for performances",
            "Direct newsletter communication with audiences",
            "Virtual performance and livestream support",
            "Two-way Google Calendar sync",
            "Cast and crew coordination",
            "Auto-generated promotional graphics",
            "Multiple ticket types per performance",
            "Season announcement support"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        @keyframes marquee-bulb {
            0%, 100% {
                opacity: 0.3;
                box-shadow: 0 0 2px rgba(251, 191, 36, 0.3);
            }
            50% {
                opacity: 1;
                box-shadow: 0 0 8px rgba(251, 191, 36, 0.8), 0 0 12px rgba(251, 191, 36, 0.4);
            }
        }

        .animate-marquee-bulb {
            animation: marquee-bulb 1.5s ease-in-out infinite;
        }
    </style>
</x-marketing-layout>
