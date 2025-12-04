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
    $heroAlignment = \App\Support\HomePageSettings::normalizeHeroAlignment($hero['alignment'] ?? null);
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
            if (is_array($value)) {
                return count(array_filter($value, function ($item) {
                    return $item !== null && $item !== '';
                })) > 0;
            }

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

    $heroContainerClass = match ($heroAlignment) {
        \App\Support\HomePageSettings::HERO_ALIGN_LEFT => 'flex flex-col items-center gap-6 text-center md:flex-row md:items-center md:justify-between md:text-left',
        \App\Support\HomePageSettings::HERO_ALIGN_RIGHT => 'flex flex-col items-center gap-6 text-center md:flex-row-reverse md:items-center md:justify-between md:text-left',
        default => 'flex flex-col items-center gap-6 text-center',
    };

    $heroTextContainerClass = $heroAlignment === \App\Support\HomePageSettings::HERO_ALIGN_CENTER
        ? 'flex flex-col items-center gap-4 max-w-3xl'
        : 'flex flex-col gap-4 md:max-w-2xl';

    $heroLogoWrapperClass = match ($heroAlignment) {
        \App\Support\HomePageSettings::HERO_ALIGN_LEFT => 'flex justify-center md:justify-start',
        \App\Support\HomePageSettings::HERO_ALIGN_RIGHT => 'flex justify-center md:justify-end',
        default => 'flex justify-center',
    };

    $heroBodyClass = $heroAlignment === \App\Support\HomePageSettings::HERO_ALIGN_CENTER
        ? 'mt-2 text-lg sm:text-xl text-slate-200 leading-relaxed space-y-4 text-center [&_p]:m-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-white [&_a]:underline'
        : 'mt-2 text-lg sm:text-xl text-slate-200 leading-relaxed space-y-4 text-left [&_p]:m-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-white [&_a]:underline';

    $heroCtaAlignmentClass = $heroAlignment === \App\Support\HomePageSettings::HERO_ALIGN_CENTER
        ? 'justify-center'
        : 'justify-start md:justify-start';
@endphp

