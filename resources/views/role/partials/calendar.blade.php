<div class="flex h-full flex-col pt-1" id="calendar-app">
@php
    $startOfMonth = Carbon\Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek(Carbon\Carbon::SUNDAY);
    $endOfMonth = Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek(Carbon\Carbon::SATURDAY);
    $currentDate = $startOfMonth->copy();
    $totalDays = $endOfMonth->diffInDays($startOfMonth) + 1;
    $totalWeeks = ceil($totalDays / 7);
    $unavailable = [];
    $publicUrl = app_public_url();
    $calendarQueryParams = isset($calendarQueryParams) ? array_filter($calendarQueryParams, function ($value) {
        return $value !== null && $value !== '';
    }) : [];

    // Always initialize as arrays
    $eventGroupIds = [];
    $eventCategoryIds = [];

    // Create events map
    $eventsMap = [];
    foreach ($events as $event) {
        $checkDate = $startOfMonth->copy();
        // Collect group_id and category
        if (isset($event->group_id)) {
            $eventGroupIds[] = $event->group_id;
        }
        if (isset($event->category_id)) {
            $eventCategoryIds[] = $event->category_id;
        }
        while ($checkDate->lte($endOfMonth)) {
            if ($event->matchesDate($checkDate)) {
                $dateStr = $checkDate->format('Y-m-d');
                if (!isset($eventsMap[$dateStr])) {
                    $eventsMap[$dateStr] = [];
                }
                $eventsMap[$dateStr][] = $event;
            }
            $checkDate->addDay();
        }
    }
    
    if (count($eventsMap) > 0) {
        $sampleDate = array_keys($eventsMap)[0];
    }
    $uniqueGroupIds = array_unique($eventGroupIds);
    $uniqueCategoryIds = array_unique($eventCategoryIds);

    // Prepare data for Vue
    $eventsForVue = [];
    foreach ($events as $event) {
        $groupId = isset($role) ? $event->getGroupIdForSubdomain($role->subdomain) : null;
        $eventsForVue[] = [
            'id' => \App\Utils\UrlUtils::encodeId($event->id),
            'group_id' => $groupId ? \App\Utils\UrlUtils::encodeId($groupId) : null,
            'category_id' => $event->category_id,
            'name' => $event->translatedName(),
            'venue_name' => $event->getVenueDisplayName(),
            'starts_at' => $event->starts_at,
            'days_of_week' => $event->days_of_week,
            'local_starts_at' => $event->localStartsAt(),
            'local_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
            'guest_url' => $event->getGuestUrl(isset($subdomain) ? $subdomain : ''),
            'image_url' => $event->getImageUrl(),
            'can_edit' => auth()->user() && auth()->user()->canEditEvent($event),
            'edit_url' => auth()->user() && auth()->user()->canEditEvent($event)
                ? (isset($role)
                    ? $publicUrl . route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)], false)
                    : $publicUrl . route('event.edit_admin', ['hash' => App\Utils\UrlUtils::encodeId($event->id)], false))
                : null,
        ];
    }

    // Also pass the pre-calculated events map to Vue
    $eventsMapForVue = [];
    foreach ($eventsMap as $date => $eventsForDate) {
        $eventsMapForVue[$date] = array_map(function($event) {
            return \App\Utils\UrlUtils::encodeId($event->id);
        }, $eventsForDate);
    }

    // Prepare groups data for Vue
    $groupsForVue = [];
    if (isset($role) && $role->groups) {
        foreach ($role->groups as $group) {
            $groupsForVue[] = [
                'id' => \App\Utils\UrlUtils::encodeId($group->id),
                'slug' => $group->slug,
                'name' => $group->translatedName()
            ];
        }
    }
@endphp

