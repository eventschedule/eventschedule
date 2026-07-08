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

    <style {!! nonce_attr() !!}>
        .text-gradient-browse {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-browse {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    {{-- Hero --}}
    <section class="es-hero relative flex items-center overflow-hidden bg-white py-20 dark:bg-[#0a0a0f] noise lg:py-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Browse</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Discover upcoming <span class="text-gradient-browse">events</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-600 dark:text-gray-400 sm:text-xl">
                Explore public shows, classes, and meetups happening across the community. Looking for something specific?
            </p>

            <form action="{{ marketing_url('/search') }}" method="GET" class="es-fade-up es-d-3 mx-auto max-w-2xl">
                <label for="browse-search" class="sr-only">Search events and schedules</label>
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <svg aria-hidden="true" class="absolute top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400 ltr:left-4 rtl:right-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            id="browse-search"
                            type="search"
                            name="q"
                            placeholder="{{ __('messages.search') }}..."
                            class="w-full rounded-2xl border border-gray-200 bg-white py-4 text-lg text-gray-900 placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/5 dark:text-white ltr:pl-12 ltr:pr-4 rtl:pl-4 rtl:pr-12"
                        >
                    </div>
                    <button type="submit" class="rounded-2xl bg-gradient-to-r from-[#4E81FA] to-sky-500 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:scale-105">
                        {{ __('messages.search') }}
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Events --}}
    <section class="bg-gray-50 py-24 dark:bg-[#0d0d14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if(session('message'))
                <div class="mb-8 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                    {{ session('message') }}
                </div>
            @endif

            @if($events->count() > 0)
                <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.upcoming_events') }}</h2>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($events as $event)
                        @include('marketing.partials.event-card', ['event' => $event])
                    @endforeach
                </div>
            @else
                {{-- Empty state --}}
                <div class="py-16 text-center">
                    <svg aria-hidden="true" class="mx-auto mb-6 h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">No upcoming events yet</h2>
                    <p class="mx-auto mb-8 max-w-md text-gray-600 dark:text-gray-400">
                        There are no public events to show right now. Be the first to share yours.
                    </p>
                    <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-[#4E81FA] to-sky-500 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:scale-105">
                        {{ __('messages.create_your_schedule') }}
                        <svg aria-hidden="true" class="ml-2 h-5 w-5 rtl:ml-0 rtl:mr-2 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            @endif

            {{-- Admin-only: hidden events management --}}
            @if($hiddenEvents->count() > 0)
                <div class="mt-20">
                    <div class="mb-2 flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Hidden events</h2>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">{{ $hiddenEvents->count() }}</span>
                    </div>
                    <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Only admins see this. These events are hidden from the homepage, Browse, and search. Click Unhide to restore one.</p>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($hiddenEvents as $event)
                            @include('marketing.partials.event-card', ['event' => $event])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Bottom CTA --}}
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="es-balance mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">
                {{ __('messages.create_your_own_schedule') }}
            </h2>
            <p class="mx-auto mb-10 max-w-2xl text-xl text-gray-500 dark:text-gray-400">
                {{ __('messages.share_events_cta') }}
            </p>
            <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center rounded-2xl bg-gradient-to-r from-[#4E81FA] to-sky-500 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all hover:scale-105">
                {{ __('messages.get_started_free') }}
                <svg aria-hidden="true" class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1 rtl:ml-0 rtl:mr-2 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
