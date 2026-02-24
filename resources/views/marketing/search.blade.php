<x-marketing-layout>
    {{-- SEO Slots --}}
    <x-slot name="title">Search Schedules & Events | Event Schedule</x-slot>
    <x-slot name="description">Search for schedules and upcoming events on Event Schedule. Find fitness classes, music venues, community groups, and more.</x-slot>
    <x-slot name="breadcrumbTitle">{{ __('messages.search') }}</x-slot>

    @if($query)
    <x-slot name="robots">noindex, follow</x-slot>
    @endif

    {{-- Structured Data --}}
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SearchResultsPage",
        "name": "Search Event Schedule",
        "url": "{{ url('/search') }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        }
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
                <svg aria-hidden="true" class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('messages.discover') }}</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                {{ __('messages.find_schedules_and_events') }}
            </h1>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                {{ __('messages.search_page_subtitle') }}
            </p>

            <form action="{{ marketing_url('/search') }}" method="GET" class="max-w-2xl mx-auto">
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <svg aria-hidden="true" class="absolute ltr:left-4 rtl:right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="search"
                            name="q"
                            value="{{ $query }}"
                            placeholder="{{ __('messages.search') }}..."
                            class="w-full ltr:pl-12 rtl:pr-12 ltr:pr-4 rtl:pl-4 py-4 text-lg rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            autofocus
                        >
                    </div>
                    <button type="submit" class="px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-[#4E81FA] to-sky-500 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                        {{ __('messages.search') }}
                    </button>
                </div>
            </form>
        </div>
    </section>

    @if($query)
    <div class="h-24 section-fade-to-gray"></div>

    <section class="bg-gray-50 dark:bg-[#0d0d14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($searched && $schedules->count() > 0)
            {{-- Schedules Results --}}
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.schedules') }}</h2>
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400">{{ $schedules->count() }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($schedules as $schedule)
                    @php $scheduleUrl = $schedule->getGuestUrl(); @endphp
                    @if($scheduleUrl)
                    <a href="{{ $scheduleUrl }}" class="group flex flex-col bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 hover:shadow-lg hover:border-blue-400/50 dark:hover:border-blue-400/30 transition-all overflow-hidden">
                        <div class="p-6 flex flex-col flex-1">
                            <div class="flex items-center gap-4 mb-4">
                                @if($schedule->profile_image_url)
                                    <img src="{{ $schedule->profile_image_url }}" alt="{{ $schedule->name }}" class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#4E81FA] to-sky-500 flex items-center justify-center text-white font-bold text-lg">
                                        {{ strtoupper(substr($schedule->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $schedule->name }}</h3>
                                    @if($schedule->city)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $schedule->city }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($schedule->short_description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-4">{{ $schedule->short_description }}</p>
                            @endif
                            @if($schedule->type)
                                <div class="mt-auto">
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300">{{ $schedule->type }}</span>
                                </div>
                            @endif
                        </div>
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            @if($searched && $events->count() > 0)
            {{-- Events Results --}}
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.upcoming_events') }}</h2>
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400">{{ $events->count() }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($events as $event)
                    @php $eventUrl = $event->getGuestUrl(); @endphp
                    @if($eventUrl)
                    <a href="{{ $eventUrl }}" class="group flex flex-col bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 hover:shadow-lg hover:border-blue-400/50 dark:hover:border-blue-400/30 transition-all overflow-hidden">
                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-2">{{ $event->name }}</h3>
                            @if($event->short_description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-4">{{ $event->short_description }}</p>
                            @endif
                            <div class="mt-auto flex flex-col gap-2">
                                @if($event->starts_at)
                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($event->starts_at)->format('M j, Y g:ia') }}
                                    </div>
                                @elseif($event->days_of_week)
                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        {{ __('messages.recurring') }}
                                    </div>
                                @endif
                                @if($event->roles->first())
                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        {{ $event->roles->first()->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            @if(!$searched)
            {{-- Query too short --}}
            <div class="text-center py-16">
                <svg aria-hidden="true" class="w-16 h-16 mx-auto mb-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                    {{ __('messages.search_min_length') }}
                </p>
            </div>
            @elseif($schedules->count() === 0 && $events->count() === 0)
            {{-- No Results --}}
            <div class="text-center py-16">
                <svg aria-hidden="true" class="w-16 h-16 mx-auto mb-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ __('messages.no_results_found') }}</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                    {{ __('messages.no_results_message', ['query' => e($query)]) }}
                </p>
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-[#4E81FA] to-sky-500 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    {{ __('messages.create_your_schedule') }}
                    <svg aria-hidden="true" class="ml-2 w-5 h-5 rtl:ml-0 rtl:mr-2 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
            @endif
        </div>
    </section>
    <div class="h-24 section-fade-to-white"></div>
    @endif

    {{-- Bottom CTA --}}
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                {{ __('messages.create_your_own_schedule') }}
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                {{ __('messages.share_events_cta') }}
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-[#4E81FA] to-sky-500 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                {{ __('messages.get_started_free') }}
                <svg aria-hidden="true" class="ml-2 w-5 h-5 rtl:ml-0 rtl:mr-2 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
