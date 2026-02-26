<header class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
    <nav aria-label="Main navigation" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="{{ marketing_url('/') }}" class="flex items-center space-x-2">
                <picture class="dark:hidden">
                    <source srcset="{{ url('images/dark_logo.webp') }}" type="image/webp">
                    <img class="h-8 w-auto" src="{{ url('images/dark_logo.png') }}" alt="Event Schedule" width="163" height="32" />
                </picture>
                <picture class="hidden dark:block">
                    <source srcset="{{ url('images/light_logo.webp') }}" type="image/webp">
                    <img class="h-8 w-auto" src="{{ url('images/light_logo.png') }}" alt="Event Schedule" width="163" height="32" />
                </picture>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ marketing_url('/features') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600 pb-0.5 {{ request()->is('*/features') || request()->is('features') ? 'text-blue-600 dark:text-blue-400 font-medium !border-blue-600 dark:!border-blue-400 hover:!border-blue-600 dark:hover:!border-blue-400' : '' }}">
                    Features
                </a>
                <a href="{{ marketing_url('/pricing') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600 pb-0.5 {{ request()->is('*/pricing') || request()->is('pricing') ? 'text-blue-600 dark:text-blue-400 font-medium !border-blue-600 dark:!border-blue-400 hover:!border-blue-600 dark:hover:!border-blue-400' : '' }}">
                    Pricing
                </a>
                <a href="{{ marketing_url('/features/online-events') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600 pb-0.5 {{ request()->is('*/features/online-events') || request()->is('features/online-events') ? 'text-blue-600 dark:text-blue-400 font-medium !border-blue-600 dark:!border-blue-400 hover:!border-blue-600 dark:hover:!border-blue-400' : '' }}">
                    Online Events
                </a>
                <a href="{{ marketing_url('/use-cases') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600 pb-0.5 {{ request()->is('*/use-cases') || request()->is('use-cases') || request()->is('*/for-*') || request()->is('for-*') ? 'text-blue-600 dark:text-blue-400 font-medium !border-blue-600 dark:!border-blue-400 hover:!border-blue-600 dark:hover:!border-blue-400' : '' }}">
                    Use Cases
                </a>
                <a href="{{ marketing_url('/selfhost') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600 pb-0.5 {{ request()->is('selfhost') || request()->routeIs('marketing.selfhost') ? 'text-blue-600 dark:text-blue-400 font-medium !border-blue-600 dark:!border-blue-400 hover:!border-blue-600 dark:hover:!border-blue-400' : '' }}">
                    Selfhost
                </a>
                <a href="{{ marketing_url('/docs') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors border-b-2 border-transparent hover:border-gray-300 dark:hover:border-gray-600 pb-0.5 {{ request()->is('*/docs*') || request()->is('docs*') ? 'text-blue-600 dark:text-blue-400 font-medium !border-blue-600 dark:!border-blue-400 hover:!border-blue-600 dark:hover:!border-blue-400' : '' }}">
                    Docs
                </a>
            </div>

            <!-- Right side buttons -->
            <div class="flex items-center space-x-4">
                {{-- TODO: Re-enable search when there are more events worldwide
                <a
                    href="{{ marketing_url('/search') }}"
                    class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
                    aria-label="{{ __('messages.search') }}"
                >
                    <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </a>
                --}}

                <!-- Theme toggle button -->
                <button
                    id="theme-toggle"
                    class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
                    aria-label="Toggle dark mode"
                >
                    <!-- Sun icon (shown in dark mode) -->
                    <svg aria-hidden="true" id="theme-icon-light" class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Moon icon (shown in light mode) -->
                    <svg aria-hidden="true" id="theme-icon-dark" class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Auth buttons -->
                <div class="hidden sm:flex items-center space-x-6">
                    @auth
                        @if(auth()->user()->email === \App\Services\DemoService::DEMO_EMAIL)
                            <a href="{{ app_url('/login') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Sign In
                            </a>
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 text-white text-sm font-medium rounded-lg transition-all shadow-sm shadow-blue-500/25">
                                Get Started
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ app_url('/login') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Sign In
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 text-white text-sm font-medium rounded-lg transition-all shadow-sm shadow-blue-500/25">
                            Get Started
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <button
                    id="mobile-menu-button"
                    class="md:hidden p-2 text-gray-600 dark:text-gray-300"
                    aria-label="Toggle menu"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                >
                    <svg aria-hidden="true" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden md:hidden pb-4">
            <div class="flex flex-col space-y-3">
                <a href="{{ marketing_url('/features') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    Features
                </a>
                <a href="{{ marketing_url('/pricing') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    Pricing
                </a>
                <a href="{{ marketing_url('/features/online-events') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    Online Events
                </a>
                <a href="{{ marketing_url('/use-cases') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    Use Cases
                </a>
                <a href="{{ marketing_url('/selfhost') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    Selfhost
                </a>
                <a href="{{ marketing_url('/docs') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    Docs
                </a>
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex flex-col space-y-3">
                    @auth
                        @if(auth()->user()->email === \App\Services\DemoService::DEMO_EMAIL)
                            <a href="{{ app_url('/login') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                                Sign In
                            </a>
                            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 text-white text-sm font-medium rounded-lg transition-colors">
                                Get Started
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                                Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ app_url('/login') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                            Sign In
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 text-white text-sm font-medium rounded-lg transition-colors">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>

<script {!! nonce_attr() !!}>
document.getElementById('mobile-menu-button').addEventListener('click', function() {
    var menu = document.getElementById('mobile-menu');
    var isHidden = menu.classList.toggle('hidden');
    this.setAttribute('aria-expanded', !isHidden);
});

document.getElementById('theme-toggle').addEventListener('click', function() {
    var isDark = document.documentElement.classList.contains('dark');
    window.setTheme(isDark ? 'light' : 'dark');
});

window.updateThemeButtons = function() {
    // Icons auto-update via dark: CSS classes, no manual update needed
};
</script>