<x-app-layout :title="__('messages.events')">
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 text-white">
            <div class="{{ $heroContainerClass }}">
                @if($heroLogoUrl)
                    <div class="{{ $heroLogoWrapperClass }} w-full md:w-auto">
                        <img src="{{ $heroLogoUrl }}" alt="{{ $heroLogoAlt }}" class="h-20 w-auto sm:h-24 object-contain" loading="lazy">
                    </div>
                @endif

                <div class="{{ $heroTextContainerClass }}">
                    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight text-white {{ $heroAlignment === \App\Support\HomePageSettings::HERO_ALIGN_CENTER ? 'text-center' : 'text-left md:text-left' }}">
                        {{ $heroTitle }}
                    </h1>

                    @if($heroHtml)
                        <div class="{{ $heroBodyClass }}">
                            {!! $heroHtml !!}
                        </div>
                    @endif

                    @if($showHeroCta)
                        <div class="mt-4 flex {{ $heroCtaAlignmentClass }}">
                            <a href="{{ $heroCta['url'] }}"
                                class="inline-flex items-center gap-2 rounded-full bg-white/10 px-6 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-inset ring-white/20 transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                                {{ $heroCta['label'] }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
        <div class="{{ $gridWrapperClass }}">
            <div class="{{ $calendarColumnClass }}">
                @php
                    $selectedCategories = array_map('strval', $selectedFilters['category'] ?? []);
                    $selectedVenues = $selectedFilters['venue'] ?? [];
                    $selectedCurators = $selectedFilters['curator'] ?? [];
                    $selectedTalent = $selectedFilters['talent'] ?? [];
                    $filtersOpenDefault = $hasActiveFilters ?? false;
                @endphp

                <div x-data="{ filtersOpen: {{ $filtersOpenDefault ? 'true' : 'false' }} }" class="bg-white dark:bg-gray-900 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-800 p-6 sm:p-8">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ __('messages.filter') }}</span>
                                @if(($activeFilterCount ?? 0) > 0)
                                    <span class="inline-flex items-center justify-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                                        {{ $activeFilterCount }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.home_filters_hint') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" @click="filtersOpen = !filtersOpen" :aria-expanded="filtersOpen.toString()">
                                <svg class="h-4 w-4 transition" :class="{ 'rotate-180': filtersOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                                <span x-show="!filtersOpen" x-cloak>{{ __('messages.show_filters') }}</span>
                                <span x-show="filtersOpen" x-cloak>{{ __('messages.hide_filters') }}</span>
                            </button>
                        </div>
                    </div>

                    <div x-show="filtersOpen" x-cloak class="mt-5 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <form method="GET" class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            @if($isListView)
                                <input type="hidden" name="view" value="list">
                            @endif

                            <div class="space-y-2">
                                <label for="filter-category" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    {{ __('messages.event_type') }}
                                </label>
                                <select id="filter-category" name="category[]" multiple size="4" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($categories as $id => $label)
                                        <option value="{{ $id }}" @selected(in_array((string) $id, $selectedCategories, true))>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="filter-venue" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    {{ __('messages.venue') }}
                                </label>
                                <select id="filter-venue" name="venue[]" multiple size="4" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($venueOptions as $venue)
                                        <option value="{{ $venue['id'] }}" @selected(in_array($venue['id'], $selectedVenues, true))>
                                            {{ $venue['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="filter-curator" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    {{ __('messages.curator') }}
                                </label>
                                <select id="filter-curator" name="curator[]" multiple size="4" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($curatorOptions as $curator)
                                        <option value="{{ $curator['id'] }}" @selected(in_array($curator['id'], $selectedCurators, true))>
                                            {{ $curator['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label for="filter-talent" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    {{ __('messages.talent') }}
                                </label>
                                <select id="filter-talent" name="talent[]" multiple size="4" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($talentOptions as $talent)
                                        <option value="{{ $talent['id'] }}" @selected(in_array($talent['id'], $selectedTalent, true))>
                                            {{ $talent['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:col-span-2 lg:col-span-3 xl:col-span-1 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
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
                </div>

                <div class="bg-white dark:bg-gray-900 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-800 p-4 sm:p-6">
                    <div class="pt-2 sm:pt-4">
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

                                $monthOptions = collect(range(-5, 6))->map(function ($offset) use ($currentMonthDate, $listNavBase, $calendarRouteName) {
                                    $optionDate = $currentMonthDate->copy()->addMonths($offset);
                                    $params = array_merge($listNavBase, [
                                        'year' => $optionDate->year,
                                        'month' => $optionDate->month,
                                    ]);

                                    if ($calendarRouteName === 'home') {
                                        $params = array_merge(['slug' => null], $params);
                                    }

                                    return [
                                        'label' => $optionDate->copy()->locale(app()->getLocale())->translatedFormat('F Y'),
                                        'params' => $params,
                                        'is_current' => $optionDate->isSameMonth($currentMonthDate) && $optionDate->isSameYear($currentMonthDate),
                                    ];
                                });
                            @endphp

                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $localizedMonth }}</h2>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ __('messages.upcoming_events') }}</p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                        <a href="{{ route($calendarRouteName, $previousParams) }}"
                                           class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                            </svg>
                                            {{ __('messages.previous_month') }}
                                        </a>

                                        <div class="relative" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ __('messages.this_month') }}</span>
                                                <svg class="h-4 w-4 transition" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                                </svg>
                                            </button>

                                            <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-20 mt-2 w-56 overflow-hidden rounded-lg border border-gray-100 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <div class="max-h-64 overflow-y-auto py-2">
                                                    @foreach($monthOptions as $option)
                                                        <a href="{{ route($calendarRouteName, $option['params']) }}" class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                                                            <span>{{ $option['label'] }}</span>
                                                            @if($option['is_current'])
                                                                <svg class="h-4 w-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                                </svg>
                                                            @endif
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <a href="{{ route($calendarRouteName, $nextParams) }}"
                                           class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                            {{ __('messages.next_month') }}
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </a>

                                        @include('landing.partials.layout-toggle', [
                                            'calendarRouteName' => $calendarRouteName,
                                            'listViewParams' => $listViewParams,
                                            'calendarViewParams' => $calendarViewParams,
                                            'isListView' => $isListView,
                                            'wrapperClass' => 'shadow-sm'
                                        ])
                                    </div>
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
                            <div class="mb-4 flex items-center justify-end md:hidden">
                                @include('landing.partials.layout-toggle', [
                                    'calendarRouteName' => $calendarRouteName,
                                    'listViewParams' => $listViewParams,
                                    'calendarViewParams' => $calendarViewParams,
                                    'isListView' => $isListView,
                                    'wrapperClass' => 'shadow-sm'
                                ])
                            </div>
                            @include('role/partials/calendar', [
                                'route' => $calendarRouteName,
                                'tab' => '',
                                'events' => $calendarEvents,
                                'month' => $month,
                                'year' => $year,
                                'startOfMonth' => $startOfMonth,
                                'endOfMonth' => $endOfMonth,
                                'category' => $selectedFilters['category'][0] ?? null,
                                'calendarQueryParams' => $calendarQueryParams,
                                'layoutToggleParams' => [
                                    'calendarRouteName' => $calendarRouteName,
                                    'listViewParams' => $listViewParams,
                                    'calendarViewParams' => $calendarViewParams,
                                    'isListView' => $isListView,
                                    'wrapperClass' => 'shadow-sm',
                                ],
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
