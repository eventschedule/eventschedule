<x-marketing-layout>
    <x-slot name="title">Stripe Integration Documentation - Event Schedule</x-slot>
    <x-slot name="description">Set up Stripe payments for Event Schedule based on your deployment type: selfhosted or SaaS operator.</x-slot>
    <x-slot name="keywords">stripe integration, stripe connect, direct payments, laravel cashier, payment processing, ticket sales, subscription billing, selfhosted, invoice ninja</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-16 overflow-hidden border-b border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-purple-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm mb-6">
                <a href="{{ route('marketing.docs') }}" class="text-gray-400 hover:text-white transition-colors">Docs</a>
                <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-white">Stripe Integration</span>
            </nav>

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-500/20">
                    <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">Stripe Integration Setup</h1>
            </div>
            <p class="text-lg text-gray-400 max-w-3xl">
                Choose the setup guide that matches how you're using Event Schedule.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#choose-setup" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Choose Your Setup</a>
                        <a href="#selfhosted-users" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors pl-6">→ Selfhosted Users</a>
                        <a href="#saas-operators" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors pl-6">→ SaaS Operators</a>
                        <a href="#testing" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Testing</a>
                        <a href="#troubleshooting" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Troubleshooting</a>
                        <a href="#security" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Security</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-300 mb-6">Event Schedule supports Stripe for payment processing, but the setup varies depending on how you're using the platform. This guide is organized by user type to help you find exactly what you need.</p>

                            <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4 mb-6">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-green-300 font-medium mb-1">Recommended: Use Invoice Ninja with Stripe</p>
                                        <p class="text-gray-400 text-sm">For the best ticket selling experience, we recommend using <a href="https://invoiceninja.com" target="_blank" class="text-green-400 hover:text-green-300">Invoice Ninja</a> alongside Stripe. Invoice Ninja provides additional features like professional invoicing, payment reminders, and detailed financial reporting that complement Stripe's payment processing.</p>
                                    </div>
                                </div>
                            </div>

                        </section>

                        <!-- Choose Your Setup -->
                        <section id="choose-setup" class="doc-section">
                            <h2 class="doc-heading">Choose Your Setup</h2>
                            <p class="text-gray-300 mb-6">Find your setup type and follow the corresponding guide:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>You are...</th>
                                            <th>Money flows to...</th>
                                            <th>Follow this guide</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Running your <strong class="text-white">own Event Schedule instance</strong> for your organization</td>
                                            <td>Your single Stripe account</td>
                                            <td><a href="#selfhosted-users" class="text-violet-400 hover:text-violet-300">Selfhosted Users</a></td>
                                        </tr>
                                        <tr>
                                            <td>Running a <strong class="text-white">white-label SaaS</strong> platform</td>
                                            <td>Your customers' Stripe accounts + your account for subscriptions</td>
                                            <td><a href="#saas-operators" class="text-violet-400 hover:text-violet-300">SaaS Operators</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="bg-white/5 rounded-xl p-6 border border-white/10 mb-6">
                                <h3 class="text-lg font-semibold text-white mb-4">Decision Tree</h3>
                                <div class="text-gray-300 space-y-3 text-sm">
                                    <p><strong class="text-white">Q: Will multiple people create events and need their own payment accounts?</strong></p>
                                    <p class="pl-4">→ Yes: <a href="#saas-operators" class="text-violet-400 hover:text-violet-300">SaaS Operators</a></p>
                                    <p class="pl-4">→ No: <a href="#selfhosted-users" class="text-violet-400 hover:text-violet-300">Selfhosted Users</a></p>
                                </div>
                            </div>
                        </section>

                        <!-- Selfhosted Users -->
                        <section id="selfhosted-users" class="doc-section">
                            <h2 class="doc-heading">For Selfhosted Users</h2>
                            <p class="text-gray-300 mb-6">If you're running your own Event Schedule instance for your organization, venue, or community, all ticket payments go to a single Stripe account that you control.</p>

                            <div class="bg-violet-500/10 border border-violet-500/20 rounded-xl p-4 mb-6">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-violet-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-violet-300 font-medium mb-1">This guide is for you if...</p>
                                        <p class="text-gray-400 text-sm">You want all ticket revenue from all events on your instance to go to one Stripe account. Event creators don't need their own Stripe accounts.</p>
                                    </div>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">1. Get Your Stripe API Keys</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to the <a href="https://dashboard.stripe.com/" target="_blank" class="text-violet-400 hover:text-violet-300">Stripe Dashboard</a></li>
                                <li>Navigate to <strong class="text-white">Developers</strong> → <strong class="text-white">API keys</strong></li>
                                <li>Note your <strong class="text-white">Publishable key</strong> and <strong class="text-white">Secret key</strong></li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">2. Configure Environment Variables</h3>
                            <p class="text-gray-300 mb-4">Add these to your <code class="doc-inline-code">.env</code> file:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Stripe Direct Payments (selfhosted)</span>
