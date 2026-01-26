<header class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="{{ marketing_url('/') }}" class="flex items-center space-x-2">
                <img class="h-8 w-auto dark:hidden" src="{{ url('images/dark_logo.png') }}" alt="Event Schedule" />
                <img class="h-8 w-auto hidden dark:block" src="{{ url('images/light_logo.png') }}" alt="Event Schedule" />
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ marketing_url('/features') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors {{ request()->is('*/features') || request()->is('features') ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }}">
                    Features
                </a>
                <a href="{{ marketing_url('/pricing') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors {{ request()->is('*/pricing') || request()->is('pricing') ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }}">
                    Pricing
                </a>
                <a href="{{ marketing_url('/integrations') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors {{ request()->is('*/integrations') || request()->is('integrations') ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }}">
                    Integrations
                </a>
                <a href="{{ marketing_url('/selfhost') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors {{ request()->is('*/selfhost') || request()->is('selfhost') ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }}">
                    Selfhost
                </a>
                <a href="{{ marketing_url('/docs') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors {{ request()->is('*/docs*') || request()->is('docs*') ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }}">
                    Docs
                </a>
                <a href="{{ marketing_url('/about') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors {{ request()->is('*/about') || request()->is('about') ? 'text-blue-600 dark:text-blue-400 font-medium' : '' }}">
                    About
                </a>
            </div>

            <!-- Right side buttons -->
            <div class="flex items-center space-x-4">
                <!-- Auth buttons -->
                <div class="hidden sm:flex items-center space-x-6">
                    @auth
                        @if(auth()->user()->email === \App\Services\DemoService::DEMO_EMAIL)
                            <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Sign In
                            </a>
                            <a href="{{ route('sign_up') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Get Started
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('sign_up') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Get Started
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <button
                    id="mobile-menu-button"
                    onclick="toggleMobileMenu()"
                    class="md:hidden p-2 text-gray-600 dark:text-gray-300"
                    aria-label="Toggle menu"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                <a href="{{ marketing_url('/integrations') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    Integrations
                </a>
                <a href="{{ marketing_url('/selfhost') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    Selfhost
                </a>
                <a href="{{ marketing_url('/docs') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    Docs
                </a>
                <a href="{{ marketing_url('/about') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                    About
                </a>
                <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex flex-col space-y-3">
                    @auth
                        @if(auth()->user()->email === \App\Services\DemoService::DEMO_EMAIL)
                            <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                                Sign In
                            </a>
                            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Get Started
                            </a>
                        @else
                            <a href="{{ route('home') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                                Dashboard
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white py-2">
                            Sign In
                        </a>
                        <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    const button = document.getElementById('mobile-menu-button');
    const isHidden = menu.classList.toggle('hidden');
    button.setAttribute('aria-expanded', !isHidden);
}
</script>
