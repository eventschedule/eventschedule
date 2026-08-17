{{-- theme-variants opts this layout in to the six palettes. The guest portal
     renders through the same <x-app-layout> shell and deliberately does not. --}}
<x-app-layout :theme-variants="true" :title="(request()->path() != '/' ? implode(' > ', array_map('ucwords', array_slice(explode('/', str_replace(['-', '_'], ' ', request()->path())), 0, 2))) : '') . ' | Event Schedule'">

    <x-slot name="head">
        {{-- The admin portal is the one surface that genuinely is the Event Schedule app, so it
             is the one that carries the platform manifest and brand colour. The guest portal
             renders through the same shell and supplies its schedule's own. --}}
        @include('partials.web-app-manifest', ['platformApp' => true])

        <script {!! nonce_attr() !!}>
            $(document).ready(function() {
                const sidebar = document.getElementById('sidebar');
                const openButton = document.getElementById('open-sidebar');
                const closeButton = document.getElementById('close-sidebar');
                let lastFocusBeforeOpen = null;

                function closeMenu() {
                    if (sidebar.getAttribute('data-state') !== 'open') {
                        return;
                    }
                    $('#sidebar').hide();
                    sidebar.setAttribute('data-state', 'closed');
                    document.removeEventListener('keydown', onEscapeClose);
                    if (lastFocusBeforeOpen && typeof lastFocusBeforeOpen.focus === 'function') {
                        lastFocusBeforeOpen.focus();
                    } else if (openButton) {
                        openButton.focus();
                    }
                }

                function openMenu() {
                    lastFocusBeforeOpen = document.activeElement;
                    $('#sidebar').show();
                    sidebar.setAttribute('data-state', 'open');
                    document.addEventListener('keydown', onEscapeClose);
                    requestAnimationFrame(function() {
                        if (closeButton && typeof closeButton.focus === 'function') {
                            closeButton.focus();
                        }
                    });
                }

                function onEscapeClose(e) {
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        closeMenu();
                    }
                }

                function toggleMenu() {
                    const isOpen = sidebar.getAttribute('data-state') === 'open';
                    if (isOpen) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                }

                openButton.addEventListener('click', toggleMenu);
                closeButton.addEventListener('click', toggleMenu);
            });
        </script>

        {{ isset($head) ? $head : '' }}
    </x-slot>

    <div>
        <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
        <div data-state="closed" id="sidebar" class="relative z-50 hidden" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/80" aria-hidden="true"></div>

            <div class="fixed inset-0 flex {{ is_rtl() ? 'flex-row-reverse' : '' }}">
                <div class="relative me-16 flex w-full max-w-xs flex-1">
                    <div class="absolute start-full top-0 flex w-16 justify-center pt-5">
                        <button id="close-sidebar" type="button" class="-m-2.5 p-2.5">
                            <span class="sr-only">{{ __('messages.close_sidebar') }}</span>
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Sidebar component, swap this element with another sidebar if you like -->
                    {{-- Scrolling nav column + pinned footer. min-h-0 on both is load-bearing:
                         without it the flex child refuses to shrink below its content height,
                         overflow-y-auto never engages and the footer is pushed off screen. The
                         outer wrapper deliberately does NOT clip, so the footer's theme popover
                         can open upward over the nav list. --}}
                    <div class="flex grow flex-col min-h-0 bg-gray-900 dark:bg-[#1A1A1A] sidebar-gradient ring-1 ring-white/10">

                        <div class="flex flex-1 min-h-0 flex-col gap-y-5 overflow-y-auto px-6">
                            @include('layouts.navigation')
                        </div>

                        @include('layouts.sidebar-footer')

                    </div>
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col lg:start-0">
            <!-- Sidebar component, swap this element with another sidebar if you like -->
            {{-- Same scroll/footer split as the drawer above - see the comment there for why
                 min-h-0 and the non-clipping outer wrapper both matter. --}}
            <div class="flex grow flex-col min-h-0 bg-gray-900 dark:bg-[#1A1A1A] sidebar-gradient">

                <div class="flex flex-1 min-h-0 flex-col gap-y-5 overflow-y-auto px-6">
                    @include('layouts.navigation')
                </div>

                @include('layouts.sidebar-footer')

            </div>
        </div>

        <div class="lg:ps-72 flex flex-col min-h-screen bg-gray-50 dark:bg-gray-900">
            <div
                class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 header-gradient px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button id="open-sidebar" type="button" class="-m-2.5 p-2.5 text-gray-700 dark:text-gray-300 lg:hidden">
                    <span class="sr-only">{{ __('messages.open_sidebar') }}</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Separator -->
                <div class="h-6 w-px bg-gray-900/10 lg:hidden" aria-hidden="true"></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="relative flex flex-1"></div>
                    <!--
                    <form class="relative flex flex-1" action="#" method="GET">
                    <label for="search-field" class="sr-only">Search</label>
                    <svg class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                    <input id="search-field" class="block h-full w-full border-0 py-0 pl-8 pr-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm" placeholder="Search..." type="search" name="search">
                    </form>
                    -->
                    <div class="flex items-center gap-x-4 lg:gap-x-6">

                        <!--
                        <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                            <span class="sr-only">View notifications</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </button>
                        -->

                        <!-- Settings Dropdown -->
                        <div class="sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        <div>{{ Auth::user()->name }}</div>

                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')" id="logout-link">
                                            {{ __('messages.log_out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>

                        @if ($upgradeSubdomain)
                        <a href="{{ route('role.subscribe', ['subdomain' => $upgradeSubdomain]) }}"
                           class="group relative inline-flex items-center justify-center px-3 py-1 text-sm font-semibold text-white bg-green-600 hover:bg-gradient-to-r hover:from-green-600 hover:to-emerald-500 rounded-lg overflow-hidden transition-all hover:scale-105 hover:shadow-lg hover:shadow-green-500/25">
                            <span class="relative z-10">{{ __('messages.upgrade') }}</span>
                            <div class="absolute inset-0 animate-shimmer"></div>
                        </a>
                        @endif

                    </div>
                </div>
            </div>

            <main id="main-content" tabindex="-1" class="py-10">
                <div class="px-4 sm:px-6 lg:px-8">

                    @if ($errors->any())
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 card-highlight shadow-md sm:rounded-xl text-red-600 dark:text-red-400">
                        <b>{{ __('messages.there_was_a_problem') . ':' }}</b>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{ $slot }}

                </div>
            </main>

            <div class="mt-auto pb-8 px-4 sm:px-6 lg:px-8 text-sm text-gray-500 dark:text-gray-400" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
                @if (config('app.hosted'))
                    {!! str_replace(':email', '<bdi dir="ltr"><a href="mailto:'.config('app.support_email').'?subject=Feedback" class="hover:underline">'.config('app.support_email').'</a></bdi>', __('messages.questions_or_suggestions')) !!}
                @else
                    <div class="flex items-center justify-between w-full">
                        <span>
                            <!-- Per the AAL license, please do not remove the link to Event Schedule -->
                            {!! str_replace(':link', '<bdi dir="ltr"><a href="https://www.eventschedule.com" class="text-blue-600 dark:text-blue-400 hover:underline" target="_blank">eventschedule.com</a></bdi>', __('messages.powered_by_eventschedule')) !!}
                            •
                            <x-link href="https://github.com/eventschedule/eventschedule/releases" target="_blank" hideIcon>{{ config('self-update.version_installed') }}</x-link>
                        </span>
                        @if(isset($githubStars) && $githubStars)
                            <a href="https://github.com/eventschedule/eventschedule" target="_blank"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-300 transition-all duration-200 no-underline">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                                </svg>
                                <svg class="h-3.5 w-3.5 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                {{ number_format($githubStars) }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>

    @auth
        @if (config('app.hosted'))
            @include('partials.support-chat-widget')
        @endif
    @endauth

    {{-- About, opened by the sidebar footer's info button. It lives out here rather than in
         layouts/sidebar-footer.blade.php because that partial renders twice (drawer + desktop
         rail): two modals sharing the name would both answer the same open-modal event and
         stack two overlays. Out here it is rendered once, and outside the sidebar's own
         scroll and stacking context. --}}
    <x-modal name="about-app" maxWidth="sm" :ariaLabel="__('messages.about')">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.about') }}</h3>
            <button type="button" x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="{{ __('messages.close') }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6">
            {{-- The wordmark, not images/logo.png: these two config values are what a selfhost
                 operator overrides through APP_LOGO_DARK / APP_LOGO_LIGHT to rebrand, and they
                 swap with the theme so a dark mark never lands on the dark modal surface. Same
                 pair as components/application-logo.blade.php, inlined because that component
                 wraps itself in a p-6 that does not fit here. The mark carries the app name,
                 so there is no separate name line. --}}
            <div>
                <img class="h-9 w-auto dark:hidden" src="{{ asset(ltrim(config('app.logo_dark'), '/')) }}" alt="{{ config('app.name') }}">
                <img class="h-9 w-auto hidden dark:block" src="{{ asset(ltrim(config('app.logo_light'), '/')) }}" alt="{{ config('app.name') }}">
                <div class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    <bdi dir="ltr">
                        <x-link href="https://github.com/eventschedule/eventschedule/releases" target="_blank" hideIcon>{{ config('self-update.version_installed') }}</x-link>
                    </bdi>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3 text-sm">
                <x-link :href="\App\Utils\HelpUtils::getDocUrl()" target="_blank">{{ __('messages.help') }}</x-link>

                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-300 transition-all duration-200 no-underline">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                    @if (isset($githubStars) && $githubStars)
                    <svg class="h-3.5 w-3.5 text-yellow-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                    {{ number_format($githubStars) }}
                    @else
                    GitHub
                    @endif
                </a>

                @if (config('app.hosted'))
                <bdi dir="ltr"><x-link href="mailto:{{ config('app.support_email') }}?subject=Feedback" hideIcon>{{ config('app.support_email') }}</x-link></bdi>
                @endif
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="px-4 py-3 text-base font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800">
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </x-modal>

    <script {!! nonce_attr() !!}>
        document.getElementById('logout-link').addEventListener('click', function(e) {
            e.preventDefault();
            this.closest('form').submit();
        });

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.js-cancel-btn');
            if (btn) {
                e.preventDefault();
                window._skipUnsavedWarning = true;
                var fallback = btn.getAttribute('data-fallback-url') || btn.getAttribute('href');
                var referrer = document.referrer;
                var currentBase = location.origin + location.pathname + location.search;
                if (referrer && referrer.indexOf(location.origin) === 0 && referrer !== currentBase) {
                    history.back();
                } else if (fallback) {
                    window.location = fallback;
                }
            }
        });
    </script>

</x-app-layout>