<span class="code-variable">STRIPE_PLATFORM_KEY</span>=<span class="code-string">pk_live_your_publishable_key</span>
<span class="code-variable">STRIPE_PLATFORM_SECRET</span>=<span class="code-string">sk_live_your_secret_key</span>
<span class="code-variable">STRIPE_PLATFORM_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_webhook_secret</span></code></pre>
                            </div>

                            <ul class="doc-list mb-6">
                                <li><code class="doc-inline-code">STRIPE_PLATFORM_KEY</code>: Your publishable key (starts with <code class="doc-inline-code">pk_live_</code> or <code class="doc-inline-code">pk_test_</code>)</li>
                                <li><code class="doc-inline-code">STRIPE_PLATFORM_SECRET</code>: Your secret key (starts with <code class="doc-inline-code">sk_live_</code> or <code class="doc-inline-code">sk_test_</code>)</li>
                                <li><code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code>: Webhook signing secret (next step)</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-white mb-4">3. Set Up Webhooks</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>In Stripe Dashboard, go to <strong class="text-white">Developers</strong> → <strong class="text-white">Webhooks</strong></li>
                                <li>Click <strong class="text-white">Add endpoint</strong></li>
                                <li>Set URL to: <code class="doc-inline-code">https://yourdomain.com/stripe/webhook</code></li>
                                <li>Select event: <code class="doc-inline-code">checkout.session.completed</code></li>
                                <li>Save and copy the <strong class="text-white">Signing secret</strong> to <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code></li>
                            </ol>

                            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 mb-6">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-amber-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div>
                                        <p class="text-amber-300 font-medium mb-1">Important: Webhook Event</p>
                                        <p class="text-gray-400 text-sm">Make sure to select <code class="doc-inline-code">checkout.session.completed</code>—this is different from SaaS setups which use <code class="doc-inline-code">payment_intent.succeeded</code>.</p>
                                    </div>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">4. Enable Stripe for Events</h3>
                            <p class="text-gray-300 mb-6">Once configured, event creators can select "Stripe" as the payment method when creating events with tickets. All payments automatically use your platform Stripe account.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">How Checkout Works</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Customer selects tickets and fills out the checkout form</li>
                                <li>Event Schedule creates a Stripe Checkout Session</li>
                                <li>Customer completes payment on Stripe's hosted page</li>
                                <li>Webhook confirms payment and marks the sale as paid</li>
                                <li>Customer receives their tickets</li>
                            </ol>
                        </section>

                        <!-- SaaS Operators -->
                        <section id="saas-operators" class="doc-section">
                            <h2 class="doc-heading">For SaaS Operators</h2>
                            <p class="text-gray-300 mb-6">If you're running your own white-label SaaS platform (like eventschedule.com but with your own branding), you need two Stripe integrations:</p>

                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Stripe Connect</h4>
                                    <p class="text-gray-400 text-sm">So your event creator customers can connect their Stripe accounts and receive ticket payments directly.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Laravel Cashier</h4>
                                    <p class="text-gray-400 text-sm">To charge your customers for Pro subscriptions (the money you make as the platform operator).</p>
                                </div>
                            </div>

                            <!-- Part A: Stripe Connect -->
                            <div class="bg-violet-500/5 border-l-4 border-violet-500 pl-4 mb-6">
                                <h3 class="text-xl font-semibold text-white mb-2">Part A: Stripe Connect (Ticket Sales)</h3>
                                <p class="text-gray-400 text-sm">Allow your event creators to receive payments for their ticket sales</p>
                            </div>

                            <h4 class="text-lg font-semibold text-white mb-4">1. Enable Stripe Connect</h4>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to the <a href="https://dashboard.stripe.com/" target="_blank" class="text-violet-400 hover:text-violet-300">Stripe Dashboard</a></li>
                                <li>Navigate to <strong class="text-white">Settings</strong> → <strong class="text-white">Connect</strong> → <strong class="text-white">Settings</strong></li>
                                <li>Enable Connect for your platform</li>
                                <li>Configure your branding and platform profile</li>
                                <li>Get your API keys from <strong class="text-white">Developers</strong> → <strong class="text-white">API keys</strong></li>
                            </ol>

                            <h4 class="text-lg font-semibold text-white mb-4">2. Environment Configuration</h4>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Stripe Connect (for event creators to receive ticket payments)</span>
