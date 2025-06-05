<div class="flex h-full flex-col pt-1">
    <header class="py-4">
        <div class="w-full">
            <div class="md:flex md:flex-row md:items-center w-full mb-4">
                <div class="flex flex-row justify-between items-center w-full md:w-auto md:flex-1 md:justify-start">
                    <h1 class="text-lg font-semibold leading-6 min-w-[120px]">
                        <time datetime="{{ sprintf('%04d-%02d', $year, $month) }}">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</time>
                    </h1>
                    <div class="flex flex-row items-center bg-white rounded-md shadow-sm md:hidden">
                        <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) }}"
                            class="flex h-9 w-12 items-center justify-center rounded-l-md border-y border-l border-gray-300 pr-1 text-gray-400 hover:text-gray-500 focus:relative"
                            rel="nofollow">
                            <span class="sr-only">{{ __('messages.previous_month') }}</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="{{ $route == 'home' ? route('home') : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => now()->year, 'month' => now()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => now()->year, 'month' => now()->month]) }}"
                            class="flex items-center justify-center border-y border-gray-300 px-3.5 text-sm font-semibold text-gray-900 hover:bg-gray-50 focus:relative">
                            <div class="mt-2">{{ __('messages.today') }}</div>
                        </a>
                        <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) }}"
                            class="flex h-9 w-12 items-center justify-center rounded-r-md border-y border-r border-gray-300 pl-1 text-gray-400 hover:text-gray-500 focus:relative"
                            rel="nofollow">
                            <span class="sr-only">{{ __('messages.next_month') }}</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="hidden md:flex md:flex-row md:items-center md:justify-end md:w-auto md:gap-0">
                    @if(isset($role) && $role->groups && $role->groups->count())
                        <select name="group" id="group" class="border-gray-300 rounded-md shadow-sm h-9 md:w-auto md:ml-4" onchange="this.form.submit()">
                            <option value="">{{ __('messages.all_groups') }}</option>
                            @foreach($role->groups as $group)
                                <option value="{{ $group->id }}" {{ request('group') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select name="category" id="category" class="border-gray-300 rounded-md shadow-sm h-9 md:w-auto md:ml-4" onchange="this.form.submit()">
                        <option value="">{{ __('messages.all_categories') }}</option>
                        @foreach(config('app.event_categories', []) as $catKey => $catName)
                            <option value="{{ $catKey }}" {{ request('category') == $catKey ? 'selected' : '' }}>{{ $catName }}</option>
                        @endforeach
                    </select>
                    <div class="flex flex-row items-center bg-white rounded-md shadow-sm md:ml-4">
                        <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) }}"
                            class="flex h-9 w-12 items-center justify-center rounded-l-md border-y border-l border-gray-300 pr-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pr-0 md:hover:bg-gray-50"
                            rel="nofollow">
                            <span class="sr-only">{{ __('messages.previous_month') }}</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="{{ $route == 'home' ? route('home') : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => now()->year, 'month' => now()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => now()->year, 'month' => now()->month]) }}"
                            class="flex items-center justify-center border-y border-gray-300 px-3.5 text-sm font-semibold text-gray-900 hover:bg-gray-50 focus:relative md:block">
                            <div class="mt-2">{{ __('messages.today') }}</div>
                        </a>
                        <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month, 'embed' => isset($embed) && $embed] : ['subdomain' => $role->subdomain, 'tab' => $tab, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) }}"
                            class="flex h-9 w-12 items-center justify-center rounded-r-md border-y border-r border-gray-300 pl-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pl-0 md:hover:bg-gray-50"
                            rel="nofollow">
                            <span class="sr-only">{{ __('messages.next_month') }}</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="md:hidden flex flex-col gap-4 w-full">
                @if(isset($role) && $role->groups && $role->groups->count())
                    <select name="group" id="group" class="border-gray-300 rounded-md shadow-sm h-9 w-full mt-4" onchange="this.form.submit()">
                        <option value="">{{ __('messages.all_groups') }}</option>
                        @foreach($role->groups as $group)
                            <option value="{{ $group->id }}" {{ request('group') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                @endif
                <select name="category" id="category" class="border-gray-300 rounded-md shadow-sm h-9 w-full mt-4" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_categories') }}</option>
                    @foreach(config('app.event_categories', []) as $catKey => $catName)
                        <option value="{{ $catKey }}" {{ request('category') == $catKey ? 'selected' : '' }}>{{ $catName }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </header>
    <div class="{{ $tab == 'availability' ? '' : 'hidden' }} shadow-sm ring-1 ring-black ring-opacity-5 md:flex md:flex-auto md:flex-col {{ isset($role) && $role->isRtl() && ! session()->has('translate') ? 'rtl' : '' }}">
        <div class="{{ $tab == 'availability' ? 'hidden md:block' : '' }}"> 
            <div
                class="grid grid-cols-7 gap-px border-b border-gray-300 bg-gray-200 text-center text-xs font-semibold leading-6 text-gray-700 md:flex-none">
                @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
                <div class="flex justify-center bg-white py-2">
                    {{ __('messages.' . $day) }}
                </div>
                @endforeach
            </div>
        </div>
        <div class="flex bg-gray-200 text-xs leading-6 text-gray-700 md:flex-auto">
            @php

            $startOfMonth = Carbon\Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek(Carbon\Carbon::SUNDAY);
            $endOfMonth = Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek(Carbon\Carbon::SATURDAY);
            $currentDate = $startOfMonth->copy();
            $totalDays = $endOfMonth->diffInDays($startOfMonth) + 1;
            $totalWeeks = ceil($totalDays / 7);
            $unavailable = [];

            // Create events map
            $eventsMap = [];
            foreach ($events as $event) {
                $checkDate = $startOfMonth->copy();
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

            // Sort events by start time otherwise recurring events can be out of order
            foreach ($eventsMap as $date => $events) {
                usort($events, function($a, $b) {
                    return strtotime(explode(' ', $a->starts_at)[1]) - strtotime(explode(' ', $b->starts_at)[1]);
                });
                $eventsMap[$date] = $events;
            }

            @endphp
            <div class="w-full md:grid md:grid-cols-7 md:grid-rows-{{ $totalWeeks }} md:gap-px">
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
                            @foreach ($eventsMap[$currentDate->format('Y-m-d')] ?? [] as $each)
                            @php
                            $canEdit = auth()->user() && auth()->user()->canEditEvent($each);
                            $curator = isset($subdomain) ? $each->curatorBySubdomain($subdomain) : null;
                            $translationId = null;
                            if ($curator && $curator->pivot->name_translated && ! session()->has('translate')) {
                                $eventName = $curator->pivot->name_translated;
                                $translationId = $curator->pivot->id;
                            } else {
                                $eventName = $each->translatedName();
                            }                       
                            @endphp

                            <li class="relative group {{ $canEdit ? ((isset($role) && $role->isRtl()) ? 'hover:pl-8 hover:break-all' : 'hover:pr-8 hover:break-all') : '' }} break-words">
                                <a href="{{ $each->getGuestUrl(isset($subdomain) ? $subdomain : '', $currentDate->format('Y-m-d')) }}{{ $translationId ? '&tid=' . \App\Utils\UrlUtils::encodeId($translationId) : '' }}"
                                    class="flex has-tooltip" data-tooltip="<b>{{ $eventName }}</b><br/>{{ $each->getVenueDisplayName() }} • {{ Carbon\Carbon::parse($each->localStartsAt())->format(isset($role) && $role->use_24_hour_time ? 'H:i' : 'g:i A') }}"
                                    onclick="event.stopPropagation();" {{ ($route != 'guest' || (isset($embed) && $embed)) ? "target='_blank'" : '' }}>
                                    <p class="flex-auto font-medium group-hover:text-[#4E81FA] text-gray-900 {{ (isset($role) && $role->isRtl()) ? 'rtl' : '' }}">
                                        <span class="{{ count($eventsMap[$currentDate->format('Y-m-d')]) == 1 ? 'line-clamp-2' : 'line-clamp-1' }} hover:underline">
                                        @if (isset($subdomain) && $each->isRoleAMember($subdomain))
                                            {{ $each->getVenueDisplayName() }}
                                        @else
                                            @if ($curator && $curator->pivot->name_translated && ! session()->has('translate'))
                                                {{ $curator->pivot->name_translated }}
                                            @else
                                                {{ $eventName }}
                                            @endif
                                        @endif
                                        </span>
                                        @if (count($eventsMap[$currentDate->format('Y-m-d')]) == 1)
                                            <span class="text-gray-400">
                                                {{ Carbon\Carbon::parse($each->localStartsAt())->format(isset($role) && $role->use_24_hour_time ? 'H:i' : 'g:i A') }}
                                            </span>
                                        @endif
                                    </p>
                                </a>
                                @if ($canEdit)
                                <a href="{{ isset($role) ? config('app.url') . route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($each->id)], false) : config('app.url') . route('event.edit_admin', ['hash' => App\Utils\UrlUtils::encodeId($each->id)], false) }}"
                                    class="absolute {{ (isset($role) && $role->isRtl()) ? 'left-0' : 'right-0' }} top-0 hidden group-hover:inline-block text-[#4E81FA] hover:text-[#4E81FA] hover:underline"
                                    onclick="event.stopPropagation();">
                                    {{ __('messages.edit') }}
                                </a>
                                @endif
                            </li>
                            @endforeach
                        </ol>
                    </div>
                    @php $currentDate->addDay(); @endphp
                    @endwhile
                </div>
            </div>
        </div>
        <div class="py-10 sm:px-6 md:hidden">
            @php
            $startOfMonth = Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $endOfMonth = Carbon\Carbon::create($year, $month, 1)->endOfMonth();
            $currentDate = $startOfMonth->copy();
            $hasPastEvents = false;
            $today = now()->startOfDay();
            @endphp

            @if (count($events))
            <div class="mb-4 text-center">
                <button id="showPastEventsBtn" class="text-[#4E81FA] font-medium hidden">
                    {{ __('messages.show_past_events') }}
                </button>
            </div>
            <ol id="mobileEventsList"
                class="divide-y divide-gray-100 overflow-hidden rounded-lg bg-white text-sm shadow ring-1 ring-black ring-opacity-5">
                @while ($currentDate->lte($endOfMonth))
                @foreach ($eventsMap[$currentDate->format('Y-m-d')] ?? [] as $each)

                @php
                $canEdit = auth()->user() && auth()->user()->canEditEvent($each);
                $curator = isset($subdomain) ? $each->curatorBySubdomain($subdomain) : null;
                $translationId = null;
                if ($curator && $curator->pivot->name_translated && ! session()->has('translate')) {
                    $eventName = $curator->pivot->name_translated;
                    $translationId = $curator->pivot->id;
                } else {
                    $eventName = $each->translatedName();
                }
                $isPastEvent = $currentDate->copy()->endOfDay()->lt($today);
                if ($isPastEvent) {
                    $hasPastEvents = true;
                }
                @endphp                

                <a href="{{ $each->getGuestUrl(isset($subdomain) ? $subdomain : '', $currentDate->format('Y-m-d')) }}{{ $translationId ? '&tid=' . \App\Utils\UrlUtils::encodeId($translationId) : '' }}" 
                   {{ ((isset($embed) && $embed) || $route == 'admin') ? 'target="blank"' : '' }}>
                    <li class="relative flex items-center space-x-6 py-6 px-4 xl:static event-item {{ $isPastEvent ? 'past-event hidden' : '' }}">
                        <div class="flex-auto">
                            <h3 class="pr-16 font-semibold text-gray-900">
                                {{ $eventName }}
                            </h3>
                            <dl class="pr-16 mt-2 flex flex-col text-gray-500 xl:flex-row">
                                <div class="flex items-start space-x-3">
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
                                        <time datetime="{{ $currentDate->format('Y-m-d') }}">
                                            {{ $currentDate->format('M jS') }} • {{ Carbon\Carbon::parse($each->localStartsAt())->format(isset($role) && $role->use_24_hour_time ? 'H:i' : 'g:i A') }}
                                        </time>
                                    </dd>
                                </div>
                                <div
                                    class="mt-2 flex items-start space-x-3 xl:ml-3.5 xl:mt-0 xl:border-l xl:border-gray-400 xl:border-opacity-50 xl:pl-3.5">
                                    <dt class="mt-0.5">
                                        <span class="sr-only">Location</span>
                                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                                            aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </dt>
                                    <dd>
                                        {{ $each->getVenueDisplayName() }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        <div class="absolute right-4 text-right top-6">
                            @if ($each->getImageUrl())
                            <img src="{{ $each->getImageUrl() }}" class="h-16 w-16 flex-none rounded-lg object-cover mb-2">
                            @endif
                            @if ($canEdit)
                            <a href="{{ isset($role) ? config('app.url') . route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($each->id)], false) : config('app.url') . route('event.edit_admin', ['hash' => App\Utils\UrlUtils::encodeId($each->id)], false) }}"
                                class="text-[#4E81FA] hover:text-[#4E81FA] hover:underline"
                                onclick="event.stopPropagation();">
                                {{ __('messages.edit') }}
                            </a>
                            @endif
                        </div>
                    </li>
                </a>
                @endforeach
                @php $currentDate->addDay(); @endphp
                @endwhile
            </ol>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const showPastEventsBtn = document.getElementById('showPastEventsBtn');
                    const pastEvents = document.querySelectorAll('.past-event');
                    
                    if (pastEvents.length > 0) {
                        showPastEventsBtn.classList.remove('hidden');
                        
                        showPastEventsBtn.addEventListener('click', function() {
                            pastEvents.forEach(event => {
                                event.classList.remove('hidden');
                            });
                            showPastEventsBtn.classList.add('hidden');
                        });
                    }
                });
            </script>
            @elseif ($tab != 'availability')
            <div class="p-10 max-w-5xl mx-auto px-4">
                <div class="flex justify-center items-center pb-6 w-full">
                    <div class="text-2xl text-center">
                        {{ __('messages.no_scheduled_events') }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

<div id="tooltip" class="tooltip"></div>