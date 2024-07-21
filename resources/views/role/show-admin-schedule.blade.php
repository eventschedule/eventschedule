@if(count($events) == 0)

<div class="text-center pt-20">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="#ccc" viewBox="0 0 24 24" stroke="currentColor"
        aria-hidden="true">
        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19,19H5V8H19M16,1V3H8V1H6V3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3H18V1" />
    </svg>
    <h3 class="mt-2 text-sm font-semibold text-gray-900">{{ __('No events') }}</h3>
    <p class="mt-1 text-sm text-gray-500">{{ __('Get started by creating a new event') }}</p>
    <div class="mt-6">
        <a href="{{ route('event.create', ['subdomain1' => $role->subdomain]) }}">
            <button type="button"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                        d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                {{ __('Add Event') }}
            </button>
        </a>
    </div>
</div>

@else

<div class="lg:flex lg:h-full lg:flex-col pt-1">
    <header class="flex items-center justify-between pl-6 py-4 lg:flex-none">
        <h1 class="text-base font-semibold leading-6 text-gray-900">
            <time
                datetime="{{ sprintf('%04d-%02d', $year, $month) }}">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</time>
        </h1>
        <div class="flex items-center justify-center">
            <div class="relative flex items-center rounded-md bg-white shadow-sm md:items-stretch">
                <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule', 'year' => Carbon\Carbon::create($year, $month, 1)->subMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->subMonth()->month]) }}"
                    class="flex h-9 w-12 items-center justify-center rounded-l-md border-y border-l border-gray-300 pr-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pr-0 md:hover:bg-gray-50">
                    <span class="sr-only">Previous month</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule', 'year' => now()->year, 'month' => now()->month]) }}"
                    class="flex items-center justify-center border-y border-gray-300 px-3.5 text-sm font-semibold text-gray-900 hover:bg-gray-50 focus:relative md:block">
                    <div class="mt-2">Today</div>
                </a>
                <span class="relative -mx-px h-5 w-px bg-gray-300 md:hidden"></span>
                <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule', 'year' => Carbon\Carbon::create($year, $month, 1)->addMonth()->year, 'month' => Carbon\Carbon::create($year, $month, 1)->addMonth()->month]) }}"
                    class="flex h-9 w-12 items-center justify-center rounded-r-md border-y border-r border-gray-300 pl-1 text-gray-400 hover:text-gray-500 focus:relative md:w-9 md:pl-0 md:hover:bg-gray-50">
                    <span class="sr-only">Next month</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5-4.25a.75.75 0 01-1.06-.02z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
            <div class="hidden md:flex md:items-center">
                <!--
                <div class="relative">
                <button type="button" class="flex items-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" id="menu-button" aria-expanded="false" aria-haspopup="true">
                    Month view
                    <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div class="absolute right-0 z-10 mt-3 w-36 origin-top-right overflow-hidden rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                    <div class="py-1" role="none">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-0">Day view</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-1">Week view</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-2">Month view</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1" id="menu-item-3">Year view</a>
                    </div>
                </div>
                </div>
                -->
                <div class="ml-3 h-6 w-px bg-gray-300"></div>
                <a href="{{ route('event.create', ['subdomain1' => $role->subdomain]) }}">
                    <button type="button"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path
                                d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        {{ __('Add Event') }}
                    </button>
                </a>
                <!--
            <div class="relative ml-6 md:hidden">
                <button type="button"
                    class="-mx-2 flex items-center rounded-full border border-transparent p-2 text-gray-400 hover:text-gray-500"
                    id="menu-0-button" aria-expanded="false" aria-haspopup="true">
                    <span class="sr-only">Open menu</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path
                            d="M3 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM8.5 10a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zM15.5 8.5a.75.75 0 100 3 .75.75 0 000-3z" />
                    </svg>
                </button>
                <div class="absolute right-0 z-10 mt-3 w-36 origin-top-right divide-y divide-gray-100 overflow-hidden rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                    role="menu" aria-orientation="vertical" aria-labelledby="menu-0-button" tabindex="-1">
                    <div class="py-1" role="none">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1"
                            id="menu-0-item-0">Add Event</a>
                    </div>
                    <div class="py-1" role="none">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1"
                            id="menu-0-item-1">Go to today</a>
                    </div>
                    <div class="py-1" role="none">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1"
                            id="menu-0-item-2">Day view</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1"
                            id="menu-0-item-3">Week view</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1"
                            id="menu-0-item-4">Month view</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700" role="menuitem" tabindex="-1"
                            id="menu-0-item-5">Year view</a>
                    </div>
                </div>
            </div>
            -->
            </div>
    </header>
    <div class="shadow ring-1 ring-black ring-opacity-5 lg:flex lg:flex-auto lg:flex-col">
        <div
            class="grid grid-cols-7 gap-px border-b border-gray-300 bg-gray-200 text-center text-xs font-semibold leading-6 text-gray-700 lg:flex-none">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
            <div class="flex justify-center bg-white py-2">
                <span class="sr-only sm:not-sr-only">{{ $day }}</span>
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
            <div class="hidden w-full lg:grid lg:grid-cols-7 lg:grid-rows-{{ $totalWeeks }} lg:gap-px">
                @while ($currentDate->lte($endOfMonth))
                <div class="cursor-pointer relative {{ $currentDate->month == $month ? 'bg-white hover:bg-gray-100 hover:border-gray-300' : 'bg-gray-50 text-gray-500' }} px-3 py-2 min-h-[100px] border-1 border-transparent hover:border-gray-300"
                    onclick="window.location = '{{ route('event.create', ['subdomain1' => $role->subdomain, 'date' => $currentDate->format('Y-m-d')]) }}';">
                    <time datetime="{{ $currentDate->format('Y-m-d') }}"
                        class="{{ $currentDate->day == now()->day && $currentDate->month == now()->month && $currentDate->year == now()->year ? 'flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 font-semibold text-white' : '' }}">{{ $currentDate->day }}</time>
                    <ol class="mt-2">
                        @foreach ($events as $event)
                        @if ($event->starts_at &&
                        Carbon\Carbon::parse($event->starts_at)->isSameDay($currentDate))
                        <li>
                            <a href="{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => base64_encode($event->id)]) }}"
                                class="group flex">
                                <p class="flex-auto truncate font-medium text-gray-900 group-hover:text-indigo-600">
                                    {{ $event->role->name }}</p>
                                <time datetime="{{ $event->starts_at }}"
                                    class="ml-3 hidden flex-none text-gray-500 group-hover:text-indigo-600 xl:block">{{ Carbon\Carbon::parse($event->starts_at)->format('g:i A') }}</time>
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
    <!--
    <div class="px-4 py-10 sm:px-6 lg:hidden">
        <ol
            class="divide-y divide-gray-100 overflow-hidden rounded-lg bg-white text-sm shadow ring-1 ring-black ring-opacity-5">
            @foreach ($events as $event)
            <li class="group flex p-4 pr-6 focus-within:bg-gray-50 hover:bg-gray-50">
                <div class="flex-auto">
                    <p class="font-semibold text-gray-900">{{ $event->name }}</p>
                    <time datetime="{{ $event->starts_at }}" class="mt-2 flex items-center text-gray-700">
                        <svg class="mr-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                clip-rule="evenodd" />
                        </svg>
                        {{ Carbon\Carbon::parse($event->starts_at)->format('g:i A') }}
                    </time>
                </div>
                <a href="#"
                    class="ml-6 flex-none self-center rounded-md bg-white px-3 py-2 font-semibold text-gray-900 opacity-0 shadow-sm ring-1 ring-inset ring-gray-300 hover:ring-gray-400 focus:opacity-100 group-hover:opacity-100">Edit<span
                        class="sr-only">, {{ $event->name }}</span></a>
            </li>
            @endforeach
        </ol>
    </div>
    -->