<span class="code-variable">STRIPE_KEY</span>=<span class="code-string">sk_live_your_stripe_secret_key</span>
<span class="code-variable">STRIPE_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_connect_webhook_secret</span></code></pre>
                            </div>

                            <ul class="doc-list mb-6">
                                <li><code class="doc-inline-code">STRIPE_KEY</code>: Your platform's Stripe secret key</li>
                                <li><code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code>: Webhook secret for Connect events</li>
                            </ul>

                            <h4 class="text-lg font-semibold text-white mb-4">3. Webhook Configuration</h4>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-white">Developers</strong> → <strong class="text-white">Webhooks</strong></li>
                                <li>Click <strong class="text-white">Add endpoint</strong></li>
                                <li>Set URL to: <code class="doc-inline-code">https://yourdomain.com/stripe/webhook</code></li>
                                <li>Select event: <code class="doc-inline-code">payment_intent.succeeded</code></li>
                                <li>Save and copy the signing secret to <code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code></li>
                            </ol>

                            <h4 class="text-lg font-semibold text-white mb-4">User Onboarding Flow</h4>
                            <p class="text-gray-300 mb-4">Your event creators will:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-white">Profile</strong> → <strong class="text-white">Payment Methods</strong></li>
                                <li>Click <strong class="text-white">"Connect Stripe Account"</strong></li>
                                <li>Complete Stripe's onboarding</li>
                                <li>Return to your platform ready to sell tickets</li>
                            </ol>

                            <h4 class="text-lg font-semibold text-white mb-4">Connect API Endpoints</h4>
                            <div class="overflow-x-auto mb-8">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Endpoint</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /stripe/link</code></td>
                                            <td>Start Stripe Connect onboarding</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /stripe/complete</code></td>
                                            <td>Complete onboarding callback</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /stripe/unlink</code></td>
                                            <td>Disconnect Stripe account</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /stripe/webhook</code></td>
                                            <td>Handle <code class="doc-inline-code">payment_intent.succeeded</code></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Part B: Laravel Cashier -->
                            <div class="bg-purple-500/5 border-l-4 border-purple-500 pl-4 mb-6">
                                <h3 class="text-xl font-semibold text-white mb-2">Part B: Laravel Cashier (Subscription Billing)</h3>
                                <p class="text-gray-400 text-sm">Charge your customers for Pro plan subscriptions</p>
                            </div>

                            <h4 class="text-lg font-semibold text-white mb-4">1. Create Subscription Products</h4>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>In Stripe Dashboard, go to <strong class="text-white">Products</strong> → <strong class="text-white">Add product</strong></li>
                                <li>Create a "Pro Plan" product with:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>Monthly price (e.g., $9.99/month, recurring)</li>
                                        <li>Yearly price (e.g., $99.99/year, recurring)</li>
                                    </ul>
                                </li>
                                <li>Note the <strong class="text-white">Price IDs</strong> (starts with <code class="doc-inline-code">price_</code>)</li>
                            </ol>

                            <h4 class="text-lg font-semibold text-white mb-4">2. Environment Configuration</h4>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Laravel Cashier (for subscription billing)</span>
