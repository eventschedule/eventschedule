<div class="flex h-full flex-col pt-1">
    <header class="flex items-center justify-between py-4 md:flex-none">
        <h1 class="text-lg font-semibold leading-6">
            <time
                datetime="{{ sprintf('%04d-%02d', $year, $month) }}">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</time>
        </h1>
        <div class="flex items-center justify-center">
            <div class="relative flex items-center rounded-md bg-white shadow-sm md:items-stretch">
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
            @if ($route == 'admin' && $role->email_verified_at)
            <div class="mr-3"></div>
            @if ($tab == 'schedule')
            <span class="hidden sm:block">
                <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain, 'embed' => 'true']) }}" target="_blank">
                    <button type="button"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 mr-3">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12.89,3L14.85,3.4L11.11,21L9.15,20.6L12.89,3M19.59,12L16,8.41V5.58L22.42,12L16,18.41V15.58L19.59,12M1.58,12L8,5.58V8.41L4.41,12L8,15.58V18.41L1.58,12Z" />
                        </svg>
                        {{ __('messages.embed') }}
                    </button>
                </a>
            </span>
            <a href="{{ route('event.show_import', ['subdomain' => $role->subdomain]) }}">
                <button type="button"
                    class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 mr-3">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,12L10,8V11H2V13H10V16M20,18V6C20,4.89 19.1,4 18,4H6A2,2 0 0,0 4,6V9H6V6H18V18H6V15H4V18A2,2 0 0,0 6,20H18A2,2 0 0,0 20,18Z" />
                    </svg>
                    {{ __('messages.import') }}
                </button>
            </a>
            <a href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}">
                <button type="button"
                    class="inline-flex items-center rounded-md shadow-sm bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3A6BE0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    {{ __('messages.add_event') }}
                </button>
            </a>
            @elseif ($tab == 'availability')
            <button type="button" id="saveButton" disabled
                class="inline-flex items-center rounded-md shadow-sm bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3A6BE0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M6.5 20Q4.22 20 2.61 18.43 1 16.85 1 14.58 1 12.63 2.17 11.1 3.35 9.57 5.25 9.15 5.88 6.85 7.75 5.43 9.63 4 12 4 14.93 4 16.96 6.04 19 8.07 19 11 20.73 11.2 21.86 12.5 23 13.78 23 15.5 23 17.38 21.69 18.69 20.38 20 18.5 20H13Q12.18 20 11.59 19.41 11 18.83 11 18V12.85L9.4 14.4L8 13L12 9L16 13L14.6 14.4L13 12.85V18H18.5Q19.55 18 20.27 17.27 21 16.55 21 15.5 21 14.45 20.27 13.73 19.55 13 18.5 13H17V11Q17 8.93 15.54 7.46 14.08 6 12 6 9.93 6 8.46 7.46 7 8.93 7 11H6.5Q5.05 11 4.03 12.03 3 13.05 3 14.5 3 15.95 4.03 17 5.05 18 6.5 18H9V20M12 13Z" />
                </svg>
                {{ __('messages.save') }}
            </button>
            @endif
            @elseif ($route == 'home' && \App\Utils\HostedUtils::isHostedOrAdmin())
            <div style="font-family: sans-serif" class="ml-3 shadow-sm relative inline-block text-left">
                <button type="button" 
                    onclick="onPopUpClick('calendar-pop-up-menu', event)"
                    class="inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" id="menu-button" aria-expanded="true" aria-haspopup="true">
                    {{ __('messages.new_schedule') }}
                    <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div id="calendar-pop-up-menu" class="pop-up-menu hidden absolute right-0 z-10 mt-2 w-64 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                    <div class="py-1" role="none" onclick="onPopUpClick('calendar-pop-up-menu', event)">
                        <a href="{{ route('new', ['type' => 'schedule']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-1">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                            </svg>                        
                            <div>
                                {{ __('messages.schedule') }}
                                <div class="text-xs text-gray-500">{{ __('messages.new_schedule_tooltip') }}</div>
                            </div>
                        </a>
                        <a href="{{ route('new', ['type' => 'venue']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-0">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
                            </svg>
                            <div>
                                {{ __('messages.venue') }}
                                <div class="text-xs text-gray-500">{{ __('messages.new_venue_tooltip') }}</div>
                            </div>
                        </a>
                        @if (config('app.hosted'))
                        <a href="{{ route('new', ['type' => 'curator']) }}" class="group flex items-center px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-1">
                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z" />
                            </svg>
                            <div>
                                {{ __('messages.curator') }}
                                <div class="text-xs text-gray-500">{{ __('messages.new_curator_tooltip') }}</div>
                            </div>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </header>
    <div class="{{ $tab == 'availability' ? '' : 'hidden' }} shadow-sm ring-1 ring-black ring-opacity-5 md:flex md:flex-auto md:flex-col">
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
                            @endphp
                            <li class="relative group {{ $canEdit ? 'hover:pr-8' : '' }} hover:break-all break-words">
                                <a href="{{ $each->getGuestUrl(isset($subdomain) ? $subdomain : '', $currentDate->format('Y-m-d')) }}"
                                    class="flex has-tooltip" data-tooltip="<b>{{ $each->name }}</b><br/>{{ $each->getVenueDisplayName() }} • {{ Carbon\Carbon::parse($each->localStartsAt())->format(isset($role) && $role->use_24_hour_time ? 'H:i' : 'g:i A') }}"
                                    onclick="event.stopPropagation();" {{ ($route != 'guest' || (isset($embed) && $embed)) ? "target='_blank'" : '' }}>
                                    <p class="flex-auto font-medium group-hover:text-[#4E81FA] text-gray-900">
                                        <span class="{{ count($eventsMap[$currentDate->format('Y-m-d')]) == 1 ? 'line-clamp-2' : 'line-clamp-1' }} hover:underline">
                                        @if (isset($subdomain) && $each->isRoleAMember($subdomain))
                                            {{ $each->getVenueDisplayName() }}
                                        @else
                                            {{ $each->name }}
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
                                    class="absolute right-0 top-0 hidden group-hover:inline-block text-[#4E81FA] hover:text-[#4E81FA] hover:underline"
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
            @endphp

            @if (count($events))
            <ol
                class="divide-y divide-gray-100 overflow-hidden rounded-lg bg-white text-sm shadow ring-1 ring-black ring-opacity-5">
                @while ($currentDate->lte($endOfMonth))
                @foreach ($eventsMap[$currentDate->format('Y-m-d')] ?? [] as $each)
                <a href="{{ $each->getGuestUrl(isset($subdomain) ? $subdomain : '', $currentDate->format('Y-m-d')) }}" 
                   {{ ((isset($embed) && $embed) || $route == 'admin') ? 'target="blank"' : '' }}>
                    <li class="relative flex items-center space-x-6 py-6 px-4 xl:static">
                        <div class="flex-auto">
                            <h3 class="pr-10 font-semibold text-gray-900 xl:pr-0">
                                {{ $each->name }}
                            </h3>
                            <dl class="mt-2 flex flex-col text-gray-500 xl:flex-row">
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
                                            {{ $currentDate->format($each->getUse24HourTime() ? 'M jS, Y • H:i' : 'M jS, Y • g:i A') }}
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
                        @if (auth()->user() && auth()->user()->canEditEvent($each))                        
                        <div class="absolute right-4 text-right">
                            @if ($each->getImageUrl())
                            <img src="{{ $each->getImageUrl() }}" class="h-14 w-14 mb-4 flex-none rounded-lg object-cover">
                            @endif
                            <a href="{{ isset($role) ? config('app.url') . route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($each->id)], false) : config('app.url') . route('event.edit_admin', ['hash' => App\Utils\UrlUtils::encodeId($each->id)], false) }}"
                                class="text-[#4E81FA] hover:text-[#4E81FA] hover:underline"
                                onclick="event.stopPropagation();">
                                {{ __('messages.edit') }}
                            </a>
                        </div>
                        @endif
                    </li>
                </a>
                @endforeach
                @php $currentDate->addDay(); @endphp
                @endwhile
            </ol>
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