@php
    $content = $homeContent ?? [];
    $layout = $content['layout'] ?? \App\Support\HomePageSettings::LAYOUT_FULL;
    $hero = $content['hero'] ?? [];
    $aside = $content['aside'] ?? [];
    $heroTitle = $hero['title'] ?? ($isAuthenticated ? __('messages.your_events') : __('messages.upcoming_events'));
    $heroHtml = $hero['html'] ?? null;
    $heroCta = $hero['cta'] ?? ['label' => null, 'url' => null];
    $heroLogo = $hero['logo'] ?? ['url' => null, 'alt' => null];
    $heroLogoUrl = $heroLogo['url'] ?? null;
    $heroLogoAlt = $heroLogo['alt'] ?? $heroTitle;
    $loginCta = null;
    $showHeroCta = filled($heroCta['label'] ?? null) && filled($heroCta['url'] ?? null);

    if ($showHeroCta) {
        $defaultLoginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : null;
        $isLoginCta = $defaultLoginUrl
            && ($heroCta['label'] ?? null) === __('messages.log_in')
            && ($heroCta['url'] ?? null) === $defaultLoginUrl;

        if ($isLoginCta) {
            $loginCta = $heroCta;
            $showHeroCta = false;
        }
    }
    $asideImage = $aside['image'] ?? ['url' => null, 'alt' => null];
    $showAside = filled($aside['title'] ?? null) || filled($aside['html'] ?? null) || filled($asideImage['url'] ?? null);

    if (! $showAside) {
        $layout = \App\Support\HomePageSettings::LAYOUT_FULL;
    }

    $gridWrapperClass = $layout === \App\Support\HomePageSettings::LAYOUT_FULL
        ? 'space-y-10'
        : 'grid gap-10 lg:grid-cols-12 items-start';

    $calendarColumnClass = match ($layout) {
        \App\Support\HomePageSettings::LAYOUT_LEFT => 'space-y-6 lg:col-span-7',
        \App\Support\HomePageSettings::LAYOUT_RIGHT => 'space-y-6 lg:col-span-7 lg:order-2',
        default => 'space-y-6',
    };

    $asideColumnClass = match ($layout) {
        \App\Support\HomePageSettings::LAYOUT_LEFT => 'lg:col-span-5',
        \App\Support\HomePageSettings::LAYOUT_RIGHT => 'lg:col-span-5 lg:order-1',
        default => '',
    };

    $calendarRouteName = request()->routeIs('landing') ? 'landing' : 'home';
    $viewMode = $viewMode ?? 'calendar';
    $isListView = $viewMode === 'list';
    $viewToggleBaseParams = array_merge(
        ['month' => $month, 'year' => $year],
        collect($selectedFilters ?? [])->filter(function ($value) {
            return $value !== null && $value !== '';
        })->all()
    );

    if (request()->has('embed')) {
        $viewToggleBaseParams['embed'] = request()->query('embed');
    }

    if ($calendarRouteName === 'home') {
        $viewToggleBaseParams = array_merge(['slug' => null], $viewToggleBaseParams);
    }

    $calendarViewParams = $viewToggleBaseParams;
    $listViewParams = array_merge($viewToggleBaseParams, ['view' => 'list']);
@endphp

