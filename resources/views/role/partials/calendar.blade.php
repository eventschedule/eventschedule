<style>
    [v-cloak] { display: none !important; }
    .hover-accent:not(:disabled):hover {
        background-color: var(--es-accent) !important;
        color: var(--es-contrast) !important;
    }
</style>
<div class="flex h-full flex-col" id="calendar-app">
@php
    $isAdminRoute = $route == 'admin';
    $stickyBleedClass = ($route === 'guest' && !(isset($embed) && $embed)) ? '-mx-5 px-5' : '-mx-4 px-4';
    $firstDay = $role->first_day_of_week ?? 0;
    $lastDay = ($firstDay + 6) % 7;
    $startOfMonth = Carbon\Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek($firstDay);
    $endOfMonth = Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek($lastDay);
    $currentDate = $startOfMonth->copy();
    $totalDays = $endOfMonth->diffInDays($startOfMonth) + 1;
    $totalWeeks = ceil($totalDays / 7);
    $unavailable = [];
    
    // Calculate today's date considering user's timezone if logged in
    $today = auth()->check() && auth()->user()->timezone 
        ? Carbon\Carbon::now(auth()->user()->timezone)->startOfDay()
        : Carbon\Carbon::now()->startOfDay();

    $role = $role ?? null;
    $subdomain = $subdomain ?? null;

    if (request()->graphic) {
        // Keep event processing for graphic mode (calendar-graphic.blade.php uses $events directly)
        $eventGroupIds = [];
        $eventCategoryIds = [];

        $eventsMap = [];
        foreach ($events as $event) {
            $checkDate = $startOfMonth->copy();
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

        $uniqueCategoryIds = array_unique($eventCategoryIds);
        $hasOnlineEvents = collect($events)->contains(fn($event) => !empty($event->event_url));

        $eventToVueArray = function($event) use ($role, $subdomain) {
            $groupId = isset($role) ? $event->getGroupIdForSubdomain($role->subdomain) : null;
            return [
                'id' => \App\Utils\UrlUtils::encodeId($event->id),
                'group_id' => $groupId ? \App\Utils\UrlUtils::encodeId($groupId) : null,
                'category_id' => $event->category_id,
                'name' => $event->translatedName(),
                'short_description' => $event->translatedShortDescription(),
                'venue_name' => $event->getVenueDisplayName(),
                'venue_subdomain' => $event->venue?->subdomain ?: null,
                'is_free' => $event->isFree(),
                'starts_at' => $event->starts_at,
                'days_of_week' => $event->days_of_week,
                'local_starts_at' => $event->localStartsAt(),
                'local_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
                'utc_date' => $event->starts_at ? $event->getStartDateTime(null, false)->format('Y-m-d') : null,
                'guest_url' => $event->getGuestUrl(isset($subdomain) ? $subdomain : '', ''),
                'image_url' => $event->getImageUrl(),
                'flyer_url' => $event->flyer_image_url ?: null,
                'can_edit' => auth()->user() && auth()->user()->canEditEvent($event),
                'edit_url' => auth()->user() && auth()->user()->canEditEvent($event)
                    ? (isset($role) ? config('app.url') . route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)], false) : config('app.url') . route('event.edit_admin', ['hash' => App\Utils\UrlUtils::encodeId($event->id)], false))
                    : null,
                'recurring_end_type' => $event->recurring_end_type ?? 'never',
                'recurring_end_value' => $event->recurring_end_value,
                'start_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
                'is_online' => !empty($event->event_url),
                'registration_url' => $event->registration_url,
                'description_excerpt' => Str::words(strip_tags($event->translatedDescription()), 25, '...'),
                'duration' => $event->duration,
                'parts' => $event->parts->map(fn($part) => [
                    'id' => \App\Utils\UrlUtils::encodeId($part->id),
                    'name' => $part->translatedName(),
                    'start_time' => $part->start_time,
                    'end_time' => $part->end_time,
                ])->values()->toArray(),
                'video_count' => $event->approved_videos_count ?? 0,
                'comment_count' => $event->approved_comments_count ?? 0,
                'venue_profile_image' => $event->venue?->profile_image_url ?: null,
                'venue_header_image' => ($event->venue && $event->venue->getAttributes()['header_image'] && $event->venue->getAttributes()['header_image'] !== 'none') ? $event->venue->getHeaderImageUrlAttribute($event->venue->getAttributes()['header_image']) : null,
                'venue_guest_url' => ($event->venue && isset($role) && $event->venue->subdomain === $role->subdomain) ? null : ($event->venue?->getGuestUrl() ?: null),
                'talent' => $event->roles->filter(fn($r) => $r->type === 'talent')->map(fn($r) => [
                    'name' => $r->name,
                    'profile_image' => $r->profile_image_url ?: null,
                    'header_image' => ($r->getAttributes()['header_image'] && $r->getAttributes()['header_image'] !== 'none') ? $r->getHeaderImageUrlAttribute($r->getAttributes()['header_image']) : null,
                    'guest_url' => (isset($role) && $r->subdomain === $role->subdomain) ? null : ($r->getGuestUrl() ?: null),
                ])->values()->toArray(),
                'videos' => $event->relationLoaded('approvedVideos') ? $event->approvedVideos->take(3)->map(fn($v) => [
                    'youtube_url' => $v->youtube_url,
                    'thumbnail_url' => \App\Utils\UrlUtils::getYouTubeThumbnail($v->youtube_url),
                    'embed_url' => \App\Utils\UrlUtils::getYouTubeEmbed($v->youtube_url),
                ])->values()->toArray() : [],
                'recent_comments' => $event->relationLoaded('approvedComments') ? $event->approvedComments->take(2)->map(fn($c) => [
                    'author' => $c->user ? ($c->user->first_name ?: 'User') : 'User',
                    'text' => Str::limit($c->comment, 80),
                ])->values()->toArray() : [],
                'occurrenceDate' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
                'uniqueKey' => \App\Utils\UrlUtils::encodeId($event->id),
                'submit_video_url' => isset($role) ? route('event.submit_video', ['subdomain' => $role->subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($event->id)]) : null,
                'submit_comment_url' => isset($role) ? route('event.submit_comment', ['subdomain' => $role->subdomain, 'event_hash' => \App\Utils\UrlUtils::encodeId($event->id)]) : null,
            ];
        };

        $eventsForVue = [];
        foreach ($events as $event) {
            $eventsForVue[] = $eventToVueArray($event);
        }

        $pastEventsForVue = [];
        foreach (($pastEvents ?? collect()) as $event) {
            $pastEventsForVue[] = $eventToVueArray($event);
        }

        $eventsMapForVue = [];
        foreach ($eventsMap as $date => $eventsForDate) {
            $eventsMapForVue[$date] = array_map(function($event) {
                return \App\Utils\UrlUtils::encodeId($event->id);
            }, $eventsForDate);
        }
    } else {
        // Ajax mode - event data will be loaded via fetch
        $eventsForVue = [];
        $eventsMapForVue = [];
        $pastEventsForVue = [];
        $uniqueCategoryIds = [];
        $hasOnlineEvents = false;
    }

    // Prepare groups data for Vue
    $groupsForVue = [];
    if (isset($role) && $role->groups) {
        foreach ($role->groups as $group) {
            $groupsForVue[] = [
                'id' => \App\Utils\UrlUtils::encodeId($group->id),
                'slug' => $group->slug,
                'name' => $group->translatedName(),
                'color' => $group->color,
            ];
        }
    }

    $accentColor = $accentColor ?? (isset($role) && $role->accent_color ? $role->accent_color : '#4E81FA');
    $contrastColor = accent_contrast_color($accentColor);
@endphp

{{-- Panel wrapper --}}
<div style="--es-accent: {{ $accentColor }}; --es-contrast: {{ $contrastColor }}">

@if (! request()->graphic)
<header class="{{ (isset($force_mobile) && $force_mobile) ? 'hidden' : '' }} {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}"
    @if ($route == 'guest')
        :class="currentView === 'list' ? 'pt-0 pb-0' : 'pt-2 pb-4'"
    @else
        :class="currentView === 'list' ? (hasDesktopFilters ? 'pt-2 pb-4' : 'pt-0 pb-0') : 'pt-2 pb-4'"
    @endif
