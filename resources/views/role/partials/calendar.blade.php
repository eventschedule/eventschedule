<div class="flex h-full flex-col pt-1" id="calendar-app">
@php
    $startOfMonth = Carbon\Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek(Carbon\Carbon::SUNDAY);
    $endOfMonth = Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek(Carbon\Carbon::SATURDAY);
    $currentDate = $startOfMonth->copy();
    $totalDays = $endOfMonth->diffInDays($startOfMonth) + 1;
    $totalWeeks = ceil($totalDays / 7);
    $unavailable = [];
    
    // Calculate today's date considering user's timezone if logged in
    $today = auth()->check() && auth()->user()->timezone 
        ? Carbon\Carbon::now(auth()->user()->timezone)->startOfDay()
        : Carbon\Carbon::now()->startOfDay();

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
            'utc_date' => $event->starts_at ? $event->getStartDateTime(null, false)->format('Y-m-d') : null,
            'guest_url' => $event->getGuestUrl(isset($subdomain) ? $subdomain : '', ''),
            'image_url' => $event->getImageUrl(),
            'can_edit' => auth()->user() && auth()->user()->canEditEvent($event),
            'edit_url' => auth()->user() && auth()->user()->canEditEvent($event) 
                ? (isset($role) ? config('app.url') . route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)], false) : config('app.url') . route('event.edit_admin', ['hash' => App\Utils\UrlUtils::encodeId($event->id)], false))
                : null,
            'recurring_end_type' => $event->recurring_end_type ?? 'never',
            'recurring_end_value' => $event->recurring_end_value,
            'start_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
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
        <h1 class="text-2xl font-semibold leading-6 flex-shrink-0 hidden md:block text-gray-900 dark:text-gray-100">
            <time datetime="{{ sprintf('%04d-%02d', $year, $month) }}">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</time>
        </h1>

        {{-- All Controls Wrapper: Groups all interactive elements. Stacks on mobile, row on desktop. --}}
        <div class="flex flex-col md:flex-row md:items-center gap-3">

            {{-- Schedule and Category Selects Container --}}
            <div class="flex flex-row gap-2 w-full md:w-auto">
                {{-- Schedule Select --}}
                @if(isset($role) && $role->groups && $role->groups->count() > 1)
                    <select v-model="selectedGroup" style="color: rgb(107 114 128) !important; font-size: 1rem !important; font-weight: 700 !important; line-height: 1.5 !important; padding-top: 0.75rem !important; padding-bottom: 0.75rem !important;" class="border-gray-300 dark:border-gray-600 rounded-md shadow-sm flex-1 hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-800 dark:text-gray-300 px-3 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}">
                        <option value="">{{ __('messages.all_schedules') }}</option>
                        @foreach($role->groups as $group)
                            <option value="{{ $group->slug }}">{{ $group->translatedName() }}</option>
                        @endforeach
                    </select>
                @endif

                {{-- Category Select --}}
                @if(count($uniqueCategoryIds ?? []) > 1)
                    <select v-model="selectedCategory" style="color: rgb(107 114 128) !important; font-size: 1rem !important; font-weight: 700 !important; line-height: 1.5 !important; padding-top: 0.75rem !important; padding-bottom: 0.75rem !important;" class="border-gray-300 dark:border-gray-600 rounded-md shadow-sm flex-1 hover:bg-gray-50 dark:hover:bg-gray-700 bg-white dark:bg-gray-800 dark:text-gray-300 px-3 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}">
                        <option value="">{{ __('messages.all_categories') }}</option>
                        <option v-for="category in availableCategories" :key="category.id" :value="category.id" v-text="category.name"></option>
                    </select>
                @endif
            </div>

                        {{-- Save Button --}}
            @if ($route == 'admin' && $role->email_verified_at)
                @if ($tab == 'availability')
                    <button type="button" id="saveButton" disabled class="w-full md:w-auto inline-flex items-center justify-center rounded-md shadow-sm bg-[#4E81FA] px-4 py-3 text-base font-semibold text-white transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 disabled:hover:shadow-sm">
                        <svg class="-ml-0.5 mr-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6.5 20Q4.22 20 2.61 18.43 1 16.85 1 14.58 1 12.63 2.17 11.1 3.35 9.57 5.25 9.15 5.88 6.85 7.75 5.43 9.63 4 12 4 14.93 4 16.96 6.04 19 8.07 19 11 20.73 11.2 21.86 12.5 23 13.78 23 15.5 23 17.38 21.69 18.69 20.38 20 18.5 20H13Q12.18 20 11.59 19.41 11 18.83 11 18V12.85L9.4 14.4L8 13L12 9L16 13L14.6 14.4L13 12.85V18H18.5Q19.55 18 20.27 17.27 21 16.55 21 15.5 21 14.45 20.27 13.73 19.55 13 18.5 13H17V11Q17 8.93 15.54 7.46 14.08 6 12 6 9.93 6 8.46 7.46 7 8.93 7 11H6.5Q5.05 11 4.03 12.03 3 13.05 3 14.5 3 15.95 4.03 17 5.05 18 6.5 18H9V20M12 13Z" />
                        </svg>
                        {{ __('messages.save') }}
                    </button>
                @endif
            @elseif ($route == 'home')
                <div style="font-family: sans-serif" class="relative inline-block text-left w-full md:w-auto">
                    <button type="button" onclick="onPopUpClick('calendar-pop-up-menu', event)" class="inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-5 py-3 text-base font-bold text-gray-500 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700" id="menu-button" aria-expanded="true" aria-haspopup="true">
                        {{ __('messages.new_schedule') }}
                        <svg class="{{ isset($role) && $role->isRtl() && ! session()->has('translate') ? '-ml-1' : '-mr-1' }} h-6 w-6 text-gray-400 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="calendar-pop-up-menu" class="pop-up-menu hidden absolute {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'left-0' : 'right-0' }} z-10 mt-2 w-64 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-gray-700 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-gray-600 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                        <div class="py-1" role="none" onclick="onPopUpClick('calendar-pop-up-menu', event)">
                        <a href="{{ route('new', ['type' => 'talent']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" role="menuitem" tabindex="-1">
                                <svg class="{{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'ml-3' : 'mr-3' }} h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                                </svg>                        
                                <div>
                                    {{ __('messages.talent') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_schedule_tooltip') }}</div>
                                </div>
                            </a>
                            <a href="{{ route('new', ['type' => 'venue']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" role="menuitem" tabindex="-1">
                                <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
                                </svg>
                                <div>
                                    {{ __('messages.venue') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_venue_tooltip') }}</div>
                                </div>
                            </a>
                            <a href="{{ route('new', ['type' => 'curator']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" role="menuitem" tabindex="-1">
                                <svg class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z" />
                                </svg>
                                <div>
                                    {{ __('messages.curator') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_curator_tooltip') }}</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Month Navigation Controls --}}
            <div class="flex items-center bg-white dark:bg-gray-800 rounded-md shadow-sm hidden md:flex">
                <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) }}" class="flex h-11 w-14 items-center justify-center rounded-l-md border-y border-l border-gray-300 dark:border-gray-600 pr-1 text-gray-400 dark:text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:relative md:w-11 md:pr-0 md:hover:bg-gray-50 dark:md:hover:bg-gray-700" rel="nofollow">
                    <span class="sr-only">{{ __('messages.previous_month') }}</span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="{{ $route == 'home' ? route('home') : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => now()->year, 'month' => now()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => now()->year, 'month' => now()->month]) }}" class="flex h-11 items-center justify-center border-y border-gray-300 dark:border-gray-600 px-4 text-base font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 focus:relative">
                    <span class="h-6 flex items-center">{{ __('messages.this_month') }}</span>
                </a>
                <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) }}" class="flex h-11 w-14 items-center justify-center rounded-r-md border-y border-r border-gray-300 dark:border-gray-600 pl-1 text-gray-400 dark:text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:relative md:w-11 md:pl-0 md:hover:bg-gray-50 dark:md:hover:bg-gray-700" rel="nofollow">
                    <span class="sr-only">{{ __('messages.next_month') }}</span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            {{-- Add Event Button --}}
            @if ($route == 'admin' && $role->email_verified_at && $tab == 'schedule')
                <a href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}" class="w-full md:w-auto">
                    <button type="button" class="w-full inline-flex items-center justify-center rounded-md shadow-sm bg-[#4E81FA] px-4 py-3 text-base font-semibold text-white transition-all duration-200 hover:scale-105 hover:shadow-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                        <svg class="{{ isset($role) && $role->isRtl() && ! session()->has('translate') ? '-mr-0.5 ml-1.5' : '-ml-0.5 mr-1.5' }} h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        {{ __('messages.add_event') }}
                    </button>
                </a>
            @endif
        </div>
    </div>
