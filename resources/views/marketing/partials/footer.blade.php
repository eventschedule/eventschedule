<footer class="bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <!-- Product -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Product</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ marketing_url('/features') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Features
                        </a>
                    </li>
                    <li>
                        <a href="{{ marketing_url('/pricing') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Pricing
                        </a>
                    </li>
                    <li>
                        <a href="{{ marketing_url('/ticketing') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Ticketing
                        </a>
                    </li>
                    <li>
                        <a href="{{ marketing_url('/integrations') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Integrations
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Company</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ marketing_url('/about') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            About
                        </a>
                    </li>
                    <li>
                        <a href="https://blog.eventschedule.com" target="_blank" rel="noopener" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Blog
                        </a>
                    </li>
                    <li>
                        <a href="https://invoiceninja.com" target="_blank" rel="noopener" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Invoice Ninja
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Legal</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ marketing_url('/privacy') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Privacy Policy
                        </a>
                    </li>
                    <li>
                        <a href="{{ marketing_url('/terms-of-service') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Terms of Service
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Get Started -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Get Started</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ route('sign_up') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Register
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('login') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                            Sign In
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-2">
                    <img class="h-6 w-auto dark:hidden" src="{{ url('images/dark_logo.png') }}" alt="Event Schedule" />
                    <img class="h-6 w-auto hidden dark:block" src="{{ url('images/light_logo.png') }}" alt="Event Schedule" />
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm">
                    &copy; {{ date('Y') }} Event Schedule. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</footer>