@if (! request()->graphic)
<header class="py-4 {{ (isset($force_mobile) && $force_mobile) ? 'hidden' : '' }}">
    {{-- Main container: Stacks content on mobile, aligns in a row on desktop. --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        {{-- Month and Year Title: Always visible and positioned first. --}}
        <h1 class="text-lg font-semibold leading-6 flex-shrink-0 hidden md:block text-gray-900 dark:text-gray-100">
            <time datetime="{{ sprintf('%04d-%02d', $year, $month) }}">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</time>
        </h1>

        {{-- All Controls Wrapper: Groups all interactive elements. Stacks on mobile, row on desktop. --}}
        <div class="flex flex-col md:flex-row md:items-center gap-3">

            {{-- Schedule and Category Selects Container --}}
            <div class="flex flex-row gap-2 w-full md:w-auto">
                {{-- Schedule Select --}}
                @if(isset($role) && $role->groups && $role->groups->count() > 1)
                    <select v-model="selectedGroup" class="border-gray-300 rounded-md shadow-sm h-9 text-sm flex-1 hover:bg-gray-50 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}">
                        <option value="">{{ __('messages.all_schedules') }}</option>
                        @foreach($role->groups as $group)
                            <option value="{{ $group->slug }}">{{ $group->translatedName() }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Category Select --}}
                @if(count($uniqueCategoryIds ?? []) > 1)
                    <select v-model="selectedCategory" class="border-gray-300 rounded-md shadow-sm h-9 text-sm flex-1 hover:bg-gray-50 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}">
                        <option value="">{{ __('messages.all_categories') }}</option>
                        <option v-for="category in availableCategories" :key="category.id" :value="category.id" v-text="category.name"></option>
                    </select>
                @endif
            </div>

                        {{-- Save Button --}}
            @if ($route == 'admin' && $role->email_verified_at)
                @if ($tab == 'availability')
                    <button type="button" id="saveButton" disabled class="w-full md:w-auto inline-flex items-center justify-center rounded-md shadow-sm bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3A6BE0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6.5 20Q4.22 20 2.61 18.43 1 16.85 1 14.58 1 12.63 2.17 11.1 3.35 9.57 5.25 9.15 5.88 6.85 7.75 5.43 9.63 4 12 4 14.93 4 16.96 6.04 19 8.07 19 11 20.73 11.2 21.86 12.5 23 13.78 23 15.5 23 17.38 21.69 18.69 20.38 20 18.5 20H13Q12.18 20 11.59 19.41 11 18.83 11 18V12.85L9.4 14.4L8 13L12 9L16 13L14.6 14.4L13 12.85V18H18.5Q19.55 18 20.27 17.27 21 16.55 21 15.5 21 14.45 20.27 13.73 19.55 13 18.5 13H17V11Q17 8.93 15.54 7.46 14.08 6 12 6 9.93 6 8.46 7.46 7 8.93 7 11H6.5Q5.05 11 4.03 12.03 3 13.05 3 14.5 3 15.95 4.03 17 5.05 18 6.5 18H9V20M12 13Z" />
                        </svg>
                        {{ __('messages.save') }}
                    </button>
                @endif
            @elseif ($route == 'home' && ($canCreateEvent ?? false))
                <div style="font-family: sans-serif" class="relative inline-block text-left w-full md:w-auto">
                    <button type="button" onclick="onPopUpClick('calendar-pop-up-menu', event)" class="inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" id="menu-button" aria-expanded="true" aria-haspopup="true">
                        {{ __('messages.new_schedule') }}
                        <svg class="{{ isset($role) && $role->isRtl() && ! session()->has('translate') ? '-ml-1' : '-mr-1' }} h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="calendar-pop-up-menu" class="pop-up-menu hidden absolute {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'left-0' : 'right-0' }} z-10 mt-2 w-64 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                        <div class="py-1" role="none" onclick="onPopUpClick('calendar-pop-up-menu', event)">
                        <a href="{{ route('new', ['type' => 'talent']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">
                                <svg class="{{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'ml-3' : 'mr-3' }} h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                                </svg>                        
                                <div>
                                    {{ __('messages.talent') }}
                                    <div class="text-xs text-gray-500">{{ __('messages.new_schedule_tooltip') }}</div>
                                </div>
                            </a>
                            <a href="{{ route('new', ['type' => 'venue']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">
                                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
                                </svg>
                                <div>
                                    {{ __('messages.venue') }}
                                    <div class="text-xs text-gray-500">{{ __('messages.new_venue_tooltip') }}</div>
                                </div>
                            </a>
                            <a href="{{ route('new', ['type' => 'curator']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1">
                                <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z" />
                                </svg>
                                <div>
                                    {{ __('messages.curator') }}
                                    <div class="text-xs text-gray-500">{{ __('messages.new_curator_tooltip') }}</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

        {{-- Month Navigation Controls --}}
        @php
            $previousMonthDate = Carbon\Carbon::create($year, $month, 1)->subMonth();
            $nextMonthDate = Carbon\Carbon::create($year, $month, 1)->addMonth();
            $previousParams = array_merge($calendarQueryParams, [
                'year' => $previousMonthDate->year,
                'month' => $previousMonthDate->month,
            ]);
            $nextParams = array_merge($calendarQueryParams, [
                'year' => $nextMonthDate->year,
                'month' => $nextMonthDate->month,
            ]);
            $currentParams = array_merge($calendarQueryParams, [
                'year' => now()->year,
                'month' => now()->month,
            ]);

            if (in_array($route, ['guest', 'admin'])) {
                $roleBaseParams = ['subdomain' => $role->subdomain];
                if ($route === 'guest') {
                    $roleBaseParams['embed'] = isset($embed) && $embed;
                } else {
                    $roleBaseParams['tab'] = $tab;
                }
            }

            $resolveRoute = function (array $params) use ($route, $roleBaseParams) {
                $routeName = match ($route) {
                    'home' => 'home',
                    'landing' => 'landing',
                    default => 'role.view_' . $route,
                };

                $finalParams = in_array($route, ['home', 'landing'], true)
                    ? $params
                    : array_merge($roleBaseParams ?? [], $params);

                return route($routeName, $finalParams);
            };

            $previousUrl = $resolveRoute($previousParams);
            $nextUrl = $resolveRoute($nextParams);
            $currentUrl = $resolveRoute($currentParams);

            $monthOptions = collect(range(-5, 6))->map(function ($offset) use ($currentMonthDate, $calendarQueryParams, $resolveRoute) {
                $optionDate = Carbon\Carbon::create($currentMonthDate->year, $currentMonthDate->month, 1)->addMonths($offset);
                $params = array_merge($calendarQueryParams, [
                    'year' => $optionDate->year,
                    'month' => $optionDate->month,
                ]);

                return [
                    'label' => $optionDate->copy()->locale(app()->getLocale())->translatedFormat('F Y'),
                    'url' => $resolveRoute($params),
                    'is_current' => $optionDate->isSameMonth($currentMonthDate) && $optionDate->isSameYear($currentMonthDate),
                ];
            });
        @endphp
        <div class="hidden md:flex flex-wrap items-center gap-2 bg-white rounded-md shadow-sm p-1">
            <a href="{{ $previousUrl }}" class="flex h-9 items-center rounded-md border border-gray-200 px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" rel="nofollow">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                <span class="ml-1 hidden sm:inline">{{ __('messages.previous_month') }}</span>
            </a>

            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" :aria-expanded="open.toString()" class="flex h-9 items-center gap-2 rounded-md border border-gray-200 px-3 text-sm font-semibold text-gray-900 transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('messages.this_month') }}</span>
                    <svg class="h-4 w-4 transition" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <div x-show="open" x-cloak @click.outside="open = false" class="absolute left-0 z-20 mt-2 w-56 overflow-hidden rounded-lg border border-gray-100 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                    <div class="max-h-64 overflow-y-auto py-2">
                        @foreach($monthOptions as $option)
                            <a href="{{ $option['url'] }}" class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
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

            <a href="{{ $nextUrl }}" class="flex h-9 items-center rounded-md border border-gray-200 px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800" rel="nofollow">
                <span class="mr-1 hidden sm:inline">{{ __('messages.next_month') }}</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>

            @if(!empty($layoutToggleParams ?? []))
                <div class="ml-auto">
                    @include('landing.partials.layout-toggle', array_merge($layoutToggleParams, [
                        'wrapperClass' => trim(($layoutToggleParams['wrapperClass'] ?? '') . ' shadow-sm'),
                    ]))
                </div>
            @endif
        </div>

            {{-- Add Event Button --}}
            @if ($route == 'admin' && $role->email_verified_at && $tab == 'schedule')
                <a href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}" class="w-full md:w-auto">
                    <button type="button" class="w-full inline-flex items-center justify-center rounded-md shadow-sm bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3A6BE0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                        <svg class="{{ isset($role) && $role->isRtl() && ! session()->has('translate') ? '-mr-0.5 ml-1.5' : '-ml-0.5 mr-1.5' }} h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        {{ __('messages.add_event') }}
                    </button>
                </a>
            @elseif ($route == 'home' && ($canCreateEvent ?? false))
                <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'create-event')"
                    class="w-full md:w-auto inline-flex items-center justify-center rounded-md shadow-sm bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3A6BE0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                    <svg class="{{ isset($role) && $role->isRtl() && ! session()->has('translate') ? '-mr-0.5 ml-1.5' : '-ml-0.5 mr-1.5' }} h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    {{ __('messages.add_event') }}
                </button>
            @endif
        </div>
    </div>