>
    {{-- Main container: Stacks content on mobile, aligns in a row on desktop. --}}
    <div class="flex flex-col md:flex-row md:flex-wrap md:items-center md:justify-between gap-4">

        {{-- Month and Year Title: Always visible and positioned first (hidden in list view). --}}
        <h1 v-show="currentView === 'calendar'" class="text-2xl font-semibold leading-6 flex-shrink-0 hidden md:block text-gray-900 dark:text-gray-100">
            <time datetime="{{ sprintf('%04d-%02d', $year, $month) }}">{{ Carbon\Carbon::create($year, $month, 1)->locale($isAdminRoute && auth()->check() ? app()->getLocale() : (session()->has('translate') ? 'en' : (isset($role) && $role->language_code ? $role->language_code : 'en')))->translatedFormat('F Y') }}</time>
        </h1>


        {{-- All Controls Wrapper: Groups all interactive elements. Stacks on mobile, row on desktop. --}}
        <div class="flex flex-col md:flex-row md:items-center md:ms-auto gap-3">

            {{-- Month Navigation Controls --}}
            <div v-show="currentView === 'calendar'" class="flex items-center bg-white/95 dark:bg-gray-900/95 rounded-md shadow-sm hidden md:flex">
                <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) }}" class="flex h-11 w-14 items-center justify-center rounded-s-md border-s border-y border-gray-300 dark:border-gray-600 pe-1 text-gray-400 dark:text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:relative md:w-11 md:pe-0 md:hover:bg-gray-50 dark:md:hover:bg-gray-700" rel="nofollow">
                    <span class="sr-only">{{ __('messages.previous_month') }}</span>
                    <svg class="h-6 w-6 {{ is_rtl() ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="{{ $route == 'home' ? route('home') : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => now()->year, 'month' => now()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => now()->year, 'month' => now()->month]) }}" class="flex h-11 items-center justify-center border-y border-gray-300 dark:border-gray-600 px-4 text-base font-semibold text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 focus:relative">
                    <span class="h-6 flex items-center">{{ __('messages.this_month') }}</span>
                </a>
                <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) }}" class="flex h-11 w-14 items-center justify-center rounded-e-md border-e border-y border-gray-300 dark:border-gray-600 ps-1 text-gray-400 dark:text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:relative md:w-11 md:ps-0 md:hover:bg-gray-50 dark:md:hover:bg-gray-700" rel="nofollow">
                    <span class="sr-only">{{ __('messages.next_month') }}</span>
                    <svg class="h-6 w-6 {{ is_rtl() ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>

            {{-- Save Button --}}
            @if ($route == 'admin' && $role->email_verified_at)
                @if ($tab == 'availability')
                    <x-brand-button id="saveButton" :disabled="true" class="w-full md:w-auto">
                        <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6.5 20Q4.22 20 2.61 18.43 1 16.85 1 14.58 1 12.63 2.17 11.1 3.35 9.57 5.25 9.15 5.88 6.85 7.75 5.43 9.63 4 12 4 14.93 4 16.96 6.04 19 8.07 19 11 20.73 11.2 21.86 12.5 23 13.78 23 15.5 23 17.38 21.69 18.69 20.38 20 18.5 20H13Q12.18 20 11.59 19.41 11 18.83 11 18V12.85L9.4 14.4L8 13L12 9L16 13L14.6 14.4L13 12.85V18H18.5Q19.55 18 20.27 17.27 21 16.55 21 15.5 21 14.45 20.27 13.73 19.55 13 18.5 13H17V11Q17 8.93 15.54 7.46 14.08 6 12 6 9.93 6 8.46 7.46 7 8.93 7 11H6.5Q5.05 11 4.03 12.03 3 13.05 3 14.5 3 15.95 4.03 17 5.05 18 6.5 18H9V20M12 13Z" />
                        </svg>
                        {{ __('messages.save') }}
                    </x-brand-button>
                @endif
            @elseif ($route == 'home' && !is_demo_mode())
                <div style="font-family: sans-serif" class="relative inline-block text-left w-full md:w-auto">
                    <button type="button" data-popup-target="calendar-pop-up-menu" class="popup-toggle inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-5 py-3 text-base font-bold text-gray-500 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700" id="menu-button" aria-expanded="true" aria-haspopup="true">
                        {{ __('messages.new_schedule') }}
                        <svg class="-me-1 h-6 w-6 text-gray-400 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="calendar-pop-up-menu" class="pop-up-menu hidden absolute end-0 z-10 mt-2 w-64 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-gray-700 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-gray-600 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                        <div class="py-1" role="none" data-popup-target="calendar-pop-up-menu">
                        <a href="{{ route('new', ['type' => 'talent']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" role="menuitem" tabindex="-1">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                                </svg>
                                <div>
                                    {{ __('messages.talent') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_schedule_tooltip') }}</div>
                                </div>
                            </a>
                            <a href="{{ route('new', ['type' => 'venue']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" role="menuitem" tabindex="-1">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
                                </svg>
                                <div>
                                    {{ __('messages.venue') }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.new_venue_tooltip') }}</div>
                                </div>
                            </a>
                            <a href="{{ route('new', ['type' => 'curator']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700" role="menuitem" tabindex="-1">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
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

            {{-- Mobile: Filters + Add Event buttons side-by-side (not shown on guest route - hero version used instead) --}}
            @if ($route != 'guest')
            <div class="md:hidden flex flex-row gap-2 w-full mb-3">
                {{-- Mobile Filters Button (always shown when filters exist) --}}
                <template v-if="dynamicFilterCount > 0">
                    <button @click="showFiltersDrawer = true"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5
                                   border border-gray-300 dark:border-gray-600 rounded-md
                                   bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                   text-base font-semibold {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/>
                        </svg>
                        {{ __('messages.filters') }}
                        <span v-if="activeFilterCount > 0"
                              class="ms-1 px-1.5 py-0.5 text-xs bg-[#4E81FA] text-white rounded-full">
                            @{{ activeFilterCount }}
                        </span>
                    </button>
                </template>
                {{-- Mobile Add Event Button --}}
                @if ($route == 'admin' && $role->email_verified_at && $tab == 'schedule')
                    <x-brand-link href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}" class="flex-1">
                        <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        {{ __('messages.add_event') }}
                    </x-brand-link>
                @endif
            </div>
            @endif

            {{-- Desktop: Filters Button with label - AP only --}}
            @if ($route == 'admin')
            <template v-if="dynamicFilterCount > 0">
                <button @click="showDesktopFiltersModal = true"
                        :class="currentView === 'list' ? 'md:!inline-flex' : ''"
                        class="hidden md:inline-flex items-center justify-center gap-2 px-4 py-2.5
                               border border-gray-300 dark:border-gray-600 rounded-md
                               bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                               text-base font-semibold hover:bg-gray-50 dark:hover:bg-gray-700
                               relative">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/>
                    </svg>
                    {{ __('messages.filters') }}
                    {{-- Active filter count badge --}}
                    <span v-if="activeFilterCount > 0"
                          class="ms-1 px-1.5 py-0.5 text-xs bg-[#4E81FA] text-white rounded-full">
                        @{{ activeFilterCount }}
                    </span>
                </button>
            </template>
            @endif

            {{-- Desktop Add Event Button --}}
            @if ($route == 'admin' && $role->email_verified_at && $tab == 'schedule')
                <x-brand-link href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}" class="hidden md:inline-flex w-auto">
                    <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    {{ __('messages.add_event') }}
                </x-brand-link>
            @endif
        </div>
    </div>
</header>
@endif

    <div v-show="currentView === 'calendar'" class="{{ ($tab == 'availability' || (isset($embed) && $embed) || (isset($force_mobile) && $force_mobile)) ? '' : 'hidden' }} md:block {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">

        @if (request()->graphic)
            @include('role.partials.calendar-graphic')
        @else
        <div v-if="isLoadingEvents">
            {{-- Desktop skeleton --}}
            <div class="hidden md:block {{ (isset($force_mobile) && $force_mobile) ? '!hidden' : '' }} border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden animate-pulse">
                <div class="grid grid-cols-7 gap-px border-b border-gray-300 dark:border-gray-700 bg-gray-200 dark:bg-gray-700">
                    @for ($i = 0; $i < 7; $i++)
                    <div class="flex justify-center bg-white dark:bg-gray-900 py-2">
                        <div class="h-4 w-8 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    </div>
                    @endfor
                </div>
                <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
                    @for ($i = 0; $i < 35; $i++)
                    <div class="bg-white dark:bg-gray-900 p-2 min-h-[100px]">
                        <div class="h-4 w-6 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                        <div class="h-3 w-full bg-gray-200 dark:bg-gray-700 rounded mb-1"></div>
                        <div class="h-3 w-3/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    </div>
                    @endfor
                </div>
            </div>
            {{-- Mobile skeleton --}}
            <div class="md:hidden {{ (isset($force_mobile) && $force_mobile) ? '!block' : '' }} space-y-3 px-1 py-4 animate-pulse">
                @for ($i = 0; $i < 5; $i++)
                <div class="flex items-center bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-3/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-3 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-3 w-1/3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    </div>
                    <div class="w-24 h-16 bg-gray-200 dark:bg-gray-700 rounded ml-3"></div>
                </div>
                @endfor
            </div>
        </div>
        <div v-show="!isLoadingEvents" class="hidden md:block {{ (isset($force_mobile) && $force_mobile) ? '!hidden' : '' }} border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
            <div
                class="grid grid-cols-7 gap-px border-b border-gray-300 dark:border-gray-700 bg-gray-200 dark:bg-gray-700 text-center text-xs font-semibold leading-6 text-gray-700 dark:text-gray-300">
                @php
                    $dayKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
                    $dayKeys = array_merge(array_slice($dayKeys, $firstDay), array_slice($dayKeys, 0, $firstDay));
                @endphp
                @foreach ($dayKeys as $day)
                <div class="flex justify-center bg-white dark:bg-gray-900 py-2">
                    {{ __('messages.' . $day) }}
                </div>
                @endforeach
            </div>
        <div class="bg-gray-200 dark:bg-gray-700 text-xs leading-6 text-gray-700 dark:text-gray-300">
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
                <div class="cursor-pointer relative calendar-day-navigate {{ count($unavailable) ? ($currentDate->month == $month ? 'bg-orange-50 dark:bg-orange-900/30 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' : 'bg-orange-50 dark:bg-orange-900/30 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-500 dark:text-gray-400') : ($currentDate->month == $month ? 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600') }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300 dark:hover:border-gray-600"
                    data-href="{{ route('event.create', ['subdomain' => $role->subdomain, 'date' => $currentDate->format('Y-m-d')]) }}">
                    @elseif ($route == 'admin' && $tab == 'availability' && $role->email_verified_at)
                        <div class="{{ $tab == 'availability' && $currentDate->month != $month ? 'hidden md:block' : '' }} cursor-pointer relative {{ $currentDate->month == $month ? 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400' }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300 dark:hover:border-gray-600 day-element" data-date="{{ $currentDate->format('Y-m-d') }}">
                        @if (is_array($datesUnavailable) && in_array($currentDate->format('Y-m-d'), $datesUnavailable))
                            <div class="day-x"></div>
                        @endif
                    @elseif ($route == 'home' && auth()->check())
                        @if ($firstRole)
                        <div class="cursor-pointer relative calendar-day-navigate {{ $currentDate->month == $month ? 'bg-white dark:bg-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300 dark:hover:border-gray-600"
                            data-href="{{ route('event.create', ['subdomain' => $firstRole->subdomain, 'date' => $currentDate->format('Y-m-d')]) }}">
                        @else
                        <div
                            class="relative {{ $currentDate->month == $month ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400' }} px-3 py-2 min-h-[100px] border-1 border-transparent">
                        @endif
                    @else
                    <div
                        class="relative {{ $currentDate->month == $month ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400' }} px-3 py-2 min-h-[100px] border-1 border-transparent">
                        @endif
                        <div class="flex justify-between">
                        @if ($route == 'admin')
                        <time datetime="{{ $currentDate->format('Y-m-d') }}"
                            class="{{ $currentDate->day == $today->day && $currentDate->month == $today->month && $currentDate->year == $today->year ? 'flex h-6 w-6 items-center justify-center rounded bg-[#4E81FA] font-semibold text-white' : '' }}">{{ $currentDate->day }}</time>
                        @else
                        @php
                            $isToday = $currentDate->day == $today->day && $currentDate->month == $today->month && $currentDate->year == $today->year;
                            $todayAccent = (isset($otherRole) && $otherRole->accent_color ? $otherRole->accent_color : (isset($role) && $role->accent_color ? $role->accent_color : '#4E81FA'));
                        @endphp
                        <time datetime="{{ $currentDate->format('Y-m-d') }}"
                            style="{{ $isToday ? 'background-color: ' . $todayAccent . '; color: ' . accent_contrast_color($todayAccent) : '' }}"
                            class="{{ $isToday ? 'flex h-6 w-6 items-center justify-center rounded font-semibold' : '' }}">{{ $currentDate->day }}</time>
                        @endif
                        @if (count($unavailable))
                            <div class="has-tooltip" data-tooltip="{!! __('messages.unavailable') . ":<br/>" . implode("<br/>", $unavailable) !!}">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#888">
                                    <path d="M11,9H13V7H11M12,20C7.59,20 4,16.41 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,16.41 16.41,20 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M11,17H13V11H11V17Z" />
                                </svg>
                            </div>
                        @endif
                        </div>
                        @if (($tab ?? '') != 'availability')
                        <ol class="mt-4 divide-y divide-gray-100 dark:divide-gray-700 text-sm leading-6 md:col-span-7 xl:col-span-8">
                            <li v-for="event in getEventsForDate('{{ $currentDate->format('Y-m-d') }}')" :key="event.id"
                                class="relative group"
                                :class="event.can_edit ? 'hover:pe-8' : ''"
                                v-show="isEventVisible(event)">
                                <a :href="getEventUrl(event, '{{ $currentDate->format('Y-m-d') }}')"
                                    class="flex event-link-popup"
                                    :data-event-id="event.id"
                                    @click.stop {{ ($route != 'guest' || (isset($embed) && $embed)) ? "target='_blank'" : '' }}>
                                    <p class="flex-auto font-medium group-hover:text-[#4E81FA] text-gray-900 dark:text-gray-100 {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }} truncate">
                                        <span class="flex items-start gap-1.5">
                                            <span v-if="getEventGroupColor(event)" class="inline-block w-2 h-2 rounded-full flex-shrink-0 mt-1.5" :style="{ backgroundColor: getEventGroupColor(event) }"></span>
                                            <span :class="getEventsForDate('{{ $currentDate->format('Y-m-d') }}').filter(e => isEventVisible(e)).length == 1 ? 'line-clamp-2' : 'line-clamp-1'"
                                              class="hover:underline truncate" dir="auto" v-text="getEventDisplayName(event)">
                                            </span>
                                        </span>
                                        <span v-if="getEventsForDate('{{ $currentDate->format('Y-m-d') }}').filter(e => isEventVisible(e)).length == 1"
                                              class="text-gray-500 dark:text-gray-400 truncate" v-text="getEventTime(event)">
                                        </span>
                                    </p>
                                </a>
                                <a v-if="event.can_edit" :href="event.edit_url"
                                    class="absolute end-0 top-0 hidden group-hover:inline-block text-gray-900 dark:text-white hover:underline"
                                    @click.stop>
                                    {{ __('messages.edit') }}
                                </a>
                            </li>
                        </ol>
                        @endif
                    </div>
                    @php $currentDate->addDay(); @endphp
                    @endwhile
                </div>
            </div>
        </div>
        @endif


        @if (($tab ?? '') != 'availability')
        <div v-show="currentView === 'calendar' && !isLoadingEvents" class="{{ (isset($force_mobile) && $force_mobile) ? '' : 'md:hidden' }}">
            <div v-if="mobileEventsList.length">
                <button id="showPastEventsBtn" class="text-[#4E81FA] font-medium hidden mb-4 w-full text-center">
                    {{ __('messages.show_past_events') }}
                </button>
                <div id="mobileEventsList" class="space-y-6">
                    <template v-for="(group, groupIndex) in eventsGroupedByDate" :key="'date-' + group.date">
                        {{-- Date Header --}}
                        <div class="sticky top-0 z-10 {{ $stickyBleedClass }} {{ (isset($force_mobile) && $force_mobile) ? 'bg-[#F5F9FE] dark:bg-gray-800' : 'bg-white dark:bg-gray-900' }}"
                            :class="isPastEvent(group.date) ? 'past-event hidden' : ''">
                            <div class="px-4 pb-5 pt-3 flex items-center gap-4">
                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100 text-center" v-text="formatDateHeader(group.date)" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></div>
                                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                            </div>
                        </div>
                        {{-- Events for this date --}}
                        <div class="space-y-6" :class="isPastEvent(group.date) ? 'past-event hidden' : ''">
                            <template v-for="event in group.events" :key="'mobile-' + event.uniqueKey">
                                <div v-if="isEventVisible(event)"
                                     @click="navigateToEvent(event, $event)"
                                     class="block cursor-pointer">
                                    <div class="event-item bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-lg hover:bg-gray-50/95 dark:hover:bg-gray-800/95"
                                        :class="isPastEvent(event.occurrenceDate) ? 'past-event hidden' : ''">
                                        <div class="flex" :class="isRtl ? 'flex-row-reverse' : ''">
                                            {{-- Content Section --}}
                                            <div class="flex-1 py-3 px-4 flex flex-col min-w-0">
                                                <div class="flex items-start gap-1.5">
                                                    <span v-if="getEventGroupColor(event)" class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0 mt-1" :style="{ backgroundColor: getEventGroupColor(event) }"></span>
                                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base leading-snug line-clamp-2" dir="auto" v-text="event.name"></h3>
                                                </div>
                                                <p v-if="event.short_description" class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-1" dir="auto" v-text="event.short_description"></p>
                                                <a v-if="event.venue_name && event.venue_guest_url" :href="event.venue_guest_url" class="mt-1.5 flex items-center min-w-0 text-sm text-gray-500 dark:text-gray-400 hover:opacity-80 transition-opacity">
                                                    <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2" viewBox="0 0 24 24" fill="currentColor">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                                    </svg>
                                                    <span class="line-clamp-2" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
                                                </a>
                                                <div v-else-if="event.venue_name" class="mt-1.5 flex items-center min-w-0 text-sm text-gray-500 dark:text-gray-400">
                                                    <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2" viewBox="0 0 24 24" fill="currentColor">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                                    </svg>
                                                    <span class="line-clamp-2" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
                                                </div>
                                                <div class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                    <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span v-text="getEventTime(event)"></span>
                                                </div>
                                                <div v-if="event.can_edit" class="mt-auto pt-3">
                                                    <a :href="event.edit_url"
                                                        class="hover-accent inline-flex items-center px-4 py-1.5 text-sm font-medium text-gray-900 dark:text-white rounded-md border transition-all duration-200 hover:scale-105"
                                                        style="border-color: {{ $accentColor }}"
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
                    </template>
                </div>
            </div>
            <div v-else-if="!isLoadingEvents && {{ $tab != 'availability' ? 'true' : 'false' }}" class="pb-4 text-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 py-12 px-8">
                    <div class="text-xl text-gray-500 dark:text-gray-400">
                        {{ __('messages.no_scheduled_events') }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>


{{-- List View Skeleton (Desktop) --}}
        <div v-if="currentView === 'list' && isLoadingEvents" class="hidden md:block {{ (isset($force_mobile) && $force_mobile) ? '!hidden' : '' }} space-y-4 animate-pulse">
            @for ($i = 0; $i < 4; $i++)
            <div class="rounded-2xl shadow-sm overflow-hidden bg-white/95 dark:bg-gray-900/95">
                <div class="flex flex-col md:flex-row">
                    {{-- Details Column --}}
                    <div class="md:flex-1 md:min-w-0 px-5 py-6 md:px-8 lg:px-16 md:py-8 flex flex-col gap-5">
                        {{-- Title --}}
                        <div class="h-8 w-3/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        {{-- Short description --}}
                        <div class="h-4 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        {{-- Date Badge --}}
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"></div>
                            <div class="flex flex-col gap-2">
                                <div class="h-5 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                        </div>
                        {{-- Venue Badge --}}
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"></div>
                            <div class="h-5 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </div>
                        {{-- Talent Avatars --}}
                        <div class="flex items-center gap-2">
                            <div class="flex items-center -space-x-2">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-700"></div>
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-700"></div>
                            </div>
                            <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </div>
                    </div>
                    {{-- Image Column --}}
                    <div class="flex-shrink-0 w-80 lg:w-96 h-64 md:h-auto bg-gray-200 dark:bg-gray-700"></div>
                </div>
            </div>
            @endfor
        </div>

{{-- List View (Desktop) --}}
        <div v-show="currentView === 'list' && !isLoadingEvents" class="hidden md:block {{ (isset($force_mobile) && $force_mobile) ? '!hidden' : '' }} {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
            {{-- Upcoming Events --}}
            <div v-if="allListEvents.length" class="space-y-4">
                <template v-for="(event, eventIndex) in allListEvents" :key="'list-' + event.uniqueKey">
                    <div v-if="event._isPast && (eventIndex === 0 || !allListEvents[eventIndex - 1]._isPast)"
                         class="py-4 flex items-center gap-4">
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-full px-4 py-1 bg-white dark:bg-gray-900">
                            {{ __('messages.past_events') }}
                        </span>
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                    </div>
                    <div @click="navigateToEvent(event, $event)" class="block cursor-pointer">
                        <div class="rounded-2xl shadow-sm overflow-hidden transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm">
                            {{-- Side-by-side layout when flyer image exists --}}
                            <template v-if="event.flyer_url">
                                <div class="flex flex-col md:flex-row" :class="isRtl ? 'md:flex-row-reverse' : ''">
                                    {{-- Details Column --}}
                                    <div class="md:flex-1 md:min-w-0 px-5 py-6 md:px-8 lg:px-16 md:py-8 flex flex-col gap-5">
                                        {{-- Event Title --}}
                                        <div class="flex items-start gap-2">
                                            <span v-if="getEventGroupColor(event)" class="inline-block w-3 h-3 rounded-full flex-shrink-0 mt-2" :style="{ backgroundColor: getEventGroupColor(event) }"></span>
                                            <h3 class="font-bold text-2xl md:text-3xl leading-snug line-clamp-2 text-gray-900 dark:text-gray-100" dir="auto" v-text="event.name"></h3>
                                        </div>
                                        <p v-if="event.short_description" class="text-gray-600 dark:text-gray-400 mt-2" dir="auto" v-text="event.short_description"></p>

                                        {{-- Date Badge --}}
                                        <div v-if="event.occurrenceDate" class="flex items-center gap-4">
                                            <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex flex-col items-center justify-center shadow-sm">
                                                <span class="text-[11px] font-bold uppercase tracking-wider leading-none pt-1" style="color: {{ $accentColor }}" v-text="getMonthAbbr(event.occurrenceDate)"></span>
                                                <span class="text-2xl font-bold text-gray-900 dark:text-white leading-none" v-text="getDayNum(event.occurrenceDate)"></span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-lg font-semibold text-gray-900 dark:text-white" v-text="formatDayName(event.occurrenceDate)"></span>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                                    <span v-text="getEventTime(event)"></span>
                                                    <span v-if="event.duration" class="ms-1" v-text="'(' + formatDuration(event.duration) + ')'"></span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Venue Badge --}}
                                        <a v-if="event.venue_name && event.venue_guest_url" :href="event.venue_guest_url" class="flex items-center gap-4 min-w-0 hover:opacity-80 transition-opacity">
                                            <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                                <img v-if="event.venue_profile_image" :src="event.venue_profile_image" class="w-11 h-11 rounded-lg object-cover" :alt="event.venue_name">
                                                <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="{{ $accentColor }}" aria-hidden="true">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                                </svg>
                                            </div>
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2 hover:underline" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
                                            <svg class="w-5 h-5 flex-shrink-0 fill-gray-900 dark:fill-gray-100 opacity-70" :class="isRtl ? 'scale-x-[-1]' : ''" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                            </svg>
                                        </a>
                                        <div v-else-if="event.venue_name" class="flex items-center gap-4 min-w-0">
                                            <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                                <img v-if="event.venue_profile_image" :src="event.venue_profile_image" class="w-11 h-11 rounded-lg object-cover" :alt="event.venue_name">
                                                <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="{{ $accentColor }}" aria-hidden="true">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                                </svg>
                                            </div>
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
                                        </div>

                                        {{-- Talent Avatars + Names --}}
                                        <div v-if="event.talent && event.talent.length > 0" class="flex items-center gap-2">
                                            <div class="flex items-center -space-x-2" :class="isRtl ? 'space-x-reverse' : ''">
                                                <template v-for="(t, tIndex) in event.talent.slice(0, 5)" :key="'ta-' + tIndex">
                                                    <img v-if="t.profile_image" :src="t.profile_image" class="w-8 h-8 rounded-full object-cover border-2 border-white dark:border-gray-700" :alt="t.name" :title="t.name">
                                                    <div v-else class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-700 bg-gray-200 dark:bg-gray-600 flex items-center justify-center" :title="t.name">
                                                        <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400" v-text="t.name.charAt(0).toUpperCase()"></span>
                                                    </div>
                                                </template>
                                            </div>
                                            <template v-for="(t, tIndex) in event.talent" :key="'tn-' + tIndex">
                                                <span v-if="tIndex > 0" class="text-base text-gray-600 dark:text-gray-300">, </span>
                                                <a v-if="t.guest_url" :href="t.guest_url" class="text-base text-gray-600 dark:text-gray-300 hover:opacity-80 hover:underline transition-opacity truncate" v-text="t.name"></a>
                                                <span v-else class="text-base text-gray-600 dark:text-gray-300 truncate" v-text="t.name"></span>
                                            </template>
                                            <svg v-if="event.talent.some(t => t.guest_url)" class="w-5 h-5 flex-shrink-0 fill-gray-900 dark:fill-gray-100 opacity-70" :class="isRtl ? 'scale-x-[-1]' : ''" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                            </svg>
                                        </div>

                                        {{-- Video Thumbnails --}}
                                        <div v-if="event.videos && event.videos.length > 0" class="mt-3 space-y-2">
                                            {{-- Playing video iframe (full width, above thumbnails) --}}
                                            <div v-if="event.videos.some((v, i) => playingVideo === event.uniqueKey + '-' + i)"
                                                 class="w-full aspect-video rounded-lg overflow-hidden shadow-sm" @click.stop>
                                                <template v-for="(vid, vidIdx) in event.videos" :key="'playing-' + vidIdx">
                                                    <iframe v-if="playingVideo === event.uniqueKey + '-' + vidIdx"
                                                            :src="vid.embed_url + '?autoplay=1'"
                                                            class="w-full h-full" frameborder="0"
                                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                            allowfullscreen></iframe>
                                                </template>
                                            </div>
                                            {{-- Thumbnail row --}}
                                            <div class="flex gap-2 overflow-x-auto">
                                                <div v-for="(vid, vidIdx) in event.videos" :key="'vid-' + vidIdx"
                                                     @click.stop="playVideo(event.uniqueKey + '-' + vidIdx)"
                                                     class="relative flex-shrink-0 w-28 h-20 rounded-lg overflow-hidden shadow-sm group/vid cursor-pointer"
                                                     :class="playingVideo === event.uniqueKey + '-' + vidIdx ? 'ring-2 ring-blue-500' : ''">
                                                    <img :src="vid.thumbnail_url" class="w-full h-full object-cover" alt="">
                                                    <div class="absolute inset-0 flex items-center justify-center"
                                                         :class="playingVideo === event.uniqueKey + '-' + vidIdx ? 'bg-black/50' : 'bg-black/30'">
                                                        <svg v-if="playingVideo !== event.uniqueKey + '-' + vidIdx"
                                                             class="w-8 h-8 text-white opacity-80 group-hover/vid:opacity-100"
                                                             viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M8 5v14l11-7z"/>
                                                        </svg>
                                                        <svg v-else class="w-6 h-6 text-white"
                                                             viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Recent Comments --}}
                                        <div v-if="event.recent_comments && event.recent_comments.length > 0" class="space-y-1.5" :dir="isRtl ? 'rtl' : 'ltr'">
                                            <div v-for="(comment, cIdx) in event.recent_comments" :key="'c-' + cIdx"
                                                 class="flex items-start gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="h-4 w-4 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M3.43 2.524A41.29 41.29 0 0110 2c2.236 0 4.43.18 6.57.524 1.437.231 2.43 1.49 2.43 2.902v5.148c0 1.413-.993 2.67-2.43 2.902a41.202 41.202 0 01-5.183.501.78.78 0 00-.528.224l-3.579 3.58A.75.75 0 016 17.25v-3.443a41.033 41.033 0 01-2.57-.33C2.993 13.244 2 11.986 2 10.574V5.426c0-1.413.993-2.67 2.43-2.902z" clip-rule="evenodd" />
                                                </svg>
                                                <span>"<span v-text="comment.text"></span>" - <span class="font-medium" v-text="comment.author"></span></span>
                                            </div>
                                        </div>

                                        {{-- Mini Timeline for Parts --}}
                                        <div v-if="event.parts && event.parts.length > 0" :dir="isRtl ? 'rtl' : 'ltr'">
                                            <div class="relative ps-2">
                                                <div class="absolute top-1 bottom-1 w-0.5 start-0" :style="'background-color: {{ $accentColor }}30'"></div>
                                                <div class="space-y-2">
                                                    <div v-for="(part, partIndex) in event.parts.slice(0, 4)" :key="'p-' + partIndex" class="relative flex items-start gap-2">
                                                        <div class="absolute top-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0 -start-[3px]" :style="'background-color: {{ $accentColor }}'"></div>
                                                        <div class="ps-3">
                                                            <span class="text-sm text-gray-700 dark:text-gray-300" v-text="part.name"></span>
                                                            <span v-if="part.start_time" class="text-xs ms-1 text-gray-500 dark:text-gray-400" v-text="part.start_time"></span>
                                                        </div>
                                                    </div>
                                                    <div v-if="event.parts.length > 4" class="text-xs text-gray-500 dark:text-gray-400 ps-3">
                                                        +<span v-text="event.parts.length - 4"></span> {{ __('messages.more') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Description excerpt --}}
                                        <p v-if="event.description_excerpt" class="hidden md:block text-base text-gray-500 dark:text-gray-400 line-clamp-2" dir="auto" v-text="event.description_excerpt"></p>

                                        {{-- Action buttons row --}}
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a v-if="event.can_edit" :href="event.edit_url"
                                               class="hover-accent inline-flex items-center gap-1.5 px-5 py-2 text-base font-medium text-gray-900 dark:text-white rounded-md border transition-all duration-200 hover:scale-105"
                                               style="border-color: {{ $accentColor }}"
                                               @click.stop>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                                {{ __('messages.edit') }}
                                            </a>
                                            <template v-if="isAuthenticated">
                                                <button @click.stop="toggleVideoForm(event, $event)"
                                                        class="hover-accent inline-flex items-center gap-1.5 px-5 py-2 text-base font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 hover:shadow-md border"
                                                        style="border-color: {{ $accentColor }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                                    {{ __('messages.add_video') }}
                                                </button>
                                                <button @click.stop="toggleCommentForm(event, $event)"
                                                        class="hover-accent inline-flex items-center gap-1.5 px-5 py-2 text-base font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 hover:shadow-md border"
                                                        style="border-color: {{ $accentColor }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                                                    {{ __('messages.add_comment') }}
                                                </button>
                                            </template>
                                        </div>

                                        {{-- Video form --}}
                                        <form v-if="openVideoForm[event.uniqueKey]" @click.stop
                                              method="POST" :action="event.submit_video_url">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                            <div class="flex flex-col gap-2">
                                                <select v-if="event.parts.length > 0" name="event_part_id"
                                                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                    <option value="">{{ __('messages.general') }}</option>
                                                    <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                                </select>
                                                <input type="text" name="youtube_url" placeholder="{{ __('messages.paste_youtube_url') }}"
                                                       class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" required>
                                                <button type="submit" class="self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                        style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.submit') }}</button>
                                            </div>
                                        </form>

                                        {{-- Comment form --}}
                                        <form v-if="openCommentForm[event.uniqueKey]" @click.stop
                                              method="POST" :action="event.submit_comment_url">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                            <div class="flex flex-col gap-2">
                                                <select v-if="event.parts.length > 0" name="event_part_id"
                                                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                    <option value="">{{ __('messages.general') }}</option>
                                                    <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                                </select>
                                                <textarea name="comment" placeholder="{{ __('messages.write_a_comment') }}" maxlength="1000"
                                                          class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" rows="2" required></textarea>
                                                <button type="submit" class="self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                        style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.submit') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                    {{-- Flyer Image Column --}}
                                    <div class="md:w-[35%] md:flex-shrink-0">
                                        <img :src="event.flyer_url" :class="event._isPast ? 'grayscale' : ''" class="w-full" :alt="event.name">
                                    </div>
                                </div>
                            </template>

                            {{-- Stacked layout when no flyer image --}}
                            <template v-else>
                                {{-- Hero Banner (only when no flyer) --}}
                                <div v-if="getHeaderImage(event)" class="h-40 relative overflow-hidden">
                                    <img :src="getHeaderImage(event)" :class="event._isPast ? 'grayscale' : ''" class="w-full h-full object-cover" :alt="event.name" v-on:error="$event?.target?.closest('.h-40') && ($event.target.closest('.h-40').style.display='none')">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                </div>

                                {{-- Content --}}
                                <div class="px-5 py-6 md:px-8 lg:px-16 md:py-8 flex flex-col gap-5">
                                    {{-- Event Title --}}
                                    <div class="flex items-start gap-2">
                                        <span v-if="getEventGroupColor(event)" class="inline-block w-3 h-3 rounded-full flex-shrink-0 mt-2" :style="{ backgroundColor: getEventGroupColor(event) }"></span>
                                        <h3 class="font-bold text-2xl md:text-3xl leading-snug line-clamp-2 text-gray-900 dark:text-gray-100" dir="auto" v-text="event.name"></h3>
                                    </div>
                                    <p v-if="event.short_description" class="text-gray-600 dark:text-gray-400 mt-2" dir="auto" v-text="event.short_description"></p>

                                    {{-- Date Badge --}}
                                    <div v-if="event.occurrenceDate" class="flex items-center gap-4">
                                        <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex flex-col items-center justify-center shadow-sm">
                                            <span class="text-[11px] font-bold uppercase tracking-wider leading-none pt-1" style="color: {{ $accentColor }}" v-text="getMonthAbbr(event.occurrenceDate)"></span>
                                            <span class="text-2xl font-bold text-gray-900 dark:text-white leading-none" v-text="getDayNum(event.occurrenceDate)"></span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-lg font-semibold text-gray-900 dark:text-white" v-text="formatDayName(event.occurrenceDate)"></span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                <span v-text="getEventTime(event)"></span>
                                                <span v-if="event.duration" class="ms-1" v-text="'(' + formatDuration(event.duration) + ')'"></span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Venue Badge --}}
                                    <a v-if="event.venue_name && event.venue_guest_url" :href="event.venue_guest_url" class="w-fit flex items-center gap-4 hover:opacity-80 transition-opacity">
                                        <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                            <img v-if="event.venue_profile_image" :src="event.venue_profile_image" class="w-11 h-11 rounded-lg object-cover" :alt="event.venue_name">
                                            <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="{{ $accentColor }}" aria-hidden="true">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                            </svg>
                                        </div>
                                        <span class="text-lg font-semibold text-gray-900 dark:text-white truncate hover:underline" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
                                        <svg class="w-5 h-5 flex-shrink-0 fill-gray-900 dark:fill-gray-100 opacity-70" :class="isRtl ? 'scale-x-[-1]' : ''" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                        </svg>
                                    </a>
                                    <div v-else-if="event.venue_name" class="flex items-center gap-4">
                                        <div class="flex-shrink-0 w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center shadow-sm">
                                            <img v-if="event.venue_profile_image" :src="event.venue_profile_image" class="w-11 h-11 rounded-lg object-cover" :alt="event.venue_name">
                                            <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="{{ $accentColor }}" aria-hidden="true">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                            </svg>
                                        </div>
                                        <span class="text-lg font-semibold text-gray-900 dark:text-white truncate" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
                                    </div>

                                    {{-- Talent Avatars + Names --}}
                                    <div v-if="event.talent && event.talent.length > 0" class="flex items-center gap-2">
                                        <div class="flex items-center -space-x-2" :class="isRtl ? 'space-x-reverse' : ''">
                                            <template v-for="(t, tIndex) in event.talent.slice(0, 5)" :key="'ta-' + tIndex">
                                                <img v-if="t.profile_image" :src="t.profile_image" class="w-8 h-8 rounded-full object-cover border-2 border-white dark:border-gray-700" :alt="t.name" :title="t.name">
                                                <div v-else class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-700 bg-gray-200 dark:bg-gray-600 flex items-center justify-center" :title="t.name">
                                                    <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400" v-text="t.name.charAt(0).toUpperCase()"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <template v-for="(t, tIndex) in event.talent" :key="'tn2-' + tIndex">
                                            <span v-if="tIndex > 0" class="text-base text-gray-600 dark:text-gray-300">, </span>
                                            <a v-if="t.guest_url" :href="t.guest_url" class="text-base text-gray-600 dark:text-gray-300 hover:opacity-80 hover:underline transition-opacity truncate" v-text="t.name"></a>
                                            <span v-else class="text-base text-gray-600 dark:text-gray-300 truncate" v-text="t.name"></span>
                                        </template>
                                        <svg v-if="event.talent.some(t => t.guest_url)" class="w-5 h-5 flex-shrink-0 fill-gray-900 dark:fill-gray-100 opacity-70" :class="isRtl ? 'scale-x-[-1]' : ''" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"/>
                                        </svg>
                                    </div>

                                    {{-- Video Thumbnails --}}
                                    <div v-if="event.videos && event.videos.length > 0" class="mt-3 space-y-2">
                                        {{-- Playing video iframe (full width, above thumbnails) --}}
                                        <div v-if="event.videos.some((v, i) => playingVideo === event.uniqueKey + '-' + i)"
                                             class="w-full aspect-video rounded-lg overflow-hidden shadow-sm" @click.stop>
                                            <template v-for="(vid, vidIdx) in event.videos" :key="'playing-' + vidIdx">
                                                <iframe v-if="playingVideo === event.uniqueKey + '-' + vidIdx"
                                                        :src="vid.embed_url + '?autoplay=1'"
                                                        class="w-full h-full" frameborder="0"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                        allowfullscreen></iframe>
                                            </template>
                                        </div>
                                        {{-- Thumbnail row --}}
                                        <div class="flex gap-2 overflow-x-auto">
                                            <div v-for="(vid, vidIdx) in event.videos" :key="'vid-' + vidIdx"
                                                 @click.stop="playVideo(event.uniqueKey + '-' + vidIdx)"
                                                 class="relative flex-shrink-0 w-28 h-20 rounded-lg overflow-hidden shadow-sm group/vid cursor-pointer"
                                                 :class="playingVideo === event.uniqueKey + '-' + vidIdx ? 'ring-2 ring-blue-500' : ''">
                                                <img :src="vid.thumbnail_url" class="w-full h-full object-cover" alt="">
                                                <div class="absolute inset-0 flex items-center justify-center"
                                                     :class="playingVideo === event.uniqueKey + '-' + vidIdx ? 'bg-black/50' : 'bg-black/30'">
                                                    <svg v-if="playingVideo !== event.uniqueKey + '-' + vidIdx"
                                                         class="w-8 h-8 text-white opacity-80 group-hover/vid:opacity-100"
                                                         viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                    <svg v-else class="w-6 h-6 text-white"
                                                         viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Recent Comments --}}
                                    <div v-if="event.recent_comments && event.recent_comments.length > 0" class="space-y-1.5" :dir="isRtl ? 'rtl' : 'ltr'">
                                        <div v-for="(comment, cIdx) in event.recent_comments" :key="'c-' + cIdx"
                                             class="flex items-start gap-2 text-sm text-gray-500 dark:text-gray-400">
                                            <svg class="h-4 w-4 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M3.43 2.524A41.29 41.29 0 0110 2c2.236 0 4.43.18 6.57.524 1.437.231 2.43 1.49 2.43 2.902v5.148c0 1.413-.993 2.67-2.43 2.902a41.202 41.202 0 01-5.183.501.78.78 0 00-.528.224l-3.579 3.58A.75.75 0 016 17.25v-3.443a41.033 41.033 0 01-2.57-.33C2.993 13.244 2 11.986 2 10.574V5.426c0-1.413.993-2.67 2.43-2.902z" clip-rule="evenodd" />
                                            </svg>
                                            <span>"<span v-text="comment.text"></span>" - <span class="font-medium" v-text="comment.author"></span></span>
                                        </div>
                                    </div>

                                    {{-- Mini Timeline for Parts --}}
                                    <div v-if="event.parts && event.parts.length > 0" :dir="isRtl ? 'rtl' : 'ltr'">
                                        <div class="relative ps-2">
                                            <div class="absolute top-1 bottom-1 w-0.5 start-0" :style="'background-color: {{ $accentColor }}30'"></div>
                                            <div class="space-y-2">
                                                <div v-for="(part, partIndex) in event.parts.slice(0, 4)" :key="'p-' + partIndex" class="relative flex items-start gap-2">
                                                    <div class="absolute top-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0 -start-[3px]" :style="'background-color: {{ $accentColor }}'"></div>
                                                    <div class="ps-3">
                                                        <span class="text-sm text-gray-700 dark:text-gray-300" v-text="part.name"></span>
                                                        <span v-if="part.start_time" class="text-xs ms-1 text-gray-500 dark:text-gray-400" v-text="part.start_time"></span>
                                                    </div>
                                                </div>
                                                <div v-if="event.parts.length > 4" class="text-xs text-gray-500 dark:text-gray-400 ps-3">
                                                    +<span v-text="event.parts.length - 4"></span> {{ __('messages.more') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Description excerpt --}}
                                    <p v-if="event.description_excerpt" class="hidden md:block text-base text-gray-500 dark:text-gray-400 line-clamp-2" dir="auto" v-text="event.description_excerpt"></p>

                                    {{-- Action buttons row --}}
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a v-if="event.can_edit" :href="event.edit_url"
                                           class="hover-accent inline-flex items-center gap-1.5 px-5 py-2 text-base font-medium text-gray-900 dark:text-white rounded-md border transition-all duration-200 hover:scale-105"
                                           style="border-color: {{ $accentColor }}"
                                           @click.stop>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            {{ __('messages.edit') }}
                                        </a>
                                        <template v-if="isAuthenticated">
                                            <button @click.stop="toggleVideoForm(event, $event)"
                                                    class="hover-accent inline-flex items-center gap-1.5 px-5 py-2 text-base font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 border"
                                                    style="border-color: {{ $accentColor }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                                {{ __('messages.add_video') }}
                                            </button>
                                            <button @click.stop="toggleCommentForm(event, $event)"
                                                    class="hover-accent inline-flex items-center gap-1.5 px-5 py-2 text-base font-medium text-gray-900 dark:text-white rounded-md transition-all duration-200 hover:scale-105 border"
                                                    style="border-color: {{ $accentColor }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" /></svg>
                                                {{ __('messages.add_comment') }}
                                            </button>
                                        </template>
                                    </div>

                                    {{-- Video form --}}
                                    <form v-if="openVideoForm[event.uniqueKey]" @click.stop
                                          method="POST" :action="event.submit_video_url">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                        <div class="flex flex-col gap-2">
                                            <select v-if="event.parts.length > 0" name="event_part_id"
                                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                <option value="">{{ __('messages.general') }}</option>
                                                <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                            </select>
                                            <input type="text" name="youtube_url" placeholder="{{ __('messages.paste_youtube_url') }}"
                                                   class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" required>
                                            <button type="submit" class="self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                    style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.submit') }}</button>
                                        </div>
                                    </form>

                                    {{-- Comment form --}}
                                    <form v-if="openCommentForm[event.uniqueKey]" @click.stop
                                          method="POST" :action="event.submit_comment_url">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input v-if="event.days_of_week" type="hidden" name="event_date" :value="event.occurrenceDate">
                                        <div class="flex flex-col gap-2">
                                            <select v-if="event.parts.length > 0" name="event_part_id"
                                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2">
                                                <option value="">{{ __('messages.general') }}</option>
                                                <option v-for="part in event.parts" :key="part.id" :value="part.id" v-text="part.name"></option>
                                            </select>
                                            <textarea name="comment" placeholder="{{ __('messages.write_a_comment') }}" maxlength="1000"
                                                      class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-3 py-2" rows="2" required></textarea>
                                            <button type="submit" class="self-start px-4 py-2 border border-transparent text-sm rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                                                    style="background-color: {{ $accentColor }}; color: {{ $contrastColor }}">{{ __('messages.submit') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Load More Button --}}
            <div v-if="!hidePastEvents && hasMorePastEvents" class="mt-6 text-center">
                <button @click.stop="loadMorePastEvents()"
                        :disabled="loadingPastEvents"
                        class="hover-accent inline-flex items-center px-6 py-2.5 text-sm font-semibold text-gray-900 dark:text-white rounded-xl border-2 transition-all duration-200 hover:scale-105"
                        style="border-color: {{ $accentColor }}">
                    <svg v-if="loadingPastEvents" class="animate-spin -ms-1 me-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('messages.load_more') }}
                </button>
            </div>


            {{-- Empty State --}}
            <div v-if="!isLoadingEvents && flatUpcomingEvents.length === 0 && pastEvents.length === 0" class="pb-4 text-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 py-12 px-8">
                    <div class="text-xl text-gray-500 dark:text-gray-400">
                        {{ __('messages.no_scheduled_events') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- List View Skeleton (Mobile) --}}
        <div v-if="currentView === 'list' && isLoadingEvents" class="{{ (isset($force_mobile) && $force_mobile) ? '' : 'md:hidden' }} animate-pulse">
            {{-- Date Header Skeleton --}}
            <div class="sticky top-0 z-10 {{ $stickyBleedClass }} bg-white dark:bg-gray-800">
                <div class="px-4 pb-5 pt-3 flex items-center gap-4">
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                    <div class="h-5 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                </div>
            </div>
            {{-- Card Skeletons --}}
            <div class="space-y-3">
                @for ($i = 0; $i < 5; $i++)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="flex">
                        <div class="flex-1 py-3 px-4 flex flex-col min-w-0 gap-2">
                            {{-- Title --}}
                            <div class="h-5 w-3/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            {{-- Short description --}}
                            <div class="h-4 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            {{-- Venue --}}
                            <div class="flex items-center gap-2">
                                <div class="h-4 w-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                            {{-- Time --}}
                            <div class="flex items-center gap-2">
                                <div class="h-4 w-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                        </div>
                        {{-- Image Thumbnail --}}
                        <div class="flex-shrink-0 w-24 h-28 bg-gray-200 dark:bg-gray-700"></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        {{-- List View (Mobile) --}}
        <div v-show="!isLoadingEvents" class="{{ (isset($force_mobile) && $force_mobile) ? 'hidden' : 'md:hidden' }} {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
            {{-- All events grouped by date --}}
            <div v-if="allMobileListGroups.length > 0" class="space-y-6">
                <template v-for="(group, groupIndex) in allMobileListGroups" :key="'list-m-' + group.date">
                    {{-- Past Events Divider --}}
                    <div v-if="group.events.every(e => e._isPast) && (groupIndex === 0 || !allMobileListGroups[groupIndex - 1].events.every(e => e._isPast))"
                         class="py-1 flex items-center gap-4">
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-full px-4 py-1 bg-white dark:bg-gray-900">
                            {{ __('messages.past_events') }}
                        </span>
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                    </div>
                    {{-- Date Header --}}
                    <div class="sticky top-0 z-10 {{ $stickyBleedClass }} bg-white dark:bg-gray-800">
                        <div :class="groupIndex === 0 ? 'pb-5 pt-0' : 'pb-5 pt-3'" class="px-4 flex items-center gap-4">
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                            <div class="font-semibold text-gray-900 dark:text-gray-100 text-center" v-text="formatDateHeader(group.date)" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></div>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                        </div>
                    </div>
                    {{-- Compact cards --}}
                    <div class="space-y-3">
                        <template v-for="event in group.events" :key="'list-mob-' + event.uniqueKey">
                            <div v-if="isEventVisible(event)" @click="navigateToEvent(event, $event)" class="block cursor-pointer">
                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <div class="flex">
                                        <div class="flex-1 py-3 px-4 flex flex-col min-w-0">
                                            <div class="flex items-start gap-1.5">
                                                <span v-if="getEventGroupColor(event)" class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0 mt-1" :style="{ backgroundColor: getEventGroupColor(event) }"></span>
                                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base leading-snug line-clamp-2" dir="auto" v-text="event.name"></h3>
                                            </div>
                                            <p v-if="event.short_description" class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-1" dir="auto" v-text="event.short_description"></p>
                                            <a v-if="event.venue_name && event.venue_guest_url" :href="event.venue_guest_url" class="w-fit mt-1.5 flex items-center text-sm text-gray-500 dark:text-gray-400 hover:opacity-80 transition-opacity">
                                                <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2" viewBox="0 0 24 24" fill="currentColor">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                                </svg>
                                                <span class="truncate hover:underline" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
                                            </a>
                                            <div v-else-if="event.venue_name" class="mt-1.5 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2" viewBox="0 0 24 24" fill="currentColor">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
                                                </svg>
                                                <span class="truncate" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
                                            </div>
                                            <div class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                                </svg>
                                                <span v-text="getEventTime(event)"></span>
                                            </div>
                                            <div v-if="event.can_edit" class="mt-auto pt-3">
                                                <a :href="event.edit_url"
                                                    class="hover-accent inline-flex items-center px-4 py-1.5 text-sm font-medium text-gray-900 dark:text-white rounded-md border transition-all duration-200 hover:scale-105"
                                                    style="border-color: {{ $accentColor }}"
                                                    @click.stop>
                                                    {{ __('messages.edit') }}
                                                </a>
                                            </div>
                                        </div>
                                        <div v-if="event.flyer_url" class="flex-shrink-0 w-24 self-stretch">
                                            <img :src="event.flyer_url" :class="event._isPast ? 'grayscale' : ''" class="w-full h-full object-cover" :alt="event.name">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Load More Button --}}
            <div v-if="!hidePastEvents && hasMorePastEvents" class="mt-6 text-center">
                <button @click.stop="loadMorePastEvents()"
                        :disabled="loadingPastEvents"
                        class="hover-accent inline-flex items-center px-6 py-2.5 text-sm font-semibold text-gray-900 dark:text-white rounded-xl border-2 transition-all duration-200 hover:scale-105"
                        style="border-color: {{ $accentColor }}">
                    <svg v-if="loadingPastEvents" class="animate-spin -ms-1 me-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('messages.load_more') }}
                </button>
            </div>

            {{-- Empty State --}}
            <div v-if="!isLoadingEvents && flatUpcomingEvents.length === 0 && pastEvents.length === 0" class="pb-4 text-center">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 py-12 px-8">
                    <div class="text-xl text-gray-500 dark:text-gray-400">
                        {{ __('messages.no_scheduled_events') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Mobile Filters Bottom Sheet Drawer - Teleported to body to escape stacking context --}}
