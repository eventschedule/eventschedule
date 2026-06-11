<x-marketing-layout>
    {{-- SEO Slots --}}
    <x-slot name="title">Browse Upcoming Events | Event Schedule</x-slot>
    <x-slot name="description">Discover upcoming public events, shows, classes, and meetups happening across Event Schedule. Browse what's on, or search for something specific.</x-slot>
    <x-slot name="breadcrumbTitle">Browse</x-slot>

    {{-- Structured data: list only the publicly visible events --}}
    <x-slot name="structuredData">
    @php
        $itemListElements = [];
        $pos = 1;
        foreach ($events as $e) {
            $u = $e->getGuestUrl();
            if (! $u) {
                continue;
            }
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'url' => $u,
                'name' => $e->name,
            ];
        }
    @endphp
    @if(count($itemListElements))
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Upcoming events on Event Schedule',
        'url' => url('/browse'),
        'itemListElement' => $itemListElements,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
    </x-slot>

    {{-- Hero --}}
    <section class="relative bg-white dark:bg-[#0a0a0f] py-28 lg:py-32 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Browse</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Discover upcoming <span class="text-gradient">events</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Explore public shows, classes, and meetups happening across the community. Looking for something specific?
            </p>

            <form action="{{ marketing_url('/search') }}" method="GET" class="max-w-2xl mx-auto animate-reveal delay-300" style="opacity: 0;">
                <label for="browse-search" class="sr-only">Search events and schedules</label>
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <svg aria-hidden="true" class="absolute ltr:left-4 rtl:right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            id="browse-search"
                            type="search"
                            name="q"
                            placeholder="{{ __('messages.search') }}..."
                            class="w-full ltr:pl-12 rtl:pr-12 ltr:pr-4 rtl:pl-4 py-4 text-lg rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-transparent"
                        >
                    </div>
                    <button type="submit" class="px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-[#4E81FA] to-sky-500 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                        {{ __('messages.search') }}
                    </button>
                </div>
            </form>
        </div>
    </section>

    <div class="h-24 section-fade-to-gray"></div>

    {{-- Events --}}
    <section class="bg-gray-50 dark:bg-[#0d0d14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('message'))
                <div class="mb-8 rounded-xl border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 px-4 py-3 text-sm text-blue-700 dark:text-blue-300">
                    {{ session('message') }}
                </div>
            @endif

            @if($events->count() > 0)
                <div class="flex items-center gap-3 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.upcoming_events') }}</h2>
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400">{{ $events->count() }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($events as $event)
                        @include('marketing.partials.event-card', ['event' => $event])
                    @endforeach
                </div>
            @else
                {{-- Empty state --}}
                <div class="text-center py-16">
                    <svg aria-hidden="true" class="w-16 h-16 mx-auto mb-6 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">No upcoming events yet</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                        There are no public events to show right now. Be the first to share yours.
                    </p>
                    <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-[#4E81FA] to-sky-500 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                        {{ __('messages.create_your_schedule') }}
                        <svg aria-hidden="true" class="ml-2 w-5 h-5 rtl:ml-0 rtl:mr-2 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            @endif

            {{-- Admin-only: hidden events management --}}
            @if($hiddenEvents->count() > 0)
                <div class="mt-20">
                    <div class="flex items-center gap-3 mb-2">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Hidden events</h2>
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400">{{ $hiddenEvents->count() }}</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Only admins see this. These events are hidden from the homepage, Browse, and search. Click Unhide to restore one.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($hiddenEvents as $event)
                            @include('marketing.partials.event-card', ['event' => $event])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <div class="h-24 section-fade-to-white"></div>

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
