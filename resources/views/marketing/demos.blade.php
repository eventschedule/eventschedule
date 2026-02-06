<x-marketing-layout>
    {{-- SEO Slots --}}
    <x-slot name="title">Event Schedule Examples | Live Demo Schedules to Explore</x-slot>
    <x-slot name="description">Explore {{ $scheduleCount }} live demo schedules showcasing Event Schedule. See real examples for fitness studios, music venues, yoga retreats, coworking spaces, and more.</x-slot>
    <x-slot name="keywords">event schedule examples, event calendar demos, live schedule examples, event management demo, calendar widget examples, venue schedule examples, fitness class schedule, music venue calendar</x-slot>
    <x-slot name="socialImage">social/demos.png</x-slot>
    <x-slot name="breadcrumbTitle">Examples</x-slot>

    {{-- Structured Data for Rich Results --}}
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "Event Schedule Examples",
        "description": "A gallery of {{ $scheduleCount }} live demo schedules showcasing Event Schedule features for various industries",
        "url": "{{ url('/demos') }}",
        "numberOfItems": {{ $scheduleCount }},
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "Live Event Schedule Demos",
        "description": "Explore real examples of Event Schedule in action",
        "numberOfItems": {{ $scheduleCount }},
        "itemListElement": [
            @foreach($allSchedules as $index => $schedule)
            {
                "@type": "ListItem",
                "position": {{ $index + 1 }},
                "name": "{{ $schedule['name'] }}",
                "url": "{{ $schedule['url'] }}"
            }@if(!$loop->last),@endif
            @endforeach
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Can I create a schedule like these?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes! Event Schedule is free to use. Sign up and you can create a schedule just like these examples in minutes. All the features you see are available on the free plan."
                }
            },
            {
                "@type": "Question",
                "name": "Are these real schedules?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "These are demo schedules we've created to showcase different use cases and features. They demonstrate what's possible with Event Schedule for various industries including fitness, music, coworking, and more."
                }
            },
            {
                "@type": "Question",
                "name": "How long does it take to set up?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Most users have their schedule live within 5 minutes. Simply sign up, add your events (or import from Google Calendar), customize your colors, and share your link. No technical skills required."
                }
            }
        ]
    }
    </script>
    </x-slot>

    {{-- Hero Section --}}
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background blobs -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $scheduleCount }} Live Examples</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                See Event Schedule<br>
                <span class="text-gradient">in action</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                Explore demo schedules for fitness studios, music venues, coworking spaces, and more. Click any example to see Event Schedule live.
            </p>
        </div>
    </section>

    {{-- Demo Grid Section with Category Grouping --}}
    <section class="bg-gray-50 dark:bg-[#0d0d14] py-24" aria-labelledby="demos-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="demos-heading" class="sr-only">Demo Schedules Gallery</h2>

            @foreach($categories as $categoryName => $schedules)
            <div class="mb-16 last:mb-0">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">{{ $categoryName }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8" role="list">
                @foreach($schedules as $schedule)
                <article role="listitem">
                    <a href="{{ $schedule['url'] }}"
                       target="_blank"
                       rel="noopener"
                       aria-label="View {{ $schedule['name'] }} schedule"
                       class="group block relative overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/20 hover:border-blue-400/50 dark:hover:border-blue-400/30 transition-all duration-300 ease-out h-full">

                        @if($schedule['header_image_url'] ?? false)
                            {{-- Header image background --}}
                            <div class="absolute inset-0 bg-cover bg-center bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900/50 dark:to-sky-900/50 transition-transform duration-500 group-hover:scale-110"
                                 style="background-image: url('{{ $schedule['header_image_url'] }}');"
                                 role="img"
                                 aria-label="{{ $schedule['name'] }} header image">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-black/20"></div>
                            </div>

                            {{-- Content overlay --}}
                            <div class="relative p-8 min-h-[280px] flex flex-col justify-end">
                                @if($schedule['profile_image_url'] ?? false)
                                    <img src="{{ $schedule['profile_image_url'] }}"
                                         alt="{{ $schedule['name'] }} logo"
                                         class="w-16 h-16 rounded-full object-cover mb-4 border-2 border-white/20 transition-transform duration-300 group-hover:-translate-y-1"
                                         loading="lazy"
                                         width="64"
                                         height="64">
                                @else
                                    {{-- Initial letter fallback for schedules without profile image --}}
                                    <div class="w-16 h-16 rounded-full mb-4 flex items-center justify-center text-white text-xl font-bold bg-[#4E81FA] border-2 border-white/20"
                                         aria-hidden="true">
                                        {{ strtoupper(substr($schedule['name'], 0, 1)) }}
                                    </div>
                                @endif
                                <h4 class="text-2xl font-bold text-white mb-2">{{ $schedule['name'] }}</h4>
                            </div>
                        @else
                            {{-- Fallback gradient background --}}
                            <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900/50 dark:to-sky-900/50 h-full">
                                <div class="p-8 min-h-[280px] flex flex-col justify-end">
                                    @if($schedule['profile_image_url'] ?? false)
                                        <img src="{{ $schedule['profile_image_url'] }}"
                                             alt="{{ $schedule['name'] }} logo"
                                             class="w-16 h-16 rounded-full object-cover mb-4 border-2 border-gray-200 dark:border-white/20 transition-transform duration-300 group-hover:-translate-y-1"
                                             loading="lazy"
                                             width="64"
                                             height="64">
                                    @else
                                        <div class="w-16 h-16 rounded-full mb-4 flex items-center justify-center text-white text-xl font-bold bg-[#4E81FA]"
                                             aria-hidden="true">
                                            {{ strtoupper(substr($schedule['name'], 0, 1)) }}
                                        </div>
                                    @endif
                                    <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ $schedule['name'] }}</h4>
                                </div>
                            </div>
                        @endif

                        {{-- Hover overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                        {{-- External link indicator --}}
                        <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-5 h-5 {{ ($schedule['header_image_url'] ?? false) ? 'text-white' : 'text-gray-600 dark:text-white' }} drop-shadow-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </div>
                    </a>
                </article>
                @endforeach
                </div>
            </div>
            @endforeach

            @if(count($categories) === 0)
                <div class="text-center py-12">
                    <p class="text-gray-600 dark:text-gray-400">Demo schedules coming soon.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- FAQ Section for Rich Snippets --}}
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-12 text-center">Frequently Asked Questions</h2>

            <div class="space-y-6">
                <details class="group bg-gray-50 dark:bg-white/5 rounded-2xl p-6">
                    <summary class="flex justify-between items-center cursor-pointer list-none">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Can I create a schedule like these?</h3>
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </summary>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">Yes! Event Schedule is free to use. Sign up and you can create a schedule just like these examples in minutes. All the features you see are available on the free plan.</p>
                </details>

                <details class="group bg-gray-50 dark:bg-white/5 rounded-2xl p-6">
                    <summary class="flex justify-between items-center cursor-pointer list-none">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Are these real schedules?</h3>
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </summary>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">These are demo schedules we've created to showcase different use cases and features. They demonstrate what's possible with Event Schedule for various industries including fitness, music, coworking, and more.</p>
                </details>

                <details class="group bg-gray-50 dark:bg-white/5 rounded-2xl p-6">
                    <summary class="flex justify-between items-center cursor-pointer list-none">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">How long does it take to set up?</h3>
                        <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </summary>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">Most users have their schedule live within 5 minutes. Simply sign up, add your events (or import from Google Calendar), customize your colors, and share your link. No technical skills required.</p>
                </details>
            </div>
        </div>
    </section>

    {{-- Use Cases Section (internal linking for SEO) --}}
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Built for every industry
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    See how different organizations use Event Schedule
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ marketing_url('/for-fitness-and-yoga') }}" class="p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors text-center">
                    <span class="text-gray-700 dark:text-gray-300">Fitness & Yoga</span>
                </a>
                <a href="{{ marketing_url('/for-music-venues') }}" class="p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors text-center">
                    <span class="text-gray-700 dark:text-gray-300">Music Venues</span>
                </a>
                <a href="{{ marketing_url('/for-workshop-instructors') }}" class="p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors text-center">
                    <span class="text-gray-700 dark:text-gray-300">Workshops</span>
                </a>
                <a href="{{ marketing_url('/for-community-centers') }}" class="p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors text-center">
                    <span class="text-gray-700 dark:text-gray-300">Community Centers</span>
                </a>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to create your own?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start sharing your events in minutes. Free forever, no credit card required.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