<Teleport to="body">
<div v-cloak v-if="dynamicFilterCount >= 1 && showFiltersDrawer" class="md:hidden fixed inset-0 z-50">
    {{-- Backdrop --}}
    <div @click="showFiltersDrawer = false"
         class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>

    {{-- Bottom sheet panel --}}
    <div class="fixed inset-x-0 bottom-0 bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl max-h-[80vh] overflow-y-auto {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
        {{-- Handle bar --}}
        <div class="flex justify-center py-3 sticky top-0 bg-white dark:bg-gray-800">
            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
        </div>

        {{-- Header --}}
        <div class="px-6 pb-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.filters') }}</h3>
            <button v-if="activeFilterCount > 0"
                    @click="clearFilters"
                    class="text-sm text-[#4E81FA] hover:text-[#3d6fd9] font-medium">
                {{ __('messages.clear_filters') }}
            </button>
        </div>

        {{-- Schedule Filter --}}
        @if(isset($role) && $role->groups && $role->groups->count() > 1)
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.schedule') }}</label>
            <select v-model="selectedGroup" style="font-family: sans-serif"
                    class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                           bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
                <option value="">{{ __('messages.all_schedules') }} (@{{ eventCountByGroup[''] }})</option>
                <option v-for="group in groups" :key="group.slug" :value="group.slug">
                    @{{ group.name }} (@{{ eventCountByGroup[group.slug] || 0 }})
                </option>
            </select>
        </div>
        @endif

        {{-- Category Filter --}}
        <div v-if="availableCategories.length > 1" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.category') }}</label>
            <select v-model="selectedCategory" style="font-family: sans-serif"
                    class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                           bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                <option value="">{{ __('messages.all_categories') }} (@{{ eventCountByCategory[''] }})</option>
                <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                    @{{ category.name }} (@{{ eventCountByCategory[category.id] || 0 }})
                </option>
            </select>
        </div>

        {{-- Venue Filter --}}
        <div v-if="uniqueVenues.length > 1" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.venue') }}</label>
            <select v-model="selectedVenue" style="font-family: sans-serif"
                    class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                           bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                <option value="">{{ __('messages.all_venues') }} (@{{ eventCountByVenue[''] }})</option>
                <option v-for="venue in uniqueVenues" :key="venue.subdomain" :value="venue.subdomain">
                    @{{ venue.name }} (@{{ eventCountByVenue[venue.subdomain] || 0 }})
                </option>
            </select>
        </div>

        {{-- Price Filter --}}
        <div v-if="hasFreeEvents" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div @click="showFreeOnly = !showFreeOnly" class="flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.free') }}</span>
                <button role="switch" :aria-checked="showFreeOnly.toString()"
                        class="relative w-11 h-6 rounded-full transition-colors cursor-pointer flex-shrink-0"
                        :class="showFreeOnly ? 'bg-[#4E81FA]' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200"
                          :class="showFreeOnly ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
        </div>

        {{-- Online Filter --}}
        <div v-if="hasOnlineEvents" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <div @click="showOnlineOnly = !showOnlineOnly" class="flex items-center justify-between cursor-pointer">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.online') }}</span>
                <button role="switch" :aria-checked="showOnlineOnly.toString()"
                        class="relative w-11 h-6 rounded-full transition-colors cursor-pointer flex-shrink-0"
                        :class="showOnlineOnly ? 'bg-[#4E81FA]' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200"
                          :class="showOnlineOnly ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                </button>
            </div>
        </div>

        {{-- Done button --}}
        <div class="px-6 py-4">
                <x-brand-button @click="showFiltersDrawer = false" class="w-full">
                {{ __('messages.done') }}
            </x-brand-button>
        </div>
    </div>
