<x-marketing-layout>
    <x-slot name="title">Event Polls for Audience Engagement | Interactive Voting - Event Schedule</x-slot>
    <x-slot name="description">Add interactive polls to your events. Guests vote on multiple choice questions with real-time results. Pro feature with one vote per user. No credit card required.</x-slot>
    <x-slot name="breadcrumbTitle">Event Polls</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How do event polls work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Admins create polls with a question and multiple choice options on any event. Authenticated guests can vote once per poll. Results are shown immediately after voting with animated progress bars."
                }
            },
            {
                "@type": "Question",
                "name": "Who can create polls?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event organizers and admins can create up to 5 polls per event from the event edit page. Polls are a Pro plan feature."
                }
            },
            {
                "@type": "Question",
                "name": "Can guests see results before voting?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Results are hidden until a guest votes. Once they vote, they see the results with vote counts and percentages. Admins can always see results."
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
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern" aria-hidden="true"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg class="w-4 h-4 text-blue-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Audience Engagement</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Interactive event<br>
                <span class="text-gradient">polls</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Add multiple choice polls to any event. Guests vote with one tap and see real-time results with animated progress bars. A simple way to engage your audience.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    Get started free
                    <svg class="ml-2 w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">Simple polls, real engagement</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- 1: Interactive Multiple Choice -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Multiple Choice
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Interactive multiple choice</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Create polls with 2 to 10 options. Each poll has a question and a set of choices for guests to pick from.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="text-xs font-medium text-gray-900 dark:text-white mb-2">What genre next week?</div>
                            <div class="space-y-1.5">
                                <div class="text-[10px] py-1.5 px-3 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-gray-600 dark:text-gray-300">Jazz</div>
                                <div class="text-[10px] py-1.5 px-3 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-gray-600 dark:text-gray-300">Rock</div>
                                <div class="text-[10px] py-1.5 px-3 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-gray-600 dark:text-gray-300">Blues</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2: Real-Time Results -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        Results
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Real-time results</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Animated progress bars with vote counts and percentages appear instantly after voting.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="text-xs font-medium text-gray-900 dark:text-white mb-2">Results</div>
                            <div class="space-y-2">
                                <div>
                                    <div class="flex justify-between text-[10px] text-gray-600 dark:text-gray-300 mb-1"><span>Jazz</span><span>45%</span></div>
                                    <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full"><div class="h-2 bg-blue-500 rounded-full" style="width: 45%"></div></div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] text-gray-600 dark:text-gray-300 mb-1"><span>Rock</span><span>35%</span></div>
                                    <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full"><div class="h-2 bg-sky-500 rounded-full" style="width: 35%"></div></div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] text-gray-600 dark:text-gray-300 mb-1"><span>Blues</span><span>20%</span></div>
                                    <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full"><div class="h-2 bg-sky-400 rounded-full" style="width: 20%"></div></div>
                                </div>
                            </div>
                            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">20 votes</div>
                        </div>
                    </div>
                </div>

                <!-- 3: One Vote Per User -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Integrity
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One vote per user</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Authenticated voting prevents duplicates. Each signed-in guest can vote once per poll for fair results.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-6 h-6 rounded-full bg-blue-300 dark:bg-blue-500/40 flex-shrink-0"></div>
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Sarah voted</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 ml-auto">Recorded</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-sky-300 dark:bg-sky-500/40 flex-shrink-0"></div>
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Mike voted</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 ml-auto">Recorded</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4: Open and Close Polls -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Control
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Open and close polls</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Toggle polls open or closed at any time. Close voting when you have enough responses and keep results visible.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Best opener?</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Open</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Favorite song?</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400">Closed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5: Mobile-Friendly -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Mobile
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Mobile-friendly</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Full-width buttons with generous touch targets make voting easy on any device. Guests can vote right from their phones.</p>

                    <div class="flex justify-center" aria-hidden="true">
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-3 w-36">
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mb-2">Vote now</div>
                            <div class="space-y-1.5">
                                <div class="text-[10px] py-2 rounded-lg bg-blue-500 text-white text-center font-medium">Option A</div>
                                <div class="text-[10px] py-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-gray-600 dark:text-gray-300 text-center">Option B</div>
                                <div class="text-[10px] py-2 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 text-gray-600 dark:text-gray-300 text-center">Option C</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6: Pro Feature -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        Pro
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Pro feature</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Event polls are available on Pro and Enterprise plans. Up to 5 polls per event with unlimited votes.</p>

                    <div class="flex flex-wrap gap-3" aria-hidden="true">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Pro plan</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Enterprise plan</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">5 polls per event</span>
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
                    Everything you need to know about event polls.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <!-- Q1: blue-to-sky -->
                <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">How do event polls work?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Admins create polls with a question and multiple choice options on any event. Authenticated guests can vote once per poll. Results are shown immediately after voting with animated progress bars.</p>
                    </div>
                </div>

                <!-- Q2: blue-to-cyan -->
                <div class="bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Who can create polls?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Event organizers and admins can create up to 5 polls per event from the event edit page. Polls are a Pro plan feature.</p>
                    </div>
                </div>

                <!-- Q3: emerald-to-teal -->
                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Can guests see results before voting?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">No. Results are hidden until a guest votes. Once they vote, they see the results with vote counts and percentages. Admins can always see results.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay" aria-hidden="true"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Engage your audience with polls
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start collecting feedback from your guests today. No credit card required.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule Event Polls",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Audience Engagement Software",
        "operatingSystem": "Web",
        "description": "Add interactive polls to your events. Guests vote on multiple choice questions with real-time results. Pro feature with one vote per user. No credit card required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free trial, Pro plan feature"
        },
        "featureList": [
            "Interactive multiple choice polls",
            "Real-time results with progress bars",
            "One vote per authenticated user",
            "Open and close polls on demand",
            "Mobile-friendly voting interface",
            "Up to 5 polls per event"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
