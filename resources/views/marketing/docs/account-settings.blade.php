<x-marketing-layout>
    <x-slot name="title">Account Settings Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Account Settings</x-slot>
    <x-slot name="description">Learn how to manage your profile, payment methods, API access, and Google integrations in Event Schedule.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Account Settings Documentation - Event Schedule",
        "description": "Learn how to manage your profile, payment methods, API access, and Google integrations in Event Schedule.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png",
                "width": 712,
                "height": 140
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        },
        "datePublished": "2024-01-01",
        "dateModified": "2026-03-01"
    }
    </script>
    </x-slot>

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
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        <div class="doc-nav-group">
                            <a href="#payments" class="doc-nav-group-header doc-nav-link">Payment Methods <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#stripe" class="doc-nav-link">Stripe</a>
                                <a href="#invoice-ninja" class="doc-nav-link">Invoice Ninja</a>
                                <a href="#payment-url" class="doc-nav-link">Payment URL</a>
                            </div>
                        </div>
                        <a href="#api" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">API Settings</a>
                        <a href="#webhooks" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Webhooks</a>
                        <a href="#google" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Google Settings</a>
                        <a href="#backup" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Backup & Restore</a>
                        <a href="#app-update" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">App Update</a>
                        <a href="#password" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Update Password</a>
                        <a href="#two-factor" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Two-Factor Authentication</a>
                        <a href="#delete-account" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Delete Account</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Profile Information -->
                        <section id="profile" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                                Profile Information
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Access your account settings by clicking <strong>Settings</strong> in the main navigation. The Profile Information section lets you manage your personal details:
                            </p>

                            <x-doc-screenshot id="account-settings--settings" alt="Account settings page" loading="eager" />

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
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Phone Number</span></td>
                                            <td>Your phone number in international format. On the hosted platform, you'll need to verify your phone via SMS before it can be displayed on your schedules. Used for identity verification and displayed on schedules that have "Show phone number" enabled.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Timezone</span></td>
                                            <td>Controls how dates and times are displayed throughout the app. Event times are stored in UTC and converted to your timezone for display.</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Language</span></td>
                                            <td>Sets the interface language. Eleven languages are supported: English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, Arabic, Estonian, and Russian.</td>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                </svg>
                                Payment Methods
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                To sell tickets, you need to connect at least one payment method. Event Schedule supports three options, each available as a tab in your Settings page.
                            </p>

                            <h3 id="stripe" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stripe</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Stripe provides the most integrated payment experience with automatic ticket delivery and QR codes.
                            </p>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hosted Platform</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Click "Link Stripe Account" to connect via Stripe Connect. You'll be redirected to Stripe to authorize the connection. Payments go directly to your Stripe account with standard processing fees (2.9% + $0.30).</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Selfhosted</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Stripe is configured at the server level via environment variables (<code class="text-xs bg-gray-200 dark:bg-white/10 px-1.5 py-0.5 rounded">STRIPE_PLATFORM_KEY</code> and <code class="text-xs bg-gray-200 dark:bg-white/10 px-1.5 py-0.5 rounded">STRIPE_PLATFORM_SECRET</code> in your <code class="text-xs bg-gray-200 dark:bg-white/10 px-1.5 py-0.5 rounded">.env</code> file) rather than through account settings. See the <x-link href="{{ route('marketing.docs.selfhost.stripe') }}">selfhosted Stripe setup guide</x-link> for details.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Unlinking</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">To disconnect your Stripe account, click "Unlink Stripe Account" in the Stripe tab. This does not affect your Stripe account itself, only the connection to Event Schedule.</p>
                                </div>
                            </div>

                            <h3 id="invoice-ninja" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Invoice Ninja</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Connect your <x-link href="https://invoiceninja.com" target="_blank">Invoice Ninja</x-link> account to process payments through Invoice Ninja's payment gateways. This is especially useful for selfhosted deployments.
                            </p>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Connecting</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Provide your Invoice Ninja API Token and API URL to link the accounts. You can find your API token in your Invoice Ninja settings under Settings &rarr; Account Management.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Checkout Modes</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Invoice Ninja supports two checkout modes: invoice mode (ticket selection in Event Schedule) and payment link mode (ticket selection on Invoice Ninja's purchase page with grouped invoices). See <x-link href="{{ route('marketing.docs.tickets') }}#invoiceninja-modes">Invoice Ninja Modes</x-link> for a full comparison.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Unlinking</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">To disconnect Invoice Ninja, click "Unlink Invoice Ninja" in the Invoice Ninja tab.</p>
                                </div>
                            </div>
                            <div class="doc-callout doc-callout-tip mb-6">
                                <div class="doc-callout-title">Tip</div>
                                <p>First-time Invoice Ninja users may be eligible for a free 1-year Pro upgrade. Look for the special offer banner when connecting.</p>
                            </div>

                            <h3 id="payment-url" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment URL</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Use any external payment page by providing its URL. This works with PayPal.me links, custom checkout pages, or any other payment system.
                            </p>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How It Works</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Enter the URL where buyers should complete payment. When a buyer purchases a ticket, they are redirected to this URL to pay.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Unlinking</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">To remove the payment URL, click "Unlink Payment URL" in the Payment URL tab.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>You only need to configure one payment method. Stripe provides the most integrated experience with automatic ticket delivery and QR codes.</p>
                            </div>
                        </section>

                        <!-- API Settings -->
                        <section id="api" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                </svg>
                                API Settings
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The API settings section lets you enable programmatic access to your schedules and events (Pro plan required). See the <a href="{{ route('marketing.docs.developer.api') }}" class="text-cyan-400 hover:text-cyan-300">API Reference</a> for full endpoint documentation.
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
                                <p>Your API key grants full access to your account. If you suspect it has been compromised, disable and re-enable the API to generate a new key. Note that disabling API access immediately invalidates the existing key.</p>
                            </div>
                        </section>

                        <!-- Webhooks -->
                        <section id="webhooks" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                </svg>
                                Webhooks
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Configure webhook endpoints to receive real-time POST notifications when events occur in your schedules (Pro plan required). See the <a href="{{ route('marketing.docs.developer.webhooks') }}" class="text-cyan-400 hover:text-cyan-300">Webhook documentation</a> for payload formats and verification details.
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Add Webhook</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Enter a URL, optional description, and select which event types to subscribe to. A signing secret is generated automatically and shown once after creation.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Manage Webhooks</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Edit URLs, toggle active/inactive, send test pings, view delivery logs, and regenerate signing secrets from the webhook list.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Google Settings -->
                        <section id="google" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                Google Settings
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Connect your Google account to enable Google Calendar sync and other Google integrations:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Google Account</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Link your Google account to enable login with Google. This is independent from Google Calendar sync and can be connected or disconnected separately. To disconnect your Google account, you must first have a password set on your account.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Google Calendar</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Connect Google Calendar to enable two-way sync between Event Schedule and Google Calendar. This is a separate integration from the Google Account login above and can be connected or disconnected independently. Events created in either platform are automatically synced to the other. Once connected, configure sync per schedule in <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="text-cyan-400 hover:text-cyan-300">Integrations</a>.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Google Calendar sync is configured per schedule in the schedule settings. Connect your Google account here first, then enable sync on each schedule you want to keep in sync.</p>
                            </div>
                        </section>

                        <!-- Backup & Restore -->
                        <section id="backup" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                </svg>
                                Backup & Restore
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                Export your schedules as portable backup files or import data from a previous backup. Backups include events, tickets, sales, sub-schedules, newsletters, and optionally images.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Exporting</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                To create a backup, select which schedules to include and whether to include images. The export runs in the background, and you'll receive an email with a download link when it's ready. Download links expire after 7 days.
                            </p>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule Selection</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose one or more schedules to include in the backup. Each schedule's events, tickets, sales, sub-schedules, and newsletters are exported together.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Include Images</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Toggle this on to include event images, schedule logos, and other media in the backup file. This increases the file size but ensures a complete backup.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Importing</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Upload a previously exported .zip backup file to restore your data. After uploading, you'll see a preview showing the schedules, event counts, ticket counts, and sale counts contained in the file. Select which schedules to import and confirm. Imported schedules are created as new schedules and will not overwrite existing data. You'll receive an email notification when the import is complete.
                            </p>

                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">Note</div>
                                <p>On the hosted platform, newsletter recipient emails, segment contacts, and unsubscribe lists are excluded from exports for privacy.</p>
                            </div>

                            <div class="doc-callout doc-callout-warning">
                                <div class="doc-callout-title">Important</div>
                                <p>On selfhosted installations, exports contain personal data including email addresses. Store backup files securely. For migrating between selfhosted instances, consider using <code class="text-xs bg-gray-200 dark:bg-white/10 px-1.5 py-0.5 rounded">mysqldump</code> for a complete database transfer.</p>
                            </div>
                        </section>

                        <!-- App Update (Selfhosted) -->
                        <section id="app-update" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                App Update
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                For selfhosted installations, the App Update section displays your currently installed version alongside the latest available version. If an update is available, you can apply it with a single click.
                            </p>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>This section is only visible on selfhosted installations. Users on the hosted platform (eventschedule.com) are always on the latest version automatically.</p>
                            </div>
                        </section>

                        <!-- Update Password -->
                        <section id="password" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                Update Password
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Change your account password by entering your current password and choosing a new one. Passwords must be at least 8 characters long.
                            </p>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                If you signed up using Google or Facebook login, you can set a password here to enable email/password login as an alternative.
                            </p>
                        </section>

                        <!-- Two-Factor Authentication -->
                        <section id="two-factor" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                                Two-Factor Authentication
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Add an extra layer of security to your account with two-factor authentication (2FA). When enabled, you'll need to enter a code from your authenticator app each time you log in.
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Enabling 2FA</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Click "Enable" and enter your password to begin setup. You'll be shown a QR code to scan with an authenticator app such as Google Authenticator, Authy, or 1Password. After scanning, enter the code displayed in your app to confirm setup.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Recovery Codes</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">After enabling 2FA, you'll be shown a set of recovery codes. Save these codes in a safe place - they allow you to access your account if you lose your authenticator device. Each recovery code can only be used once. You can regenerate recovery codes at any time from this section.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Disabling 2FA</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">To disable two-factor authentication, click "Disable" and enter your password. This removes the 2FA requirement from your account and invalidates any existing recovery codes.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-warning">
                                <div class="doc-callout-title">Important</div>
                                <p>Store your recovery codes securely. If you lose access to your authenticator app and don't have your recovery codes, you will not be able to log in to your account.</p>
                            </div>
                        </section>

                        <!-- Delete Account -->
                        <section id="delete-account" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                                Delete Account
                            </h2>
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

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>The Delete Account option is only available on the hosted platform (eventschedule.com). Selfhosted administrators manage accounts directly at the server level.</p>
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

    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to Configure Your Account Settings",
        "description": "Manage your profile, payment methods, API access, and connected services in Event Schedule.",
        "totalTime": "PT10M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Access Account Settings",
                "text": "Navigate to Settings from the main navigation to access your account configuration.",
                "url": "{{ url(route('marketing.docs.account_settings')) }}#profile"
            },
            {
                "@type": "HowToStep",
                "name": "Update Profile Information",
                "text": "Configure your name, email, timezone, language, and profile image.",
                "url": "{{ url(route('marketing.docs.account_settings')) }}#profile"
            },
            {
                "@type": "HowToStep",
                "name": "Configure Payment Methods",
                "text": "Connect Stripe, Invoice Ninja, or set a custom payment URL to start selling tickets.",
                "url": "{{ url(route('marketing.docs.account_settings')) }}#payments"
            },
            {
                "@type": "HowToStep",
                "name": "Connect Google Services",
                "text": "Link your Google account and enable Google Calendar sync for bidirectional event synchronization.",
                "url": "{{ url(route('marketing.docs.account_settings')) }}#google"
            }
        ]
    }
    </script>
</x-marketing-layout>