<span class="code-variable">STRIPE_PLATFORM_KEY</span>=<span class="code-string">pk_live_your_publishable_key</span>
<span class="code-variable">STRIPE_PLATFORM_SECRET</span>=<span class="code-string">sk_live_your_secret_key</span>
<span class="code-variable">STRIPE_PLATFORM_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_subscription_webhook_secret</span>
<span class="code-variable">STRIPE_PRICE_MONTHLY</span>=<span class="code-string">price_monthly_price_id</span>
<span class="code-variable">STRIPE_PRICE_YEARLY</span>=<span class="code-string">price_yearly_price_id</span></code></pre>
                            </div>

                            <ul class="doc-list mb-6">
                                <li><code class="doc-inline-code">STRIPE_PLATFORM_KEY</code>: Publishable key for your platform</li>
                                <li><code class="doc-inline-code">STRIPE_PLATFORM_SECRET</code>: Secret key for your platform</li>
                                <li><code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code>: Webhook secret for subscription events</li>
                                <li><code class="doc-inline-code">STRIPE_PRICE_MONTHLY</code>: Price ID for monthly subscription</li>
                                <li><code class="doc-inline-code">STRIPE_PRICE_YEARLY</code>: Price ID for yearly subscription</li>
                            </ul>

                            <h4 class="text-lg font-semibold text-white mb-4">3. Subscription Webhook</h4>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-white">Developers</strong> → <strong class="text-white">Webhooks</strong></li>
                                <li>Click <strong class="text-white">Add endpoint</strong> (this is a second webhook, separate from Connect)</li>
                                <li>Set URL to: <code class="doc-inline-code">https://yourdomain.com/stripe/subscription-webhook</code></li>
                                <li>Select events:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li><code class="doc-inline-code">customer.subscription.created</code></li>
                                        <li><code class="doc-inline-code">customer.subscription.updated</code></li>
                                        <li><code class="doc-inline-code">customer.subscription.deleted</code></li>
                                        <li><code class="doc-inline-code">customer.subscription.trial_will_end</code></li>
                                        <li><code class="doc-inline-code">invoice.payment_succeeded</code></li>
                                        <li><code class="doc-inline-code">invoice.payment_failed</code></li>
                                    </ul>
                                </li>
                                <li>Save and copy signing secret to <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code></li>
                            </ol>

                            <h4 class="text-lg font-semibold text-white mb-4">4. Customer Portal Setup</h4>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-white">Settings</strong> → <strong class="text-white">Billing</strong> → <strong class="text-white">Customer portal</strong></li>
                                <li>Enable subscription management features:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>Subscription cancellation</li>
                                        <li>Plan switching</li>
                                        <li>Payment method updates</li>
                                    </ul>
                                </li>
                                <li>Customize branding to match your platform</li>
                            </ol>

                            <h4 class="text-lg font-semibold text-white mb-4">Subscription API Endpoints</h4>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Endpoint</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /{subdomain}/subscribe</code></td>
                                            <td>Show subscription page</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /{subdomain}/subscribe</code></td>
                                            <td>Create new subscription</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">GET /{subdomain}/subscription/portal</code></td>
                                            <td>Redirect to Stripe Customer Portal</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /{subdomain}/subscription/cancel</code></td>
                                            <td>Cancel subscription</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /{subdomain}/subscription/resume</code></td>
                                            <td>Resume cancelled subscription</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /{subdomain}/subscription/swap</code></td>
                                            <td>Switch between monthly/yearly</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /stripe/subscription-webhook</code></td>
                                            <td>Handle subscription events</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="bg-violet-500/10 border border-violet-500/20 rounded-xl p-4 mb-6">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-violet-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-violet-300 font-medium mb-1">Architecture Note</p>
                                        <p class="text-gray-400 text-sm">Laravel Cashier uses the <code class="doc-inline-code">Role</code> model (schedule/calendar) as the billable entity, not <code class="doc-inline-code">User</code>. This means each schedule has its own subscription, and users can have multiple schedules with different plans.</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Testing -->
                        <section id="testing" class="doc-section">
                            <h2 class="doc-heading">Testing</h2>

                            <h3 class="text-lg font-semibold text-white mb-4">Test Mode Setup</h3>
                            <p class="text-gray-300 mb-4">For development and testing:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Toggle to <strong class="text-white">"Test mode"</strong> in the Stripe Dashboard</li>
                                <li>Use test API keys (starting with <code class="doc-inline-code">pk_test_</code> and <code class="doc-inline-code">sk_test_</code>)</li>
                                <li>Create test webhook endpoints pointing to your development environment</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">Test Card Numbers</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Card Number</th>
                                            <th>Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">4242 4242 4242 4242</code></td>
                                            <td>Successful payment</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">4000 0000 0000 3220</code></td>
                                            <td>3D Secure required</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">4000 0000 0000 9995</code></td>
                                            <td>Declined (insufficient funds)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">4000 0000 0000 0002</code></td>
                                            <td>Declined (generic)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-gray-400 text-sm mb-6">Use any future expiration date and any 3-digit CVC.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">Testing Webhooks Locally</h3>
                            <p class="text-gray-300 mb-4">Use the <a href="https://stripe.com/docs/stripe-cli" target="_blank" class="text-violet-400 hover:text-violet-300">Stripe CLI</a> to forward webhooks:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Install Stripe CLI</span>
brew install stripe/stripe-cli/stripe

<span class="code-comment"># Login to your Stripe account</span>
stripe login

<span class="code-comment"># Forward webhooks (selfhosted or Connect)</span>
stripe listen --forward-to localhost:8000/stripe/webhook

