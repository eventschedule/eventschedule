<x-marketing-layout>
    <x-slot name="title">Event Carpool Matching - Event Schedule</x-slot>
    <x-slot name="description">Let attendees coordinate rides to your events. Drivers offer seats, riders request spots, with approvals, reminders, and reviews. Pro feature.</x-slot>
    <x-slot name="breadcrumbTitle">Carpool</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How does carpool matching work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Drivers create ride offers specifying their city, direction (to event, from event, or both), departure time, meeting point, and available spots. Riders browse offers and request a spot with an optional message. Drivers then approve or decline each request."
                }
            },
            {
                "@type": "Question",
                "name": "Who can offer or request rides?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any authenticated guest can offer or request rides for events that have carpool enabled. Organizers enable carpool matching per event in the admin portal."
                }
            },
            {
                "@type": "Question",
                "name": "How does carpool handle safety?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "After a ride, passengers can leave 1 to 5 star ratings and written reviews for drivers. Users can report problematic behavior, and organizers can moderate all carpool activity from the admin portal."
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
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-green-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern" aria-hidden="true"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg class="w-4 h-4 text-emerald-500" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 17h2m10 0h2M5 17a2 2 0 01-2-2v-4a1 1 0 011-1h1l2-4h10l2 4h1a1 1 0 011 1v4a2 2 0 01-2 2M5 17a2 2 0 002 2h10a2 2 0 002-2M7.5 14h.01M16.5 14h.01" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Ride Together</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Coordinate rides<br>
                <span class="text-gradient">to your events</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Let your attendees share rides. Drivers offer seats, riders request spots, and everyone gets to the event together.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-600 to-green-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-emerald-500/25">
                    Get started free
                    <svg class="ml-2 w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-gray-500 dark:text-gray-400 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-carpool" class="underline hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Read the Carpool guide</a>
            </p>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">Smarter commutes for every event</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- 1: Ride Offers -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 17h2m10 0h2M5 17a2 2 0 01-2-2v-4a1 1 0 011-1h1l2-4h10l2 4h1a1 1 0 011 1v4a2 2 0 01-2 2M5 17a2 2 0 002 2h10a2 2 0 002-2M7.5 14h.01M16.5 14h.01" />
                        </svg>
                        Offers
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Ride offers</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Drivers post offers with their city, direction, departure time, meeting point, and available spots from 1 to 10.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-medium text-gray-900 dark:text-white">Downtown</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">To event</span>
                            </div>
                            <div class="flex items-center gap-3 text-[10px] text-gray-500 dark:text-gray-400">
                                <span>3 spots</span>
                                <span>Departs 6:30 PM</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2: Request and Approval -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Approval
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Request and approval</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Riders request spots with a message. Drivers review each request and approve or decline.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-full bg-emerald-300 dark:bg-emerald-500/40 flex-shrink-0"></div>
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">Sarah</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 ml-auto">Approved</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-full bg-green-300 dark:bg-green-500/40 flex-shrink-0"></div>
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">Mike</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 ml-auto">Pending</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3: To, From, or Both -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                        Flexible
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">To, from, or both</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Offers support to-event, from-event, or round trip directions. Riders find exactly what they need.</p>

                    <div class="flex flex-wrap gap-2" aria-hidden="true">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-[10px] font-medium text-gray-700 dark:text-gray-300">
                            <svg class="w-3 h-3 mr-1.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            To event
                        </span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-[10px] font-medium text-gray-700 dark:text-gray-300">
                            <svg class="w-3 h-3 mr-1.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" /></svg>
                            From event
                        </span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-[10px] font-medium text-gray-700 dark:text-gray-300">
                            <svg class="w-3 h-3 mr-1.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                            Round trip
                        </span>
                    </div>
                </div>

                <!-- 4: Notifications and Reminders -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        Alerts
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Notifications and reminders</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Email notifications for requests, approvals, and cancellations. Automatic 24-hour reminders before departure.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></div>
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">New ride request from Sarah</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></div>
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">Your request was approved</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></div>
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">Ride reminder: departing tomorrow</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5: Reviews and Safety -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        Trust
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Reviews and safety</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Post-ride 1 to 5 star ratings. Report problematic behavior. Admin moderation tools keep rides safe.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center gap-1 mb-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                            <div class="text-[10px] text-gray-600 dark:text-gray-300 italic">"Smooth ride, great conversation!"</div>
                        </div>
                    </div>
                </div>

                <!-- 6: Pro Feature -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        Pro
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Pro feature</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Carpool matching is available on Pro and Enterprise plans. Enable it for any event in the admin portal.</p>

                    <div class="flex flex-wrap gap-3" aria-hidden="true">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Pro plan</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Enterprise plan</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Unlimited rides</span>
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
                    Everything you need to know about carpool matching.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <!-- Q1: emerald-to-green -->
                <div class="bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">How does carpool matching work?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Drivers create ride offers specifying their city, direction (to event, from event, or both), departure time, meeting point, and available spots. Riders browse offers and request a spot with an optional message. Drivers then approve or decline each request.</p>
                    </div>
                </div>

                <!-- Q2: green-to-lime -->
                <div class="bg-gradient-to-br from-green-100 to-lime-100 dark:from-green-900 dark:to-lime-900 rounded-2xl border border-green-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Who can offer or request rides?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Any authenticated guest can offer or request rides for events that have carpool enabled. Organizers enable carpool matching per event in the admin portal.</p>
                    </div>
                </div>

                <!-- Q3: teal-to-emerald -->
                <div class="bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 rounded-2xl border border-teal-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">How does carpool handle safety?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">After a ride, passengers can leave 1 to 5 star ratings and written reviews for drivers. Users can report problematic behavior, and organizers can moderate all carpool activity from the admin portal.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-emerald-600 to-green-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay" aria-hidden="true"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Make events easier to reach
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Enable carpool matching and let your community share rides. No credit card required.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-emerald-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule Carpool Matching",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Ride Sharing Coordination Software",
        "operatingSystem": "Web",
        "description": "Let attendees coordinate rides to your events. Drivers offer seats, riders request spots, with approvals, reminders, and reviews. Pro feature.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free trial, Pro plan feature"
        },
        "featureList": [
            "Driver ride offers with city and direction",
            "Rider request and driver approval workflow",
            "To-event, from-event, and round trip support",
            "Email notifications and 24-hour reminders",
            "Post-ride star ratings and reviews",
            "Admin moderation and reporting tools"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