</header>
@endif

    <div class="{{ ($tab == 'availability' || (isset($embed) && $embed) || (isset($force_mobile) && $force_mobile)) ? '' : 'hidden' }} {{ (isset($force_mobile) && $force_mobile) ? '' : 'md:shadow-sm md:ring-1 md:ring-black md:ring-opacity-5 md:dark:border md:dark:border-gray-700 md:rounded-md md:overflow-hidden md:flex md:flex-auto md:flex-col' }} {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}">

        @if (request()->graphic)
            @include('role.partials.calendar-graphic')
        @else
        <div class="hidden md:block {{ (isset($force_mobile) && $force_mobile) ? '!hidden' : '' }}">
            <div
                class="grid grid-cols-7 gap-px border-b border-gray-300 dark:border-gray-700 bg-gray-200 dark:bg-gray-700 text-center text-xs font-semibold leading-6 text-gray-700 dark:text-gray-300 rounded-t-md overflow-hidden">
                @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                <div class="flex justify-center bg-white dark:bg-gray-800 py-2">
                    {{ __('messages.' . $day) }}
                </div>
                @endforeach
            </div>
        </div>
        <div class="hidden md:block bg-gray-200 dark:bg-gray-700 text-xs leading-6 text-gray-700 dark:text-gray-300 rounded-b-md overflow-hidden {{ (isset($force_mobile) && $force_mobile) ? '!hidden' : '' }}">
            @php
            // Get first role for home route (only calculate once)
            $firstRole = null;
            if ($route == 'home' && auth()->check()) {
                $firstRole = auth()->user()->member()->where('email_verified_at', '!=', null)->first();
            }
            @endphp
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
                <div class="cursor-pointer relative {{ count($unavailable) ? ($currentDate->month == $month ? 'bg-orange-50 dark:bg-orange-900/30 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' : 'bg-orange-50 dark:bg-orange-900/30 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-500 dark:text-gray-400') : ($currentDate->month == $month ? 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600') }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300 dark:hover:border-gray-600"
                    onclick="window.location = '{{ route('event.create', ['subdomain' => $role->subdomain, 'date' => $currentDate->format('Y-m-d')]) }}';">
                    @elseif ($route == 'admin' && $tab == 'availability' && $role->email_verified_at)
                        <div class="{{ $tab == 'availability' && $currentDate->month != $month ? 'hidden md:block' : '' }} cursor-pointer relative {{ $currentDate->month == $month ? 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400' }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300 dark:hover:border-gray-600 day-element" data-date="{{ $currentDate->format('Y-m-d') }}">
                        @if (is_array($datesUnavailable) && in_array($currentDate->format('Y-m-d'), $datesUnavailable))
                            <div class="day-x"></div>
                        @endif
                    @elseif ($route == 'home' && auth()->check())
                        @if ($firstRole)
                        <div class="cursor-pointer relative {{ $currentDate->month == $month ? 'bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300 dark:hover:border-gray-600"
                            onclick="window.location = '{{ route('event.create', ['subdomain' => $firstRole->subdomain, 'date' => $currentDate->format('Y-m-d')]) }}';">
                        @else
                        <div
                            class="relative {{ $currentDate->month == $month ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400' }} px-3 py-2 min-h-[100px] border-1 border-transparent">
                        @endif
                    @else
                    <div
                        class="relative {{ $currentDate->month == $month ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400' }} px-3 py-2 min-h-[100px] border-1 border-transparent">
                        @endif
                        <div class="flex justify-between">
                        @if ($route == 'admin')
                        <time datetime="{{ $currentDate->format('Y-m-d') }}"
                            class="{{ $currentDate->day == $today->day && $currentDate->month == $today->month && $currentDate->year == $today->year ? 'flex h-6 w-6 items-center justify-center rounded bg-[#4E81FA] font-semibold text-white' : '' }}">{{ $currentDate->day }}</time>
                        @else
                        <time datetime="{{ $currentDate->format('Y-m-d') }}"
                            style="{{ $currentDate->day == $today->day && $currentDate->month == $today->month && $currentDate->year == $today->year ? ('background-color: ' . (isset($otherRole) && $otherRole->accent_color ? $otherRole->accent_color : (isset($role) && $role->accent_color ? $role->accent_color : '#4E81FA'))) : '' }}"
                            class="{{ $currentDate->day == $today->day && $currentDate->month == $today->month && $currentDate->year == $today->year ? 'flex h-6 w-6 items-center justify-center rounded font-semibold text-white' : '' }}">{{ $currentDate->day }}</time>
                        @endif
                        @if (count($unavailable))
                            <div class="has-tooltip" data-tooltip="{!! __('messages.unavailable') . ":<br/>" . implode("<br/>", $unavailable) !!}">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#888">
                                    <path d="M11,9H13V7H11M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V11H11V17Z" />
                                </svg>
                            </div>
                        @endif
                        </div>
                        <ol class="mt-4 divide-y divide-gray-100 dark:divide-gray-700 text-sm leading-6 md:col-span-7 xl:col-span-8">
                            <li v-for="event in getEventsForDate('{{ $currentDate->format('Y-m-d') }}')" :key="event.id" 
                                class="relative group" 
                                :class="event.can_edit ? '{{ (isset($role) && $role->isRtl()) ? 'hover:pl-8' : 'hover:pr-8' }}' : ''"
                                v-show="isEventVisible(event)">
                                <a :href="getEventUrl(event, '{{ $currentDate->format('Y-m-d') }}')"
                                    class="flex event-link-popup" 
                                    :data-event-id="event.id"
                                    @click.stop {{ ($route != 'guest' || (isset($embed) && $embed)) ? "target='_blank'" : '' }}>
                                    <p class="flex-auto font-medium group-hover:text-[#4E81FA] text-gray-900 dark:text-gray-100 {{ (isset($role) && $role->isRtl()) ? 'rtl' : '' }} truncate">
                                        <span :class="getEventsForDate('{{ $currentDate->format('Y-m-d') }}').filter(e => isEventVisible(e)).length == 1 ? 'line-clamp-2' : 'line-clamp-1'" 
                                              class="hover:underline truncate" v-text="getEventDisplayName(event)">
                                        </span>
                                        <span v-if="getEventsForDate('{{ $currentDate->format('Y-m-d') }}').filter(e => isEventVisible(e)).length == 1" 
                                              class="text-gray-400 dark:text-gray-500 truncate" v-text="getEventTime(event)">
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
                <button id="showPastEventsBtn" class="text-[#4E81FA] font-medium hidden mb-4 w-full text-center">
                    {{ __('messages.show_past_events') }}
                </button>
                <div id="mobileEventsList" class="space-y-6">
                    <template v-for="(group, groupIndex) in eventsGroupedByDate" :key="'date-' + group.date">
                        {{-- Date Group --}}
                        <div :class="isPastEvent(group.date) ? 'past-event hidden' : ''">
                            {{-- Date Header --}}
                            <div class="sticky top-0 z-10 -mx-4 px-4 {{ (isset($force_mobile) && $force_mobile) ? 'bg-[#F5F9FE] dark:bg-gray-800' : 'bg-white dark:bg-gray-800' }}">
                                <div class="px-4 pb-5 flex items-center gap-4" :class="groupIndex > 0 ? 'pt-3' : ''">
                                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                                    <div class="font-semibold text-gray-900 dark:text-gray-100 text-center" v-text="formatDateHeader(group.date)" {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'dir=rtl' : '' }}></div>
                                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                                </div>
                            </div>
                            {{-- Events for this date --}}
                            <div class="space-y-4">
                                <template v-for="event in group.events" :key="'mobile-' + event.uniqueKey">
                                    <div v-if="isEventVisible(event)"
                                         @click="navigateToEvent(event, $event)"
                                         class="block cursor-pointer">
                                        <div class="event-item bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 overflow-hidden transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-600"
                                            :class="isPastEvent(event.occurrenceDate) ? 'past-event hidden' : ''">
                                            <div class="flex {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'flex-row-reverse' : '' }}">
                                                {{-- Content Section --}}
                                                <div class="flex-1 py-3 px-4 flex flex-col min-w-0 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'text-right' : '' }}">
                                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base leading-snug line-clamp-2" v-text="event.name"></h3>
                                                    <div class="mt-1.5 flex items-center text-sm text-gray-500 dark:text-gray-400 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'flex-row-reverse' : '' }}">
                                                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'ml-2' : 'mr-2' }}" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span v-text="getEventTime(event)"></span>
                                                    </div>
                                                    <div v-if="event.venue_name" class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'flex-row-reverse' : '' }}">
                                                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0 {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'ml-2' : 'mr-2' }}" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span class="truncate" v-text="event.venue_name" {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'dir=rtl' : '' }}></span>
                                                    </div>
                                                    <div v-if="event.can_edit" class="mt-auto pt-3">
                                                        <a :href="event.edit_url"
                                                            class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-[#4E81FA] bg-transparent border border-[#4E81FA] rounded-md hover:bg-[#4E81FA] hover:text-white transition-colors duration-200"
                                                            @click.stop>
                                                            {{ __('messages.edit') }}
                                                        </a>
                                                    </div>
                                                </div>
                                                {{-- Image Section --}}
                                                <div v-if="event.image_url" class="flex-shrink-0 w-24 self-stretch">
                                                    <img :src="event.image_url" class="w-full h-full object-cover" :alt="event.name">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div v-else-if="{{ $tab != 'availability' ? 'true' : 'false' }}" class="py-10 text-center">
                <div class="text-xl text-gray-500 dark:text-gray-400">
                    {{ __('messages.no_scheduled_events') }}
                </div>
            </div>
        </div>
        @endif


<!-- Event Popup Component -->
<div id="event-popup" class="event-popup">
    <div class="event-popup-content">
        <img id="event-popup-image" class="event-popup-image" style="display: none;" />
        <div class="event-popup-body">
            <h3 id="event-popup-title" class="event-popup-title"></h3>
            <div class="event-popup-details">
                <div id="event-popup-venue" class="event-popup-detail" style="display: none;">
                    <svg class="event-popup-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                    </svg>
                    <span id="event-popup-venue-text"></span>
                </div>
                <div id="event-popup-time" class="event-popup-detail" style="display: none;">
                    <svg class="event-popup-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
                    </svg>
                    <span id="event-popup-time-text"></span>
                </div>
            </div>
            <p id="event-popup-description" class="event-popup-description" style="display: none;"></p>
        </div>
    </div>
</div>

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
            isRtl: {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'true' : 'false' }},
            languageCode: '{{ session()->has('translate') ? 'en' : (isset($role) && $role->language_code ? $role->language_code : 'en') }}',
            userTimezone: '{{ auth()->check() && auth()->user()->timezone ? auth()->user()->timezone : null }}',
            popupTimeout: null
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
            // Create a mobile-friendly events list that includes all upcoming occurrences
            const mobileEvents = [];
            
            // Get today's date
            let today = new Date();
            if (this.userTimezone) {
                const userNow = new Date().toLocaleString("en-US", {timeZone: this.userTimezone});
                today = new Date(userNow);
            }
            today.setHours(0, 0, 0, 0);
            
            // Calculate upcoming dates for the next 4 months
            const endDate = new Date(today);
            endDate.setMonth(endDate.getMonth() + 4);
            
            // Helper function to check if a date should be included based on recurring end settings
            const shouldIncludeDate = (event, dateStr) => {
                if (!event.days_of_week || event.days_of_week.length === 0) {
                    return true; // Not a recurring event
                }
                
                const recurringEndType = event.recurring_end_type || 'never';
                
                if (recurringEndType === 'never') {
                    return true;
                }
                
                if (recurringEndType === 'on_date' && event.recurring_end_value) {
                    const endDate = new Date(event.recurring_end_value + 'T00:00:00');
                    const checkDate = new Date(dateStr + 'T00:00:00');
                    return checkDate <= endDate;
                }
                
                if (recurringEndType === 'after_events' && event.recurring_end_value && event.start_date) {
                    const maxOccurrences = parseInt(event.recurring_end_value);
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    const checkDate = new Date(dateStr + 'T00:00:00');
                    
                    // Count occurrences from start date up to and including the check date
                    let occurrenceCount = 0;
                    const currentDate = new Date(startDate);
                    
                    while (currentDate <= checkDate) {
                        const dayOfWeek = currentDate.getDay(); // 0=Sunday, 1=Monday, ..., 6=Saturday
                        const daysOfWeekStr = event.days_of_week;
                        
                        // Check if this day matches (days_of_week is a string like "0100000" where index 0=Sunday, 1=Monday, etc.)
                        if (daysOfWeekStr && daysOfWeekStr[dayOfWeek] === '1') {
                            occurrenceCount++;
                        }
                        
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                    
                    return occurrenceCount <= maxOccurrences;
                }
                
                return true;
            };
            
            // Process all filtered events
            this.filteredEvents.forEach(event => {
                if (event.days_of_week && event.days_of_week.length > 0) {
                    // Recurring event - generate all occurrences
                    const currentDate = new Date(today);
                    
                    while (currentDate <= endDate) {
                        const dayOfWeek = currentDate.getDay(); // 0 = Sunday, 6 = Saturday
                        const daysOfWeekStr = event.days_of_week;
                        
                        // Check if this day matches (days_of_week is a string like "0100000" where 1 means the day is selected)
                        if (daysOfWeekStr && daysOfWeekStr[dayOfWeek] === '1') {
                            const dateStr = currentDate.getFullYear() + '-' + 
                                          String(currentDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                          String(currentDate.getDate()).padStart(2, '0');
                            
                            // Check if this date should be included based on recurring end settings
                            if (shouldIncludeDate(event, dateStr)) {
                                mobileEvents.push({
                                    ...event,
                                    occurrenceDate: dateStr,
                                    uniqueKey: `${event.id}-${dateStr}`
                                });
                            }
                        }
                        
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                } else if (event.starts_at || event.local_date) {
                    // One-time event
                    const eventDate = event.local_date || event.utc_date;
                    if (eventDate) {
                        const [year, month, day] = eventDate.split('-').map(Number);
                        const checkDate = new Date(year, month - 1, day);
                        checkDate.setHours(0, 0, 0, 0);
                        
                        if (checkDate >= today && checkDate <= endDate) {
                            mobileEvents.push({
                                ...event,
                                occurrenceDate: eventDate,
                                uniqueKey: event.id
                            });
                        }
                    }
                }
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
        },
        eventsGroupedByDate() {
            const grouped = {};
            this.mobileEventsList.forEach(event => {
                const date = event.occurrenceDate;
                if (!grouped[date]) {
                    grouped[date] = [];
                }
                grouped[date].push(event);
            });
            return Object.keys(grouped).sort().map(date => ({
                date: date,
                events: grouped[date]
            }));
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
        getEventUrl(event, occurrenceDate = null) {
            let url = event.guest_url;
            let queryParams = [];
            
            // For recurring events, use the occurrence date (the date being viewed)
            // For mobile view, use occurrenceDate if available (already set for recurring events)
            // For desktop view, use the passed occurrenceDate parameter
            // Always ensure we use UTC date
            let dateToUse = null;
            
            // Check if this is a recurring event
            const isRecurring = event.days_of_week && event.days_of_week.length > 0;
            
            if (isRecurring) {
                // For recurring events, prioritize the occurrence date over the original start date
                const dateStr = occurrenceDate || event.occurrenceDate;
                if (dateStr) {
                    // Parse the date string as UTC to ensure it's always UTC
                    // Date strings in Y-m-d format are treated as UTC dates
                    const [year, month, day] = dateStr.split('-').map(Number);
                    const utcDate = new Date(Date.UTC(year, month - 1, day));
                    dateToUse = utcDate.toISOString().split('T')[0];
                } else {
                    // Fallback to utc_date if occurrence date is not available
                    dateToUse = event.utc_date;
                }
            } else {
                // For non-recurring events, use utc_date
                dateToUse = event.utc_date;
            }
            
            if (dateToUse) {
                queryParams.push('date=' + dateToUse);
            }
            
            // Preserve current filter values
            if (this.selectedCategory) {
                queryParams.push('category=' + this.selectedCategory);
            }
            
            if (this.selectedGroup) {
                queryParams.push('schedule=' + this.selectedGroup);
            }
            
            if (queryParams.length > 0) {
                url += '?' + queryParams.join('&');
            }
            
            return url;
        },
        navigateToEvent(event, e) {
            // Don't navigate if clicking on the edit link
            if (e.target.closest('a')) return;

            const url = this.getEventUrl(event);
            const openInNewTab = this.embed || this.route === 'admin';

            if (openInNewTab) {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        },
        getEventPopupData(event) {
            const time = this.getEventTime(event);
            return {
                name: event.name || '',
                venue_name: event.venue_name || '',
                time: time || '',
                image_url: event.image_url || '',
                description: '' // Description not currently in event data
            };
        },
        getEventDisplayName(event) {
            if (this.subdomain && this.isRoleAMember(event)) {
                return event.venue_name || event.name;
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
            // Parse the date string manually to avoid timezone issues
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day); // month is 0-indexed
            eventDate.setHours(23, 59, 59, 999);
            
            let today = new Date();
            
            // If user has a timezone, adjust today's date to their timezone
            if (this.userTimezone) {
                // Create a date in the user's timezone
                const userNow = new Date().toLocaleString("en-US", {timeZone: this.userTimezone});
                today = new Date(userNow);
            }
            
            today.setHours(0, 0, 0, 0);
            return eventDate < today;
        },
        formatMobileDate(dateStr) {
            if (!dateStr) return '';

            // Parse the date string manually to avoid timezone issues
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day); // month is 0-indexed
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                              'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const dayNum = eventDate.getDate();
            const suffix = this.getDaySuffix(dayNum);
            const monthName = monthNames[eventDate.getMonth()];

            return `${monthName} ${dayNum}${suffix}`;
        },
        formatDateHeader(dateStr) {
            if (!dateStr) return '';
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day);
            return eventDate.toLocaleDateString(this.languageCode, {
                weekday: 'long',
                month: 'long',
                day: 'numeric'
            });
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
        initPopups() {
            // Initialize popup event listeners after Vue updates
            this.$nextTick(() => {
                const eventLinks = document.querySelectorAll('.event-link-popup');
                eventLinks.forEach(el => {
                    el.addEventListener('mouseenter', (e) => {
                        const eventId = el.getAttribute('data-event-id');
                        const event = this.allEvents.find(ev => ev.id === eventId);
                        if (event) {
                            this.showEventPopup(event, el, e);
                        }
                    });
                    el.addEventListener('mouseleave', () => {
                        this.hideEventPopup();
                    });
                    el.addEventListener('mousemove', (e) => {
                        if (this.popupTimeout) {
                            clearTimeout(this.popupTimeout);
                        }
                        this.popupTimeout = setTimeout(() => {
                            this.updatePopupPosition(e);
                        }, 10);
                    });
                });
            });
        },
        showEventPopup(event, element, e) {
            // Clear any existing timeout
            if (this.popupTimeout) {
                clearTimeout(this.popupTimeout);
            }

            // Get popup data
            const popupData = this.getEventPopupData(event);

            // Update popup content
            const popup = document.getElementById('event-popup');
            if (!popup) return;

            const titleEl = document.getElementById('event-popup-title');
            const imageEl = document.getElementById('event-popup-image');
            const venueEl = document.getElementById('event-popup-venue');
            const venueTextEl = document.getElementById('event-popup-venue-text');
            const timeEl = document.getElementById('event-popup-time');
            const timeTextEl = document.getElementById('event-popup-time-text');
            const descriptionEl = document.getElementById('event-popup-description');

            if (titleEl) titleEl.textContent = popupData.name || '';

            if (imageEl) {
                if (popupData.image_url) {
                    imageEl.src = popupData.image_url;
                    imageEl.style.display = 'block';
                } else {
                    imageEl.style.display = 'none';
                }
            }

            if (venueEl && venueTextEl) {
                if (popupData.venue_name) {
                    venueTextEl.textContent = popupData.venue_name;
                    venueEl.style.display = 'flex';
                } else {
                    venueEl.style.display = 'none';
                }
            }

            if (timeEl && timeTextEl) {
                if (popupData.time) {
                    timeTextEl.textContent = popupData.time;
                    timeEl.style.display = 'flex';
                } else {
                    timeEl.style.display = 'none';
                }
            }

            if (descriptionEl) {
                if (popupData.description) {
                    descriptionEl.textContent = popupData.description;
                    descriptionEl.style.display = 'block';
                } else {
                    descriptionEl.style.display = 'none';
                }
            }

            // Show popup
            popup.classList.add('show');
            this.updatePopupPosition(e, element);
        },
        hideEventPopup() {
            if (this.popupTimeout) {
                clearTimeout(this.popupTimeout);
            }
            const popup = document.getElementById('event-popup');
            if (popup) {
                popup.classList.remove('show');
            }
        },
        updatePopupPosition(e, element = null) {
            const popup = document.getElementById('event-popup');
            if (!popup || !popup.classList.contains('show')) return;

            // 1. One-time setup for performance (GPU acceleration)
            if (popup.style.position !== 'fixed') {
                Object.assign(popup.style, {
                    position: 'fixed',
                    top: '0',
                    left: '0',
                    willChange: 'transform',
                    pointerEvents: 'none' // Prevents flickering when mouse overlaps popup
                });
            }

            // 2. Throttle the update to the browser's refresh rate (rAF)
            if (popup._ticking) return;
            popup._ticking = true;

            window.requestAnimationFrame(() => {
                popup._ticking = false;
                
                const content = popup.querySelector('.event-popup-content') || popup;
                
                // 3. Cache dimensions (Layout Reads)
                const pW = content.offsetWidth || 320;
                const pH = content.offsetHeight || 200;
                const vW = window.innerWidth;
                const vH = window.innerHeight;
                const offset = 15;

                let left, top;

                // 4. Calculate Raw Position
                if (e) {
                    left = e.clientX + offset;
                    top = e.clientY + offset;
                } else if (element) {
                    const rect = element.getBoundingClientRect();
                    left = rect.left + rect.width / 2;
                    top = rect.top + rect.height + offset;
                } else {
                    return;
                }

                // 5. Boundary Logic (Flip if hitting right/bottom edges)
                if (left + pW > vW) {
                    left = e ? e.clientX - pW - offset : left - pW - (offset * 2);
                }
                if (top + pH > vH) {
                    top = e ? e.clientY - pH - offset : top - pH - (offset * 2);
                }

                // 6. Hard Constraints (Keep inside viewport)
                left = Math.max(10, Math.min(left, vW - pW - 10));
                top = Math.max(10, Math.min(top, vH - pH - 10));

                // 7. Apply via Transform (GPU Composite instead of Layout/Reflow)
                popup.style.transform = `translate3d(${Math.round(left)}px, ${Math.round(top)}px, 0)`;
            });
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
        // Initialize popup functionality
        this.initPopups();
        
        // Reinitialize popups when events change
        this.$watch('filteredEvents', () => {
            this.initPopups();
        });
        
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