<span class="code-comment"># For SaaS: Forward subscription webhooks (separate terminal)</span>
stripe listen --forward-to localhost:8000/stripe/subscription-webhook</code></pre>
                            </div>

                            <p class="text-gray-300 mb-4">The CLI displays a webhook signing secret to use in your <code class="doc-inline-code">.env</code> file.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">Trigger Test Events</h3>
                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Test checkout completion (selfhosted)</span>
stripe trigger checkout.session.completed

<span class="code-comment"># Test payment success (Connect)</span>
stripe trigger payment_intent.succeeded

<span class="code-comment"># Test subscription creation</span>
stripe trigger customer.subscription.created</code></pre>
                            </div>
                        </section>

                        <!-- Troubleshooting -->
                        <section id="troubleshooting" class="doc-section">
                            <h2 class="doc-heading">Troubleshooting</h2>

                            <h3 class="text-lg font-semibold text-white mb-4">Common Issues</h3>

                            <div class="space-y-4 mb-8">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">"Stripe account not connected" error</h4>
                                    <p class="text-gray-400 text-sm mb-2"><strong class="text-white">Applies to:</strong> SaaS operators (Connect)</p>
                                    <ul class="doc-list text-sm">
                                        <li>User needs to complete Stripe Connect onboarding</li>
                                        <li>Check if <code class="doc-inline-code">stripe_account_id</code> and <code class="doc-inline-code">stripe_completed_at</code> are set on the user</li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">"Invalid signature" webhook error</h4>
                                    <p class="text-gray-400 text-sm mb-2"><strong class="text-white">Applies to:</strong> All setups</p>
                                    <ul class="doc-list text-sm">
                                        <li>Verify the webhook secret matches the one in Stripe Dashboard</li>
                                        <li>Make sure you're using the correct secret for each endpoint:
                                            <ul class="doc-list mt-2">
                                                <li><code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code> for Connect webhooks</li>
                                                <li><code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code> for selfhosted/subscription webhooks</li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Payments not being recorded</h4>
                                    <p class="text-gray-400 text-sm mb-2"><strong class="text-white">Applies to:</strong> Selfhosted users, SaaS operators</p>
                                    <ul class="doc-list text-sm">
                                        <li>Check webhook logs in Stripe Dashboard → Webhooks → View logs</li>
                                        <li>Verify the webhook endpoint is accessible (not blocked by firewall)</li>
                                        <li>Check <code class="doc-inline-code">storage/logs/laravel.log</code> for errors</li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Subscription not updating after payment</h4>
                                    <p class="text-gray-400 text-sm mb-2"><strong class="text-white">Applies to:</strong> SaaS operators</p>
                                    <ul class="doc-list text-sm">
                                        <li>Verify webhook events are being received</li>
                                        <li>Check that all required subscription events are selected in Stripe</li>
                                        <li>Review <code class="doc-inline-code">storage/logs/laravel.log</code> for errors</li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">"No such price" error</h4>
                                    <p class="text-gray-400 text-sm mb-2"><strong class="text-white">Applies to:</strong> SaaS operators</p>
                                    <ul class="doc-list text-sm">
                                        <li>Verify <code class="doc-inline-code">STRIPE_PRICE_MONTHLY</code> and <code class="doc-inline-code">STRIPE_PRICE_YEARLY</code> contain valid Price IDs</li>
                                        <li>Ensure the prices exist in the same Stripe mode (test vs live)</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">Debugging Logs</h3>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-white">Application:</strong> <code class="doc-inline-code">storage/logs/laravel.log</code></li>
                                <li><strong class="text-white">Stripe API:</strong> Dashboard → Developers → Logs</li>
                                <li><strong class="text-white">Webhooks:</strong> Dashboard → Developers → Webhooks → Select endpoint → View logs</li>
                            </ul>
                        </section>

                        <!-- Security -->
                        <section id="security" class="doc-section">
                            <h2 class="doc-heading">Security</h2>
                            <ol class="doc-list doc-list-numbered">
                                <li><span class="font-semibold text-white">API Key Security:</span> Never expose secret keys in client-side code or version control. Use environment variables.</li>
                                <li><span class="font-semibold text-white">Webhook Verification:</span> Always verify webhook signatures—Event Schedule does this automatically.</li>
                                <li><span class="font-semibold text-white">HTTPS Required:</span> Stripe requires HTTPS for webhook endpoints in production.</li>
                                <li><span class="font-semibold text-white">PCI Compliance:</span> Using Stripe Checkout and Elements keeps you out of PCI scope—card data never touches your server.</li>
                            </ol>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
