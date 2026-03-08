<x-marketing-layout>
    <x-slot name="title">Custom Labels for Event Schedules | Rename Default Terms - Event Schedule</x-slot>
    <x-slot name="description">Override default labels in your event schedule. Rename "Events" to "Shows", "Venue" to "Location", and more. Multi-language support with auto-translation. Pro feature. No credit card required.</x-slot>
    <x-slot name="breadcrumbTitle">Custom Labels</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What are custom labels?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Custom labels let you rename the default terms used in your schedule. For example, you can change 'Events' to 'Shows', 'Venue' to 'Location', or 'Talent' to 'Artist' to match your community's language."
                }
            },
            {
                "@type": "Question",
                "name": "Which labels can I customize?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You can customize labels for events, venues, talent, curators, sub-schedules, and other key terms that appear throughout your schedule. Select from a predefined list of alternatives for each label."
                }
            },
            {
                "@type": "Question",
                "name": "Do custom labels work with translations?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. When you select a custom label, it is automatically translated into all supported languages. Your schedule displays the correct label regardless of the viewer's language setting."
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
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern" aria-hidden="true"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg class="w-4 h-4 text-teal-400" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Customization</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Custom labels<br>
                <span class="text-gradient">for your schedule</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Override default terms to match your community's language. Rename "Events" to "Shows", "Venue" to "Location", and more.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-teal-600 to-cyan-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-teal-500/25">
                    Get started free
                    <svg class="ml-2 w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-gray-500 dark:text-gray-400 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-labels" class="underline hover:text-teal-600 dark:hover:text-teal-400 transition-colors">Read the Custom Labels guide</a>
            </p>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">Make your schedule speak your language</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- 1: Override Labels -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                        Rename
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Override labels</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Replace default terms like "Events", "Venue", and "Talent" with words that fit your community.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 line-through">Events</span>
                                <svg class="w-3 h-3 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                <span class="text-[10px] font-medium text-gray-900 dark:text-white">Shows</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 line-through">Venue</span>
                                <svg class="w-3 h-3 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                <span class="text-[10px] font-medium text-gray-900 dark:text-white">Location</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2: Multi-Language Support -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" />
                        </svg>
                        Languages
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Multi-language support</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Custom labels work across all supported languages. Your renamed terms display correctly for every visitor.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">EN</span>
                                    <span class="text-[10px] font-medium text-gray-900 dark:text-white">Shows</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">ES</span>
                                    <span class="text-[10px] font-medium text-gray-900 dark:text-white">Espect&aacute;culos</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] text-gray-600 dark:text-gray-300">FR</span>
                                    <span class="text-[10px] font-medium text-gray-900 dark:text-white">Spectacles</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3: Per-Schedule Settings -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75M10.5 18a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 18H7.5m3-6h9.75M10.5 12a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 12H7.5" />
                        </svg>
                        Settings
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Per-schedule settings</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Each schedule has its own label configuration. A music venue and a conference can use different terms.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">Jazz Club</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">Shows, Artists</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300">Tech Conf</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">Sessions, Speakers</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4: Select from List -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        Selection
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Select from list</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Choose from a curated list of alternative labels. No free-text input needed, so translations stay consistent.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mb-2">Replace "Events" with:</div>
                            <div class="flex flex-wrap gap-1.5">
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-teal-200 text-teal-800 dark:bg-teal-500/30 dark:text-teal-200 ring-1 ring-teal-400/50">Shows</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400">Sessions</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400">Classes</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400">Performances</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5: Auto-Translation -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                        </svg>
                        Auto
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Auto-translation</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Selected labels are automatically translated into all supported languages. No manual translation work required.</p>

                    <div class="space-y-2" aria-hidden="true">
                        <div class="bg-white dark:bg-white/10 rounded-xl p-3 border border-gray-200 dark:border-white/10">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-3.5 h-3.5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">11 languages supported</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-[10px] text-gray-600 dark:text-gray-300">Zero configuration needed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6: Pro Feature -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        Pro
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Pro feature</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Custom labels are available on Pro and Enterprise plans. Tailor your schedule's terminology to your audience.</p>

                    <div class="flex flex-wrap gap-3" aria-hidden="true">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Pro plan</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Enterprise plan</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Per-schedule config</span>
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
                    Everything you need to know about custom labels.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <!-- Q1: teal-to-cyan -->
                <div class="bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 rounded-2xl border border-teal-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">What are custom labels?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Custom labels let you rename the default terms used throughout your schedule. For example, you can change "Events" to "Shows", "Venue" to "Location", or "Talent" to "Artist" to match your community's language.</p>
                    </div>
                </div>

                <!-- Q2: cyan-to-sky -->
                <div class="bg-gradient-to-br from-cyan-100 to-sky-100 dark:from-cyan-900 dark:to-sky-900 rounded-2xl border border-cyan-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Which labels can I customize?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">You can customize labels for events, venues, talent, curators, sub-schedules, and other key terms that appear throughout your schedule. Select from a predefined list of alternatives for each label.</p>
                    </div>
                </div>

                <!-- Q3: emerald-to-teal -->
                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Do custom labels work with translations?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Yes. When you select a custom label, it is automatically translated into all supported languages. Your schedule displays the correct label regardless of the viewer's language setting.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-teal-600 to-cyan-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay" aria-hidden="true"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Your schedule, your words
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start customizing your schedule's labels today. No credit card required.
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
        "name": "Event Schedule Custom Labels",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Management Software",
        "operatingSystem": "Web",
        "description": "Override default labels in your event schedule. Rename 'Events' to 'Shows', 'Venue' to 'Location', and more. Multi-language support with auto-translation. Pro feature. No credit card required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free trial, Pro plan feature"
        },
        "featureList": [
            "Override default schedule labels",
            "Multi-language support",
            "Per-schedule configuration",
            "Predefined label alternatives",
            "Automatic translation to 11 languages",
            "Pro and Enterprise plans"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
