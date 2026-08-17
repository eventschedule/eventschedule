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
    @php
        // One row shape for every link, so the dialog reads as a list rather than as the three
        // clashing treatments it used to carry (a blue text link, a grey star pill, and another
        // blue text link, all in one line).
        $aboutRow = 'flex items-center gap-3 px-6 py-3 text-sm text-gray-700 dark:text-gray-300'
            .' hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors no-underline'
            .' focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)]';
        $aboutIcon = 'h-5 w-5 flex-shrink-0 text-gray-400 dark:text-gray-500';
        // Terms and privacy are hosted-only on purpose. marketing_url() falls back to
        // eventschedule.com, and those documents do not govern somebody else's selfhost install.
        // Never route('marketing.*') here - those routes are nexus-gated.
        $aboutHosted = config('app.hosted');
    @endphp

    <x-modal name="about-app" maxWidth="md" :ariaLabel="__('messages.about')">
        <div class="relative">
            {{-- Floating rather than sitting in a titled header bar: the wordmark below IS the
                 title. `end`, not `right`, so it crosses over in Hebrew and Arabic. --}}
            <button type="button" class="js-close-modal absolute top-4 end-4 z-10 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                data-modal="about-app" aria-label="{{ __('messages.close') }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Brand block. The wordmark, not images/logo.png: these two config values are what
                 a selfhost operator overrides through APP_LOGO_DARK / APP_LOGO_LIGHT to
                 rebrand, and they swap with the theme so a dark mark never lands on the dark
                 modal surface. Same pair as components/application-logo.blade.php, inlined
                 because that component wraps itself in a p-6 that does not fit here. The mark
                 carries the app name, so there is no separate name line. --}}
            <div class="px-6 pt-8 pb-6 text-center">
                <img class="h-10 w-auto mx-auto dark:hidden" src="{{ asset(ltrim(config('app.logo_dark'), '/')) }}" alt="{{ config('app.name') }}">
                <img class="h-10 w-auto mx-auto hidden dark:block" src="{{ asset(ltrim(config('app.logo_light'), '/')) }}" alt="{{ config('app.name') }}">

                {{-- bdi + dir=ltr: a version is a left-to-right token even on an RTL page, and
                     without this the leading "v" jumps to the wrong end. --}}
                <div class="mt-4">
                    <bdi dir="ltr">
                        <a href="https://github.com/eventschedule/eventschedule/releases" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-xs font-medium text-gray-600 dark:text-gray-400 no-underline transition-colors hover:bg-gray-200 hover:text-gray-800 dark:hover:bg-gray-700 dark:hover:text-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                            {{ config('self-update.version_installed') }}
                        </a>
                    </bdi>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700">
                {{-- User guide. HelpUtils::getDocUrl() is section-aware, so this lands on the
                     docs page for whatever the user has open behind the dialog. --}}
                <a href="{{ \App\Utils\HelpUtils::getDocUrl() }}" target="_blank" rel="noopener noreferrer" class="{{ $aboutRow }}">
                    <svg class="{{ $aboutIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>{{ __('messages.help') }}</span>
                    <svg class="ms-auto h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5zm7.25-.75a.75.75 0 01.75-.75h3.5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0V6.31l-5.47 5.47a.75.75 0 01-1.06-1.06l5.47-5.47H12.25a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                    </svg>
                </a>

                {{-- Source. The star count is a view-composer value cached for an hour
                     (AppServiceProvider -> GitHubUtils::getStars); when the fetch has failed or
                     not run yet the row simply carries no count. --}}
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="{{ $aboutRow }}">
                    <svg class="{{ $aboutIcon }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                    <span>GitHub</span>
                    <span class="ms-auto flex items-center gap-3">
                        @if (isset($githubStars) && $githubStars)
                        <bdi dir="ltr" class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                            <svg class="h-3.5 w-3.5 text-yellow-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            {{ number_format($githubStars) }}
                        </bdi>
                        @endif
                        <svg class="h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5zm7.25-.75a.75.75 0 01.75-.75h3.5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0V6.31l-5.47 5.47a.75.75 0 01-1.06-1.06l5.47-5.47H12.25a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </a>

                {{-- Contact. Selfhost gets this too now: the address is configurable, and the
                     sidebar's own Contact button has always offered it there. --}}
                <a href="mailto:{{ config('app.support_email') }}?subject=Feedback" class="{{ $aboutRow }}">
                    <svg class="{{ $aboutIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>{{ __('messages.contact_us') }}</span>
                    <bdi dir="ltr" class="ms-auto truncate text-xs text-gray-500 dark:text-gray-400">{{ config('app.support_email') }}</bdi>
                </a>

                @if ($aboutHosted)
                <a href="{{ marketing_url('/terms-of-service') }}" target="_blank" rel="noopener noreferrer" class="{{ $aboutRow }}">
                    <svg class="{{ $aboutIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('messages.terms_of_service') }}</span>
                    <svg class="ms-auto h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5zm7.25-.75a.75.75 0 01.75-.75h3.5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0V6.31l-5.47 5.47a.75.75 0 01-1.06-1.06l5.47-5.47H12.25a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                    </svg>
                </a>

                <a href="{{ marketing_url('/privacy') }}" target="_blank" rel="noopener noreferrer" class="{{ $aboutRow }}">
                    <svg class="{{ $aboutIcon }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    <span>{{ __('messages.privacy_policy') }}</span>
                    <svg class="ms-auto h-4 w-4 flex-shrink-0 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5zm7.25-.75a.75.75 0 01.75-.75h3.5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0V6.31l-5.47 5.47a.75.75 0 01-1.06-1.06l5.47-5.47H12.25a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                    </svg>
                </a>
                @endif
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                {{-- bdi, like the version badge: "©" is bidi-neutral, so on an RTL page the
                     algorithm resolves it to the far end and the line renders as
                     "Event Schedule 2026 ©". --}}
                <bdi dir="ltr" class="text-xs text-gray-500 dark:text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}</bdi>
                {{-- A plain button carrying x-secondary-link's classes: this is a standalone
                     action, so it takes that sizing rather than x-secondary-button's small
                     utility one, and it cannot BE the link component because it dismisses a
                     dialog rather than navigating. The old inline bg-gray-100 was nearly the
                     same value as the modal surface in the light palettes, so it read as
                     unstyled text. --}}
                <button type="button" data-modal="about-app"
                    class="js-close-modal ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
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

        // Dismisses a dialog by name. components/modal.blade.php already listens for
        // close-modal on window, so this needs no Alpine directive on the button - which is
        // what the About buttons used to carry, against the repo's no-Alpine rule. The shell
        // itself stays Alpine; it is shared with four other call sites.
        document.addEventListener('click', function(e) {
            var closeBtn = e.target.closest('.js-close-modal');
            if (closeBtn) {
                window.dispatchEvent(new CustomEvent('close-modal', {
                    detail: closeBtn.getAttribute('data-modal')
                }));
            }
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
