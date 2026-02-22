<x-marketing-layout>
    <x-slot name="title">Recurring Events | Flexible Repeat Scheduling - Event Schedule</x-slot>
    <x-slot name="description">Set events to repeat daily, weekly, biweekly, monthly, or yearly with flexible end conditions, per-occurrence tickets, and automatic Google Calendar sync.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">Recurring Events</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Recurring Events",
        "description": "Set events to repeat daily, weekly, biweekly, monthly, or yearly with flexible end conditions, per-occurrence tickets, and automatic Google Calendar sync.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Recurring Event Scheduling"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What recurrence patterns are available?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You can set events to repeat daily, weekly on specific days, every N weeks, monthly on the same date, monthly on the same weekday, or yearly. Each pattern supports flexible end conditions."
                }
            },
            {
                "@type": "Question",
                "name": "Can I edit a single occurrence without changing the whole series?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Each occurrence of a recurring event is an independent event with its own details, tickets, and attendance tracking. You can edit any single occurrence without affecting the rest of the series."
                }
            },
            {
                "@type": "Question",
                "name": "How do recurring events sync with Google Calendar?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Recurring events sync to Google Calendar as individual occurrences. Each date appears separately in your Google Calendar, and changes sync both ways automatically."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        .text-gradient {
            background: linear-gradient(135deg, #84cc16 0%, #22c55e 50%, #16a34a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .text-gradient {
            background: linear-gradient(135deg, #a3e635 0%, #4ade80 50%, #22c55e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-lime-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-green-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Recurring Events</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Set it and<br>
                <span class="text-gradient">forget it</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Schedule events to repeat daily, weekly, monthly, or yearly. Set end conditions and let Google Calendar sync handle the rest.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-lime-600 to-green-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-lime-500/25">
                    Start for free
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                <!-- Frequency Options (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-lime-100 to-green-100 dark:from-lime-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-lime-100 text-lime-700 dark:bg-lime-500/20 dark:text-lime-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Flexible Recurrence
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Choose your frequency</h2>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Pick from six recurrence options to match any schedule. Daily, weekly, biweekly, monthly, or yearly.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Daily</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Weekly</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Monthly</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Yearly</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="animate-float">
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-6 border border-gray-200 dark:border-white/10 max-w-xs">
                                    <div class="text-gray-500 dark:text-gray-400 text-xs mb-3">Frequency</div>
                                    <div class="space-y-1.5 mb-4">
                                        <div class="px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-500 dark:text-gray-400 text-xs">Daily</div>
                                        <div class="px-3 py-1.5 rounded-lg bg-lime-500 text-white text-xs font-semibold shadow-lg shadow-lime-500/25">Weekly</div>
                                        <div class="px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-500 dark:text-gray-400 text-xs">Every N Weeks</div>
                                        <div class="px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-500 dark:text-gray-400 text-xs">Monthly (same date)</div>
                                        <div class="px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-500 dark:text-gray-400 text-xs">Monthly (same day)</div>
                                        <div class="px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-500 dark:text-gray-400 text-xs">Yearly</div>
                                    </div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs mb-2">Repeat on</div>
                                    <div class="flex gap-1.5">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 flex items-center justify-center text-gray-500 dark:text-gray-400 text-[10px]">S</div>
                                        <div class="w-8 h-8 rounded-full bg-lime-500 text-white flex items-center justify-center text-[10px] font-semibold shadow-lg shadow-lime-500/25">M</div>
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 flex items-center justify-center text-gray-500 dark:text-gray-400 text-[10px]">T</div>
                                        <div class="w-8 h-8 rounded-full bg-lime-500 text-white flex items-center justify-center text-[10px] font-semibold shadow-lg shadow-lime-500/25">W</div>
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 flex items-center justify-center text-gray-500 dark:text-gray-400 text-[10px]">T</div>
                                        <div class="w-8 h-8 rounded-full bg-lime-500 text-white flex items-center justify-center text-[10px] font-semibold shadow-lg shadow-lime-500/25">F</div>
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 flex items-center justify-center text-gray-500 dark:text-gray-400 text-[10px]">S</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- End Conditions -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        End Conditions
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Control the run</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Choose when your recurring series stops.</p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-lime-100 dark:bg-lime-500/20 border border-lime-200 dark:border-lime-500/30">
                            <div class="w-4 h-4 rounded-full border-4 border-lime-500"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Never (ongoing)</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                            <div class="w-4 h-4 rounded-full border-2 border-gray-400 dark:border-gray-500"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">On a specific date</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                            <div class="w-4 h-4 rounded-full border-2 border-gray-400 dark:border-gray-500"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">After N occurrences</span>
                        </div>
                    </div>
                </div>

                <!-- Independent Tickets -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Per-Event Tickets
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sell per event</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Each occurrence has its own ticket count and sales.</p>

                    <div class="space-y-2">
                        <div class="p-3 rounded-xl bg-lime-500/10 border border-lime-500/20">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-900 dark:text-white text-sm font-medium">Mon, Feb 3</span>
                                <span class="text-lime-600 dark:text-lime-300 text-xs">12 sold</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-300 text-sm font-medium">Wed, Feb 5</span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs">8 sold</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600 dark:text-gray-300 text-sm font-medium">Fri, Feb 7</span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs">5 sold</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Preview (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-lime-100 to-emerald-100 dark:from-lime-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-lime-100 text-lime-700 dark:bg-lime-500/20 dark:text-lime-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Calendar View
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">See the series</h2>
                            <p class="text-gray-600 dark:text-white/80 text-lg">All recurring dates appear on your schedule automatically, regardless of frequency. Guests see each occurrence individually with its own ticket availability.</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                            <div class="text-gray-500 dark:text-gray-400 text-xs mb-3 font-medium">February 2026</div>
                            <div class="grid grid-cols-7 gap-1 text-center text-[10px]">
                                <div class="text-gray-400 dark:text-gray-500 py-1">S</div>
                                <div class="text-gray-400 dark:text-gray-500 py-1">M</div>
                                <div class="text-gray-400 dark:text-gray-500 py-1">T</div>
                                <div class="text-gray-400 dark:text-gray-500 py-1">W</div>
                                <div class="text-gray-400 dark:text-gray-500 py-1">T</div>
                                <div class="text-gray-400 dark:text-gray-500 py-1">F</div>
                                <div class="text-gray-400 dark:text-gray-500 py-1">S</div>
                                <div class="py-1 text-gray-400 dark:text-gray-600">1</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">2</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">3</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">4</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">5</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">6</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">7</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">8</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">9</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">10</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">11</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">12</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">13</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">14</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">15</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">16</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">17</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">18</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">19</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">20</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">21</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">22</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">23</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">24</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">25</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">26</div>
                                <div class="py-1 rounded bg-lime-500 text-white font-semibold">27</div>
                                <div class="py-1 text-gray-600 dark:text-gray-300">28</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-100 to-lime-100 dark:from-green-900 dark:to-lime-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Auto Sync
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Syncs automatically</h2>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Recurring events sync to Google Calendar as individual occurrences. Each date appears separately so attendees see the full series.</p>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="animate-float" style="animation-delay: 0.3s;">
                                <div class="flex items-center gap-4">
                                    <div class="bg-lime-100 dark:bg-lime-500/20 rounded-xl border border-lime-200 dark:border-lime-400/30 p-4 w-28">
                                        <div class="text-xs text-lime-600 dark:text-lime-300 mb-2 text-center">Event Schedule</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 bg-lime-400/40 rounded"></div>
                                            <div class="h-2 bg-lime-400/40 rounded w-3/4"></div>
                                            <div class="h-2 bg-lime-400/40 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <svg aria-hidden="true" class="w-6 h-6 text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                        <svg aria-hidden="true" class="w-6 h-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                    </div>
                                    <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-4 w-28">
                                        <div class="text-xs text-gray-600 dark:text-gray-300 mb-2 text-center">Google Calendar</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 bg-blue-400/40 rounded"></div>
                                            <div class="h-2 bg-green-400/40 rounded w-3/4"></div>
                                            <div class="h-2 bg-yellow-400/40 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Use Cases -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-lime-100 to-green-100 dark:from-lime-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-lime-100 text-lime-700 dark:bg-lime-500/20 dark:text-lime-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Use Cases
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Built for regulars</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Perfect for events that repeat on any schedule.</p>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-lime-500/10 border border-lime-500/20 text-lime-700 dark:text-lime-300 text-sm">Yoga classes</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-lime-500/10 border border-lime-500/20 text-lime-700 dark:text-lime-300 text-sm">Trivia nights</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-lime-500/10 border border-lime-500/20 text-lime-700 dark:text-lime-300 text-sm">Daily standups</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-lime-500/10 border border-lime-500/20 text-lime-700 dark:text-lime-300 text-sm">Board meetings</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-lime-500/10 border border-lime-500/20 text-lime-700 dark:text-lime-300 text-sm">Open mic</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-lime-500/10 border border-lime-500/20 text-lime-700 dark:text-lime-300 text-sm">Annual galas</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Guide & Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-cyan-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.creating_events') }}" class="group block">
                    <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-lime-500/10 border border-lime-500/20 mb-6">
                            <svg aria-hidden="true" class="w-6 h-6 text-lime-500 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-lime-600 dark:group-hover:text-lime-400 transition-colors">Read the guide</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Learn how to get the most out of recurring events.</p>
                        <span class="inline-flex items-center text-lime-500 dark:text-lime-400 font-medium group-hover:gap-3 gap-2 transition-all">
                            Read guide
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Next feature -->
                <a href="{{ marketing_url('/features/custom-fields') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 rounded-3xl border border-amber-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-amber-600 dark:group-hover:text-amber-300 transition-colors">Custom Fields</h2>
                        <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Collect additional info from ticket buyers with text, dropdown, date, and yes/no fields.</p>
                        <span class="inline-flex items-center text-amber-500 dark:text-amber-400 font-medium group-hover:gap-3 gap-2 transition-all">
                            Learn more
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Popular with -->
                <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 mb-6">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-bars') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Bars</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-fitness-and-yoga') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Fitness & Yoga</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-libraries') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Libraries</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
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
                    Everything you need to know about recurring events.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What recurrence patterns are available?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            You can set events to repeat daily, weekly on specific days, every N weeks, monthly on the same date, monthly on the same weekday, or yearly. Each pattern supports flexible end conditions.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I edit a single occurrence without changing the whole series?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Each occurrence of a recurring event is an independent event with its own details, tickets, and attendance tracking. You can edit any single occurrence without affecting the rest of the series.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How do recurring events sync with Google Calendar?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Recurring events sync to Google Calendar as individual occurrences. Each date appears separately in your Google Calendar, and changes sync both ways automatically.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Related features</h2>
            <div class="space-y-3">
                <x-feature-link-card
                    name="Calendar Sync"
                    description="Two-way sync with Google Calendar"
                    :url="marketing_url('/features/calendar-sync')"
                    icon-color="blue"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Online Events"
                    description="Host virtual events with Zoom, Google Meet, and more"
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
                    name="Ticketing"
                    description="Sell tickets with QR check-in and zero platform fees"
                    :url="marketing_url('/features/ticketing')"
                    icon-color="emerald"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-lime-600 to-green-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Automate your schedule
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Set up recurring events today. Free to use, no credit card required.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-lime-700 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        "name": "Event Schedule - Recurring Events",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Scheduling Software",
        "operatingSystem": "Web",
        "description": "Set events to repeat daily, weekly, biweekly, monthly, or yearly with flexible end conditions, per-occurrence tickets, and automatic Google Calendar sync.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "Daily recurrence",
            "Weekly day-of-week recurrence",
            "Every N weeks recurrence",
            "Monthly same-date recurrence",
            "Monthly same-weekday recurrence",
            "Yearly recurrence",
            "Three end conditions",
            "Per-occurrence tickets",
            "Google Calendar sync",
            "Individual ticket sales"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
