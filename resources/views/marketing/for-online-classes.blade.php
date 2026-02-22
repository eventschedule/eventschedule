<x-marketing-layout>
    <x-slot name="title">Event Schedule for Online Classes | Schedule, Sell & Manage Virtual Classes</x-slot>
    <x-slot name="description">Schedule and sell online classes with built-in registration, recurring sessions, student email notifications, and payment processing. Works with Zoom, Google Meet, and any platform. Zero platform fees.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Online Classes</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Online Classes",
        "description": "Schedule and sell online classes with built-in registration, recurring sessions, student email notifications, and payment processing. Works with Zoom, Google Meet, and any platform. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Online Instructors"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section - Mesh Gradient -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute bottom-0 left-[-20%] w-[70%] h-[70%] bg-emerald-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-0 right-[-10%] w-[50%] h-[60%] bg-teal-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] h-[40%] bg-green-600/10 rounded-full blur-[100px]"></div>
        </div>

        <!-- Grid overlay for texture -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 backdrop-blur-sm">
                <div class="relative">
                    <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium tracking-wide">For Instructors, Coaches & Educators</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Teach online classes your way.<br>
                <span class="classes-glow-text">No platform fees.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                From yoga sessions to coding bootcamps. Schedule recurring classes, manage enrollments, and get paid directly. Your students, your schedule.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-lg shadow-emerald-500/25">
                    Create your class schedule
                    <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-8 text-base text-gray-400 dark:text-gray-500 max-w-2xl mx-auto">
                The online class scheduling platform with built-in student registration, recurring session management, email notifications, and payment processing for instructors and educators.
            </p>

            <!-- Type tags -->
            <div class="mt-14 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium border border-emerald-200 dark:border-emerald-800/50">Yoga & Fitness</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300 text-xs font-medium border border-teal-200 dark:border-teal-800/50">Cooking Classes</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 text-xs font-medium border border-green-200 dark:border-green-800/50">Art & Music Lessons</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-800/50">Language Courses</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium border border-emerald-200 dark:border-emerald-800/50">Coding Bootcamps</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300 text-xs font-medium border border-teal-200 dark:border-teal-800/50">Tutoring</span>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-emerald-400 mb-2">62%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">of adults have taken an online class</div>
                </div>
                <div class="p-6 border-x border-gray-200 dark:border-white/5">
                    <div class="text-4xl font-bold text-teal-400 mb-2">2x</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">higher retention with recurring class schedules</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-green-400 mb-2">$0</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">platform fees on class payments</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Bento Grid -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white text-center mb-12">
                Everything you need to teach online classes
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Email students before class (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/5 rounded-full blur-[100px]"></div>

                    <div class="relative flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-5 border border-emerald-200 dark:border-emerald-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Email Students
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Email students before each class</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Send class reminders, share materials and prep instructions, and follow up with recordings. Your students, your inbox.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Class reminders</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Material sharing</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Recording links</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-950 dark:to-teal-950 rounded-2xl border border-emerald-300 dark:border-emerald-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-sm font-semibold">YS</div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-semibold text-sm">Yoga Studio</div>
                                            <div class="text-emerald-600 dark:text-emerald-300 text-xs">Class reminder</div>
                                        </div>
                                    </div>
                                    <div class="bg-gradient-to-br from-emerald-600/30 to-teal-600/30 rounded-xl p-3 border border-emerald-400/20">
                                        <div class="text-center">
                                            <div class="text-gray-900 dark:text-white text-xs font-semibold mb-1">TOMORROW AT 7 AM</div>
                                            <div class="text-emerald-700 dark:text-emerald-300 text-sm font-bold">Morning Flow Yoga</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">Join via Zoom - $12</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-4 text-xs">
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-emerald-500 dark:text-emerald-400 font-semibold">74%</span> opened</div>
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-amber-500 dark:text-amber-400 font-semibold">51%</span> clicked</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sell class passes and drop-ins -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900 dark:to-emerald-900 border border-green-200 dark:border-white/10 p-8">
                    <div class="absolute bottom-0 right-0 w-64 h-64 bg-green-500/5 rounded-full blur-[80px]"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm font-medium mb-5 border border-green-200 dark:border-green-800/30">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Class Payments
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sell class spots with zero fees</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Per-class pricing or free sessions. 100% of Stripe payments go to you. See all <a href="{{ route('marketing.ticketing') }}" class="text-green-600 dark:text-green-400 underline hover:no-underline">ticketing features</a>.</p>

                        <div class="bg-green-500/20 rounded-xl border border-green-400/30 p-4">
                            <div class="space-y-2 mb-3">
                                <div class="flex items-center justify-between p-2 rounded-lg bg-green-400/20">
                                    <span class="text-gray-900 dark:text-white text-xs font-medium">Drop-in Class</span>
                                    <span class="text-green-600 dark:text-green-400 text-xs font-semibold">$15</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-400/20">
                                    <span class="text-gray-900 dark:text-white text-xs font-medium">Free Trial</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 text-xs font-semibold">Free</span>
                                </div>
                            </div>
                            <div class="border-t border-green-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="text-green-600 dark:text-green-400 font-semibold">$0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- One link for your class schedule -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 text-sm font-medium mb-5 border border-sky-200 dark:border-sky-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Share Link
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One link for all your classes</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Share on your website, social profiles, or email signature. Students see your full schedule at a glance.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-sky-500/20 border border-sky-400/30 mb-3">
                            <svg aria-hidden="true" class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-gray-900 dark:text-white text-xs font-mono">eventschedule.com/yourclasses</span>
                        </div>
                        <div class="grid grid-cols-3 gap-1 text-center">
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-700 dark:text-sky-300 text-[10px]">Website</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-700 dark:text-sky-300 text-[10px]">Instagram</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-700 dark:text-sky-300 text-[10px]">Email</div>
                        </div>
                    </div>
                </div>

                <!-- Works with any video platform (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-green-100 dark:from-teal-900 dark:to-green-900 border border-teal-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 text-sm font-medium mb-5 border border-teal-200 dark:border-teal-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Any Platform
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Teach on any video platform</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Zoom, Google Meet, YouTube Live, or your own streaming setup. Add your class link, students join from your schedule. Learn more about <a href="{{ route('marketing.online_events') }}" class="text-teal-600 dark:text-teal-400 underline hover:no-underline">online event features</a>.</p>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex items-center gap-4">
                                <div class="bg-teal-100 dark:bg-teal-500/20 rounded-xl border border-teal-400/30 p-4 w-36">
                                    <div class="text-teal-700 dark:text-teal-300 text-xs text-center mb-2 font-semibold">Your Schedule</div>
                                    <div class="space-y-1.5">
                                        <div class="h-2 bg-gray-300 dark:bg-white/20 rounded"></div>
                                        <div class="h-2 bg-teal-400/40 rounded w-3/4"></div>
                                    </div>
                                    <div class="mt-3 p-2 rounded-lg bg-teal-200 dark:bg-teal-400/20 border border-teal-400/30">
                                        <div class="text-[10px] text-gray-900 dark:text-white text-center font-medium">Morning Yoga</div>
                                        <div class="text-[8px] text-teal-700 dark:text-teal-300 text-center mt-0.5">Mon 7:00 AM</div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <svg aria-hidden="true" class="w-6 h-6 text-teal-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    <span class="text-teal-600 dark:text-teal-400 text-[10px]">class link</span>
                                </div>
                                <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-4 w-36">
                                    <div class="text-gray-600 dark:text-gray-300 text-xs text-center mb-2 font-semibold">Platform</div>
                                    <div class="space-y-2 text-center">
                                        <div class="p-1.5 rounded bg-blue-400/20 text-[10px] text-blue-700 dark:text-blue-300">Zoom</div>
                                        <div class="p-1.5 rounded bg-green-400/20 text-[10px] text-green-700 dark:text-green-300">Google Meet</div>
                                        <div class="p-1.5 rounded bg-red-400/20 text-[10px] text-red-700 dark:text-red-300">YouTube Live</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recurring class schedule -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-sm font-medium mb-5 border border-amber-200 dark:border-amber-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recurring
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Schedule recurring classes automatically</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Weekly yoga, biweekly workshops, monthly masterclasses. Set it once and let students follow along.</p>

                    <div class="bg-amber-500/20 rounded-xl border border-amber-400/30 p-3">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/20">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-900 dark:text-white text-[10px] font-medium">Mon - Morning Flow</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Wed - Power Vinyasa</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Restorative Yoga</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Sat - Weekend Workshop</span>
                            </div>
                        </div>
                        <div class="mt-2 text-center text-amber-600 dark:text-amber-300 text-[10px]">Repeats weekly</div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-sm font-medium mb-5 border border-blue-200 dark:border-blue-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sync classes with Google Calendar</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Two-way sync keeps your class schedule, private lessons, and prep time all in one place.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-700 dark:text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-emerald-400/80 dark:bg-emerald-400/40 rounded text-[6px] text-white px-1">Class</div>
                                <div class="h-1.5 bg-amber-400/80 dark:bg-amber-400/40 rounded text-[6px] text-white px-1">Prep</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg aria-hidden="true" class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-600 dark:text-gray-300 mb-1 text-center">Google</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                <div class="h-1.5 bg-green-400/40 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students follow your schedule -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-emerald-100 dark:from-cyan-900 dark:to-emerald-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-100 dark:bg-cyan-900/40 text-cyan-700 dark:text-cyan-300 text-sm font-medium mb-5 border border-cyan-200 dark:border-cyan-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Followers
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Students follow your class schedule</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Students get notified when you add new classes or update your schedule.</p>

                    <div class="flex items-center justify-center">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">A</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">B</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">C</div>
                            <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-white/20 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-gray-600 dark:text-white text-xs">+340</div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-cyan-600 dark:text-cyan-300 text-xs">343 students following your schedule</div>
                </div>

            </div>
        </div>
    </section>

    <!-- Journey Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    From your first class to a full teaching business
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Event Schedule grows with your online teaching practice
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- First online class -->
                <div class="bg-gradient-to-br from-emerald-100 to-emerald-50 dark:from-[#0f1a15] dark:to-[#0a0a0f] rounded-2xl p-6 border border-emerald-200 dark:border-emerald-900/20 hover:border-emerald-300 dark:hover:border-emerald-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">First online class</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Share a link and start teaching. Free registration gets students in the door.</p>
                </div>

                <!-- Weekly class schedule -->
                <div class="bg-gradient-to-br from-teal-100 to-teal-50 dark:from-[#0f1a1a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-teal-200 dark:border-teal-900/20 hover:border-teal-300 dark:hover:border-teal-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Weekly class schedule</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Set up recurring classes. Students follow your schedule and sign up each week.</p>
                </div>

                <!-- Paid classes -->
                <div class="bg-gradient-to-br from-green-100 to-green-50 dark:from-[#0f1a10] dark:to-[#0a0a0f] rounded-2xl p-6 border border-green-200 dark:border-green-900/20 hover:border-green-300 dark:hover:border-green-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Paid classes</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Start charging for your expertise. Sell individual class spots with zero platform fees.</p>
                </div>

                <!-- Multi-level curriculum -->
                <div class="bg-gradient-to-br from-cyan-100 to-cyan-50 dark:from-[#0f1a1c] dark:to-[#0a0a0f] rounded-2xl p-6 border border-cyan-200 dark:border-cyan-900/20 hover:border-cyan-300 dark:hover:border-cyan-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Multi-level curriculum</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Organize classes into beginner, intermediate, and advanced groups so students find the right level.</p>
                </div>

                <!-- Student community -->
                <div class="bg-gradient-to-br from-blue-100 to-blue-50 dark:from-[#0f1520] dark:to-[#0a0a0f] rounded-2xl p-6 border border-blue-200 dark:border-blue-900/20 hover:border-blue-300 dark:hover:border-blue-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Student community</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Build a following. Students subscribe, get notified, and keep coming back week after week.</p>
                </div>

                <!-- Full teaching studio -->
                <div class="bg-gradient-to-br from-amber-100 to-amber-50 dark:from-[#1a1510] dark:to-[#0a0a0f] rounded-2xl p-6 border border-amber-200 dark:border-amber-900/20 hover:border-amber-300 dark:hover:border-amber-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Full teaching studio</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">A professional schedule with past and upcoming classes. Your online studio that students can browse.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for every type of online class
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether it's a fitness class or a coding bootcamp, Event Schedule works for instructors of all kinds. Also see Event Schedule for <a href="{{ route('marketing.for_webinars') }}" class="text-gray-600 dark:text-gray-300 underline hover:no-underline">Webinars</a> and <a href="{{ route('marketing.for_virtual_conferences') }}" class="text-gray-600 dark:text-gray-300 underline hover:no-underline">Virtual Conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Yoga & Fitness -->
                <x-sub-audience-card
                    name="Yoga & Fitness Instructors"
                    description="Schedule daily or weekly classes, manage drop-ins, and build a community of regular students."
                    icon-color="cyan"
                    blog-slug="for-yoga-fitness-instructors-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Cooking Classes -->
                <x-sub-audience-card
                    name="Cooking Instructors"
                    description="Share ingredient lists beforehand, teach live, and follow up with recipes. Perfect for interactive cooking sessions."
                    icon-color="teal"
                    blog-slug="for-cooking-instructors-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Art & Music Teachers -->
                <x-sub-audience-card
                    name="Art & Music Teachers"
                    description="Teach drawing, painting, guitar, piano, or any creative skill with scheduled live sessions."
                    icon-color="sky"
                    blog-slug="for-art-music-teachers-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Language Tutors -->
                <x-sub-audience-card
                    name="Language Tutors"
                    description="Run group language lessons or private tutoring sessions with recurring weekly schedules."
                    icon-color="blue"
                    blog-slug="for-language-tutors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Coding & Tech -->
                <x-sub-audience-card
                    name="Coding & Tech Educators"
                    description="Bootcamps, workshops, and study groups. Organize classes by skill level and let students find their track."
                    icon-color="amber"
                    blog-slug="for-coding-tech-educators"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Business & Professional -->
                <x-sub-audience-card
                    name="Business Coaches"
                    description="Host coaching sessions, masterminds, and professional development classes with paid registration."
                    icon-color="emerald"
                    blog-slug="for-business-coaches-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Three steps to your first online class
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-600 to-teal-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-600/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create your class</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Add the topic, date, and video link. Set up free or paid registration.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-600 to-teal-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-600/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share your schedule</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Students register in one click. They get reminders before each class.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-600 to-teal-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-600/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Teach</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Focus on your students. No platform fees, no middleman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white text-center mb-12">
                Frequently asked questions about online classes
            </h2>

            <div class="space-y-6">
                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">What video platforms can I use to teach?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, YouTube Live, and any other platform. Event Schedule is platform-agnostic - just paste your link and students join from your class schedule.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Can I charge for online classes?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. Set per-class pricing with Stripe. You keep 100% of the revenue - Event Schedule charges zero platform fees at any plan level. Stripe charges its standard processing fee (2.9% + $0.30).</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Can I schedule recurring weekly classes?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. Set up daily, weekly, or monthly recurring classes. Create your schedule once and it repeats automatically. Students can follow your schedule and get notified when new sessions are added.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">How do students get the class link?</h3>
                    <p class="text-gray-500 dark:text-gray-400">When students register or purchase a spot, the video link appears on their registration page. You can also email all registered students directly from your dashboard before class starts.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Is Event Schedule free for teaching online classes?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. The free plan includes unlimited classes, student email notifications, recurring schedules, and follower features. There are zero platform fees on payments at any plan level. You only pay Stripe's standard processing fee if you charge for classes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-20 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Key features</h2>
            <div class="space-y-3">
                <x-feature-link-card
                    name="Online Events"
                    description="Host virtual events with any streaming platform"
                    :url="marketing_url('/features/online-events')"
                    icon-color="sky"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Analytics"
                    description="Track page views, devices, and traffic sources"
                    :url="marketing_url('/features/analytics')"
                    icon-color="emerald"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Newsletters"
                    description="Send event updates directly to followers' inboxes"
                    :url="marketing_url('/features/newsletters')"
                    icon-color="green"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
            </div>
        </div>
    </section>

    <!-- Related Pages -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Related pages</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ marketing_url('/for-workshop-instructors') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Workshop Instructors</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-webinars') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Webinars</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-fitness-and-yoga') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Fitness & Yoga</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-virtual-conferences') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Virtual Conferences</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden border-t border-emerald-200 dark:border-emerald-900/20">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-[-10%] w-[50%] h-[60%] bg-emerald-600/15 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 right-[-10%] w-[50%] h-[60%] bg-teal-600/15 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Your classes. Your students. No middleman.
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                Stop paying platform fees. Start teaching online.<br class="hidden md:block">Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-xl shadow-emerald-500/20">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            <p class="mt-6 text-gray-500 text-sm">No credit card required</p>
        </div>
    </section>

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Online Classes",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Online Class Scheduling Software",
        "operatingSystem": "Web",
        "description": "Schedule and sell online classes with built-in registration, recurring sessions, student email notifications, and payment processing. Works with Zoom, Google Meet, and any platform.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Email notifications to registered students",
            "One link for your full class schedule",
            "Zero-fee payments for paid classes",
            "Google Calendar two-way sync",
            "Works with Zoom, Google Meet, YouTube Live",
            "Recurring class scheduling",
            "Student registration management",
            "Follower notifications for new classes",
            "Multi-level class organization"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "online class scheduling, virtual class platform, sell online classes, online teaching",
        "screenshot": "{{ asset('social/features.png') }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <!-- FAQ Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What video platforms can I use to teach?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, YouTube Live, and any other platform. Event Schedule is platform-agnostic - just paste your link and students join from your class schedule."
                }
            },
            {
                "@type": "Question",
                "name": "Can I charge for online classes?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set per-class pricing with Stripe. You keep 100% of the revenue - Event Schedule charges zero platform fees at any plan level. Stripe charges its standard processing fee (2.9% + $0.30)."
                }
            },
            {
                "@type": "Question",
                "name": "Can I schedule recurring weekly classes?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up daily, weekly, or monthly recurring classes. Create your schedule once and it repeats automatically. Students can follow your schedule and get notified when new sessions are added."
                }
            },
            {
                "@type": "Question",
                "name": "How do students get the class link?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When students register or purchase a spot, the video link appears on their registration page. You can also email all registered students directly from your dashboard before class starts."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free for teaching online classes?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The free plan includes unlimited classes, student email notifications, recurring schedules, and follower features. There are zero platform fees on payments at any plan level. You only pay Stripe's standard processing fee if you charge for classes."
                }
            }
        ]
    }
    </script>

    <style {!! nonce_attr() !!}>
        .classes-glow-text {
            background: linear-gradient(135deg, #059669, #0d9488, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(5, 150, 105, 0.3);
        }
        .dark .classes-glow-text {
            background: linear-gradient(135deg, #34d399, #2dd4bf, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(52, 211, 153, 0.3);
        }
    </style>
</x-marketing-layout>
