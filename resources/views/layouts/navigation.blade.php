@php
    $schedules = isset($schedules) ? $schedules : collect();
    $venues = isset($venues) ? $venues : collect();
    $curators = isset($curators) ? $curators : collect();

    $scheduleRoutes = ['role.pages'];
    $scheduleRoutes = array_values(array_filter($scheduleRoutes, function ($routeName) {
        return \Illuminate\Support\Facades\Route::has($routeName);
    }));
    $schedulesActive = false;
    foreach ($scheduleRoutes as $routeName) {
        if (request()->routeIs($routeName)) {
            $schedulesActive = true;
            break;
        }
    }

    $venuesSectionOpen = request()->routeIs('role.venues');
    foreach ($venues as $venue) {
        if (request()->is($venue->subdomain) || request()->is($venue->subdomain . '/*')) {
            $venuesSectionOpen = true;
            break;
        }
    }

    $curatorsSectionOpen = request()->routeIs('role.curators');
    foreach ($curators as $curator) {
        if (request()->is($curator->subdomain) || request()->is($curator->subdomain . '/*')) {
            $curatorsSectionOpen = true;
            break;
        }
    }

    $talentSectionOpen = request()->routeIs('role.talent');
    foreach ($schedules as $schedule) {
        if (request()->is($schedule->subdomain) || request()->is($schedule->subdomain . '/*')) {
            $talentSectionOpen = true;
            break;
        }
    }
    $navLinkBase = 'group flex gap-x-3 rounded-md p-2 text-sm leading-6 transition-colors duration-150';
    $navLinkDefault = 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white';
    $navLinkActive = 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white';
    $navSectionLabel = 'px-2 text-xs font-semibold leading-6 text-gray-500 dark:text-gray-400';
    $pillBase = 'flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border text-[0.625rem] font-medium transition-colors duration-150';
    $pillDefault = 'border-gray-300 bg-gray-100 text-gray-600 group-hover:text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:group-hover:text-white';
    $pillActive = 'border-indigo-500 bg-indigo-500 text-white';
@endphp

<a href="https://www.eventschedule.com" class="block">
    <div class="flex h-16 shrink-0 items-center pt-2">
        <img class="h-10 w-auto dark:hidden" src="{{ url('images/light_logo.png') }}" alt="Event Schedule">
        <img class="hidden h-10 w-auto dark:block" src="{{ url('images/dark_logo.png') }}" alt="Event Schedule">
    </div>
