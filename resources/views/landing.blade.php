<x-app-layout :title="__('messages.events')">
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center text-white">
            <h1 class="text-4xl sm:text-5xl font-bold tracking-tight">
                {{ $isAuthenticated ? __('messages.your_events') : __('messages.upcoming_events') }}
            </h1>
            <p class="mt-4 text-lg sm:text-xl text-slate-200">
                @if($isAuthenticated)
                    {{ __('messages.manage_calendar_intro') }}
                @else
                    {{ __('messages.discover_public_events') }}
                @endif
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-800 p-6 sm:p-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">

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
                    @endphp
                    <a href="{{ route('landing', $resetParams) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                        {{ __('messages.clear_filters') }}
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-800 p-4 sm:p-6">
            @include('role/partials/calendar', [
                'route' => 'landing',
                'tab' => '',
                'events' => $calendarEvents,
                'month' => $month,
                'year' => $year,
                'startOfMonth' => $startOfMonth,
                'endOfMonth' => $endOfMonth,
                'category' => $selectedFilters['category'] ?? null,
                'calendarQueryParams' => $calendarQueryParams,
            ])
        </div>
    </div>
</x-app-layout>
