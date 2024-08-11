<div class="lg:flex lg:h-full lg:flex-col pt-1">
    <header class="flex items-center justify-between pl-6 py-4 lg:flex-none">
        <h1 class="text-base font-semibold leading-6">
            <time
                datetime="{{ sprintf('%04d-%02d', $year, $month) }}">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</time>
        </h1>
        <div class="flex items-center justify-center">
            <div class="relative flex items-center rounded-md bg-white shadow-md md:items-stretch">
                <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month] : ['subdomain' => $role->subdomain, 'tab' => 'schedule', 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) }}"
                    class="flex h-9 w-12 items-center justify-center rounded-l-md border-y border-l border-gray-300 pr-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pr-0 md:hover:bg-gray-50">
                    <span class="sr-only">{{ __('messages.previous_month') }}</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="{{ $route == 'home' ? route('home') : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => now()->year, 'month' => now()->month] : ['subdomain' => $role->subdomain, 'tab' => 'schedule', 'year' => now()->year, 'month' => now()->month]) }}"
                    class="flex items-center justify-center border-y border-gray-300 px-3.5 text-sm font-semibold text-gray-900 hover:bg-gray-50 focus:relative md:block">
                    <div class="mt-2">{{ __('messages.today') }}</div>
                </a>                
                <a href="{{ $route == 'home' ? route('home', ['year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) : route('role.view_' . $route, $route == 'guest' ? ['subdomain' => $role->subdomain, 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month] : ['subdomain' => $role->subdomain, 'tab' => 'schedule', 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) }}"
                    class="flex h-9 w-12 items-center justify-center rounded-r-md border-y border-r border-gray-300 pl-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pl-0 md:hover:bg-gray-50">
                    <span class="sr-only">{{ __('messages.next_month') }}</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
            @if ($showAdd)
                <div class="ml-3 h-6 w-px bg-gray-300"></div>
                <a href="{{ route('event.create', ['subdomain' => $role->subdomain]) }}">
                    <button type="button"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        {{ __('messages.add_event') }}
                    </button>
                </a>
            @endif
        </div>
    </header>
    <div class="hidden shadow-md ring-1 ring-black ring-opacity-5 lg:flex lg:flex-auto lg:flex-col">
        <div
            class="grid grid-cols-7 gap-px border-b border-gray-300 bg-gray-200 text-center text-xs font-semibold leading-6 text-gray-700 lg:flex-none">
            @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day)
            <div class="flex justify-center bg-white py-2">
                {{ __('messages.' . $day) }}
            </div>
            @endforeach
        </div>
        <div class="flex bg-gray-200 text-xs leading-6 text-gray-700 lg:flex-auto">
            @php
            $startOfMonth = Carbon\Carbon::create($year, $month, 1)->startOfMonth()->startOfWeek(Carbon\Carbon::SUNDAY);
            $endOfMonth = Carbon\Carbon::create($year, $month, 1)->endOfMonth()->endOfWeek(Carbon\Carbon::SATURDAY);
            $currentDate = $startOfMonth->copy();
            $totalDays = $endOfMonth->diffInDays($startOfMonth) + 1;
            $totalWeeks = ceil($totalDays / 7);
            @endphp
            <div class="w-full lg:grid lg:grid-cols-7 lg:grid-rows-{{ $totalWeeks }} lg:gap-px">
                @while ($currentDate->lte($endOfMonth))
                @if ($showAdd)
                    <div class="cursor-pointer relative {{ $currentDate->month == $month ? 'bg-white hover:bg-gray-100 hover:border-gray-300' : 'bg-gray-50 text-gray-500' }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300"
                        onclick="window.location = '{{ route('event.create', ['subdomain' => $role->subdomain, 'date' => $currentDate->format('Y-m-d')]) }}';">
                @else
                <div class="relative {{ $currentDate->month == $month ? 'bg-white' : 'bg-gray-50 text-gray-500' }} px-3 py-2 min-h-[100px] border-1 border-transparent">
                @endif
                    @if ($showAdd)
                        <time datetime="{{ $currentDate->format('Y-m-d') }}" class="{{ $currentDate->day == now()->day && $currentDate->month == now()->month && $currentDate->year == now()->year ? 'flex h-6 w-6 items-center justify-center rounded bg-indigo-600 font-semibold text-white' : '' }}">{{ $currentDate->day }}</time>
                    @else
                        <time datetime="{{ $currentDate->format('Y-m-d') }}" style="{{ $currentDate->day == now()->day && $currentDate->month == now()->month && $currentDate->year == now()->year ? ('background-color: ' . ((isset($event) && $event) ? $event->role->accent_color : (isset($role) ? $role->accent_color : '#5348E9'))) : '' }}" class="{{ $currentDate->day == now()->day && $currentDate->month == now()->month && $currentDate->year == now()->year ? 'flex h-6 w-6 items-center justify-center rounded font-semibold text-white' : '' }}">{{ $currentDate->day }}</time>
                    @endif
                    <ol class="mt-2">
                        @foreach ($events as $each)
                        @if ($each->starts_at &&
                        Carbon\Carbon::parse($each->localStartsAt())->isSameDay($currentDate))
                        <li>
                            <a href="{{ $showAdd ? route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($each->id)]) : route('role.view_guest', ['subdomain' => isset($subdomain) ? $subdomain : $each->role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($each->id)]) }}"
                                class="group flex">
                                <p class="flex-auto truncate font-medium group-hover:text-indigo-600 text-gray-900">
                                    {{ isset($subdomain) && $subdomain == $each->role->subdomain ? $each->venue->name : $each->role->name }}</p>
                                <time datetime="{{ $each->localStartsAt() }}"
                                    class="ml-3 flex-none group-hover:text-indigo-600 xl:block text-gray-500">{{ Carbon\Carbon::parse($each->localStartsAt())->format(isset($role) && $role->use_24_hour_time ? 'H:i' : 'g:i A') }}</time>
                            </a>
                        </li>
                        @endif
                        @endforeach
                    </ol>
                </div>
                @php $currentDate->addDay(); @endphp
                @endwhile
            </div>
        </div>
    </div>
    <div class="px-4 py-10 sm:px-6 lg:hidden">
        @if (count($events))
        <ol
            class="divide-y divide-gray-100 overflow-hidden rounded-lg bg-white text-sm shadow ring-1 ring-black ring-opacity-5">
            @foreach ($events as $each)
            <li class="group flex p-4 pr-6 focus-within:bg-gray-50 hover:bg-gray-50">
                <div class="flex-auto">
                    <p class="font-semibold text-gray-900">{{ $each->role->name }}</p>
                    <time datetime="{{ $each->starts_at }}" class="mt-2 flex items-center text-gray-700">
                        <svg class="mr-2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ Carbon\Carbon::parse($each->localStartsAt())->format('g:i A') }}
                    </time>
                </div>
                <a href="#"
                    class="ml-6 flex-none self-center rounded-md bg-white px-3 py-2 font-semibold text-gray-900 opacity-0 shadow-sm ring-1 ring-inset ring-gray-300 hover:ring-gray-400 focus:opacity-100 group-hover:opacity-100">Edit<span
                        class="sr-only">, {{ $each->name }}</span></a>
            </li>
            @endforeach
        </ol>
        @endif
    </div>
</div>