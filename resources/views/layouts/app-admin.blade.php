<x-app-layout :title="(request()->path() != '/' ? implode(' > ', array_map('ucwords', array_slice(explode('/', str_replace(['-', '_'], ' ', request()->path())), 0, 2))) : '') . ' | Event Schedule'">

    <x-slot name="head">
        <link rel="preconnect" href="https://rsms.me/">
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

        <style>
            @media (min-width: 1024px) {
                #desktop-sidebar {
                    will-change: transform;
                }

                #desktop-sidebar[data-state="closed"] {
                    transform: translateX(-100%);
                    pointer-events: none;
                }

                #main-content {
                    transition: padding-left 0.3s ease;
                }

                #main-content[data-sidebar-state="closed"] {
                    padding-left: 0 !important;
                }
            }
        </style>

        <script {!! nonce_attr() !!}>
            $(document).ready(function() {
                const sidebar = document.getElementById('sidebar');
                const openButton = document.getElementById('open-sidebar');
                const closeButton = document.getElementById('close-sidebar');
                const desktopSidebar = document.getElementById('desktop-sidebar');
                const mainContent = document.getElementById('main-content');
                const desktopToggleButton = document.getElementById('toggle-desktop-sidebar');
                const desktopToggleIcon = desktopToggleButton ? desktopToggleButton.querySelector('[data-icon]') : null;
                const desktopToggleLabel = desktopToggleButton ? desktopToggleButton.querySelector('[data-sidebar-toggle-label]') : null;
                const openSidebarText = desktopToggleButton ? desktopToggleButton.getAttribute('data-open-text') : '';
                const closeSidebarText = desktopToggleButton ? desktopToggleButton.getAttribute('data-close-text') : '';
                const desktopSidebarStateKey = 'adminDesktopSidebarState';

                function toggleMenu() {
                    const isOpen = sidebar.getAttribute('data-state') === 'open';
                    if (isOpen) {
                        $('#sidebar').hide();
                        sidebar.setAttribute('data-state', 'closed');
                    } else {
                        $('#sidebar').show();
                        sidebar.setAttribute('data-state', 'open');
                    }
                }

                openButton.addEventListener('click', toggleMenu);
                closeButton.addEventListener('click', toggleMenu);

                const setDesktopSidebarState = function(isOpen) {
                    if (!desktopSidebar || !mainContent || !desktopToggleButton) {
                        return;
                    }

                    const nextState = isOpen ? 'open' : 'closed';
                    desktopSidebar.setAttribute('data-state', nextState);
                    desktopSidebar.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                    mainContent.setAttribute('data-sidebar-state', nextState);
                    desktopToggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                    if (desktopToggleIcon) {
                        if (isOpen) {
                            desktopToggleIcon.classList.remove('rotate-180');
                        } else {
                            desktopToggleIcon.classList.add('rotate-180');
                        }
                    }

                    if (desktopToggleLabel) {
                        desktopToggleLabel.textContent = isOpen ? closeSidebarText : openSidebarText;
                    }

                    if (typeof window !== 'undefined' && window.localStorage) {
                        try {
                            window.localStorage.setItem(desktopSidebarStateKey, nextState);
                        } catch (error) {
                            // Ignored — localStorage may be unavailable (e.g. private browsing)
                        }
                    }
                };

                if (desktopToggleButton && desktopSidebar && mainContent) {
                    let storedDesktopState = null;

                    if (typeof window !== 'undefined' && window.localStorage) {
                        try {
                            storedDesktopState = window.localStorage.getItem(desktopSidebarStateKey);
                        } catch (error) {
                            storedDesktopState = null;
                        }
                    }

                    setDesktopSidebarState(storedDesktopState !== 'closed');

                    desktopToggleButton.addEventListener('click', function() {
                        const isOpen = desktopSidebar.getAttribute('data-state') === 'open';
                        setDesktopSidebarState(!isOpen);
                    });

                    $(window).on('resize', function() {
                        if (window.innerWidth < 1024) {
                            setDesktopSidebarState(true);
                        }
                    });
                }

                $('[data-collapse-trigger]').each(function() {
                    const $trigger = $(this);
                    const $container = $trigger.closest('[data-collapse-container]');
                    const contentId = $trigger.attr('aria-controls');
                    let $content = $container.find('[data-collapse-content]');

                    if (contentId) {
                        $content = $content.filter(function() {
                            return $(this).attr('id') === contentId;
                        });
                    }
                    const $icon = $trigger.find('[data-collapse-icon]');
                    const initialOpen = $container.attr('data-collapse-state') === 'open';

                    const setState = function(isOpen) {
                        if ($content.length) {
                            if (isOpen) {
                                $content.removeClass('hidden');
                            } else {
                                $content.addClass('hidden');
                            }
                        }

                        $container.attr('data-collapse-state', isOpen ? 'open' : 'closed');
                        $trigger.attr('aria-expanded', isOpen ? 'true' : 'false');

                        if ($icon.length) {
                            if (isOpen) {
                                $icon.addClass('rotate-180');
                            } else {
                                $icon.removeClass('rotate-180');
                            }
                        }
                    };

                    setState(initialOpen);

                    $trigger.on('click', function() {
                        const isOpen = $container.attr('data-collapse-state') === 'open';
                        setState(!isOpen);
                    });
                });
            });
        </script>

        {{ isset($head) ? $head : '' }}
    </x-slot>

    <x-slot name="footer"></x-slot>

    <div>
        <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
        <div data-state="closed" id="sidebar" class="relative z-50 hidden" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/80 dark:bg-black/70" aria-hidden="true"></div>

            <div class="fixed inset-0 flex">
                <div class="relative mr-16 flex w-full max-w-xs flex-1">
                    <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button id="close-sidebar" type="button" class="-m-2.5 p-2.5">
                            <span class="sr-only">{{ __('messages.close_sidebar') }}</span>
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Sidebar component, swap this element with another sidebar if you like -->
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4 text-gray-900 ring-1 ring-black/10 dark:bg-gray-950 dark:text-gray-100 dark:ring-white/10">

                        @include('layouts.navigation')

                    </div>
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div id="desktop-sidebar" data-state="open" class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col lg:transform lg:transition-transform lg:duration-300" aria-hidden="false">
            <!-- Sidebar component, swap this element with another sidebar if you like -->
            <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6 pb-4 dark:border-gray-800 dark:bg-gray-950">

                @include('layouts.navigation')

            </div>
        </div>

        <div id="main-content" data-sidebar-state="open" class="lg:pl-72 flex flex-col min-h-screen">
            <div
                class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm transition-colors duration-200 dark:border-gray-800 dark:bg-gray-900 dark:shadow-black/30 sm:gap-x-6 sm:px-6 lg:px-8">
                <button id="open-sidebar" type="button" class="-m-2.5 p-2.5 text-gray-700 transition-colors duration-150 hover:text-gray-900 lg:hidden dark:text-gray-200 dark:hover:text-gray-50">
                    <span class="sr-only">{{ __('messages.open_sidebar') }}</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Separator -->
                <div class="h-6 w-px bg-gray-900/10 lg:hidden dark:bg-white/10" aria-hidden="true"></div>

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
                        <button id="toggle-desktop-sidebar" type="button" aria-expanded="true" data-open-text="{{ __('messages.open_sidebar') }}" data-close-text="{{ __('messages.close_sidebar') }}"
                            class="hidden lg:inline-flex items-center justify-center rounded-md border border-gray-200 bg-white p-2 text-gray-600 transition-colors duration-150 hover:border-gray-300 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-gray-600 dark:hover:text-gray-100">
                            <span data-sidebar-toggle-label class="sr-only">{{ __('messages.close_sidebar') }}</span>
                            <svg data-icon class="h-5 w-5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25 9 12l6.75 6.75" />
                            </svg>
                        </button>

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

                        <!-- Separator -->
                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-900/10 dark:lg:bg-white/10" aria-hidden="true"></div>


                        <div class="flex items-center">
                            <label for="theme-preference" class="sr-only">{{ __('Theme') }}</label>
                            <select id="theme-preference" data-theme-select
                                class="rounded-md border border-gray-300 bg-white px-2.5 py-1 text-sm font-medium text-gray-700 shadow-sm transition-colors duration-150 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-indigo-400">
                                <option value="system">{{ __('System') }}</option>
                                <option value="light">{{ __('Light') }}</option>
                                <option value="dark">{{ __('Dark') }}</option>
                            </select>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="sm:flex sm:items-center sm:ms-6">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    @php
                                        $authenticatedUser = Auth::user();
                                        $userName = trim((string) data_get($authenticatedUser, 'name', ''));
                                        $userEmail = trim((string) data_get($authenticatedUser, 'email', ''));
                                        $displayName = $userName !== '' ? $userName : $userEmail;
                                    @endphp
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 dark:bg-gray-800 dark:text-gray-200 dark:hover:text-gray-100">
                                        <div>{{ $displayName }}</div>

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
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('messages.manage_account') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                            {{ __('messages.log_out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>

                    </div>
                </div>
            </div>

            <main class="pb-10">
                <div class="px-4 sm:px-6 lg:px-8">

                    @if ($errors->any())
                    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
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

            <div class="mt-auto pb-8 px-8 text-sm text-gray-500">
                {!! str_replace(':link', '<a href="https://www.eventschedule.com" class="hover:underline" target="_blank">EventSchedule</a>', __('messages.powered_by_eventschedule')) !!}
                @if (config('app.hosted'))
                    • {!! str_replace(':email', '<a href="mailto:contact@eventschedule.com?subject=Feedback" class="hover:underline">contact@eventschedule.com</a>', __('messages.questions_or_suggestions')) !!}
                @else
                    • <a href="{{ config('self-update.repository_types.github.repository_url') }}" target="_blank" class="hover:underline">{{ config('self-update.version_installed') }}</a>
                @endif
            </div>

        </div>
    </div>

</x-app-layout>
