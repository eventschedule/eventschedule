<x-marketing-layout>
    <x-slot name="title">Availability Management for Schedules | Booking Windows - Event Schedule</x-slot>
    <x-slot name="description">Set availability windows for your schedule. Define when you're open for bookings and let guests see your available times. Enterprise feature. No credit card required.</x-slot>
    <x-slot name="breadcrumbTitle">Availability</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How does availability management work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Schedule owners set their available days and time slots. Guests can see when the schedule is available and plan accordingly. Available times are displayed on the schedule's public page."
                }
            },
            {
                "@type": "Question",
                "name": "Who can set availability?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Schedule admins and owners can configure availability from the schedule settings. Availability management is an Enterprise plan feature."
                }
            },
            {
                "@type": "Question",
                "name": "Can I set different availability for different days?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. You can configure unique time slots for each day of the week, set days as unavailable, and adjust your schedule as needed."
                }
            }
        ]
    }
    </script>
    </x-slot>


    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern" aria-hidden="true"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg class="w-4 h-4 text-teal-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Schedule Management</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Share your<br>
                <span class="text-gradient">availability</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Set your available days and times so guests always know when to reach you. Display your availability right on your schedule page.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-teal-600 to-emerald-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-teal-500/25">
                    Get started free
                    <svg class="ml-2 w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-gray-500 dark:text-gray-400 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ route('marketing.docs.managing_schedules') }}#availability" class="underline hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Read the Availability guide</a>
            </p>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">Simple availability, clear communication</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- 1: Weekly Schedule -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        Weekly
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Weekly schedule</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Define your availability for each day of the week. Set different hours for different days to match your routine.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-[10px]">
                                    <span class="text-gray-600 dark:text-gray-300">Monday</span>
                                    <span class="text-teal-600 dark:text-teal-400">9:00 AM - 5:00 PM</span>
                                </div>
                                <div class="flex justify-between text-[10px]">
                                    <span class="text-gray-600 dark:text-gray-300">Tuesday</span>
                                    <span class="text-teal-600 dark:text-teal-400">9:00 AM - 5:00 PM</span>
                                </div>
                                <div class="flex justify-between text-[10px]">
                                    <span class="text-gray-600 dark:text-gray-300">Wednesday</span>
                                    <span class="text-gray-400 dark:text-gray-500">Unavailable</span>
                                </div>
                                <div class="flex justify-between text-[10px]">
                                    <span class="text-gray-600 dark:text-gray-300">Thursday</span>
                                    <span class="text-teal-600 dark:text-teal-400">10:00 AM - 6:00 PM</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2: Public Display -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Visibility
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Public display</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Your availability is shown directly on your schedule page. Guests see your open hours without needing to ask.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="text-[10px] font-medium text-gray-900 dark:text-white mb-2">Available times</div>
                            <div class="grid grid-cols-2 gap-1">
                                <div class="text-[10px] py-1 px-2 rounded bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 text-center">Mon 9-5</div>
                                <div class="text-[10px] py-1 px-2 rounded bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 text-center">Tue 9-5</div>
                                <div class="text-[10px] py-1 px-2 rounded bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 text-center">Thu 10-6</div>
                                <div class="text-[10px] py-1 px-2 rounded bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300 text-center">Fri 9-5</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3: Easy Configuration -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Setup
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Easy configuration</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Set up your availability in the schedule settings. Toggle days on or off and set time ranges with a few clicks.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded bg-teal-500"></div>
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">Monday: On</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded bg-teal-500"></div>
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">Tuesday: On</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded bg-gray-300 dark:bg-gray-600"></div>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">Wednesday: Off</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4: Talent Schedules -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Talent
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Perfect for talent</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Musicians, DJs, speakers, and performers can share when they're available for bookings directly on their schedule.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="text-[10px] font-medium text-gray-900 dark:text-white mb-2">DJ Alex - Available</div>
                            <div class="space-y-1">
                                <div class="text-[10px] text-teal-600 dark:text-teal-400">Fri & Sat evenings</div>
                                <div class="text-[10px] text-teal-600 dark:text-teal-400">Sun afternoons</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5: Venue Schedules -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                        </svg>
                        Venues
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Great for venues</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Venues can display their operating hours and available booking slots. Visitors see open times at a glance.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="text-[10px] font-medium text-gray-900 dark:text-white mb-2">The Blue Note</div>
                            <div class="space-y-1">
                                <div class="text-[10px] text-teal-600 dark:text-teal-400">Wed-Sun: 7 PM - 2 AM</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500">Mon-Tue: Closed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6: Enterprise Feature -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        Enterprise
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Enterprise feature</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Availability management is available on Enterprise plans. Display your open hours on any schedule type.</p>

                    <div class="flex flex-wrap gap-3" aria-hidden="true">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Enterprise plan</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">All schedule types</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Weekly hours</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gray-100 dark:bg-black/30 py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently asked questions
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Everything you need to know about availability.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <!-- Q1: teal-to-emerald -->
                <div class="bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 rounded-2xl border border-teal-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">How does availability management work?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Schedule owners set their available days and time slots. Guests can see when the schedule is available and plan accordingly. Available times are displayed on the schedule's public page.</p>
                    </div>
                </div>

                <!-- Q2: emerald-to-green -->
                <div class="bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Who can set availability?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Schedule admins and owners can configure availability from the schedule settings. Availability management is an Enterprise plan feature.</p>
                    </div>
                </div>

                <!-- Q3: cyan-to-sky -->
                <div class="bg-gradient-to-br from-cyan-100 to-sky-100 dark:from-cyan-900 dark:to-sky-900 rounded-2xl border border-cyan-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Can I set different availability for different days?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Yes. You can configure unique time slots for each day of the week, set days as unavailable, and adjust your schedule as needed.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-teal-600 to-emerald-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay" aria-hidden="true"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Let guests see your availability
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Set your hours and start sharing your schedule today. No credit card required.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-teal-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Start for free
                <svg class="ml-2 w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        "name": "Event Schedule Availability Management",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Scheduling Software",
        "operatingSystem": "Web",
        "description": "Set availability windows for your schedule. Define when you're open for bookings and let guests see your available times. Enterprise feature. No credit card required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free trial, Enterprise plan feature"
        },
        "featureList": [
            "Weekly availability schedule",
            "Per-day time slot configuration",
            "Public availability display",
            "Works with all schedule types",
            "Easy toggle on/off per day",
            "Enterprise plan feature"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