</div>

@if (count($unscheduled))
<div class="lg:flex lg:h-full lg:flex-col pt-5">
    <header class="flex items-center justify-between pl-6 py-4 lg:flex-none">
        <h1 class="text-base font-semibold leading-6 text-gray-900">
            {{ __('Unscheduled') }}
        </h1>
    </header>
    <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 pt-5">
        @foreach($unscheduled as $event)
        @if(! $event->starts_at)
        <li class="col-span-1 flex flex-col divide-y divide-gray-200 rounded-lg bg-white text-center shadow">
            <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}" target="_blank">
                <div class="flex flex-1 flex-col p-8">
                    <img class="mx-auto h-32 w-32 flex-shrink-0 rounded-full"
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=4&w=256&h=256&q=60"
                        alt="">
                    <h3 class="mt-6 text-sm font-medium text-gray-900">{{ $event->role->name }}</h3>
                    <dl class="mt-1 flex flex-grow flex-col justify-between">
                        <dd class="text-sm text-gray-500 line-clamp-3">{{ $event->role->description }}</dd>
                    </dl>
                </div>
            </a>
            <div>
                <div class="-mt-px flex divide-x divide-gray-200">
                    <div class="flex w-0 flex-1 cursor-pointer"
                        onclick="location.href = '{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => base64_encode($event->id)]) }}'; return false;">
                        <div
                            class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ __('Schedule') }}
                        </div>
                    </div>
                    <div class="-ml-px flex w-0 flex-1 cursor-pointer"
                        onclick="var confirmed = confirm('{{ __('Are you sure?') }}'); if (confirmed) { location.href = '{{ route('event.decline', ['subdomain' => $role->subdomain, 'hash' => base64_encode($event->id), 'redirect_to' => 'schedule']) }}'; }">
                        <div
                            class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-gray-900">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path
                                    d="M12,2C17.53,2 22,6.47 22,12C22,17.53 17.53,22 12,22C6.47,22 2,17.53 2,12C2,6.47 6.47,2 12,2M15.59,7L12,10.59L8.41,7L7,8.41L10.59,12L7,15.59L8.41,17L12,13.41L15.59,17L17,15.59L13.41,12L17,8.41L15.59,7Z" />
                            </svg>
                            {{ __('Decline') }}
                        </div>
                    </div>
                </div>
            </div>
        </li>
        @endif
        @endforeach
    </ul>
</div>
@endif

@endif