<x-app-layout :title="__('messages.events')">
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 text-center text-white">
            @if($heroLogoUrl)
                <div class="flex justify-center mb-6">
                    <img src="{{ $heroLogoUrl }}" alt="{{ $heroLogoAlt }}" class="h-20 w-auto sm:h-24 object-contain" loading="lazy">
                </div>
            @endif
            <h1 class="text-4xl sm:text-5xl font-bold tracking-tight">
                {{ $heroTitle }}
            </h1>
            @if($heroHtml)
                <div class="mt-4 text-lg sm:text-xl text-slate-200 leading-relaxed space-y-4 [&_p]:m-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-white [&_a]:underline">
                    {!! $heroHtml !!}
                </div>
            @endif
            @if($showHeroCta)
                <div class="mt-8 flex justify-center">
                    <a href="{{ $heroCta['url'] }}"
                        class="inline-flex items-center gap-2 rounded-full bg-white/10 px-6 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-white/20 transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        {{ $heroCta['label'] }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
        <div class="{{ $gridWrapperClass }}">
            <div class="{{ $calendarColumnClass }}">
                <div class="bg-white dark:bg-gray-900 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-800 p-6 sm:p-8">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        @if($isListView)
                            <input type="hidden" name="view" value="list">
                        @endif

                        <div>
                            <label for="filter-category" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                {{ __('messages.event_type') }}
                            </label>
                            <select id="filter-category" name="category" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('messages.all_categories') }}</option>
                                @foreach($categories as $id => $label)
                                    <option value="{{ $id }}" @selected(($selectedFilters['category'] ?? '') == (string) $id)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter-venue" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                {{ __('messages.venue') }}
                            </label>
                            <select id="filter-venue" name="venue" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('messages.all_venues') }}</option>
                                @foreach($venueOptions as $venue)
                                    <option value="{{ $venue['id'] }}" @selected(($selectedFilters['venue'] ?? '') === $venue['id'])>
                                        {{ $venue['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter-curator" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                {{ __('messages.curator') }}
                            </label>
                            <select id="filter-curator" name="curator" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('messages.all_curators') }}</option>
                                @foreach($curatorOptions as $curator)
                                    <option value="{{ $curator['id'] }}" @selected(($selectedFilters['curator'] ?? '') === $curator['id'])>
                                        {{ $curator['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter-talent" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                {{ __('messages.talent') }}
                            </label>
                            <select id="filter-talent" name="talent" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('messages.all_talent') }}</option>
                                @foreach($talentOptions as $talent)
                                    <option value="{{ $talent['id'] }}" @selected(($selectedFilters['talent'] ?? '') === $talent['id'])>
                                        {{ $talent['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2 xl:col-span-4 flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{ __('messages.apply_filters') }}
                            </button>
                            @php
                                $resetParams = array_merge(['month' => $month, 'year' => $year], []);

                                if ($calendarRouteName === 'home') {
                                    $resetParams = array_merge(['slug' => null], $resetParams);
                                }

                                if ($isListView) {
                                    $resetParams['view'] = 'list';
                                }

                                if (request()->has('embed')) {
                                    $resetParams['embed'] = request()->query('embed');
                                }
                            @endphp
                            <a href="{{ route($calendarRouteName, $resetParams) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                {{ __('messages.clear_filters') }}
                            </a>
                        </div>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-900 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-800 p-4 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                        <div class="inline-flex items-center rounded-full bg-slate-100 p-1 text-sm font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <a href="{{ route($calendarRouteName, $calendarViewParams) }}"
                               class="@class([
                                   'inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
                                   'bg-white text-slate-900 shadow-sm dark:bg-slate-900 dark:text-white' => ! $isListView,
                                   'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white' => $isListView,
                               ])">
                                {{ __('messages.calendar') }}
                            </a>
                            <a href="{{ route($calendarRouteName, $listViewParams) }}"
                               class="@class([
                                   'inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
                                   'bg-white text-slate-900 shadow-sm dark:bg-slate-900 dark:text-white' => $isListView,
                                   'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white' => ! $isListView,
                               ])">
                                {{ __('messages.list') }}
                            </a>
                        </div>
                    </div>

                    <div class="pt-4 sm:pt-6">
                        @if($isListView)
                            @php
                                $currentMonthDate = \Carbon\Carbon::create($year, $month, 1);
                                $localizedMonth = $currentMonthDate->copy()->locale(app()->getLocale())->translatedFormat('F Y');
                                $previousMonthDate = $currentMonthDate->copy()->subMonth();
                                $nextMonthDate = $currentMonthDate->copy()->addMonth();
                                $listNavBase = $calendarQueryParams;

                                if (request()->has('embed')) {
                                    $listNavBase['embed'] = request()->query('embed');
                                }

                                $previousParams = array_merge($listNavBase, [
                                    'year' => $previousMonthDate->year,
                                    'month' => $previousMonthDate->month,
                                ]);
                                $nextParams = array_merge($listNavBase, [
                                    'year' => $nextMonthDate->year,
                                    'month' => $nextMonthDate->month,
                                ]);
                                $currentParams = array_merge($listNavBase, [
                                    'year' => now()->year,
                                    'month' => now()->month,
                                ]);

                                if ($calendarRouteName === 'home') {
                                    $previousParams = array_merge(['slug' => null], $previousParams);
                                    $nextParams = array_merge(['slug' => null], $nextParams);
                                    $currentParams = array_merge(['slug' => null], $currentParams);
                                }
                            @endphp

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $localizedMonth }}</h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ __('messages.upcoming_events') }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route($calendarRouteName, $previousParams) }}"
                                       class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                        </svg>
                                        {{ __('messages.previous_month') }}
                                    </a>
                                    <a href="{{ route($calendarRouteName, $currentParams) }}"
                                       class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ __('messages.today') }}
                                    </a>
                                    <a href="{{ route($calendarRouteName, $nextParams) }}"
                                       class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                        {{ __('messages.next_month') }}
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <div class="mt-6 space-y-5">
                                @forelse($calendarEvents as $occurrence)
                                    @php
                                        $event = $occurrence['event'];
                                        $eventUrl = $event->getGuestUrl(false, true);
                                        $startsAt = $occurrence['occurs_at_display'] ?? $event->localStartsAt(true);
                                        $venueName = $event->getVenueDisplayName();
                                        $talentList = $event->roles->filter(function ($role) {
                                            return $role->isTalent();
                                        })->map->translatedName()->implode(', ');
                                        $categoryLabel = ($event->category_id && isset($categories[$event->category_id]))
                                            ? $categories[$event->category_id]
                                            : null;
                                        $isRecurringEvent = ! empty($event->days_of_week);
                                    @endphp

                                    <article class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-900">
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="space-y-3">
                                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                                        <svg class="h-4 w-4 text-slate-500 dark:text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $startsAt }}
                                                    </span>
                                                    @if($isRecurringEvent)
                                                        <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.227V5.121" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-3.874-7.442" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.977 14.652H3.75v4.227" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 013.874-7.442" />
                                                            </svg>
                                                            {{ __('messages.recurring') }}
                                                        </span>
                                                    @endif
                                                    @if($categoryLabel)
                                                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                                            {{ $categoryLabel }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <h3 class="text-2xl font-semibold text-gray-900 transition-colors group-hover:text-indigo-600 dark:text-gray-100">
                                                    @if($eventUrl)
                                                        <a href="{{ $eventUrl }}">{{ $event->translatedName() }}</a>
                                                    @else
                                                        {{ $event->translatedName() }}
                                                    @endif
                                                </h3>

                                                @if($venueName)
                                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                        <svg class="h-4 w-4 text-slate-500 dark:text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.5-7.5 11.25-7.5 11.25S4.5 18 4.5 10.5a7.5 7.5 0 1115 0z" />
                                                        </svg>
                                                        <span>{{ $venueName }}</span>
                                                    </div>
                                                @endif

                                                @if($talentList)
                                                    <div class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                                        <svg class="h-4 w-4 text-slate-500 dark:text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 20v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 11a4 4 0 100-8 4 4 0 000 8z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 11a4 4 0 10-3.5-6.32" />
                                                        </svg>
                                                        <span>{{ $talentList }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            @if($eventUrl)
                                                <div class="flex items-start">
                                                    <a href="{{ $eventUrl }}"
                                                       class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                                        {{ __('messages.view_event') }}
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </article>
                                @empty
                                    <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                        {{ __('messages.no_events_found') }}
                                    </div>
                                @endforelse
                            </div>
                        @else
                            @include('role/partials/calendar', [
                                'route' => $calendarRouteName,
                                'tab' => '',
                                'events' => $calendarEvents,
                                'month' => $month,
                                'year' => $year,
                                'startOfMonth' => $startOfMonth,
                                'endOfMonth' => $endOfMonth,
                                'category' => $selectedFilters['category'] ?? null,
                                'calendarQueryParams' => $calendarQueryParams,
                            ])
                        @endif
                    </div>
                </div>
            </div>

            @if($showAside && $layout !== \App\Support\HomePageSettings::LAYOUT_FULL)
                <div class="space-y-6 {{ $asideColumnClass }}">
                    @include('landing.partials.aside-card', [
                        'title' => $aside['title'] ?? null,
                        'html' => $aside['html'] ?? null,
                        'imageUrl' => $asideImage['url'] ?? null,
                        'imageAlt' => $asideImage['alt'] ?? null,
                    ])
                </div>
            @endif
        </div>

        @if($showAside && $layout === \App\Support\HomePageSettings::LAYOUT_FULL)
            <div class="max-w-4xl mx-auto w-full">
                @include('landing.partials.aside-card', [
                    'title' => $aside['title'] ?? null,
                    'html' => $aside['html'] ?? null,
                    'imageUrl' => $asideImage['url'] ?? null,
                    'imageAlt' => $asideImage['alt'] ?? null,
                ])
            </div>
        @endif
    </div>

    <x-slot name="footer">
        <footer class="bg-slate-950">
            @if(request()->embed)
                <div class="px-4 py-5 text-center text-sm text-slate-300">
                    {!! str_replace(':link', '<a href="https://www.eventschedule.com" target="_blank" rel="noopener" class="hover:underline font-medium text-white">EventSchedule</a>', __('messages.powered_by_eventschedule')) !!}
                </div>
            @else
                <div class="max-w-6xl mx-auto flex flex-col gap-4 px-4 py-8 text-center sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <div class="text-sm text-slate-300">
                        {!! str_replace(':link', '<a href="https://www.eventschedule.com" target="_blank" rel="noopener" class="hover:underline font-medium text-white">EventSchedule</a>', __('messages.powered_by_eventschedule')) !!}
                    </div>

                    @if($loginCta)
                        <div>
                            <a href="{{ $loginCta['url'] }}"
                               class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                {{ $loginCta['label'] }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </footer>
    </x-slot>
</x-app-layout>
