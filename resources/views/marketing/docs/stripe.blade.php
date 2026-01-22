<x-marketing-layout>
    <x-slot name="title">Stripe Integration Documentation - Event Schedule</x-slot>
    <x-slot name="description">Set up Stripe Connect for ticket sales and Laravel Cashier for subscription billing in Event Schedule.</x-slot>
    <x-slot name="keywords">stripe integration, stripe connect, laravel cashier, payment processing, ticket sales, subscription billing</x-slot>

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
                Set up and configure Stripe for ticket sales via Stripe Connect and subscription billing via Laravel Cashier.
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
                        <a href="#prerequisites" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Prerequisites</a>
                        <a href="#stripe-connect" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Stripe Connect Setup</a>
                        <a href="#laravel-cashier" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Laravel Cashier Setup</a>
                        <a href="#testing" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Testing</a>
                        <a href="#troubleshooting" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Troubleshooting</a>
                        <a href="#security" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Security Considerations</a>
                        <a href="#architecture" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Architecture Notes</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-300 mb-6">Event Schedule uses two separate Stripe integrations:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Integration</th>
                                            <th>Purpose</th>
                                            <th>Money Flows To</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-white">Stripe Connect</span></td>
                                            <td>Enables event creators to sell tickets and receive payments directly</td>
                                            <td>Event creator</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Laravel Cashier</span></td>
                                            <td>Enables SaaS subscription billing for the Pro plan (hosted mode only)</td>
                                            <td>Platform operator</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-300">These integrations use separate Stripe accounts/API keys and can be configured independently.</p>
                        </section>

                        <!-- Prerequisites -->
                        <section id="prerequisites" class="doc-section">
                            <h2 class="doc-heading">Prerequisites</h2>
                            <ol class="doc-list doc-list-numbered">
                                <li>A Stripe account (<a href="https://stripe.com" target="_blank" class="text-violet-400 hover:text-violet-300">https://stripe.com</a>)</li>
                                <li>For Stripe Connect: Event creators need their own Stripe accounts</li>
                                <li>For Laravel Cashier: A Stripe account owned by the platform operator</li>
                            </ol>
                        </section>

                        <!-- Stripe Connect Setup -->
                        <section id="stripe-connect" class="doc-section">
                            <h2 class="doc-heading">Stripe Connect Setup (Ticket Sales)</h2>
                            <p class="text-gray-300 mb-6">Stripe Connect allows event creators to accept payments for ticket sales. Payments go directly to the event creator's connected Stripe account.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">1. Stripe Dashboard Setup</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to the <a href="https://dashboard.stripe.com/" target="_blank" class="text-violet-400 hover:text-violet-300">Stripe Dashboard</a></li>
                                <li>Navigate to <strong class="text-white">Settings</strong> > <strong class="text-white">Connect</strong> > <strong class="text-white">Settings</strong></li>
                                <li>Enable Connect for your platform</li>
                                <li>Configure your branding and platform profile</li>
                                <li>Note your platform's API keys from <strong class="text-white">Developers</strong> > <strong class="text-white">API keys</strong></li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">2. Environment Configuration</h3>
                            <p class="text-gray-300 mb-4">Add the following environment variables to your <code class="doc-inline-code">.env</code> file:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Stripe Connect (for ticket sales)</span>
<span class="code-variable">STRIPE_KEY</span>=<span class="code-string">sk_live_your_stripe_secret_key</span>
<span class="code-variable">STRIPE_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_webhook_secret</span></code></pre>
                            </div>

                            <ul class="doc-list mb-6">
                                <li><code class="doc-inline-code">STRIPE_KEY</code>: Your platform's Stripe secret key (starts with <code class="doc-inline-code">sk_live_</code> or <code class="doc-inline-code">sk_test_</code>)</li>
                                <li><code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code>: The webhook signing secret for verifying webhook events</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-white mb-4">3. Webhook Configuration</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-white">Developers</strong> > <strong class="text-white">Webhooks</strong> in the Stripe Dashboard</li>
                                <li>Click <strong class="text-white">Add endpoint</strong></li>
                                <li>Set the endpoint URL:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>Production: <code class="doc-inline-code">https://yourdomain.com/stripe/webhook</code></li>
                                        <li>Development: Use <a href="https://stripe.com/docs/stripe-cli" target="_blank" class="text-violet-400 hover:text-violet-300">Stripe CLI</a> or ngrok</li>
                                    </ul>
                                </li>
                                <li>Select events to listen to: <code class="doc-inline-code">payment_intent.succeeded</code></li>
                                <li>Save the endpoint and copy the signing secret to <code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code></li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">4. User Onboarding Flow</h3>
                            <p class="text-gray-300 mb-4">When event creators want to accept payments:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>User navigates to their profile settings > Payment Methods</li>
                                <li>User clicks "Connect Stripe Account"</li>
                                <li>User is redirected to Stripe's onboarding flow</li>
                                <li>After completing onboarding, user returns to Event Schedule</li>
                                <li>Their <code class="doc-inline-code">stripe_account_id</code> and <code class="doc-inline-code">stripe_completed_at</code> are saved</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">5. Checkout Process Flow</h3>
                            <p class="text-gray-300 mb-4">When a customer purchases tickets:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Customer selects tickets and fills out checkout form</li>
                                <li>Event Schedule creates a Stripe Checkout Session on the connected account</li>
                                <li>Customer is redirected to Stripe's hosted checkout page</li>
                                <li>After payment, customer returns to the success URL</li>
                                <li>Webhook confirms payment and updates the sale status</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">API Endpoints</h3>
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
                                            <td>Webhook handler for payment events</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Laravel Cashier Setup -->
                        <section id="laravel-cashier" class="doc-section">
                            <h2 class="doc-heading">Laravel Cashier Setup (SaaS Subscriptions)</h2>
                            <p class="text-gray-300 mb-6">Laravel Cashier manages subscription billing for the Pro plan. This is separate from Stripe Connect and uses the platform's own Stripe account.</p>

                            <h3 class="text-lg font-semibold text-white mb-4">1. Stripe Dashboard Setup</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to the <a href="https://dashboard.stripe.com/" target="_blank" class="text-violet-400 hover:text-violet-300">Stripe Dashboard</a></li>
                                <li>Navigate to <strong class="text-white">Products</strong> > <strong class="text-white">Add product</strong></li>
                                <li>Create a product for your Pro plan:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>Name: "Pro Plan" (or your preferred name)</li>
                                        <li>Add pricing: Monthly price (e.g., $9.99/month, recurring)</li>
                                        <li>Add pricing: Yearly price (e.g., $99.99/year, recurring)</li>
                                    </ul>
                                </li>
                                <li>Note the <strong class="text-white">Price IDs</strong> for both pricing options (starts with <code class="doc-inline-code">price_</code>)</li>
                                <li>Get your API keys from <strong class="text-white">Developers</strong> > <strong class="text-white">API keys</strong></li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">2. Environment Configuration</h3>
                            <p class="text-gray-300 mb-4">Add the following environment variables to your <code class="doc-inline-code">.env</code> file:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Stripe Platform (for subscription billing)</span>
<span class="code-variable">STRIPE_PLATFORM_KEY</span>=<span class="code-string">pk_live_your_publishable_key</span>
<span class="code-variable">STRIPE_PLATFORM_SECRET</span>=<span class="code-string">sk_live_your_secret_key</span>
<span class="code-variable">STRIPE_PLATFORM_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_subscription_webhook_secret</span>
<span class="code-variable">STRIPE_PRICE_MONTHLY</span>=<span class="code-string">price_monthly_price_id</span>
<span class="code-variable">STRIPE_PRICE_YEARLY</span>=<span class="code-string">price_yearly_price_id</span></code></pre>
                            </div>

                            <ul class="doc-list mb-6">
                                <li><code class="doc-inline-code">STRIPE_PLATFORM_KEY</code>: Your Stripe publishable key (starts with <code class="doc-inline-code">pk_live_</code> or <code class="doc-inline-code">pk_test_</code>)</li>
                                <li><code class="doc-inline-code">STRIPE_PLATFORM_SECRET</code>: Your Stripe secret key (starts with <code class="doc-inline-code">sk_live_</code> or <code class="doc-inline-code">sk_test_</code>)</li>
                                <li><code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code>: The webhook signing secret for subscription events</li>
                                <li><code class="doc-inline-code">STRIPE_PRICE_MONTHLY</code>: The Price ID for monthly subscription</li>
                                <li><code class="doc-inline-code">STRIPE_PRICE_YEARLY</code>: The Price ID for yearly subscription</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-white mb-4">3. Webhook Configuration</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-white">Developers</strong> > <strong class="text-white">Webhooks</strong> in the Stripe Dashboard</li>
                                <li>Click <strong class="text-white">Add endpoint</strong></li>
                                <li>Set the endpoint URL: <code class="doc-inline-code">https://yourdomain.com/stripe/subscription-webhook</code></li>
                                <li>Select events to listen to:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li><code class="doc-inline-code">customer.subscription.created</code></li>
                                        <li><code class="doc-inline-code">customer.subscription.updated</code></li>
                                        <li><code class="doc-inline-code">customer.subscription.deleted</code></li>
                                        <li><code class="doc-inline-code">customer.subscription.trial_will_end</code></li>
                                        <li><code class="doc-inline-code">invoice.payment_succeeded</code></li>
                                        <li><code class="doc-inline-code">invoice.payment_failed</code></li>
                                    </ul>
                                </li>
                                <li>Save the endpoint and copy the signing secret to <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code></li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">4. Customer Portal Setup</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-white">Settings</strong> > <strong class="text-white">Billing</strong> > <strong class="text-white">Customer portal</strong> in Stripe Dashboard</li>
                                <li>Configure the portal settings:
                                    <ul class="doc-list mt-2 mb-2">
                                        <li>Enable subscription cancellation</li>
                                        <li>Enable plan switching</li>
                                        <li>Enable payment method updates</li>
                                        <li>Customize branding to match your site</li>
                                    </ul>
                                </li>
                                <li>Save your settings</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">5. Subscription Flow</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>User navigates to their schedule's admin page > Plan tab</li>
                                <li>User clicks "Upgrade to Pro"</li>
                                <li>User enters payment details using Stripe Elements</li>
                                <li>Subscription is created with optional trial period</li>
                                <li>User can manage subscription via Stripe Customer Portal</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">API Endpoints</h3>
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
                                            <td>Switch between monthly/yearly plans</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">POST /stripe/subscription-webhook</code></td>
                                            <td>Webhook handler for subscription events</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Testing -->
                        <section id="testing" class="doc-section">
                            <h2 class="doc-heading">Testing</h2>

                            <h3 class="text-lg font-semibold text-white mb-4">Test Mode Configuration</h3>
                            <p class="text-gray-300 mb-4">For development and testing, use Stripe's test mode:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Toggle to "Test mode" in the Stripe Dashboard</li>
                                <li>Use test API keys (starting with <code class="doc-inline-code">pk_test_</code> and <code class="doc-inline-code">sk_test_</code>)</li>
                                <li>Create test webhook endpoints pointing to your development environment</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-white mb-4">Test Card Numbers</h3>
                            <p class="text-gray-300 mb-4">Use these test card numbers in test mode:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Card Number</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">4242 4242 4242 4242</code></td>
                                            <td>Successful payment</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">4000 0000 0000 3220</code></td>
                                            <td>3D Secure authentication required</td>
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
                            <p class="text-gray-300 mb-4">Use the <a href="https://stripe.com/docs/stripe-cli" target="_blank" class="text-violet-400 hover:text-violet-300">Stripe CLI</a> to forward webhooks to your local environment:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Install Stripe CLI</span>
brew install stripe/stripe-cli/stripe

<span class="code-comment"># Login to your Stripe account</span>
stripe login

<span class="code-comment"># Forward Stripe Connect webhooks</span>
stripe listen --forward-to localhost:8000/stripe/webhook

<span class="code-comment"># Forward subscription webhooks (in another terminal)</span>
stripe listen --forward-to localhost:8000/stripe/subscription-webhook</code></pre>
                            </div>

                            <p class="text-gray-300">The CLI will display a webhook signing secret to use in your <code class="doc-inline-code">.env</code> file.</p>
                        </section>

                        <!-- Troubleshooting -->
                        <section id="troubleshooting" class="doc-section">
                            <h2 class="doc-heading">Troubleshooting</h2>

                            <h3 class="text-lg font-semibold text-white mb-4">Common Issues</h3>

                            <div class="space-y-4 mb-8">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">"Stripe account not connected" error</h4>
                                    <ul class="doc-list text-sm">
                                        <li>User needs to complete Stripe Connect onboarding</li>
                                        <li>Check if <code class="doc-inline-code">stripe_account_id</code> and <code class="doc-inline-code">stripe_completed_at</code> are set on the user</li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">"Invalid signature" webhook error</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Verify the webhook secret matches the one in Stripe Dashboard</li>
                                        <li>Ensure you're using the correct secret for each endpoint:
                                            <ul class="doc-list mt-2">
                                                <li><code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code> for <code class="doc-inline-code">/stripe/webhook</code></li>
                                                <li><code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code> for <code class="doc-inline-code">/stripe/subscription-webhook</code></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Payments not being recorded</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Check that webhooks are being received (Stripe Dashboard > Webhooks > View logs)</li>
                                        <li>Verify the webhook endpoint is accessible</li>
                                        <li>Check application logs for errors</li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Subscription not updating after payment</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Verify webhook events are being received</li>
                                        <li>Check that the correct events are selected in Stripe Dashboard</li>
                                        <li>Review <code class="doc-inline-code">storage/logs/laravel.log</code> for errors</li>
                                    </ul>
                                </div>

                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">"No such price" error</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Verify <code class="doc-inline-code">STRIPE_PRICE_MONTHLY</code> and <code class="doc-inline-code">STRIPE_PRICE_YEARLY</code> contain valid Price IDs</li>
                                        <li>Ensure the prices exist in the same Stripe account (test vs live)</li>
                                    </ul>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-white mb-4">Logs</h3>
                            <p class="text-gray-300 mb-4">Check the following logs for debugging:</p>
                            <ul class="doc-list mb-6">
                                <li>Application logs: <code class="doc-inline-code">storage/logs/laravel.log</code></li>
                                <li>Stripe Dashboard: <strong class="text-white">Developers</strong> > <strong class="text-white">Logs</strong> (API requests)</li>
                                <li>Stripe Dashboard: <strong class="text-white">Developers</strong> > <strong class="text-white">Webhooks</strong> > Select endpoint > View logs</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-white mb-4">Verifying Configuration</h3>
                            <p class="text-gray-300 mb-4">To verify your Stripe configuration is correct:</p>
                            <ol class="doc-list doc-list-numbered mb-4">
                                <li><strong class="text-white">Test Stripe Connect:</strong> Go to profile settings and try connecting a Stripe account</li>
                                <li><strong class="text-white">Test Subscriptions:</strong> Try subscribing to the Pro plan with a test card</li>
                                <li><strong class="text-white">Test Webhooks:</strong> Use Stripe CLI to trigger test events:</li>
                            </ol>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn" onclick="copyCode(this)">Copy</button>
                                </div>
                                <pre><code><span class="code-comment"># Trigger a test payment_intent.succeeded event</span>
stripe trigger payment_intent.succeeded

<span class="code-comment"># Trigger a test subscription event</span>
stripe trigger customer.subscription.created</code></pre>
                            </div>
                        </section>

                        <!-- Security Considerations -->
                        <section id="security" class="doc-section">
                            <h2 class="doc-heading">Security Considerations</h2>
                            <ol class="doc-list doc-list-numbered">
                                <li><span class="font-semibold text-white">API Key Security:</span> Never expose secret keys in client-side code or version control</li>
                                <li><span class="font-semibold text-white">Webhook Verification:</span> Always verify webhook signatures to prevent spoofed events</li>
                                <li><span class="font-semibold text-white">HTTPS Required:</span> Stripe requires HTTPS for webhook endpoints in production</li>
                                <li><span class="font-semibold text-white">PCI Compliance:</span> Using Stripe Checkout and Elements keeps you out of PCI scope</li>
                            </ol>
                        </section>

                        <!-- Architecture Notes -->
                        <section id="architecture" class="doc-section">
                            <h2 class="doc-heading">Architecture Notes</h2>

                            <h3 class="text-lg font-semibold text-white mb-4">Billable Model</h3>
                            <p class="text-gray-300 mb-4">Laravel Cashier is configured to use the <code class="doc-inline-code">Role</code> model (representing a schedule/calendar) as the billable entity, not the <code class="doc-inline-code">User</code> model. This means:</p>
                            <ul class="doc-list mb-6">
                                <li>Each schedule has its own subscription status</li>
                                <li>Users can have multiple schedules with different plans</li>
                                <li>Subscription-related fields (<code class="doc-inline-code">stripe_id</code>, <code class="doc-inline-code">pm_type</code>, <code class="doc-inline-code">pm_last_four</code>, <code class="doc-inline-code">trial_ends_at</code>) are on the <code class="doc-inline-code">roles</code> table</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-white mb-4">Separate Stripe Accounts</h3>
                            <p class="text-gray-300 mb-4">The two integrations use completely separate Stripe configurations:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Feature</th>
                                            <th>Stripe Connect</th>
                                            <th>Laravel Cashier</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Purpose</td>
                                            <td>Ticket sales</td>
                                            <td>Subscription billing</td>
                                        </tr>
                                        <tr>
                                            <td>Config key</td>
                                            <td><code class="doc-inline-code">services.stripe.key</code></td>
                                            <td><code class="doc-inline-code">cashier.secret</code></td>
                                        </tr>
                                        <tr>
                                            <td>Webhook endpoint</td>
                                            <td><code class="doc-inline-code">/stripe/webhook</code></td>
                                            <td><code class="doc-inline-code">/stripe/subscription-webhook</code></td>
                                        </tr>
                                        <tr>
                                            <td>Env prefix</td>
                                            <td><code class="doc-inline-code">STRIPE_</code></td>
                                            <td><code class="doc-inline-code">STRIPE_PLATFORM_</code></td>
                                        </tr>
                                        <tr>
                                            <td>Money flows to</td>
                                            <td>Event creator</td>
                                            <td>Platform operator</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Navigation -->
                        @include('marketing.docs.partials.navigation', ['prevDoc' => $docs[1], 'nextDoc' => $docs[3]])
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
