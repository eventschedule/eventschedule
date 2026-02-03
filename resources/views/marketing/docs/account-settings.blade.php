<x-marketing-layout>
    <x-slot name="title">Account Settings Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Account Settings</x-slot>
    <x-slot name="description">Learn how to manage your profile, payment methods, API access, and Google integrations in Event Schedule.</x-slot>
    <x-slot name="keywords">account settings, profile settings, payment methods, api settings, google calendar, stripe, event schedule</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Account Settings" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Account Settings</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Manage your profile, payment methods, API access, and connected services from the Settings page.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#profile" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Profile Information</a>
                        <a href="#payments" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Payment Methods</a>
                        <a href="#api" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">API Settings</a>
                        <a href="#google" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Google Settings</a>
                        <a href="#password" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Update Password</a>
                        <a href="#delete-account" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Delete Account</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Profile Information -->
                        <section id="profile" class="doc-section">
                            <h2 class="doc-heading">Profile Information</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Access your account settings by clicking <strong>Settings</strong> in the main navigation. The Profile Information section lets you manage your personal details:
                            </p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Setting</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Name</span></td>
                                            <td>Your display name, shown in team member lists and notifications</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Email</span></td>
                                            <td>Your login email address, used for account recovery and notifications</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Timezone</span></td>
                                            <td>Controls how dates and times are displayed throughout the app. Event times are stored in UTC and converted to your timezone for display.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Language</span></td>
                                            <td>Sets the interface language. Nine languages are supported: English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, and Arabic.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">24-Hour Time</span></td>
                                            <td>Toggle between 12-hour (AM/PM) and 24-hour time format across the app</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Profile Image</span></td>
                                            <td>Upload a profile picture. This is used for your user avatar and may appear in team member lists.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Payment Methods -->
                        <section id="payments" class="doc-section">
                            <h2 class="doc-heading">Payment Methods</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                To sell tickets, you need to connect a payment method. Event Schedule supports three options:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Stripe</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Connect your Stripe account using Stripe Connect. Payments go directly to your Stripe account with standard processing fees (2.9% + $0.30). Click "Link Stripe Account" to begin the connection process.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Invoice Ninja</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Connect your Invoice Ninja account for payment processing. Provide your Invoice Ninja URL and API token to link the accounts. This is especially useful for selfhosted deployments.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment URL</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Provide a custom payment URL (e.g., your own checkout page, PayPal.me link, or any external payment system). Buyers will be redirected to this URL to complete payment.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>You only need to configure one payment method. Stripe provides the most integrated experience with automatic ticket delivery and QR codes.</p>
                            </div>
                        </section>

                        <!-- API Settings -->
                        <section id="api" class="doc-section">
                            <h2 class="doc-heading">API Settings</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The API settings section lets you enable programmatic access to your schedules and events (Pro plan required):
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Enable API</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Toggle API access on or off. When enabled, you can use the REST API to read and manage your schedules and events programmatically.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">API Key</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your secret API key is used to authenticate requests. Click "Show API Key" and enter your password to reveal it. Keep this key confidential and never share it publicly.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-warning">
                                <div class="doc-callout-title">Important</div>
                                <p>Your API key grants full access to your account. If you suspect it has been compromised, disable and re-enable the API to generate a new key.</p>
                            </div>
                        </section>

                        <!-- Google Settings -->
                        <section id="google" class="doc-section">
                            <h2 class="doc-heading">Google Settings</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Connect your Google account to enable Google Calendar sync and other Google integrations:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Google Account</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Link your Google account to enable login with Google and calendar sync features. Click "Connect Google Account" to authorize access.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Google Calendar</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Once your Google account is connected, you can enable two-way sync between Event Schedule and Google Calendar. Events created in either platform are automatically synced to the other.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Google Calendar sync is configured per schedule in the schedule settings. Connect your Google account here first, then enable sync on each schedule you want to keep in sync.</p>
                            </div>
                        </section>

                        <!-- Update Password -->
                        <section id="password" class="doc-section">
                            <h2 class="doc-heading">Update Password</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Change your account password by entering your current password and choosing a new one. Passwords must be at least 8 characters long.
                            </p>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                If you signed up using Google or Facebook login, you can set a password here to enable email/password login as an alternative.
                            </p>
                        </section>

                        <!-- Delete Account -->
                        <section id="delete-account" class="doc-section">
                            <h2 class="doc-heading">Delete Account</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Permanently delete your account and all associated data. This action:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Removes your user account and profile</li>
                                <li>Deletes all schedules you own</li>
                                <li>Removes all events, tickets, and sales data</li>
                                <li>Disconnects any linked services (Stripe, Google, etc.)</li>
                            </ul>

                            <div class="doc-callout doc-callout-warning">
                                <div class="doc-callout-title">Warning</div>
                                <p>Account deletion is permanent and cannot be undone. You will be asked to enter your password to confirm the deletion.</p>
                            </div>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.getting_started') }}" class="text-cyan-400 hover:text-cyan-300">Getting Started</a> - Set up your first schedule</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Configure ticketing after connecting a payment method</li>
                                <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Advanced Schedule Settings</a> - Configure calendar sync and integrations per schedule</li>
                                <li><a href="{{ route('marketing.docs.developer.api') }}" class="text-cyan-400 hover:text-cyan-300">API Reference</a> - Full API documentation for developers</li>
                            </ul>
                        </section>

                        @include('marketing.docs.partials.navigation')
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
