<a href="{{ marketing_url() }}">
    <div class="flex h-16 pt-2 shrink-0 items-center">
        <picture>
            <source srcset="{{ url('images/light_logo.webp') }}" type="image/webp">
            <img class="h-10 w-auto" src="{{ url('images/light_logo.png') }}" alt="Event Schedule">
        </picture>
    </div>
</a>
<nav class="flex flex-1 flex-col mt-4">
    <ul role="list" class="flex flex-1 flex-col gap-y-7">
        <li>
            <ul role="list" class="-mx-2 space-y-1">

            <li>
                    <a href="{{ route('home') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('dashboard') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('dashboard') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z" />
                        </svg>
                        {{ __('messages.dashboard') }}
                    </a>
                </li>

            </ul>
        </li>

        @if ($schedules->isNotEmpty())
        <li>
            <div class="text-sm font-semibold leading-6 text-gray-400">{{ __('messages.talent_schedules') }}</div>

            <ul role="list" class="-mx-2 mt-2 space-y-2">

                @foreach ($schedules as $each)
                <li>
                    <div class="dark-nav-hover group flex items-center rounded-lg p-2 text-lg font-semibold leading-6 hover:bg-gray-800 hover:text-white {{ request()->is($each->subdomain) || request()->is($each->subdomain . '/*') ? 'bg-gray-800 dark-nav-active text-white' : 'text-gray-400' }}">
                        <a href="{{ route('role.view_admin', ['subdomain' => $each->subdomain, 'tab' => $each->subdomain == request()->subdomain ? 'schedule' : (request()->tab ? request()->tab : 'schedule')]) }}"
                            class="flex gap-x-4 items-center min-w-0 flex-1">
                            <span
                                class="schedule-badge flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-700 bg-gray-800 text-[0.625rem] font-medium group-hover:text-white {{ request()->is($each->subdomain) || request()->is($each->subdomain . '/*') ? 'text-white' : 'text-gray-400' }}">{{ strtoupper(substr($each->name, 0, 1)) }}</span>
                            <span class="truncate">{{ $each->name }}</span>
                        </a>
                        <div class="hidden group-hover:flex items-center gap-1 shrink-0">
                            <a href="{{ route('role.edit', ['subdomain' => $each->subdomain]) }}" title="{{ __('messages.edit') }}" class="p-2 rounded hover:bg-gray-700">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z"/></svg>
                            </a>
                            @if ($each->isClaimed())
                            <a href="{{ $each->getGuestUrl() }}" target="_blank" title="{{ __('messages.view') }}" class="p-2 rounded hover:bg-gray-700">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </li>
                @endforeach

            </ul>
        </li>
        @endif

        @if ($venues->isNotEmpty())
        <li>
            <div class="text-sm font-semibold leading-6 text-gray-400">{{ __('messages.venue_schedules') }}</div>

            <ul role="list" class="-mx-2 mt-2 space-y-2">

                @foreach ($venues as $venue)
                <li>
                    <div class="dark-nav-hover group flex items-center rounded-lg p-2 text-lg font-semibold leading-6 hover:bg-gray-800 hover:text-white {{ request()->is($venue->subdomain) || request()->is($venue->subdomain . '/*') ? 'bg-gray-800 dark-nav-active text-white' : 'text-gray-400' }}">
                        <a href="{{ route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => $venue->subdomain == request()->subdomain ? 'schedule' : (request()->tab ? request()->tab : 'schedule')]) }}"
                            class="flex gap-x-4 items-center min-w-0 flex-1">
                            <span
                                class="schedule-badge flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-700 bg-gray-800 text-[0.625rem] font-medium group-hover:text-white {{ request()->is($venue->subdomain) || request()->is($venue->subdomain . '/*') ? 'text-white' : 'text-gray-400' }}">{{ strtoupper(substr($venue->name, 0, 1)) }}</span>
                            <span class="truncate">{{ $venue->name }}</span>
                        </a>
                        <div class="hidden group-hover:flex items-center gap-1 shrink-0">
                            <a href="{{ route('role.edit', ['subdomain' => $venue->subdomain]) }}" title="{{ __('messages.edit') }}" class="p-2 rounded hover:bg-gray-700">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z"/></svg>
                            </a>
                            @if ($venue->isClaimed())
                            <a href="{{ $venue->getGuestUrl() }}" target="_blank" title="{{ __('messages.view') }}" class="p-2 rounded hover:bg-gray-700">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </li>
                @endforeach

            </ul>
        </li>
        @endif

        @if ($curators->isNotEmpty())
        <li>
            <div class="text-sm font-semibold leading-6 text-gray-400">{{ __('messages.curator_schedules') }}</div>

            <ul role="list" class="-mx-2 mt-2 space-y-2">

                @foreach ($curators as $curator)
                <li>
                    <div class="dark-nav-hover group flex items-center rounded-lg p-2 text-lg font-semibold leading-6 hover:bg-gray-800 hover:text-white {{ request()->is($curator->subdomain) || request()->is($curator->subdomain . '/*') ? 'bg-gray-800 dark-nav-active text-white' : 'text-gray-400' }}">
                        <a href="{{ route('role.view_admin', ['subdomain' => $curator->subdomain, 'tab' => $curator->subdomain == request()->subdomain ? 'schedule' : (request()->tab ? request()->tab : 'schedule')]) }}"
                            class="flex gap-x-4 items-center min-w-0 flex-1">
                            <span
                                class="schedule-badge flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-700 bg-gray-800 text-[0.625rem] font-medium group-hover:text-white {{ request()->is($curator->subdomain) || request()->is($curator->subdomain . '/*') ? 'text-white' : 'text-gray-400' }}">{{ strtoupper(substr($curator->name, 0, 1)) }}</span>
                            <span class="truncate">{{ $curator->name }}</span>
                        </a>
                        <div class="hidden group-hover:flex items-center gap-1 shrink-0">
                            <a href="{{ route('role.edit', ['subdomain' => $curator->subdomain]) }}" title="{{ __('messages.edit') }}" class="p-2 rounded hover:bg-gray-700">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z"/></svg>
                            </a>
                            @if ($curator->isClaimed())
                            <a href="{{ $curator->getGuestUrl() }}" target="_blank" title="{{ __('messages.view') }}" class="p-2 rounded hover:bg-gray-700">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </li>
                @endforeach

            </ul>
        </li>
        @endif

        <li>
            <ul role="list" class="-mx-2 space-y-1">

                <li>
                    <a href="{{ route('following') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('following') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('following') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M7,5H21V7H7V5M7,13V11H21V13H7M4,4.5A1.5,1.5 0 0,1 5.5,6A1.5,1.5 0 0,1 4,7.5A1.5,1.5 0 0,1 2.5,6A1.5,1.5 0 0,1 4,4.5M4,10.5A1.5,1.5 0 0,1 5.5,12A1.5,1.5 0 0,1 4,13.5A1.5,1.5 0 0,1 2.5,12A1.5,1.5 0 0,1 4,10.5M7,19V17H21V19H7M4,16.5A1.5,1.5 0 0,1 5.5,18A1.5,1.5 0 0,1 4,19.5A1.5,1.5 0 0,1 2.5,18A1.5,1.5 0 0,1 4,16.5Z" />
                        </svg>
                        {{ __('messages.following') }}
                    </a>
                </li>

                @if (config('app.hosted'))
                <li>
                    <a href="{{ route('tickets') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('tickets') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('tickets') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M13,8.5H11V6.5H13V8.5M13,13H11V11H13V13M13,17.5H11V15.5H13V17.5M22,10V6C22,4.89 21.1,4 20,4H4A2,2 0 0,0 2,6V10C3.11,10 4,10.9 4,12A2,2 0 0,1 2,14V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V14A2,2 0 0,1 20,12A2,2 0 0,1 22,10Z" />
                        </svg>
                        {{ __('messages.tickets') }}
                    </a>
                </li>
                @endif

                <li>
                    <a href="{{ route('sales') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('sales') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('sales') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M20,8H4V6H20M20,18H4V12H20M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                        </svg>
                        {{ __('messages.sales') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('analytics') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('analytics') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('analytics') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M22,21H2V3H4V19H6V10H10V19H12V6H16V19H18V14H22V21Z" />
                        </svg>
                        {{ __('messages.analytics') }}
                    </a>
                </li>

                @if (auth()->user()->isAdmin() || auth()->user()->roles()->wherePivot('level', '!=', 'follower')->exists())
                <li>
                    <a href="{{ route('newsletter.index') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('newsletters*') || request()->is('newsletter-segments*') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('newsletters*') || request()->is('newsletter-segments*') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                        </svg>
                        {{ __('messages.newsletters') }}
                    </a>
                </li>
                @endif

                @if (\App\Services\MetaAdsService::isBoostConfigured())
                <li>
                    <a href="{{ route('boost.index') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('boost*') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('boost*') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4M19.22 2C19.06 2 18.88 2 18.7 2.04C16.56 2.11 13.5 3.31 10.77 6.04C8.95 7.87 7.57 10.04 6.63 12.46C6.37 13.1 6.55 13.85 7.07 14.33L9.65 16.91C10.13 17.42 10.87 17.61 11.53 17.35C13.95 16.42 16.12 15.04 17.95 13.22C20.67 10.5 21.88 7.44 21.95 5.3C22.04 3.5 20.87 2 19.22 2M14.54 9.46C13.76 8.68 13.76 7.41 14.54 6.63S16.59 5.85 17.37 6.63C18.14 7.41 18.15 8.68 17.37 9.46C16.59 10.24 15.32 10.24 14.54 9.46Z"/>
                        </svg>
                        {{ __('messages.boost') }}
                    </a>
                </li>
                @endif

                @if (config('app.hosted'))
                <li>
                    <a href="{{ route('referrals') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('referrals') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('referrals') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M21,12L14,5V9C7,10 4,15 3,20C5.5,16.5 9,14.9 14,14.9V19L21,12Z" />
                        </svg>
                        {{ __('messages.referrals') }}
                    </a>
                </li>
                @endif

                @if (auth()->user()->isAdmin())
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('admin/*') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('admin/*') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M13,3V9H21V3M13,21H21V11H13M3,21H11V15H3M3,13H11V3H3V13Z" />
                        </svg>
                        {{ __('messages.admin') }}
                    </a>
                </li>
                @endif

            </ul>
        </li>

        <li class="mt-auto">
            <ul role="list" class="-mx-2 space-y-1">
                <li>
                    <a href="{{ route('profile.edit') }}"
                        class="dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('settings') || request()->is('settings/*') ? 'bg-gray-800 dark-nav-active text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('settings') || request()->is('settings/*') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11.03L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11.03C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z" />
                        </svg>
                        {{ __('messages.settings') }}
                    </a>
                </li>
                <li>
                    <a href="{{ \App\Utils\HelpUtils::getDocUrl() }}" target="_blank"
                        class="js-help-link dark-nav-hover group flex gap-x-4 items-center rounded-lg p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="#666" aria-hidden="true">
                            <path d="M15.07,11.25L14.17,12.17C13.45,12.89 13,13.5 13,15H11V14.5C11,13.39 11.45,12.39 12.17,11.67L13.41,10.41C13.78,10.05 14,9.55 14,9C14,7.89 13.1,7 12,7A2,2 0 0,0 10,9H8A4,4 0 0,1 12,5A4,4 0 0,1 16,9C16,9.88 15.64,10.67 15.07,11.25M13,19H11V17H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z" />
                        </svg>
                        {{ __('messages.help') }}
                    </a>
                </li>
            </ul>
        </li>

        <li>
            <div class="-mx-2 py-2">
                <div class="theme-switcher-container flex gap-1 rounded-lg bg-gray-800/50 p-1.5 w-full" role="radiogroup" aria-label="Theme selection">
                    <button
                        type="button"
                        id="theme-light"
                        data-theme="light"
                        class="theme-btn js-theme-btn flex-1 rounded-md px-2 py-1.5 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 hover:scale-105 active:scale-95 transition-all duration-200"
                        aria-label="Light theme"
                        title="{{ __('messages.theme_light') }}"
                        aria-pressed="false">
                        <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                    <div class="theme-separator w-px self-stretch my-1.5 bg-white/[0.08] transition-opacity duration-200"></div>
                    <button
                        type="button"
                        id="theme-dark"
                        data-theme="dark"
                        class="theme-btn js-theme-btn flex-1 rounded-md px-2 py-1.5 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 hover:scale-105 active:scale-95 transition-all duration-200"
                        aria-label="Dark theme"
                        title="{{ __('messages.theme_dark') }}"
                        aria-pressed="false">
                        <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <div class="theme-separator w-px self-stretch my-1.5 bg-white/[0.08] transition-opacity duration-200"></div>
                    <button
                        type="button"
                        id="theme-system"
                        data-theme="system"
                        class="theme-btn js-theme-btn flex-1 rounded-md px-2 py-1.5 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 hover:scale-105 active:scale-95 transition-all duration-200"
                        aria-label="System theme"
                        title="{{ __('messages.theme_system') }}"
                        aria-pressed="false">
                        <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>
        </li>
    </ul>
</nav>

<style {!! nonce_attr() !!}>
    .theme-switcher-container {
        background: linear-gradient(to bottom, #2d2d30, #252526) !important;
        border: 1px solid rgba(255, 255, 255, 0.06);
    }
    .theme-btn { position: relative; }
    .theme-btn.active {
        background: #1A1A1A !important;
        color: #ffffff !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
    }
    .theme-btn.active:hover {
        background: #1c1c1c !important;
        color: #ffffff !important;
    }
    .theme-btn:not(.active) {
        background-color: transparent !important;
        color: #9ca3af !important;
    }
    .theme-btn:not(.active):hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: #ffffff !important;
    }
</style>

<script {!! nonce_attr() !!}>
    function updateThemeButtons() {
        // Get current theme - use localStorage directly as fallback
        let theme = 'system';
        if (window.getCurrentTheme) {
            theme = window.getCurrentTheme();
        } else {
            try { theme = localStorage.getItem('theme') || 'system'; } catch (e) {}
        }
        
        // Get all theme buttons
        const buttons = document.querySelectorAll('.theme-btn');
        
        buttons.forEach(button => {
            const buttonTheme = button.getAttribute('data-theme');
            if (buttonTheme === theme) {
                button.classList.add('active');
                button.setAttribute('aria-pressed', 'true');
            } else {
                button.classList.remove('active');
                button.setAttribute('aria-pressed', 'false');
            }
        });

        // Hide separators adjacent to active button
        document.querySelectorAll('.theme-separator').forEach(function(sep) {
            var prev = sep.previousElementSibling;
            var next = sep.nextElementSibling;
            sep.style.opacity = (prev && prev.classList.contains('active')) ||
                                 (next && next.classList.contains('active')) ? '0' : '1';
        });
    }
    
    // Make function globally available
    window.updateThemeButtons = updateThemeButtons;

    // Attach click listeners to theme buttons
    document.querySelectorAll('.js-theme-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            setTheme(this.getAttribute('data-theme'));
            updateThemeButtons();
        });
    });
    
    // Update buttons immediately on page load
    function initThemeButtons() {
        const buttons = document.querySelectorAll('.theme-btn');
        if (buttons.length === 3) {
            // Buttons are already in DOM, update immediately
            updateThemeButtons();
        } else if (document.readyState === 'loading') {
            // DOM is still loading, wait for it
            document.addEventListener('DOMContentLoaded', function() {
                updateThemeButtons();
            });
        } else {
            // DOM is ready but buttons might not be rendered yet, use requestAnimationFrame
            requestAnimationFrame(function() {
                updateThemeButtons();
            });
        }
    }
    
    // Initialize immediately
    initThemeButtons();
    
    // Listen for storage changes (when theme changes in another tab/window)
    window.addEventListener('storage', function(e) {
        if (e.key === 'theme') {
            updateThemeButtons();
        }
    });