</div>
</Teleport>

{{-- Desktop Filters Modal - Teleported to body to escape stacking context --}}
<Teleport to="body">
<div v-cloak v-if="dynamicFilterCount > 0 && showDesktopFiltersModal" class="hidden md:block fixed inset-0 z-[100]">
    {{-- Backdrop --}}
    <div @click="showDesktopFiltersModal = false"
         class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity z-[100]"></div>

    {{-- Modal panel --}}
    <div class="fixed inset-0 flex items-center justify-center p-4 z-[101] pointer-events-none">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md max-h-[80vh] overflow-y-auto pointer-events-auto {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
            {{-- Header --}}
            <div class="px-6 py-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.filters') }}</h3>
                <button v-if="activeFilterCount > 0"
                        @click="clearFilters"
                        class="text-sm text-[#4E81FA] hover:text-[#3d6fd9] font-medium">
                    {{ __('messages.clear_filters') }}
                </button>
            </div>

            {{-- Schedule Filter --}}
            @if(isset($role) && $role->groups && $role->groups->count() > 1)
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.schedule') }}</label>
                <select v-model="selectedGroup" style="font-family: sans-serif"
                        class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm {{ rtl_class($role ?? null, 'rtl', '', $isAdminRoute) }}">
                    <option value="">{{ __('messages.all_schedules') }} (@{{ eventCountByGroup[''] }})</option>
                    <option v-for="group in groups" :key="group.slug" :value="group.slug">
                        @{{ group.name }} (@{{ eventCountByGroup[group.slug] || 0 }})
                    </option>
                </select>
            </div>
            @endif

            {{-- Category Filter --}}
            <div v-if="availableCategories.length > 1" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.category') }}</label>
                <select v-model="selectedCategory" style="font-family: sans-serif"
                        class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="">{{ __('messages.all_categories') }} (@{{ eventCountByCategory[''] }})</option>
                    <option v-for="category in availableCategories" :key="category.id" :value="category.id">
                        @{{ category.name }} (@{{ eventCountByCategory[category.id] || 0 }})
                    </option>
                </select>
            </div>

            {{-- Venue Filter --}}
            <div v-if="uniqueVenues.length > 1" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.venue') }}</label>
                <select v-model="selectedVenue" style="font-family: sans-serif"
                        class="w-full py-2.5 px-3 border-gray-300 dark:border-gray-600 rounded-md shadow-sm
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm">
                    <option value="">{{ __('messages.all_venues') }} (@{{ eventCountByVenue[''] }})</option>
                    <option v-for="venue in uniqueVenues" :key="venue.subdomain" :value="venue.subdomain">
                        @{{ venue.name }} (@{{ eventCountByVenue[venue.subdomain] || 0 }})
                    </option>
                </select>
            </div>

            {{-- Price Filter --}}
            <div v-if="hasFreeEvents" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div @click="showFreeOnly = !showFreeOnly" class="flex items-center justify-between cursor-pointer">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.free') }}</span>
                    <button role="switch" :aria-checked="showFreeOnly.toString()"
                            class="relative w-11 h-6 rounded-full transition-colors cursor-pointer flex-shrink-0"
                            :class="showFreeOnly ? 'bg-[#4E81FA]' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200"
                              :class="showFreeOnly ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>

            {{-- Online Filter --}}
            <div v-if="hasOnlineEvents" class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div @click="showOnlineOnly = !showOnlineOnly" class="flex items-center justify-between cursor-pointer">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.online') }}</span>
                    <button role="switch" :aria-checked="showOnlineOnly.toString()"
                            class="relative w-11 h-6 rounded-full transition-colors cursor-pointer flex-shrink-0"
                            :class="showOnlineOnly ? 'bg-[#4E81FA]' : 'bg-gray-300 dark:bg-gray-600'">
                        <span class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200"
                              :class="showOnlineOnly ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>

            {{-- Done button --}}
            <div class="px-6 py-4">
                <x-brand-button @click="showDesktopFiltersModal = false" class="w-full">
                    {{ __('messages.done') }}
                </x-brand-button>
            </div>
        </div>
    </div>
</div>
</Teleport>

<!-- Event Popup Component -->
<div id="event-popup" class="event-popup">
    <div class="event-popup-content">
        <img id="event-popup-image" class="event-popup-image" style="display: none;" />
        <div class="event-popup-body">
            <h3 id="event-popup-title" class="event-popup-title"></h3>
            <div class="event-popup-details">
                <div id="event-popup-venue" class="event-popup-detail" style="display: none;">
                    <svg class="event-popup-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
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

<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
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
            use24Hour: {{ get_use_24_hour_time($role ?? null) ? 'true' : 'false' }},
            hidePastEvents: {{ (isset($hide_past_events) && $hide_past_events) ? 'true' : 'false' }},
            maxEvents: {{ isset($max_events) ? $max_events : 0 }},
            subdomain: '{{ isset($subdomain) ? $subdomain : '' }}',
            route: '{{ $route }}',
            tab: '{{ $tab ?? '' }}',
            embed: {{ isset($embed) && $embed ? 'true' : 'false' }},
            directRegistration: {{ isset($role) && $role->direct_registration ? 'true' : 'false' }},
            isRtl: {{ isset($role) && $role->isRtl() ? 'true' : 'false' }},
            languageCode: '{{ $isAdminRoute && auth()->check() ? app()->getLocale() : (session()->has('translate') ? 'en' : (isset($role) && $role->language_code ? $role->language_code : 'en')) }}',
            userTimezone: '{{ auth()->check() && auth()->user()->timezone ? auth()->user()->timezone : null }}',
            popupTimeout: null,
            showFiltersDrawer: false,
            showDesktopFiltersModal: false,
            showOnlineOnly: false,
            selectedVenue: '',
            showFreeOnly: false,
            currentView: '{{ $eventLayout ?? "calendar" }}',
            forceMobile: {{ (isset($force_mobile) && $force_mobile) ? 'true' : 'false' }},
            pastEvents: @json($pastEventsForVue ?? []),
            hasMorePastEvents: {{ isset($hasMorePastEvents) && $hasMorePastEvents ? 'true' : 'false' }},
            loadingPastEvents: false,
            showPastEvents: false,
            isAuthenticated: {{ auth()->check() ? 'true' : 'false' }},
            openVideoForm: {},
            playingVideo: null,
            openCommentForm: {},
            isLoadingEvents: {{ request()->graphic ? 'false' : 'true' }},
            uniqueCategoryIds: @json($uniqueCategoryIds ?? []),
            hasOnlineEvents: {{ ($hasOnlineEvents ?? false) ? 'true' : 'false' }}
        }
    },
    computed: {
        hasDesktopFilters() {
            return this.groups.length > 1 || this.availableCategories.length > 1 || this.hasOnlineEvents || this.uniqueVenues.length > 1 || this.hasFreeEvents;
        },
        dynamicFilterCount() {
            let count = 0;
            if (this.groups.length > 1) count++;
            if (this.availableCategories.length > 1) count++;
            if (this.hasOnlineEvents) count++;
            if (this.uniqueVenues.length > 1) count++;
            if (this.hasFreeEvents) count++;
            return count;
        },
        activeFilterCount() {
            let count = 0;
            if (this.selectedGroup) count++;
            if (this.selectedCategory) count++;
            if (this.showOnlineOnly) count++;
            if (this.selectedVenue) count++;
            if (this.showFreeOnly) count++;
            return count;
        },
        selectedGroupName() {
            if (!this.selectedGroup) return '';
            const group = this.groups.find(g => g.slug === this.selectedGroup);
            return group ? group.name : this.selectedGroup;
        },
        selectedCategoryName() {
            if (!this.selectedCategory) return '';
            const cat = this.availableCategories.find(c => c.id == this.selectedCategory);
            return cat ? cat.name : '';
        },
        eventCountByGroup() {
            // Filter by other active filters (except group)
            const baseEvents = this.allEvents.filter(e => {
                if (this.showOnlineOnly && !e.is_online) return false;
                if (this.selectedVenue && e.venue_subdomain !== this.selectedVenue) return false;
                if (this.showFreeOnly && !e.is_free) return false;
                return true;
            });

            const counts = { '': baseEvents.length };
            this.groups.forEach(g => {
                counts[g.slug] = baseEvents.filter(e => {
                    const selectedGroupObj = this.groups.find(grp => grp.slug === g.slug);
                    return selectedGroupObj && e.group_id === selectedGroupObj.id;
                }).length;
            });
            return counts;
        },
        eventCountByCategory() {
            // Filter by group, online status, venue, and price
            const filteredEvents = this.allEvents.filter(event => {
                if (this.selectedGroup) {
                    const selectedGroupObj = this.groups.find(group => group.slug === this.selectedGroup);
                    if (selectedGroupObj && event.group_id !== selectedGroupObj.id) {
                        return false;
                    }
                }
                if (this.showOnlineOnly && !event.is_online) {
                    return false;
                }
                if (this.selectedVenue && event.venue_subdomain !== this.selectedVenue) return false;
                if (this.showFreeOnly && !event.is_free) return false;
                return true;
            });
            const counts = { '': filteredEvents.length };
            this.availableCategories.forEach(c => {
                counts[c.id] = filteredEvents.filter(e => e.category_id == c.id).length;
            });
            return counts;
        },
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
                if (this.showOnlineOnly && !event.is_online) {
                    return false;
                }
                if (this.selectedVenue && event.venue_subdomain !== this.selectedVenue) {
                    return false;
                }
                if (this.showFreeOnly && !event.is_free) {
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
        uniqueVenues() {
            const venuesMap = new Map();
            this.allEvents.forEach(event => {
                if (event.venue_subdomain && event.venue_name) {
                    venuesMap.set(event.venue_subdomain, event.venue_name);
                }
            });
            return Array.from(venuesMap.entries())
                .map(([subdomain, name]) => ({ subdomain, name }))
                .sort((a, b) => a.name.localeCompare(b.name));
        },
        hasFreeEvents() {
            let hasFree = false, hasPaid = false;
            for (const event of this.allEvents) {
                if (event.is_free) hasFree = true;
                else hasPaid = true;
                if (hasFree && hasPaid) return true;
            }
            return false;
        },
        eventCountByVenue() {
            // Filter by group, category, online, price first
            const baseEvents = this.allEvents.filter(event => {
                if (this.selectedGroup) {
                    const selectedGroupObj = this.groups.find(g => g.slug === this.selectedGroup);
                    if (selectedGroupObj && event.group_id !== selectedGroupObj.id) return false;
                }
                if (this.selectedCategory && event.category_id != this.selectedCategory) return false;
                if (this.showOnlineOnly && !event.is_online) return false;
                if (this.showFreeOnly && !event.is_free) return false;
                return true;
            });

            const counts = { '': baseEvents.length };
            this.uniqueVenues.forEach(v => {
                counts[v.subdomain] = baseEvents.filter(e => e.venue_subdomain === v.subdomain).length;
            });
            return counts;
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

                    const occurrenceCount = this.countOccurrencesForFrequency(event, startDate, checkDate);

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
                        if (this.matchesFrequency(event, currentDate)) {
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
            }).slice(0, 50);
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
        },
        listViewUpcomingGroups() {
            // Filter eventsGroupedByDate to only non-past dates
            return this.eventsGroupedByDate.filter(group => !this.isPastEvent(group.date));
        },
        filteredPastEventsCount() {
            return this.flatPastEvents.length;
        },
        flatUpcomingEvents() {
            const events = [];
            this.eventsGroupedByDate.forEach(group => {
                if (!this.isPastEvent(group.date)) {
                    group.events.forEach(event => {
                        if (this.isEventVisible(event)) {
                            events.push(event);
                        }
                    });
                }
            });
            return events.slice(0, 50);
        },
        flatPastEvents() {
            // Start with server-provided past events (one-time)
            const events = this.pastEvents.filter(event => this.isEventVisible(event));

            // Also generate past occurrences from recurring events
            let today = new Date();
            if (this.userTimezone) {
                const userNow = new Date().toLocaleString("en-US", {timeZone: this.userTimezone});
                today = new Date(userNow);
            }
            today.setHours(0, 0, 0, 0);

            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            this.filteredEvents.forEach(event => {
                if (event.days_of_week && event.days_of_week.length > 0 && event.start_date) {
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    // Go back max 90 days from today, but not before the event's start_date
                    const ninetyDaysAgo = new Date(today);
                    ninetyDaysAgo.setDate(ninetyDaysAgo.getDate() - 90);
                    const rangeStart = new Date(Math.max(startDate.getTime(), ninetyDaysAgo.getTime()));

                    const currentDate = new Date(rangeStart);

                    while (currentDate <= yesterday) {
                        if (this.matchesFrequency(event, currentDate)) {
                            const dateStr = currentDate.getFullYear() + '-' +
                                String(currentDate.getMonth() + 1).padStart(2, '0') + '-' +
                                String(currentDate.getDate()).padStart(2, '0');

                            // Check recurring end conditions
                            if (this.shouldIncludePastDate(event, dateStr)) {
                                events.push({
                                    ...event,
                                    occurrenceDate: dateStr,
                                    uniqueKey: `${event.id}-past-${dateStr}`
                                });
                            }
                        }
                        currentDate.setDate(currentDate.getDate() + 1);
                    }
                }
            });

            // Sort reverse-chronologically, then by time within same date
            return events.sort((a, b) => {
                const dateComparison = (b.occurrenceDate || '').localeCompare(a.occurrenceDate || '');
                if (dateComparison !== 0) return dateComparison;
                if (a.local_starts_at && b.local_starts_at) {
                    return new Date(a.local_starts_at) - new Date(b.local_starts_at);
                }
                return 0;
            });
        },
        pastEventsGroupedByDate() {
            // Group past events by date, sorted reverse-chronologically
            const grouped = {};
            this.flatPastEvents.forEach(event => {
                if (!event.occurrenceDate) return;
                const date = event.occurrenceDate;
                if (!grouped[date]) {
                    grouped[date] = [];
                }
                grouped[date].push(event);
            });
            return Object.keys(grouped).sort().reverse().map(date => ({
                date: date,
                events: grouped[date]
            }));
        },
        allListEvents() {
            const upcoming = this.flatUpcomingEvents.map(e => ({...e, _isPast: false}));
            if (this.hidePastEvents) return upcoming;
            const past = this.flatPastEvents.map(e => ({...e, _isPast: true}));
            return upcoming.concat(past);
        },
        allMobileListGroups() {
            const groups = {};
            this.flatUpcomingEvents.forEach(event => {
                const date = event.occurrenceDate || 'no-date';
                if (!groups[date]) groups[date] = {date, events: [], hasPast: false};
                groups[date].events.push({...event, _isPast: false});
            });
            if (!this.hidePastEvents) {
                this.flatPastEvents.forEach(event => {
                    const date = event.occurrenceDate || 'no-date';
                    if (!groups[date]) groups[date] = {date, events: [], hasPast: true};
                    groups[date].events.push({...event, _isPast: true});
                    groups[date].hasPast = true;
                });
            }
            return Object.values(groups).sort((a, b) => {
                // upcoming dates first (ascending), then past dates (descending)
                const aIsPast = a.events.every(e => e._isPast);
                const bIsPast = b.events.every(e => e._isPast);
                if (aIsPast !== bIsPast) return aIsPast ? 1 : -1;
                if (aIsPast) return b.date.localeCompare(a.date);
                return a.date.localeCompare(b.date);
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
        },
        showFiltersDrawer(open) {
            document.body.style.overflow = open ? 'hidden' : '';
        },
        showDesktopFiltersModal(open) {
            document.body.style.overflow = open ? 'hidden' : '';
        },
    },
    methods: {
        matchesFrequency(event, date) {
            const frequency = event.recurring_frequency || 'weekly';
            const dayOfWeek = date.getDay();

            switch (frequency) {
                case 'daily':
                    return true;

                case 'weekly':
                    return event.days_of_week && event.days_of_week[dayOfWeek] === '1';

                case 'every_n_weeks': {
                    if (!event.days_of_week || event.days_of_week[dayOfWeek] !== '1') return false;
                    const interval = event.recurring_interval || 2;
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    // Get start of week (Sunday) for both dates
                    const startWeek = new Date(startDate);
                    startWeek.setDate(startWeek.getDate() - startWeek.getDay());
                    const dateWeek = new Date(date);
                    dateWeek.setDate(dateWeek.getDate() - dateWeek.getDay());
                    const daysDiff = Math.round((dateWeek - startWeek) / (1000 * 60 * 60 * 24));
                    const weeksDiff = Math.floor(daysDiff / 7);
                    return weeksDiff % interval === 0;
                }

                case 'monthly_date': {
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    return date.getDate() === startDate.getDate();
                }

                case 'monthly_weekday': {
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    const nthWeekday = Math.ceil(startDate.getDate() / 7);
                    const targetDayOfWeek = startDate.getDay();
                    const dateNthWeekday = Math.ceil(date.getDate() / 7);
                    return date.getDay() === targetDayOfWeek && dateNthWeekday === nthWeekday;
                }

                case 'yearly': {
                    const startDate = new Date(event.start_date + 'T00:00:00');
                    return date.getMonth() === startDate.getMonth() && date.getDate() === startDate.getDate();
                }

                default:
                    return event.days_of_week && event.days_of_week[dayOfWeek] === '1';
            }
        },
        countOccurrencesForFrequency(event, startDate, checkDate) {
            const frequency = event.recurring_frequency || 'weekly';

            switch (frequency) {
                case 'daily': {
                    const diffTime = checkDate.getTime() - startDate.getTime();
                    return Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
                }

                case 'monthly_date': {
                    let count = 0;
                    const current = new Date(startDate);
                    while (current <= checkDate) {
                        count++;
                        current.setMonth(current.getMonth() + 1);
                    }
                    return count;
                }

                case 'monthly_weekday': {
                    let count = 0;
                    const nthWeekday = Math.ceil(startDate.getDate() / 7);
                    const targetDayOfWeek = startDate.getDay();
                    const current = new Date(startDate);
                    current.setDate(1);
                    while (current <= checkDate) {
                        const targetMonth = current.getMonth();
                        let found = 0;
                        const candidate = new Date(current);
                        while (candidate.getMonth() === targetMonth) {
                            if (candidate.getDay() === targetDayOfWeek) {
                                found++;
                                if (found === nthWeekday) {
                                    if (candidate >= startDate && candidate <= checkDate) {
                                        count++;
                                    }
                                    break;
                                }
                            }
                            candidate.setDate(candidate.getDate() + 1);
                        }
                        // Move to next month
                        current.setMonth(current.getMonth() + 1);
                        current.setDate(1);
                    }
                    return count;
                }

                case 'yearly': {
                    let count = 0;
                    const current = new Date(startDate);
                    while (current <= checkDate) {
                        count++;
                        current.setFullYear(current.getFullYear() + 1);
                    }
                    return count;
                }

                case 'every_n_weeks': {
                    const interval = event.recurring_interval || 2;
                    let count = 0;
                    const current = new Date(startDate);
                    while (current <= checkDate) {
                        const startWeek = new Date(startDate);
                        startWeek.setDate(startWeek.getDate() - startWeek.getDay());
                        const currentWeek = new Date(current);
                        currentWeek.setDate(currentWeek.getDate() - currentWeek.getDay());
                        const daysDiff = Math.round((currentWeek - startWeek) / (1000 * 60 * 60 * 24));
                        const weeksDiff = Math.floor(daysDiff / 7);
                        if (weeksDiff % interval === 0 && event.days_of_week && event.days_of_week[current.getDay()] === '1') {
                            count++;
                        }
                        current.setDate(current.getDate() + 1);
                    }
                    return count;
                }

                case 'weekly':
                default: {
                    let count = 0;
                    const current = new Date(startDate);
                    while (current <= checkDate) {
                        if (event.days_of_week && event.days_of_week[current.getDay()] === '1') {
                            count++;
                        }
                        current.setDate(current.getDate() + 1);
                    }
                    return count;
                }
            }
        },
        getEventGroupColor(event) {
            if (!event.group_id) return null;
            const group = this.groups.find(g => g.id === event.group_id);
            return group && group.color ? group.color : null;
        },
        playVideo(key) {
            this.playingVideo = this.playingVideo === key ? null : key;
        },
        toggleView(view) {
            this.currentView = view;
            this.updatePanelWrapper(view);
            this.updateOuterContainers(view);
            if (this.subdomain) {
                try {
                    localStorage.setItem('es_view_' + this.subdomain, view);
                } catch (e) {
                    // localStorage not available
                }
            }
        },
        updatePanelWrapper(view) {
            const wrapper = document.getElementById('calendar-panel-wrapper');
            if (wrapper) {
                if (view === 'list') {
                    wrapper.classList.add('calendar-panel-border-transparent');
                    wrapper.classList.remove('calendar-panel-border');
                    wrapper.style.paddingLeft = '0';
                    wrapper.style.paddingRight = '0';
                    wrapper.style.paddingTop = '0';
                    wrapper.style.paddingBottom = '0';
                } else {
                    wrapper.classList.remove('calendar-panel-border-transparent');
                    wrapper.classList.add('calendar-panel-border');
                    wrapper.style.paddingLeft = '';
                    wrapper.style.paddingRight = '';
                    wrapper.style.paddingTop = '';
                    wrapper.style.paddingBottom = '';
                }
            }
        },
        updateOuterContainers(view, animate = true) {
            const maxWidth = view === 'list' ? '56rem' : '200rem';
            document.querySelectorAll('[data-view-width]').forEach(el => {
                const prevMaxWidth = el.style.maxWidth;
                el.style.maxWidth = maxWidth;
                if (animate) {
                    const anim = view === 'calendar' ? 'view-toggle-bounce-expand' : 'view-toggle-bounce-shrink';
                    const playBounce = () => {
                        el.style.animation = 'none';
                        el.offsetHeight;
                        el.style.animation = anim + ' 0.4s ease-out';
                        el.addEventListener('animationend', function handler() {
                            el.style.animation = '';
                            el.removeEventListener('animationend', handler);
                        });
                    };
                    if (view === 'calendar' || prevMaxWidth === maxWidth) {
                        playBounce();
                    } else {
                        el.addEventListener('transitionend', function handler(e) {
                            if (e.propertyName !== 'max-width') return;
                            el.removeEventListener('transitionend', handler);
                            playBounce();
                        });
                    }
                }
            });
            const listBtn = document.getElementById('toggle-list-btn');
            const calBtn = document.getElementById('toggle-calendar-btn');
            const accentColor = (listBtn && listBtn.dataset.accent) || (calBtn && calBtn.dataset.accent) || '{{ $accentColor ?? "#4E81FA" }}';
            const contrastColor = (listBtn && listBtn.dataset.contrast) || (calBtn && calBtn.dataset.contrast) || '{{ $contrastColor ?? "#FFFFFF" }}';
            if (listBtn) {
                listBtn.style.borderColor = accentColor;
                if (view === 'list') {
                    listBtn.style.backgroundColor = accentColor;
                    listBtn.style.color = contrastColor;
                    listBtn.className = listBtn.className.replace(/\btext-gray-900\b/g, '').replace(/\bdark:text-white\b/g, '').replace(/\bhover:bg-gray-50\b/g, '').replace(/\bdark:hover:bg-gray-700\b/g, '');
                } else {
                    listBtn.style.backgroundColor = '';
                    listBtn.style.color = '';
                    if (!listBtn.className.includes('text-gray-900')) {
                        listBtn.className += ' text-gray-900 dark:text-white';
                    }
                    if (!listBtn.className.includes('hover:bg-gray-50')) {
                        listBtn.className += ' hover:bg-gray-50 dark:hover:bg-gray-700';
                    }
                }
            }
            if (calBtn) {
                calBtn.style.borderColor = accentColor;
                if (view === 'calendar') {
                    calBtn.style.backgroundColor = accentColor;
                    calBtn.style.color = contrastColor;
                    calBtn.className = calBtn.className.replace(/\btext-gray-900\b/g, '').replace(/\bdark:text-white\b/g, '').replace(/\bhover:bg-gray-50\b/g, '').replace(/\bdark:hover:bg-gray-700\b/g, '');
                } else {
                    calBtn.style.backgroundColor = '';
                    calBtn.style.color = '';
                    if (!calBtn.className.includes('text-gray-900')) {
                        calBtn.className += ' text-gray-900 dark:text-white';
                    }
                    if (!calBtn.className.includes('hover:bg-gray-50')) {
                        calBtn.className += ' hover:bg-gray-50 dark:hover:bg-gray-700';
                    }
                }
            }
        },
        getHeaderImage(event) {
            if (event.venue_header_image) return event.venue_header_image;
            if (event.talent && event.talent.length > 0) {
                for (const t of event.talent) {
                    if (t.header_image) return t.header_image;
                }
            }
            return null;
        },
        formatDuration(hours) {
            if (!hours) return '';
            const totalMinutes = Math.round(hours * 60);
            const h = Math.floor(totalMinutes / 60);
            const m = totalMinutes % 60;
            if (h > 0 && m > 0) return h + 'h ' + m + 'm';
            if (h > 0) return h + 'h';
            return m + 'm';
        },
        clearFilters() {
            this.selectedGroup = '';
            this.selectedCategory = '';
            this.showOnlineOnly = false;
            this.selectedVenue = '';
            this.showFreeOnly = false;
        },
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
            if (this.showOnlineOnly && !event.is_online) {
                return false;
            }
            if (this.selectedVenue && event.venue_subdomain !== this.selectedVenue) {
                return false;
            }
            if (this.showFreeOnly && !event.is_free) {
                return false;
            }
            return true;
        },
        getEventUrl(event, occurrenceDate = null) {
            let url = event.guest_url;  // Already has /{subdomain}/{slug}/{id}
            let queryParams = [];

            // Check if this is a recurring event
            const isRecurring = event.days_of_week && event.days_of_week.length > 0;

            // Add date to path only for recurring events
            if (isRecurring) {
                // For recurring events, prioritize the occurrence date over the original start date
                const dateStr = occurrenceDate || event.occurrenceDate || event.utc_date;
                if (dateStr) {
                    // Parse the date string as UTC to ensure it's always UTC
                    const [year, month, day] = dateStr.split('-').map(Number);
                    const utcDate = new Date(Date.UTC(year, month - 1, day));
                    url += '/' + utcDate.toISOString().split('T')[0];
                }
            }

            // Keep filters as query params (these don't affect social sharing)
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
        toggleVideoForm(event, $event) {
            const key = event.uniqueKey;
            const btn = $event?.target?.closest('button');
            this.openVideoForm = { ...this.openVideoForm, [key]: !this.openVideoForm[key] };
            if (this.openVideoForm[key]) {
                this.openCommentForm = { ...this.openCommentForm, [key]: false };
                this.$nextTick(() => {
                    if (!btn) return;
                    const form = btn.closest('div').parentElement.querySelector('form input[name="youtube_url"]');
                    if (form) form.focus();
                });
            }
        },
        toggleCommentForm(event, $event) {
            const key = event.uniqueKey;
            const btn = $event?.target?.closest('button');
            this.openCommentForm = { ...this.openCommentForm, [key]: !this.openCommentForm[key] };
            if (this.openCommentForm[key]) {
                this.openVideoForm = { ...this.openVideoForm, [key]: false };
                this.$nextTick(() => {
                    if (!btn) return;
                    const form = btn.closest('div').parentElement.querySelector('form textarea[name="comment"]');
                    if (form) form.focus();
                });
            }
        },
        navigateToEvent(event, e) {
            // Don't navigate if clicking on the edit link or a form/button
            if (!e?.target || e.target.closest('a') || e.target.closest('form') || e.target.closest('button')) return;

            // Check if direct registration is enabled AND event has registration URL
            if (this.directRegistration && event.registration_url) {
                window.open(event.registration_url, '_blank');
                return;
            }

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
        shouldIncludePastDate(event, dateStr) {
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

                const occurrenceCount = this.countOccurrencesForFrequency(event, startDate, checkDate);

                return occurrenceCount <= maxOccurrences;
            }

            return true;
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
        formatDateShort(dateStr) {
            if (!dateStr) return '';
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day);
            return eventDate.toLocaleDateString(this.languageCode, {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        },
        formatDayName(dateStr) {
            if (!dateStr) return '';
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day);
            return eventDate.toLocaleDateString(this.languageCode, {
                weekday: 'long'
            });
        },
        getMonthAbbr(dateStr) {
            if (!dateStr) return '';
            const [year, month, day] = dateStr.split('-').map(Number);
            const eventDate = new Date(year, month - 1, day);
            return eventDate.toLocaleDateString(this.languageCode, { month: 'short' }).toUpperCase();
        },
        getDayNum(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            return parseInt(parts[2], 10);
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
                    el.addEventListener('mouseenter', () => {
                        const eventId = el.getAttribute('data-event-id');
                        const event = this.allEvents.find(ev => ev.id === eventId);
                        if (event) {
                            this.showEventPopup(event, el);
                        }
                    });
                    el.addEventListener('mouseleave', () => {
                        this.hideEventPopup();
                    });
                });
            });
        },
        showEventPopup(event, element) {
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

            // Move popup to body to avoid backdrop-filter containing block issues
            if (popup.parentElement !== document.body) {
                document.body.appendChild(popup);
            }

            // Show popup
            this.popupElement = element;
            popup.classList.add('show');
            this.updatePopupPosition();
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
        updatePopupPosition() {
            const popup = document.getElementById('event-popup');
            if (!popup || !popup.classList.contains('show') || !this.popupElement) return;

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

            const content = popup.querySelector('.event-popup-content') || popup;
            const rect = this.popupElement.getBoundingClientRect();

            // 2. Cache dimensions
            const pW = content.offsetWidth || 320;
            const pH = content.offsetHeight || 200;
            const vW = window.innerWidth;
            const vH = window.innerHeight;
            const gap = 8;

            // 3. Position below the element, left-aligned
            let left = rect.left;
            let top = rect.bottom + gap;

            // 4. Flip above if not enough space below
            if (top + pH > vH - 10) {
                top = rect.top - pH - gap;
            }

            // 5. Flip left if not enough space on the right
            if (left + pW > vW - 10) {
                left = rect.right - pW;
            }

            // 6. Hard Constraints (Keep inside viewport)
            left = Math.max(10, Math.min(left, vW - pW - 10));
            top = Math.max(10, Math.min(top, vH - pH - 10));

            // 7. Apply via Transform (GPU Composite instead of Layout/Reflow)
            popup.style.transform = `translate3d(${Math.round(left)}px, ${Math.round(top)}px, 0)`;
        },
        getTalentHeaderImages(event) {
            if (!event.talent) return [];
            return event.talent.filter(t => t.header_image).map(t => ({ name: t.name, image: t.header_image }));
        },
        async loadMorePastEvents() {
            if (this.loadingPastEvents || !this.hasMorePastEvents) return;
            this.loadingPastEvents = true;
            try {
                const oldestEvent = this.pastEvents[this.pastEvents.length - 1];
                if (!oldestEvent || !oldestEvent.starts_at) return;
                const baseUrl = '{{ isset($subdomain) ? route("role.list_past_events", ["subdomain" => $subdomain]) : "" }}';
                const url = baseUrl + '?before=' + encodeURIComponent(oldestEvent.starts_at);
                const response = await fetch(url);
                const data = await response.json();
                if (data.events && data.events.length > 0) {
                    this.pastEvents = this.pastEvents.concat(data.events);
                }
                this.hasMorePastEvents = data.has_more;
            } catch (e) {
                console.error('Failed to load more past events:', e);
            } finally {
                this.loadingPastEvents = false;
            }
        },
        async fetchCalendarEvents() {
            if (this.tab === 'availability') {
                this.isLoadingEvents = false;
                return;
            }
            this.isLoadingEvents = true;
            try {
                let url;
                if (this.route === 'home') {
                    url = '{{ route("home.calendar_events") }}';
                } else if (this.route === 'admin') {
                    url = '{{ isset($subdomain) ? route("role.admin_calendar_events", ["subdomain" => $subdomain]) : "" }}';
                } else {
                    url = '{{ isset($subdomain) ? route("role.calendar_events", ["subdomain" => $subdomain]) : "" }}';
                }
                const separator = url.includes('?') ? '&' : '?';
                url += separator + 'month={{ $month }}&year={{ $year }}';

                const response = await fetch(url);
                const data = await response.json();

                this.allEvents = data.events;
                this.eventsMap = data.eventsMap;
                this.pastEvents = data.pastEvents || [];
                this.hasMorePastEvents = data.hasMorePastEvents || false;
                this.uniqueCategoryIds = data.filterMeta.uniqueCategoryIds;
                this.hasOnlineEvents = data.filterMeta.hasOnlineEvents;
            } catch (e) {
                console.error('Failed to load calendar events:', e);
            } finally {
                this.isLoadingEvents = false;
                this.$nextTick(() => {
                    this.initPopups();
                    // Re-trigger past events button logic
                    const showPastEventsBtn = document.getElementById('showPastEventsBtn');
                    const pastEventEls = document.querySelectorAll('.past-event');
                    if (pastEventEls.length > 0 && showPastEventsBtn) {
                        showPastEventsBtn.classList.remove('hidden');
                        showPastEventsBtn.addEventListener('click', function() {
                            pastEventEls.forEach(event => {
                                event.classList.remove('hidden');
                            });
                            showPastEventsBtn.classList.add('hidden');
                        });
                    }
                });
            }
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
        // Check localStorage for saved view preference
        if (this.subdomain && this.route !== 'admin' && !this.forceMobile) {
            try {
                const saved = localStorage.getItem('es_view_' + this.subdomain);
                if (saved && ['calendar', 'list'].includes(saved)) {
                    this.currentView = saved;
                    this.updateOuterContainers(saved, false);
                }
            } catch (e) {
                // localStorage not available
            }
        }

        // Update panel wrapper for initial view
        this.updatePanelWrapper(this.currentView);

        if (this.isLoadingEvents) {
            // Ajax mode: fetch events, then init popups after data loads
            this.fetchCalendarEvents();
        } else {
            // Graphic mode: data already server-rendered, init popups immediately
            this.initPopups();

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

        // Reinitialize popups when events change
        this.$watch('filteredEvents', () => {
            this.initPopups();
        });

    }
});

