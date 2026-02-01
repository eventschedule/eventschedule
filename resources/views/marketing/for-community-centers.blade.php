<x-marketing-layout>
    <x-slot name="title">Event Schedule for Community Centers | Program Calendar</x-slot>
    <x-slot name="description">Reach your members directly. Manage program registration, room booking, and community events. Email your community directly. Free forever.</x-slot>
    <x-slot name="keywords">community center software, recreation center calendar, program registration, community event management, member communication, room booking software, class registration, community center scheduling</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Community Centers</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background with teal/cyan tones -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <!-- Emerald accent -->
            <div class="absolute top-40 right-1/3 w-[200px] h-[200px] bg-emerald-500/10 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For Community Centers & Recreation Facilities</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Where your community<br>
                <span class="text-gradient-teal">comes together.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Stop hoping members check your Facebook page. Email your community directly - no algorithm decides who sees your programs.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-teal-500/25">
                    Create your center's calendar
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Community center type tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-xs font-medium border border-teal-200 dark:border-teal-500/30">Recreation Center</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-500/30">Senior Center</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-xs font-medium border border-emerald-200 dark:border-emerald-500/30">Youth Center</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300 text-xs font-medium border border-green-200 dark:border-green-500/30">Cultural Center</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-500/30">Neighborhood Center</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-xs font-medium border border-indigo-200 dark:border-indigo-500/30">Faith-Based Center</span>
            </div>
        </div>
    </section>

    <!-- The Community Week Section - UNIQUE TO COMMUNITY CENTERS -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    The community week, organized
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Senior fitness Monday morning. Youth basketball Tuesday evening. Art classes Wednesday. Keep every program visible and every room booked.
                </p>
            </div>

            <!-- Weekly Calendar Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                <!-- Monday -->
                <div class="bg-gradient-to-br from-teal-900/40 to-cyan-900/40 rounded-xl border border-teal-500/20 p-4 relative overflow-hidden group hover:border-teal-500/40 transition-colors">
                    <div class="text-teal-300 text-xs font-semibold tracking-wider uppercase mb-3">Mon</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-teal-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Senior Fitness</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-cyan-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Yoga Class</span>
                        </div>
                    </div>
                </div>

                <!-- Tuesday -->
                <div class="bg-gradient-to-br from-emerald-900/40 to-green-900/40 rounded-xl border border-emerald-500/20 p-4 relative overflow-hidden group hover:border-emerald-500/40 transition-colors">
                    <div class="text-emerald-300 text-xs font-semibold tracking-wider uppercase mb-3">Tue</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Youth Basketball</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-green-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Computer Lab</span>
                        </div>
                    </div>
                </div>

                <!-- Wednesday -->
                <div class="bg-gradient-to-br from-violet-900/40 to-purple-900/40 rounded-xl border border-violet-500/20 p-4 relative overflow-hidden group hover:border-violet-500/40 transition-colors">
                    <div class="text-violet-300 text-xs font-semibold tracking-wider uppercase mb-3">Wed</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-violet-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Art Classes</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-purple-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Book Club</span>
                        </div>
                    </div>
                </div>

                <!-- Thursday -->
                <div class="bg-gradient-to-br from-amber-900/40 to-orange-900/40 rounded-xl border border-amber-500/20 p-4 relative overflow-hidden group hover:border-amber-500/40 transition-colors">
                    <div class="text-amber-300 text-xs font-semibold tracking-wider uppercase mb-3">Thu</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Town Hall</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Dance Class</span>
                        </div>
                    </div>
                </div>

                <!-- Friday -->
                <div class="bg-gradient-to-br from-rose-900/40 to-pink-900/40 rounded-xl border border-rose-500/20 p-4 relative overflow-hidden group hover:border-rose-500/40 transition-colors">
                    <div class="text-rose-300 text-xs font-semibold tracking-wider uppercase mb-3">Fri</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-rose-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Movie Night</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-pink-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Teen Program</span>
                        </div>
                    </div>
                </div>

                <!-- Saturday -->
                <div class="bg-gradient-to-br from-sky-900/40 to-blue-900/40 rounded-xl border border-sky-500/20 p-4 relative overflow-hidden group hover:border-sky-500/40 transition-colors">
                    <div class="text-sky-300 text-xs font-semibold tracking-wider uppercase mb-3">Sat</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-sky-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Kids Camp</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Family Event</span>
                        </div>
                    </div>
                </div>

                <!-- Sunday -->
                <div class="bg-gradient-to-br from-indigo-900/40 to-violet-900/40 rounded-xl border border-indigo-500/20 p-4 relative overflow-hidden group hover:border-indigo-500/40 transition-colors">
                    <div class="text-indigo-300 text-xs font-semibold tracking-wider uppercase mb-3">Sun</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-400"></div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">Open Gym</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-violet-400"></div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Craft Fair</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recurring programs note -->
            <div class="mt-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                    <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">All recurring programs in one place - members see what's happening every day</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Program Announcements - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-teal-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Newsletter
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">New program? Your members are first to know.</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Summer camp registration, new fitness classes, special events - one click emails everyone who signed up. No algorithm decides who sees your announcements.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Your community, direct reach</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">No middleman</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-teal-950 to-cyan-950 rounded-2xl border border-teal-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white text-sm font-medium">Summer Camp Registration</div>
                                            <div class="text-teal-700 dark:text-teal-300 text-xs">Sending to 2,341 members...</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Sports Camp (Ages 8-12)</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Art Camp (Ages 6-10)</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-teal-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Science Camp (Ages 10-14)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Booking -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-violet-100 dark:from-indigo-900 dark:to-violet-900 border border-indigo-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Booking Inbox
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Facility rental requests come to you</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Groups submit requests online. Review, approve, or decline from your dashboard.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-500/20 border border-indigo-400/30">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white text-xs font-semibold">PTA</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Lincoln PTA</div>
                                <div class="text-indigo-700 dark:text-indigo-300 text-xs">Meeting Room &bull; Oct 15</div>
                            </div>
                            <div class="flex gap-1">
                                <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-100 dark:bg-white/5">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center text-white text-xs font-semibold">SC</div>
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Scout Troop 42</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">Gym &bull; Oct 22</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Class Registration -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Registration
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Fill your classes and workshops</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Sell registrations, manage capacity, scan tickets at the door. Works for camps, classes, and special events.</p>

                    <!-- Class registration visual -->
                    <div class="flex justify-center">
                        <div class="bg-gradient-to-br from-emerald-100 to-green-50 rounded-xl border border-emerald-300/50 p-4 w-44 shadow-lg transform -rotate-2 hover:rotate-0 transition-transform">
                            <div class="text-center">
                                <div class="text-emerald-800 text-[10px] tracking-widest uppercase">Registration</div>
                                <div class="text-emerald-900 text-sm font-serif font-semibold mt-1">Pottery Class</div>
                                <div class="text-emerald-700 text-xl font-bold mt-2">$45<span class="text-xs font-normal">/session</span></div>
                                <div class="text-emerald-600 text-[10px] mt-1">Wednesdays &bull; 6 PM</div>
                                <div class="text-emerald-500 text-[9px] mt-1">8 spots remaining</div>
                                <div class="mt-3 w-12 h-12 bg-emerald-900/10 rounded-lg mx-auto p-1.5">
                                    <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%23065f46%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Community Calendar (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-sky-100 dark:from-cyan-900 dark:to-sky-900 border border-cyan-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Public Calendar
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Everything happening at your center</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Public events, recurring programs, meetings, and classes - all in one beautiful calendar. Embed on your website or share the link.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Mobile-friendly</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Embeddable</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Custom branding</span>
                            </div>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                            <div class="text-center mb-3">
                                <div class="text-gray-900 dark:text-white font-semibold">Riverside Community Center</div>
                                <div class="text-cyan-700 dark:text-cyan-300 text-sm">October 2024</div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-cyan-500/20 border border-cyan-400/30">
                                    <div class="text-cyan-700 dark:text-cyan-300 text-xs font-mono w-10">Oct 5</div>
                                    <span class="text-gray-900 dark:text-white text-sm">Fall Festival</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-10">Oct 8</div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Senior Lunch</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-10">Oct 12</div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Town Hall Meeting</span>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-10">Oct 15</div>
                                    <span class="text-gray-600 dark:text-gray-300 text-sm">Youth Basketball</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multi-Room Scheduling -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Spaces
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Every room at a glance</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Gym, meeting rooms, activity rooms, outdoor spaces. Filter by room and avoid scheduling conflicts.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-amber-500/20 border border-amber-500/30">
                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Gymnasium</span>
                            <span class="ml-auto text-amber-700 dark:text-amber-300 text-xs">12 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Meeting Room A</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">8 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Activity Room</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">15 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Outdoor Pavilion</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">4 events</span>
                        </div>
                    </div>
                </div>

                <!-- QR Check-in -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-violet-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        Check-in
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Track attendance easily</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Scan tickets at the door for classes and events. Know exactly who showed up.</p>

                    <div class="flex justify-center">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-white rounded-xl p-2 mx-auto mb-2">
                                <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                            </div>
                            <div class="text-violet-700 dark:text-violet-300 text-xs font-medium">Scan with any smartphone</div>
                        </div>
                    </div>
                </div>

                <!-- Event Graphics - BOTTOM RIGHT -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-pink-100 dark:from-rose-900 dark:to-pink-900 border border-rose-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Ready for social media</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Auto-generate promotional images for your programs. Download and share in seconds.</p>

                    <div class="flex justify-center">
                        <div class="relative w-32 h-32 bg-gradient-to-br from-rose-500/30 to-pink-500/30 rounded-xl border border-rose-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-teal-600/40 to-cyan-600/40 rounded-lg flex flex-col items-center justify-center">
                                <div class="text-gray-900 dark:text-white text-[10px] font-semibold mb-1">THIS SATURDAY</div>
                                <div class="text-rose-200 text-xs font-bold">Fall Festival</div>
                                <div class="text-gray-500 dark:text-gray-400 text-[8px] mt-1">Free admission</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-rose-500 rounded-full flex items-center justify-center">
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

    <!-- Virtual Events Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-indigo-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-violet-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-indigo-100 to-violet-100 dark:from-indigo-900 dark:to-violet-900 rounded-3xl border border-indigo-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Online Events
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors">Reach members who can't come in person</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">Virtual town halls, online fitness classes, livestreamed community events. Members can participate from anywhere.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Virtual meetings</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Online classes</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Livestreams</span>
                            </div>
                            <span class="inline-flex items-center text-indigo-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-6 w-52">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs">Virtual Town Hall</span>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                        <span class="text-red-400 text-[10px]">LIVE</span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-indigo-600/30 to-violet-600/30 rounded-lg p-4 text-center mb-3">
                                    <div class="text-2xl mb-1">&#127970;</div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">Budget Meeting</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">City Council</div>
                                </div>
                                <div class="flex items-center justify-center gap-2 text-gray-500 dark:text-gray-400 text-xs">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>124 watching</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for all types of community centers
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    From recreation facilities to neighborhood gathering spaces.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Recreation Centers -->
                <x-sub-audience-card
                    name="Recreation Centers"
                    description="Sports leagues, fitness classes, camps, and recreational programs. Keep your community active and engaged."
                    icon-color="teal"
                    blog-slug="for-recreation-centers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Senior Centers -->
                <x-sub-audience-card
                    name="Senior Centers"
                    description="Programs, social events, wellness activities, and meals. Keep seniors connected and supported."
                    icon-color="cyan"
                    blog-slug="for-senior-centers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Youth Centers -->
                <x-sub-audience-card
                    name="Youth Centers"
                    description="After-school programs, summer camps, teen activities. Give young people a safe place to grow."
                    icon-color="emerald"
                    blog-slug="for-youth-centers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Cultural Centers -->
                <x-sub-audience-card
                    name="Cultural Centers"
                    description="Heritage events, language classes, cultural celebrations. Preserve and share traditions with your community."
                    icon-color="violet"
                    blog-slug="for-cultural-centers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Neighborhood Centers -->
                <x-sub-audience-card
                    name="Neighborhood Centers"
                    description="Local meetings, block parties, civic events, and community gatherings. Strengthen local bonds."
                    icon-color="amber"
                    blog-slug="for-neighborhood-centers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Faith-Based Centers -->
                <x-sub-audience-card
                    name="Faith-Based Centers"
                    description="Congregation events, community outreach, classes, and fellowship gatherings. Bring people together."
                    icon-color="indigo"
                    blog-slug="for-faith-based-centers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
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
                    Get your community calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-teal-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your center</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Sign up and add your center details. Set up rooms and spaces if you have multiple areas.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-teal-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Post your programs</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Add classes, events, meetings, and rentals. Set up recurring programs once.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-teal-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Grow your following</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Members follow your calendar and get direct updates. No algorithm between you.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-teal-600 to-cyan-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>
        <!-- Emerald accent glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-emerald-500/10 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Stop hoping members check Facebook
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Email your community directly. Fill your programs. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-teal-700 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Community Centers",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Community Center Management Software",
        "operatingSystem": "Web",
        "description": "Reach your members directly without algorithms. Manage program registration, room booking, and community events. Email your community directly and fill your classes. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Program announcement newsletters",
            "Class and workshop registration",
            "Multi-room scheduling",
            "Facility rental request management",
            "Community event calendar",
            "QR code check-in",
            "Virtual event support",
            "Auto-generated social media graphics"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-teal {
            background: linear-gradient(135deg, #14b8a6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</x-marketing-layout>