</a>
<nav class="flex flex-1 flex-col">
    <ul role="list" class="flex flex-1 flex-col gap-y-7">
        <li>
            <ul role="list" class="-mx-2 space-y-1">

                <li>
                    <a href="{{ route('home') }}"
                        class="{{ $navLinkBase }} font-semibold {{ request()->is('events') ? $navLinkActive : $navLinkDefault }}">
                        <svg class="h-6 w-6 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z" />
                        </svg>
                        {{ __('messages.events') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('role.pages') }}"
                        class="{{ $navLinkBase }} font-semibold {{ $schedulesActive ? $navLinkActive : $navLinkDefault }}">
                        <svg class="h-6 w-6 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path d="M6 3a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h9.5a2 2 0 0 0 2-2V8.5L13 3H6zm7 1.5L17.5 9H13V4.5z" />
                        </svg>
                        {{ __('messages.schedules') }}
                    </a>
                </li>

                <li data-collapse-container class="space-y-1" data-collapse-state="{{ $venuesSectionOpen ? 'open' : 'closed' }}">
                    <div class="flex items-center gap-x-2">
                        <a href="{{ route('role.venues') }}"
                            class="{{ $navLinkBase }} flex-1 font-medium {{ $venuesSectionOpen ? $navLinkActive : $navLinkDefault }}">
                            <svg class="h-5 w-5 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 21h18M4.5 21V9l7.5-4.5L19.5 9V21M9 21v-6h6v6" />
                            </svg>
                            <span class="flex-1 text-left">{{ __('messages.venues') }}</span>
                        </a>

                        @if ($venues->isNotEmpty())
                            <button type="button"
                                class="ml-1 inline-flex items-center rounded-md p-1 text-gray-500 transition-colors duration-150 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-300 dark:hover:text-white"
                                data-collapse-trigger aria-controls="collapse-venues"
                                aria-expanded="{{ $venuesSectionOpen ? 'true' : 'false' }}">
                                <span class="sr-only">{{ __('Toggle :menu menu', ['menu' => __('messages.venues')]) }}</span>
                                <svg data-collapse-icon class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="none"
                                    stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    @if ($venues->isNotEmpty())
                        <ul role="list" id="collapse-venues" data-collapse-content
                            class="mt-1 space-y-1 pl-9 {{ $venuesSectionOpen ? '' : 'hidden' }}">
                            <li class="{{ $navSectionLabel }}">{{ __('messages.venue_schedules') }}</li>

                            @foreach ($venues as $venue)
                                @php
                                    $venueName = data_get($venue, 'name');
                                    $venueName = is_string($venueName) ? trim($venueName) : '';
                                    if ($venueName === '') {
                                        $venueName = __('messages.venue');
                                    }
                                    $venueInitial = mb_strtoupper(mb_substr($venueName, 0, 1));
                                @endphp
                                <li>
                                    <a href="{{ route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => $venue->subdomain == request()->subdomain ? 'schedule' : (request()->tab ? request()->tab : 'schedule')]) }}"
                                        class="{{ $navLinkBase }} font-semibold {{ request()->is($venue->subdomain) || request()->is($venue->subdomain . '/*') ? $navLinkActive : $navLinkDefault }}">
                                        <span
                                            class="{{ $pillBase }} {{ request()->is($venue->subdomain) || request()->is($venue->subdomain . '/*') ? $pillActive : $pillDefault }}">{{ $venueInitial }}</span>
                                        <span class="truncate">{{ $venueName }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>

                <li data-collapse-container class="space-y-1" data-collapse-state="{{ $curatorsSectionOpen ? 'open' : 'closed' }}">
                    <div class="flex items-center gap-x-2">
                        <a href="{{ route('role.curators') }}"
                            class="{{ $navLinkBase }} flex-1 font-medium {{ $curatorsSectionOpen ? $navLinkActive : $navLinkDefault }}">
                            <svg class="h-5 w-5 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24"
                                fill="currentColor" aria-hidden="true">
                                <path d="M12 4l2.47 5.02 5.53.8-4 3.91.94 5.5L12 16.9l-4.94 2.33.94-5.5-4-3.91 5.53-.8L12 4z" />
                            </svg>
                            <span class="flex-1 text-left">{{ __('messages.curators') }}</span>
                        </a>

                        @if ($curators->isNotEmpty())
                            <button type="button"
                                class="ml-1 inline-flex items-center rounded-md p-1 text-gray-500 transition-colors duration-150 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-300 dark:hover:text-white"
                                data-collapse-trigger aria-controls="collapse-curators"
                                aria-expanded="{{ $curatorsSectionOpen ? 'true' : 'false' }}">
                                <span class="sr-only">{{ __('Toggle :menu menu', ['menu' => __('messages.curators')]) }}</span>
                                <svg data-collapse-icon class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="none"
                                    stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    @if ($curators->isNotEmpty())
                        <ul role="list" id="collapse-curators" data-collapse-content
                            class="mt-1 space-y-1 pl-9 {{ $curatorsSectionOpen ? '' : 'hidden' }}">
                            <li class="{{ $navSectionLabel }}">{{ __('messages.curator_schedules') }}</li>

                            @foreach ($curators as $curator)
                                @php
                                    $curatorName = data_get($curator, 'name');
                                    $curatorName = is_string($curatorName) ? trim($curatorName) : '';
                                    if ($curatorName === '') {
                                        $curatorName = __('messages.curator');
                                    }
                                    $curatorInitial = mb_strtoupper(mb_substr($curatorName, 0, 1));
                                @endphp
                                <li>
                                    <a href="{{ route('role.view_admin', ['subdomain' => $curator->subdomain, 'tab' => $curator->subdomain == request()->subdomain ? 'schedule' : (request()->tab ? request()->tab : 'schedule')]) }}"
                                        class="{{ $navLinkBase }} font-semibold {{ request()->is($curator->subdomain) || request()->is($curator->subdomain . '/*') ? $navLinkActive : $navLinkDefault }}">
                                        <span
                                            class="{{ $pillBase }} {{ request()->is($curator->subdomain) || request()->is($curator->subdomain . '/*') ? $pillActive : $pillDefault }}">{{ $curatorInitial }}</span>
                                        <span class="truncate">{{ $curatorName }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>

                <li data-collapse-container class="space-y-1" data-collapse-state="{{ $talentSectionOpen ? 'open' : 'closed' }}">
                    <div class="flex items-center gap-x-2">
                        <a href="{{ route('role.talent') }}"
                            class="{{ $navLinkBase }} flex-1 font-medium {{ $talentSectionOpen ? $navLinkActive : $navLinkDefault }}">
                            <svg class="h-5 w-5 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24"
                                fill="currentColor" aria-hidden="true">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-3.33 0-6 2.24-6 5v1h12v-1c0-2.76-2.67-5-6-5z" />
                            </svg>
                            <span class="flex-1 text-left">{{ \Illuminate\Support\Str::plural(__('messages.talent')) }}</span>
                        </a>

                        @if ($schedules->isNotEmpty())
                            <button type="button"
                                class="ml-1 inline-flex items-center rounded-md p-1 text-gray-500 transition-colors duration-150 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-300 dark:hover:text-white"
                                data-collapse-trigger aria-controls="collapse-talent"
                                aria-expanded="{{ $talentSectionOpen ? 'true' : 'false' }}">
                                <span class="sr-only">{{ __('Toggle :menu menu', ['menu' => \Illuminate\Support\Str::plural(__('messages.talent'))]) }}</span>
                                <svg data-collapse-icon class="h-4 w-4 transform transition-transform duration-200" viewBox="0 0 20 20" fill="none"
                                    stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 8l4 4 4-4" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    @if ($schedules->isNotEmpty())
                        <ul role="list" id="collapse-talent" data-collapse-content
                            class="mt-1 space-y-1 pl-9 {{ $talentSectionOpen ? '' : 'hidden' }}">
                            <li class="{{ $navSectionLabel }}">{{ __('messages.talent_schedules') }}</li>

                            @foreach ($schedules as $each)
                                @php
                                    $scheduleName = data_get($each, 'name');
                                    $scheduleName = is_string($scheduleName) ? trim($scheduleName) : '';
                                    if ($scheduleName === '') {
                                        $scheduleName = \Illuminate\Support\Str::plural(__('messages.talent'));
                                    }
                                    $scheduleInitial = mb_strtoupper(mb_substr($scheduleName, 0, 1));
                                @endphp
                                <li>
                                    <a href="{{ route('role.view_admin', ['subdomain' => $each->subdomain, 'tab' => $each->subdomain == request()->subdomain ? 'schedule' : (request()->tab ? request()->tab : 'schedule')]) }}"
                                        class="{{ $navLinkBase }} font-semibold {{ request()->is($each->subdomain) || request()->is($each->subdomain . '/*') ? $navLinkActive : $navLinkDefault }}">
                                        <span
                                            class="{{ $pillBase }} {{ request()->is($each->subdomain) || request()->is($each->subdomain . '/*') ? $pillActive : $pillDefault }}">{{ $scheduleInitial }}</span>
                                        <span class="truncate">{{ $scheduleName }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>

                @if (\Illuminate\Support\Facades\Route::has('role.contacts'))
                    <li>
                        <a href="{{ route('role.contacts') }}"
                            class="{{ $navLinkBase }} font-medium {{ request()->routeIs('role.contacts') ? $navLinkActive : $navLinkDefault }}">
                            <svg class="h-5 w-5 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.25h15a.75.75 0 01.75.75v12.75a.75.75 0 01-.75.75h-15a.75.75 0 01-.75-.75V6a.75.75 0 01.75-.75z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 8.25h7.5M8.25 12h4.5M8.25 15.75H12" />
                            </svg>
                            {{ __('messages.contacts') }}
                        </a>
                    </li>
                @endif

                @if (config('app.hosted'))
                <li>
                    <a href="{{ route('tickets') }}"
                        class="{{ $navLinkBase }} font-semibold {{ request()->is('tickets') ? $navLinkActive : $navLinkDefault }}">
                        <svg class="h-6 w-6 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path d="M13,8.5H11V6.5H13V8.5M13,13H11V11H13V13M13,17.5H11V15.5H13V17.5M22,10V6C22,4.89 21.1,4 20,4H4A2,2 0 0,0 2,6V10C3.11,10 4,10.9 4,12A2,2 0 0,1 2,14V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V14A2,2 0 0,1 20,12A2,2 0 0,1 22,10Z" />
                        </svg>
                        {{ __('messages.tickets') }}
                    </a>
                </li>
                @endif

                <li>
                    <a href="{{ route('sales') }}"
                        class="{{ $navLinkBase }} font-semibold {{ request()->is('sales') ? $navLinkActive : $navLinkDefault }}">
                        <svg class="h-6 w-6 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path d="M20,8H4V6H20M20,18H4V12H20M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                        </svg>
                        {{ __('messages.sales') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('media.index') }}"
                        class="{{ $navLinkBase }} font-semibold {{ request()->routeIs('media.*') ? $navLinkActive : $navLinkDefault }}">
                        <svg class="h-6 w-6 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2" ry="2"></rect>
                            <path d="M3 16l4.5-4.5a1 1 0 0 1 1.414 0L15 18"></path>
                            <path d="M11 13l2-2a1 1 0 0 1 1.414 0L21 18"></path>
                            <circle cx="8" cy="9" r="1.5"></circle>
                        </svg>
                        {{ __('Media library') }}
                    </a>
                </li>

                @if (config('app.hosted') && auth()->user()->isAdmin())
                <li>
                    <a href="{{ route('blog.admin.index') }}"
                        class="{{ $navLinkBase }} font-semibold {{ request()->is('admin/blog*') ? $navLinkActive : $navLinkDefault }}">
                        <svg class="h-6 w-6 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path d="M19,3H5C3.89,3 3,3.89 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5C21,3.89 20.1,3 19,3M19,19H5V5H19V19M17,17H7V15H17V17M17,13H7V11H17V13M17,9H7V7H17V9Z" />
                        </svg>
                        Blog
                    </a>
                </li>
                @endif

            </ul>
        </li>


        @if (auth()->user()->isAdmin())
        <li class="mt-auto">
            <a href="{{ route('settings.index') }}"
                class="{{ $navLinkBase }} -mx-2 font-semibold {{ request()->is('settings*') ? $navLinkActive : $navLinkDefault }}">
                <svg class="h-6 w-6 shrink-0 text-gray-400 transition-colors duration-150 group-hover:text-gray-500 dark:text-gray-400 dark:group-hover:text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ __('messages.settings') }}
            </a>
        </li>
        @endif
    </ul>
</nav>