const calendarAppInstance = calendarApp.mount('#calendar-app');
window.calendarVueApp = calendarAppInstance;

// Update hero filters button visibility and badge (for CP/guest view)
function updateHeroFiltersButton() {
    const btn = document.getElementById('hero-filters-btn');
    const badge = document.getElementById('hero-filters-badge');
    const btnMobile = document.getElementById('hero-filters-btn-mobile');
    const badgeMobile = document.getElementById('hero-filters-badge-mobile');
    if (window.calendarVueApp) {
        const showBtn = window.calendarVueApp.dynamicFilterCount > 0;
        const count = window.calendarVueApp.activeFilterCount;

        // Desktop hero button
        if (btn) {
            if (showBtn) {
                btn.classList.add('md:flex');
                btn.style.display = '';
            } else {
                btn.classList.remove('md:flex');
                btn.style.display = 'none';
            }
        }
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        // Mobile hero button
        if (btnMobile) {
            if (showBtn) {
                btnMobile.style.display = ''; // Let CSS classes control display (md:hidden)
            } else {
                btnMobile.style.display = 'none';
            }
        }
        if (badgeMobile) {
            if (count > 0) {
                badgeMobile.textContent = count;
                badgeMobile.classList.remove('hidden');
                badgeMobile.classList.add('inline-flex');
            } else {
                badgeMobile.classList.add('hidden');
                badgeMobile.classList.remove('inline-flex');
            }
        }
    }
}

// Initial update and watch for changes
updateHeroFiltersButton();
calendarAppInstance.$watch('dynamicFilterCount', updateHeroFiltersButton);
calendarAppInstance.$watch('activeFilterCount', updateHeroFiltersButton);
</script>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.calendar-day-navigate').forEach(function(el) {
        el.addEventListener('click', function() {
            window.location = this.getAttribute('data-href');
        });
    });
});
</script>
</div>