</header>
@endif

    <div class="{{ ($tab == 'availability' || (isset($embed) && $embed) || (isset($force_mobile) && $force_mobile)) ? '' : 'hidden' }} shadow-sm ring-1 ring-black ring-opacity-5 md:flex md:flex-auto md:flex-col {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}">
        
        @if (request()->graphic)
            @include('role.partials.calendar-graphic')
        @else
        <div class="{{ $tab == 'availability' ? 'hidden md:block' : '' }} {{ (isset($force_mobile) && $force_mobile) ? 'hidden' : '' }}"> 
            <div
                class="grid grid-cols-7 gap-px border-b border-gray-300 bg-gray-200 text-center text-xs font-semibold leading-6 text-gray-700">
                @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                <div class="flex justify-center bg-white py-2">
                    {{ __('messages.' . $day) }}
                </div>
                @endforeach
            </div>
        </div>
        <div class="bg-gray-200 text-xs leading-6 text-gray-700 {{ (isset($force_mobile) && $force_mobile) ? 'hidden' : '' }}">
            <div class="w-full grid grid-cols-7 grid-rows-{{ $totalWeeks }} gap-px">
                @while ($currentDate->lte($endOfMonth))
                @if ($route == 'admin' && $tab == 'schedule' && $role->email_verified_at)
                @php
                $unavailable = [];
                foreach ($datesUnavailable as $user => $dates) {
                    if (is_array($dates) && in_array($currentDate->format('Y-m-d'), $dates)) {
                        $unavailable[] = $user;
                    }
                }
                @endphp
                <div class="cursor-pointer relative {{ count($unavailable) ? ($currentDate->month == $month ? 'bg-orange-50 hover:bg-gray-100 hover:border-gray-300' : 'bg-orange-50 hover:bg-gray-100 hover:border-gray-300 text-gray-500') : ($currentDate->month == $month ? 'bg-white hover:bg-gray-100 hover:border-gray-300' : 'bg-gray-50 text-gray-500 hover:bg-gray-100 hover:border-gray-300') }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300"
                    onclick="window.location = '{{ route('event.create', ['subdomain' => $role->subdomain, 'date' => $currentDate->format('Y-m-d')]) }}';">
                    @elseif ($route == 'admin' && $tab == 'availability' && $role->email_verified_at)
                        <div class="{{ $tab == 'availability' && $currentDate->month != $month ? 'hidden md:block' : '' }} cursor-pointer relative {{ $currentDate->month == $month ? 'bg-white hover:bg-gray-100 hover:border-gray-300' : 'bg-gray-50 text-gray-500' }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300 day-element" data-date="{{ $currentDate->format('Y-m-d') }}">
                        @if (is_array($datesUnavailable) && in_array($currentDate->format('Y-m-d'), $datesUnavailable))
                            <div class="day-x"></div>
                        @endif
                    @else
                    <div
                        class="relative {{ $currentDate->month == $month ? 'bg-white' : 'bg-gray-50 text-gray-500' }} px-3 py-2 min-h-[100px] border-1 border-transparent">
                        @endif
                        <div class="flex justify-between">
                        @if ($route == 'admin')
                        <time datetime="{{ $currentDate->format('Y-m-d') }}"
                            class="{{ $currentDate->day == now()->day && $currentDate->month == now()->month && $currentDate->year == now()->year ? 'flex h-6 w-6 items-center justify-center rounded bg-[#4E81FA] font-semibold text-white' : '' }}">{{ $currentDate->day }}</time>
                        @else
                        <time datetime="{{ $currentDate->format('Y-m-d') }}"
                            style="{{ $currentDate->day == now()->day && $currentDate->month == now()->month && $currentDate->year == now()->year ? ('background-color: ' . (isset($otherRole) && $otherRole->accent_color ? $otherRole->accent_color : (isset($role) && $role->accent_color ? $role->accent_color : '#4E81FA'))) : '' }}"
                            class="{{ $currentDate->day == now()->day && $currentDate->month == now()->month && $currentDate->year == now()->year ? 'flex h-6 w-6 items-center justify-center rounded font-semibold text-white' : '' }}">{{ $currentDate->day }}</time>
                        @endif
                        @if (count($unavailable))
                            <div class="has-tooltip" data-tooltip="{!! __('messages.unavailable') . ":<br/>" . implode("<br/>", $unavailable) !!}">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#888">
                                    <path d="M11,9H13V7H11M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V11H11V17Z" />
                                </svg>
                            </div>
                        @endif
                        </div>
                        <ol class="mt-4 divide-y divide-gray-100 text-sm leading-6 md:col-span-7 xl:col-span-8">
                            <li v-for="event in getEventsForDate('{{ $currentDate->format('Y-m-d') }}')" :key="event.id" 
                                class="relative group" 
                                :class="event.can_edit ? '{{ (isset($role) && $role->isRtl()) ? 'hover:pl-8' : 'hover:pr-8' }}' : ''"
                                v-show="isEventVisible(event)">
                                <a :href="getEventUrl(event, '{{ $currentDate->format('Y-m-d') }}')"
                                    class="flex has-tooltip"
                                    :data-tooltip="getEventTooltip(event)"
                                    @click.stop
                                    @if ($route != 'guest' || (isset($embed) && $embed))
                                        target="_blank"
                                    @endif>
                                    <p class="flex-auto font-medium group-hover:text-[#4E81FA] text-gray-900 {{ (isset($role) && $role->isRtl()) ? 'rtl' : '' }} truncate">
                                        <span :class="getEventsForDate('{{ $currentDate->format('Y-m-d') }}').filter(e => isEventVisible(e)).length == 1 ? 'line-clamp-2' : 'line-clamp-1'" 
                                              class="hover:underline truncate" v-text="getEventDisplayName(event)">
                                        </span>
                                        <span v-if="getEventsForDate('{{ $currentDate->format('Y-m-d') }}').filter(e => isEventVisible(e)).length == 1" 
                                              class="text-gray-400 truncate" v-text="getEventTime(event)">
                                        </span>
                                    </p>
                                </a>
                                <a v-if="event.can_edit" :href="event.edit_url"
                                    class="absolute {{ (isset($role) && $role->isRtl()) ? 'left-0' : 'right-0' }} top-0 hidden group-hover:inline-block text-[#4E81FA] hover:text-[#4E81FA] hover:underline"
                                    @click.stop>
                                    {{ __('messages.edit') }}
                                </a>
                            </li>
                        </ol>
                    </div>
                    @php $currentDate->addDay(); @endphp
                    @endwhile
                </div>
            </div>
        </div>
        @endif


        @if (! isset($embed) || ! $embed)
        <div class="{{ (isset($force_mobile) && $force_mobile) ? '' : 'md:hidden' }}">
            <div v-if="mobileEventsList.length">
                <div class="mb-4 text-center">
                    <button id="showPastEventsBtn" class="text-[#4E81FA] font-medium hidden">
                        {{ __('messages.show_past_events') }}
                    </button>
                </div>
                <ol id="mobileEventsList"
                    class="divide-y divide-gray-100 overflow-hidden rounded-lg bg-white text-sm shadow ring-1 ring-black ring-opacity-5">
                    <template v-for="event in mobileEventsList" :key="'mobile-' + event.uniqueKey">
                        <a v-if="isEventVisible(event)" :href="getEventUrl(event, event.occurrenceDate)" 
                           {{ ((isset($embed) && $embed) || $route == 'admin') ? 'target="blank"' : '' }}>
                            <li class="relative flex items-center py-6 px-4 {{ (isset($force_mobile) && $force_mobile) ? '' : 'xl:static' }} event-item {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'space-x-reverse' : '' }} space-x-6"
                                :class="isPastEvent(event.occurrenceDate) ? 'past-event hidden' : ''">
                                <div class="flex-auto pl-4">
                                    <h3 class="{{ (isset($role) && $role->isRtl() && ! session()->has('translate') ? 'pl-16' : 'pr-16') }} font-semibold text-gray-900" v-text="event.name">
                                    </h3>
                                    <dl class="{{ (isset($role) && $role->isRtl() && ! session()->has('translate') ? 'pl-16' : 'pr-16') }} mt-2 flex flex-col text-gray-500 {{ (isset($force_mobile) && $force_mobile) ? '' : 'xl:flex-row' }}">
                                        <div class="flex items-start {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'space-x-reverse' : '' }} space-x-3">
                                            <dt class="mt-0.5">
                                                <span class="sr-only">Date</span>
                                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                                    aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </dt>
                                            <dd>
                                                <time :datetime="event.occurrenceDate">
                                                    <span v-text="formatMobileDate(event.occurrenceDate)"></span> • <span v-text="getEventTime(event)"></span>
                                                </time>
                                            </dd>
                                        </div>
                                        <div
                                            class="mt-2 flex items-start {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'space-x-reverse' : '' }} space-x-3 {{ (isset($force_mobile) && $force_mobile) ? '' : (isset($role) && $role->isRtl() && ! session()->has('translate') ? 'xl:mr-3.5 xl:mt-0 xl:border-r xl:border-gray-400 xl:border-opacity-50 xl:pr-3.5' : 'xl:ml-3.5 xl:mt-0 xl:border-l xl:border-gray-400 xl:border-opacity-50 xl:pl-3.5') }}">
                                            <dt class="mt-0.5">
                                                <span class="sr-only">Location</span>
                                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                                    aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </dt>
                                            <dd v-text="event.venue_name">
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                                <div class="absolute {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'left-4' : 'right-4' }} {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'text-left' : 'text-right' }} top-6">
                                    <img v-if="event.image_url" :src="event.image_url" class="h-16 w-16 flex-none rounded-lg object-cover mb-2">
                                    <a v-if="event.can_edit" :href="event.edit_url"
                                        class="text-[#4E81FA] hover:text-[#4E81FA] hover:underline"
                                        @click.stop>
                                        {{ __('messages.edit') }}
                                    </a>
                                </div>
                            </li>
                        </a>
                    </template>
                </ol>
            </div>
            <div v-else-if="{{ $tab != 'availability' ? 'true' : 'false' }}" class="p-10 max-w-5xl mx-auto px-4">
                <div class="flex justify-center items-center pb-6 w-full">
                    <div class="text-2xl text-center">
                        {{ __('messages.no_scheduled_events') }}
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