</script>

@once
<script {!! nonce_attr() !!}>
    (function() {
        var anchorMap = @json(\App\Utils\HelpUtils::getAnchorMap());
        var baseUrl = @json(\App\Utils\HelpUtils::getDocUrl());

        function updateHelpLinks(url) {
            document.querySelectorAll('.js-help-link').forEach(function(link) {
                link.href = url;
            });
        }

        if (Object.keys(anchorMap).length === 0) {
            return;
        }

        function resolveUrl() {
            var hash = window.location.hash.replace('#', '');
            if (hash && anchorMap[hash]) {
                updateHelpLinks(anchorMap[hash]);
            } else {
                updateHelpLinks(baseUrl);
            }
        }

        // Set initial state based on URL hash
        resolveUrl();

        // On page load, check localStorage for active tabs to restore the correct help URL
        document.addEventListener('DOMContentLoaded', function() {
            var tabSources = [
                { key: 'detailsActiveTab', prefix: 'details-tab-' },
                { key: 'customizeActiveTab', prefix: 'customize-tab-' },
                { key: 'settingsActiveTab', prefix: 'settings-tab-' },
                { key: 'engagementActiveTab', prefix: 'engagement-tab-' },
                { key: 'integrationActiveTab', prefix: 'integration-tab-' },
                { key: 'paymentActiveTab', prefix: 'payment-tab-' },
            ];
            for (var i = 0; i < tabSources.length; i++) {
                try {
                    var value = localStorage.getItem(tabSources[i].key);
                    if (value) {
                        var tabKey = tabSources[i].prefix + value;
                        if (anchorMap[tabKey]) {
                            updateHelpLinks(anchorMap[tabKey]);
                            return;
                        }
                    }
                } catch (e) {}
            }
        });

        // Listen for section nav clicks (pushState doesn't fire hashchange)
        document.addEventListener('click', function(e) {
            var target = e.target.closest('.section-nav-link, .mobile-section-header');
            if (target) {
                var sectionId = target.getAttribute('data-section');
                if (sectionId && anchorMap[sectionId]) {
                    updateHelpLinks(anchorMap[sectionId]);
                } else {
                    updateHelpLinks(baseUrl);
                }
                return;
            }

            var tab = e.target.closest('.payment-tab');
            if (tab) {
                var tabKey = 'payment-tab-' + tab.getAttribute('data-tab');
                if (anchorMap[tabKey]) {
                    updateHelpLinks(anchorMap[tabKey]);
                }
            }

            var detailsTab = e.target.closest('.details-tab');
            if (detailsTab) {
                var tabKey = 'details-tab-' + detailsTab.getAttribute('data-tab');
                if (anchorMap[tabKey]) {
                    updateHelpLinks(anchorMap[tabKey]);
                }
            }

            var customizeTab = e.target.closest('.customize-tab');
            if (customizeTab) {
                var tabKey = 'customize-tab-' + customizeTab.getAttribute('data-tab');
                if (anchorMap[tabKey]) {
                    updateHelpLinks(anchorMap[tabKey]);
                }
            }

            var settingsTab = e.target.closest('.settings-tab');
            if (settingsTab) {
                var tabKey = 'settings-tab-' + settingsTab.getAttribute('data-tab');
                if (anchorMap[tabKey]) {
                    updateHelpLinks(anchorMap[tabKey]);
                }
            }

            var engagementTab = e.target.closest('.engagement-tab');
            if (engagementTab) {
                var tabKey = 'engagement-tab-' + engagementTab.getAttribute('data-tab');
                if (anchorMap[tabKey]) {
                    updateHelpLinks(anchorMap[tabKey]);
                }
            }

            var integrationTab = e.target.closest('.integration-tab');
            if (integrationTab) {
                var tabKey = 'integration-tab-' + integrationTab.getAttribute('data-tab');
                if (anchorMap[tabKey]) {
                    updateHelpLinks(anchorMap[tabKey]);
                }
            }

            var ticketModeRadio = e.target.closest('.ticket-mode-radio');
            if (ticketModeRadio) {
                var modeKey = 'ticket-mode-' + ticketModeRadio.value;
                if (anchorMap[modeKey]) {
                    updateHelpLinks(anchorMap[modeKey]);
                }
            }

            var ticketTab = e.target.closest('.ticket-tab');
            if (ticketTab) {
                var tabKey = 'ticket-tab-' + ticketTab.getAttribute('data-tab');
                if (anchorMap[tabKey]) {
                    updateHelpLinks(anchorMap[tabKey]);
                }
            }
        });

        // Handle browser back/forward
        window.addEventListener('popstate', function() {
            resolveUrl();
        });
    })();
</script>
@endonce
