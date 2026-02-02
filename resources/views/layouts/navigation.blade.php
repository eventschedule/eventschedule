<a href="{{ marketing_url() }}">
    <div class="flex h-16 pt-2 shrink-0 items-center">
        <img class="h-10 w-auto" src="{{ url('images/light_logo.png') }}"
            alt="Event Schedule">
    </div>
</a>
<nav class="flex flex-1 flex-col mt-4">
    <ul role="list" class="flex flex-1 flex-col gap-y-7">
        <li>
            <ul role="list" class="-mx-2 space-y-1">

            <li>
                    <a href="{{ route('home') }}"
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('events') ? 'bg-gray-800 text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('events') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z" />
                        </svg>
                        {{ __('messages.events') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('following') }}"
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('following') ? 'bg-gray-800 text-white' : '' }}">
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
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('tickets') ? 'bg-gray-800 text-white' : '' }}">
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
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('sales') ? 'bg-gray-800 text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('sales') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M20,8H4V6H20M20,18H4V12H20M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                        </svg>
                        {{ __('messages.sales') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('analytics') }}"
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('analytics') ? 'bg-gray-800 text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('analytics') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M22,21H2V3H4V19H6V10H10V19H12V6H16V19H18V14H22V21Z" />
                        </svg>
                        {{ __('messages.analytics') }}
                    </a>
                </li>

                @if (auth()->user()->isAdmin() || auth()->user()->roles()->wherePivot('level', '!=', 'follower')->get()->contains(fn ($role) => $role->isEnterprise()))
                <li>
                    <a href="{{ route('newsletter.index') }}"
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('newsletters*') || request()->is('newsletter-segments*') ? 'bg-gray-800 text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('newsletters*') || request()->is('newsletter-segments*') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                        </svg>
                        {{ __('messages.newsletters') }}
                    </a>
                </li>
                @endif

                @if (config('app.hosted') && auth()->user()->isAdmin())
                <li>
                    <a href="{{ route('admin.dashboard') }}"
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('admin/*') ? 'bg-gray-800 text-white' : '' }}">
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

    
        @if ($schedules->isNotEmpty())
        <li>
            <div class="text-sm font-semibold leading-6 text-gray-400">{{ __('messages.talent_schedules') }}</div>

            <ul role="list" class="-mx-2 mt-2 space-y-2">

                @foreach ($schedules as $each)
                <li>
                    <a href="{{ route('role.view_admin', ['subdomain' => $each->subdomain, 'tab' => $each->subdomain == request()->subdomain ? 'schedule' : (request()->tab ? request()->tab : 'schedule')]) }}"
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 hover:bg-gray-800 hover:text-white {{ request()->is($each->subdomain) || request()->is($each->subdomain . '/*') ? 'bg-gray-800 text-white' : 'text-gray-400' }}">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-700 bg-gray-800 text-[0.625rem] font-medium group-hover:text-white {{ request()->is($each->subdomain) || request()->is($each->subdomain . '/*') ? 'text-white' : 'text-gray-400' }}">{{ strtoupper(substr($each->name, 0, 1)) }}</span>
                        <span class="truncate">{{ $each->name }}</span>
                    </a>
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
                    <a href="{{ route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => $venue->subdomain == request()->subdomain ? 'schedule' : (request()->tab ? request()->tab : 'schedule')]) }}"
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 hover:bg-gray-800 hover:text-white {{ request()->is($venue->subdomain) || request()->is($venue->subdomain . '/*') ? 'bg-gray-800 text-white' : 'text-gray-400' }}">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-700 bg-gray-800 text-[0.625rem] font-medium group-hover:text-white {{ request()->is($venue->subdomain) || request()->is($venue->subdomain . '/*') ? 'text-white' : 'text-gray-400' }}">{{ strtoupper(substr($venue->name, 0, 1)) }}</span>
                        <span class="truncate">{{ $venue->name }}</span>
                    </a>
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
                    <a href="{{ route('role.view_admin', ['subdomain' => $curator->subdomain, 'tab' => $curator->subdomain == request()->subdomain ? 'schedule' : (request()->tab ? request()->tab : 'schedule')]) }}"
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 hover:bg-gray-800 hover:text-white {{ request()->is($curator->subdomain) || request()->is($curator->subdomain . '/*') ? 'bg-gray-800 text-white' : 'text-gray-400' }}">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-700 bg-gray-800 text-[0.625rem] font-medium group-hover:text-white {{ request()->is($curator->subdomain) || request()->is($curator->subdomain . '/*') ? 'text-white' : 'text-gray-400' }}">{{ strtoupper(substr($curator->name, 0, 1)) }}</span>
                        <span class="truncate">{{ $curator->name }}</span>
                    </a>
                </li>
                @endforeach

            </ul>
        </li>
        @endif

        <li class="mt-auto">
            <ul role="list" class="-mx-2 space-y-1">
                <li>
                    <a href="{{ route('profile.edit') }}"
                        class="group flex gap-x-4 items-center rounded-md p-2 text-lg font-semibold leading-6 text-gray-400 hover:bg-gray-800 hover:text-white {{ request()->is('account') || request()->is('account/*') ? 'bg-gray-800 text-white' : '' }}">
                        <svg class="h-8 w-8 shrink-0" viewBox="0 0 24 24"
                            fill="{{ request()->is('account') || request()->is('account/*') ? '#ccc' : '#666' }}" aria-hidden="true">
                            <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11.03L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11.03C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z" />
                        </svg>
                        {{ __('messages.settings') }}
                    </a>
                </li>
            </ul>
        </li>

        <li>
            <div class="-mx-2 py-2">
                <div class="flex gap-1 rounded-lg bg-gray-800/50 p-1.5 w-full" role="radiogroup" aria-label="Theme selection">
                    <button
                        type="button"
                        onclick="setTheme('light'); updateThemeButtons();"
                        id="theme-light"
                        data-theme="light"
                        class="theme-btn flex-1 rounded-md px-2 py-1.5 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 hover:scale-105 active:scale-95 transition-all duration-200"
                        aria-label="Light theme"
                        title="{{ __('messages.theme_light') }}"
                        aria-pressed="false">
                        <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        onclick="setTheme('dark'); updateThemeButtons();"
                        id="theme-dark"
                        data-theme="dark"
                        class="theme-btn flex-1 rounded-md px-2 py-1.5 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 hover:scale-105 active:scale-95 transition-all duration-200"
                        aria-label="Dark theme"
                        title="{{ __('messages.theme_dark') }}"
                        aria-pressed="false">
                        <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        onclick="setTheme('system'); updateThemeButtons();"
                        id="theme-system"
                        data-theme="system"
                        class="theme-btn flex-1 rounded-md px-2 py-1.5 text-sm font-medium text-gray-400 hover:text-white hover:bg-gray-700 hover:scale-105 active:scale-95 transition-all duration-200"
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
    .theme-btn.active {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: #ffffff !important;
    }
    .theme-btn.active:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
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
        } else if (typeof Storage !== 'undefined') {
            theme = localStorage.getItem('theme') || 'system';
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
    }
    
    // Make function globally available
    window.updateThemeButtons = updateThemeButtons;
    
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