<div id="tooltip" class="tooltip"></div>

<script src="{{ asset('js/vue.global.prod.js') }}"></script>
<script {!! nonce_attr() !!}>
const { createApp } = Vue;

const calendarApp = createApp({
    data() {
        return {
            selectedGroup: '{{ isset($selectedGroup) ? $selectedGroup->slug : "" }}',
            selectedCategory: '{{ $category ?? "" }}',
            allEvents: @json($eventsForVue),
            eventsMap: @json($eventsMapForVue),
            groups: @json($groupsForVue),
            categories: @json(get_translated_categories()),
            startOfMonth: '{{ $startOfMonth->format('Y-m-d') }}',
            endOfMonth: '{{ $endOfMonth->format('Y-m-d') }}',
            use24Hour: {{ isset($role) && $role->use_24_hour_time ? 'true' : 'false' }},
            subdomain: '{{ isset($subdomain) ? $subdomain : '' }}',
            route: '{{ $route }}',
            embed: {{ isset($embed) && $embed ? 'true' : 'false' }},
            isRtl: {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'true' : 'false' }}
        }
    },
    computed: {
        filteredEvents() {
            return this.allEvents.filter(event => {
                if (this.selectedGroup) {
                    // Find the group by slug to get its ID for filtering
                    const selectedGroupObj = this.groups.find(group => group.slug === this.selectedGroup);
                    if (selectedGroupObj && event.group_id !== selectedGroupObj.id) {
                        return false;
                    }
                }
                if (this.selectedCategory && event.category_id != this.selectedCategory) {
                    return false;
                }
                return true;
            });
        },
        availableCategories() {
            // Get events filtered only by group (not by category) to show all available categories
            const groupFilteredEvents = this.allEvents.filter(event => {
                if (this.selectedGroup) {
                    // Find the group by slug to get its ID for filtering
                    const selectedGroupObj = this.groups.find(group => group.slug === this.selectedGroup);
                    if (selectedGroupObj && event.group_id !== selectedGroupObj.id) {
                        return false;
                    }
                }
                return true;
            });
            
            // Get unique category IDs from the group-filtered events
            const categoryIds = [...new Set(groupFilteredEvents.map(event => event.category_id).filter(id => id))];
            
            // Convert to array of category objects
            return categoryIds.map(id => ({
                id: id,
                name: this.categories[id] || `Category ${id}`
            })).sort((a, b) => a.name.localeCompare(b.name));
        },
        mobileEventsList() {
            // Create a mobile-friendly events list that includes all occurrences of recurring events
            const mobileEvents = [];
            
            // Process each date in the events map
            Object.keys(this.eventsMap).forEach(dateStr => {
                const eventIds = this.eventsMap[dateStr];
                
                // Get the events for this date
                const eventsForDate = this.filteredEvents.filter(event => {
                    return eventIds.includes(event.id);
                });
                
                // Add each event with its occurrence date
                eventsForDate.forEach(event => {
                    mobileEvents.push({
                        ...event,
                        occurrenceDate: dateStr,
                        // Create a unique key for recurring events
                        uniqueKey: event.days_of_week ? `${event.id}-${dateStr}` : event.id
                    });
                });
            });
            
            // Sort by date, then by time
            return mobileEvents.sort((a, b) => {
                const dateComparison = a.occurrenceDate.localeCompare(b.occurrenceDate);
                if (dateComparison !== 0) return dateComparison;
                
                // If same date, sort by time
                if (a.local_starts_at && b.local_starts_at) {
                    return new Date(a.local_starts_at) - new Date(b.local_starts_at);
                }
                return 0;
            });
        }
    },
    watch: {
        selectedGroup(newGroupSlug) {
            if (this.route === 'guest' && !this.embed) {
                this.updateUrlWithGroup(newGroupSlug);
            }
            // Reset category selection when group changes, as available categories may change
            if (this.selectedCategory && !this.availableCategories.find(cat => cat.id == this.selectedCategory)) {
                this.selectedCategory = '';
            }
        }
    },
    methods: {
        getEventsForDate(dateStr) {
            // Use the pre-calculated events map from the backend
            if (this.eventsMap[dateStr]) {
                const eventIds = this.eventsMap[dateStr];
                return this.filteredEvents.filter(event => {
                    return eventIds.includes(event.id);
                });
            }
            return [];
        },
        eventMatchesDate(event, dateStr) {
            // Convert dateStr to Date object for comparison
            const checkDate = new Date(dateStr + 'T00:00:00');
            
            if (event.starts_at) {
                // Use the local_date that's already converted to the role's timezone
                if (event.local_date) {
                    return event.local_date === dateStr;
                }
                
                // Fallback for events without local_date
                const eventDate = new Date(event.starts_at);
                return eventDate.toDateString() === checkDate.toDateString();
            } else if (event.days_of_week) {
                // For recurring events, check if the date is on or after the start date
                // and if the day of the week matches
                if (event.starts_at) {
                    const eventStartDate = new Date(event.starts_at);
                    if (checkDate < eventStartDate) {
                        return false;
                    }
                }
                
                const dayOfWeek = checkDate.getDay(); // 0 = Sunday, 1 = Monday, etc.
                return event.days_of_week[dayOfWeek] === '1';
            }
            return false;
        },
        isEventVisible(event) {
            if (this.selectedGroup) {
                // Find the group by slug to get its ID for filtering
                const selectedGroupObj = this.groups.find(group => group.slug === this.selectedGroup);
                if (selectedGroupObj && event.group_id !== selectedGroupObj.id) {
                    return false;
                }
            }
            if (this.selectedCategory && event.category_id != this.selectedCategory) {
                return false;
            }
            return true;
        },
        getEventUrl(event, _date) {
            let url = event.guest_url;
            const queryParams = [];

            if (this.selectedCategory) {
                queryParams.push('category=' + this.selectedCategory);
            }

            if (this.selectedGroup) {
                queryParams.push('schedule=' + this.selectedGroup);
            }

            if (queryParams.length > 0) {
                url += (url.includes('?') ? '&' : '?') + queryParams.join('&');
            }

            return url;
        },
        getEventTooltip(event) {
            const time = this.getEventTime(event);
            return `<b>${event.name}</b><br/>${event.venue_name} • ${time}`;
        },
        getEventDisplayName(event) {
            if (this.subdomain && this.isRoleAMember(event)) {
                return event.venue_name;
            }
            return event.name;
        },
        getEventTime(event) {
            if (!event.local_starts_at) return '';
            const date = new Date(event.local_starts_at);
            if (this.use24Hour) {
                return date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
            } else {
                return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            }
        },
        isRoleAMember(event) {
            // This would need to be determined server-side and passed to the frontend
            // For now, return false as a placeholder
            return false;
        },
        isPastEvent(dateStr) {
            const eventDate = new Date(dateStr + 'T23:59:59');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return eventDate < today;
        },
        getEventDate(event) {
            if (event.starts_at) {
                // Use the local_date that's already converted to the role's timezone
                if (event.local_date) {
                    return event.local_date;
                }
                
                // Fallback for events without local_date
                const eventDate = new Date(event.starts_at);
                return eventDate.toISOString().split('T')[0]; // Return YYYY-MM-DD format
            }
            // For recurring events, we need to determine the date based on the current month
            // This is a simplified approach - you might need to adjust based on your specific logic
            const currentMonth = new Date(this.startOfMonth);
            const dayOfWeek = this.getDayOfWeekFromDaysOfWeek(event.days_of_week);
            if (dayOfWeek !== null) {
                const firstDayOfMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
                const firstOccurrence = this.getFirstOccurrenceOfDay(firstDayOfMonth, dayOfWeek);
                return firstOccurrence.toISOString().split('T')[0];
            }
            return null;
        },
        getEventDisplayDate(event) {
            const date = this.getEventDate(event);
            if (!date) return '';
            
            const eventDate = new Date(date);
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                              'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const day = eventDate.getDate();
            const suffix = this.getDaySuffix(day);
            const month = monthNames[eventDate.getMonth()];
            
            return `${month} ${day}${suffix}`;
        },
        formatMobileDate(dateStr) {
            if (!dateStr) return '';
            
            const eventDate = new Date(dateStr);
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                              'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const day = eventDate.getDate();
            const suffix = this.getDaySuffix(day);
            const month = monthNames[eventDate.getMonth()];
            
            return `${month} ${day}${suffix}`;
        },
        getDayOfWeekFromDaysOfWeek(daysOfWeek) {
            if (!daysOfWeek) return null;
            // Find the first day of the week that is set to '1'
            for (let i = 0; i < daysOfWeek.length; i++) {
                if (daysOfWeek[i] === '1') {
                    return i; // 0 = Sunday, 1 = Monday, etc.
                }
            }
            return null;
        },
        getFirstOccurrenceOfDay(startDate, dayOfWeek) {
            const currentDay = startDate.getDay();
            const daysToAdd = (dayOfWeek - currentDay + 7) % 7;
            const firstOccurrence = new Date(startDate);
            firstOccurrence.setDate(startDate.getDate() + daysToAdd);
            return firstOccurrence;
        },
        getDaySuffix(day) {
            if (day >= 11 && day <= 13) return 'th';
            switch (day % 10) {
                case 1: return 'st';
                case 2: return 'nd';
                case 3: return 'rd';
                default: return 'th';
            }
        },
        initTooltips() {
            // Reinitialize tooltips after Vue updates
            this.$nextTick(() => {
                const tooltipElements = document.querySelectorAll('.has-tooltip');
                tooltipElements.forEach(el => {
                    el.addEventListener('mouseenter', this.showTooltip);
                    el.addEventListener('mouseleave', this.hideTooltip);
                });
            });
        },
        showTooltip(e) {
            const tooltip = document.getElementById('tooltip');
            const tooltipText = e.currentTarget.dataset.tooltip;
            tooltip.innerHTML = tooltipText;
            tooltip.style.display = 'block';
            tooltip.style.left = e.pageX + 10 + 'px';
            tooltip.style.top = e.pageY + 10 + 'px';
        },
        hideTooltip() {
            const tooltip = document.getElementById('tooltip');
            tooltip.style.display = 'none';
        },
        updateUrlWithGroup(newGroupSlug) {
            if (!newGroupSlug) {
                // If no group selected, redirect to base guest URL
                const baseUrl = `/${this.subdomain}`;
                const currentUrl = new URL(window.location);
                const params = new URLSearchParams(currentUrl.search);
                
                // Keep year and month if they exist
                let newUrl = baseUrl;
                if (params.get('year') && params.get('month')) {
                    newUrl += `?year=${params.get('year')}&month=${params.get('month')}`;
                }
                
                window.history.pushState({}, '', newUrl);
                return;
            }
            
            // Build new URL with group slug
            const currentUrl = new URL(window.location);
            const params = new URLSearchParams(currentUrl.search);
            let newUrl = `/${this.subdomain}/${newGroupSlug}`;
            
            // Keep year and month parameters if they exist
            if (params.get('year') && params.get('month')) {
                newUrl += `?year=${params.get('year')}&month=${params.get('month')}`;
            }
            
            // Update the URL without page reload
            window.history.pushState({}, '', newUrl);
        }
    },
    mounted() {
        // Initialize tooltip functionality
        this.initTooltips();
        
        // Handle past events button
        this.$nextTick(() => {
            const showPastEventsBtn = document.getElementById('showPastEventsBtn');
            const pastEvents = document.querySelectorAll('.past-event');
            
            if (pastEvents.length > 0) {
                showPastEventsBtn?.classList.remove('hidden');
                
                showPastEventsBtn?.addEventListener('click', function() {
                    pastEvents.forEach(event => {
                        event.classList.remove('hidden');
                    });
                    showPastEventsBtn.classList.add('hidden');
                });
            }
        });
    }
});

const calendarAppInstance = calendarApp.mount('#calendar-app');
window.calendarVueApp = calendarAppInstance;
</script>

@if ($route == 'admin' && $role->email_verified_at && $tab == 'schedule')
    @include('components.embed-modal')
@endif