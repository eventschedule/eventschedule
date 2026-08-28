<x-docs-page
    key="selfhost/stripe"
    description="Configure Stripe for ticket sales on a selfhosted Event Schedule install, or Stripe Connect plus Cashier subscription billing if you run Event Schedule as your own SaaS."
    lede="A selfhosted install takes card payments with one set of platform keys. A SaaS operator needs two integrations: Connect for ticket sales and Cashier for plan subscriptions."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-group label="Choose Your Setup" href="#choose-setup">
            <x-doc-nav-link href="#selfhosted-users">Selfhosted Users</x-doc-nav-link>
            <x-doc-nav-link href="#saas-operators">SaaS Operators</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#invoice-ninja">Invoice Ninja</x-doc-nav-link>
        <x-doc-nav-link href="#payfast">Payfast</x-doc-nav-link>
        <x-doc-nav-link href="#testing">Testing</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
        <x-doc-nav-link href="#security">Security</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Ticket payments in Event Schedule run through <strong class="text-gray-900 dark:text-white">Stripe Checkout</strong>: the buyer pays on Stripe's own hosted page, Stripe calls a webhook back, and the sale is marked paid. What you have to configure depends on who collects the money - one account for the whole install, or a separate account per event owner.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The same keys and the same webhook endpoint also cover <a href="{{ route('marketing.docs.gift_cards') }}" class="doc-link">gift card</a> purchases and paid <a href="{{ route('marketing.docs.appointments') }}" class="doc-link">appointment bookings</a>, so you only set this up once.</p>

        <div class="doc-callout doc-callout-success">
            <div class="doc-callout-title">No platform fees</div>
            <p>Event Schedule never takes a cut of a ticket sale. The Checkout Session is created without an application fee or a transfer, so the full amount lands in the account that took the payment - the one named in your <code class="doc-inline-code">.env</code> on a selfhosted install, or the seller's own connected account under Connect - and Stripe's own processing fee is the only deduction.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Recommended: Stripe behind Invoice Ninja</div>
            <p>Stripe is not the only option. If you want proper invoices, payment reminders and financial reporting on top of card processing, connect <a href="https://invoiceninja.com" target="_blank" rel="noopener noreferrer" class="doc-link">Invoice Ninja</a> instead and add Stripe as a gateway inside it. Buyers still pay by card; the paperwork lives in Invoice Ninja. See <a href="#invoice-ninja" class="doc-link">Invoice Ninja</a> below.</p>
        </div>

        <h3 class="doc-subheading">Where the payment method is chosen</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Server configuration only makes Stripe <em>available</em>. Each event still picks one payment method in the event editor, under <strong class="text-gray-900 dark:text-white">Tickets &rarr; Payment</strong>: Cash, Stripe, Invoice Ninja, Payfast or Payment Link. The five options are described on the <a href="{{ route('marketing.docs.tickets') }}#payment" class="doc-link">Tickets</a> page.</p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Plan requirement</div>
            <p>Selling tickets is included on the <strong>Free</strong> plan on eventschedule.com, capped at 25 paid tickets per schedule per calendar month; Pro and Enterprise lift the cap. Scanning tickets at the door is free too. A few extras around selling stay Pro there: the live check-in dashboard, the ticket widget embed, custom checkout fields and the ticket waitlist.</p>
            <p class="mt-2">A selfhosted install has no monthly ticket allowance at all, and it resolves to the Enterprise tier, so nothing on this page is plan-gated on your own server.</p>
        </div>
    </section>

    <!-- Choose Your Setup -->
    <section id="choose-setup" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Choose Your Setup
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Find your setup type and follow the corresponding guide:</p>

        <div class="doc-table-wrap">
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
                        <td>Running your <strong class="text-gray-900 dark:text-white">own Event Schedule instance</strong> for your organization</td>
                        <td>The one Stripe account named in your <code class="doc-inline-code">.env</code></td>
                        <td><a href="#selfhosted-users" class="doc-link">Selfhosted Users</a></td>
                    </tr>
                    <tr>
                        <td>Running a <strong class="text-gray-900 dark:text-white">white-label SaaS</strong> platform</td>
                        <td>Each customer's own connected Stripe account, plus your account for plan subscriptions</td>
                        <td><a href="#saas-operators" class="doc-link">SaaS Operators</a></td>
                    </tr>
                    <tr>
                        <td>Using <strong class="text-gray-900 dark:text-white">eventschedule.com</strong> (nothing to install)</td>
                        <td>Your own connected Stripe account</td>
                        <td>No server setup. Connect Stripe in <a href="{{ route('marketing.docs.account_settings') }}#payments" class="doc-link">Settings &rarr; Payment Methods</a></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Which one am I?</h3>
        <ul class="doc-list mb-6">
            <li>Will several people each need to be paid into <strong class="text-gray-900 dark:text-white">their own</strong> Stripe account? You are a SaaS operator.</li>
            <li>Should every ticket sold anywhere on the install settle into <strong class="text-gray-900 dark:text-white">one</strong> account you control? You are a selfhosted user.</li>
        </ul>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Stripe Connect needs hosted mode</div>
            <p>Connect is only used when <code class="doc-inline-code">IS_HOSTED=true</code>. On a plain selfhosted install every ticket charge is created with your platform keys, even if a user has linked a Stripe account of their own, so per-owner payouts are a SaaS-operator setup rather than a selfhost one.</p>
        </div>
    </section>

    <!-- Selfhosted Users -->
    <section id="selfhosted-users" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.228a4.5 4.5 0 00-.12-1.03l-2.268-9.64a3.375 3.375 0 00-3.285-2.602H7.923a3.375 3.375 0 00-3.285 2.602l-2.268 9.64a4.5 4.5 0 00-.12 1.03v.228m19.5 0a3 3 0 01-3 3H5.25a3 3 0 01-3-3m19.5 0a3 3 0 00-3-3H5.25a3 3 0 00-3 3m16.5 0h.008v.008h-.008v-.008zm-3 0h.008v.008h-.008v-.008z" />
            </svg>
            For Selfhosted Users
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">If you're running your own Event Schedule instance for your organization, venue, or community, all ticket payments go to a single Stripe account that you control.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">This guide is for you if...</div>
            <p>You want all ticket revenue from all events on your instance to go to one Stripe account. Event creators don't need Stripe accounts of their own, and there is nothing for them to connect.</p>
        </div>

        <h3 class="doc-subheading">1. Get Your Stripe API Keys</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to the <a href="https://dashboard.stripe.com/" target="_blank" rel="noopener noreferrer" class="doc-link">Stripe Dashboard</a></li>
            <li>Open <strong class="text-gray-900 dark:text-white">Developers</strong> &rarr; <strong class="text-gray-900 dark:text-white">API keys</strong></li>
            <li>Note your <strong class="text-gray-900 dark:text-white">Publishable key</strong> and <strong class="text-gray-900 dark:text-white">Secret key</strong></li>
        </ol>

        <h3 class="doc-subheading">2. Configure Environment Variables</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add these to your <code class="doc-inline-code">.env</code> file:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Stripe Direct Payments (selfhosted)</span>
<span class="code-variable">STRIPE_PLATFORM_KEY</span>=<span class="code-string">pk_live_your_publishable_key</span>
<span class="code-variable">STRIPE_PLATFORM_SECRET</span>=<span class="code-string">sk_live_your_secret_key</span>
<span class="code-variable">STRIPE_PLATFORM_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_webhook_secret</span></code></pre>
        </div>

        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">STRIPE_PLATFORM_SECRET</code>: Your secret key (starts with <code class="doc-inline-code">sk_live_</code> or <code class="doc-inline-code">sk_test_</code>). This is the value that switches Stripe on: with it set, Stripe becomes an available payment method for every event owner on the install.</li>
            <li><code class="doc-inline-code">STRIPE_PLATFORM_KEY</code>: Your publishable key (starts with <code class="doc-inline-code">pk_live_</code> or <code class="doc-inline-code">pk_test_</code>). Ticket checkout happens on Stripe's hosted page and does not need it, but the in-app card form for paid boosts and on-network promotions reads it, so set it too.</li>
            <li><code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code>: Webhook signing secret (next step)</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">If you cache your configuration, run <code class="doc-inline-code">php artisan config:clear</code> after editing <code class="doc-inline-code">.env</code> so the new values are picked up.</p>

        <h3 class="doc-subheading">3. Set Up Webhooks</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In Stripe Dashboard, go to <strong class="text-gray-900 dark:text-white">Developers</strong> &rarr; <strong class="text-gray-900 dark:text-white">Webhooks</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Add endpoint</strong></li>
            <li>Set URL to: <code class="doc-inline-code">https://yourdomain.com/stripe/webhook</code></li>
            <li>Select event: <code class="doc-inline-code">checkout.session.completed</code></li>
            <li>Save and copy the <strong class="text-gray-900 dark:text-white">Signing secret</strong> to <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code></li>
        </ol>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Important: webhook event and secret</div>
            <p>Select <code class="doc-inline-code">checkout.session.completed</code>. That is different from a SaaS Connect setup, which uses <code class="doc-inline-code">payment_intent.succeeded</code>.</p>
            <p class="mt-2">The signing secret is not optional. Until <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code> is set, every call to <code class="doc-inline-code">/stripe/webhook</code> is rejected with <code class="doc-inline-code">400 Invalid signature</code> and no sale is ever marked paid.</p>
        </div>

        <h3 class="doc-subheading">4. Enable Stripe for Events</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Settings</strong> &rarr; <strong class="text-gray-900 dark:text-white">Payment Methods</strong>. The <strong class="text-gray-900 dark:text-white">Stripe</strong> tab should read "Stripe is configured".</li>
            <li>Edit an event and open its <strong class="text-gray-900 dark:text-white">Tickets</strong> section, then choose <strong class="text-gray-900 dark:text-white">Tickets</strong> rather than External or Registration.</li>
            <li>On the <strong class="text-gray-900 dark:text-white">Payment</strong> tab, set <strong class="text-gray-900 dark:text-white">Payment method</strong> to <strong class="text-gray-900 dark:text-white">Stripe</strong> and pick the <strong class="text-gray-900 dark:text-white">Currency</strong> the tickets are priced in.</li>
            <li>Add your ticket types on the <strong class="text-gray-900 dark:text-white">General</strong> tab and save. Payments automatically use your platform Stripe account.</li>
        </ol>

        <h3 class="doc-subheading">How Checkout Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>The buyer selects tickets and fills out the checkout form</li>
            <li>Event Schedule creates a Stripe Checkout Session on your platform account, tagged with the sale ID</li>
            <li>The buyer completes payment on Stripe's hosted page</li>
            <li>Stripe calls <code class="doc-inline-code">/stripe/webhook</code>; Event Schedule verifies the signature, checks the amount charged against the amount owed, and marks the sale paid</li>
            <li>The buyer is emailed their tickets, with a QR code for check-in</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Amounts are verified, not trusted</div>
            <p>If the amount Stripe reports differs from the order total by more than one cent, the sale is <strong>not</strong> marked paid. It is flagged <code class="doc-inline-code">amount_mismatch</code> instead, and the mismatch is written to your application log for you to reconcile by hand.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Gift card purchases and paid appointment bookings use exactly the same keys and the same endpoint, so no extra configuration is needed for either.</p>
    </section>

    <!-- SaaS Operators -->
    <section id="saas-operators" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            For SaaS Operators
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If you're running your own white-label SaaS platform (like eventschedule.com but with your own branding), you need two Stripe integrations:</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Both require <code class="doc-inline-code">IS_HOSTED=true</code>. The rest of the SaaS setup, including domains and plan limits, is covered in the <a href="{{ route('marketing.docs.saas.setup') }}#stripe" class="doc-link">SaaS setup guide</a>.</p>

        <div class="doc-fields doc-fields--2">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Stripe Connect</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">So your event creator customers can connect their Stripe accounts and receive ticket payments directly. You never hold their ticket money.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Laravel Cashier</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">To charge your customers for Pro and Enterprise plans (the money you make as the platform operator), billed to your own account.</p>
            </div>
        </div>

        <!-- Part A: Stripe Connect -->
        <h3 class="doc-subheading">Part A: Stripe Connect (ticket sales)</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Lets your event creators receive payments for their own ticket sales.</p>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">1. Enable Stripe Connect</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to the <a href="https://dashboard.stripe.com/" target="_blank" rel="noopener noreferrer" class="doc-link">Stripe Dashboard</a></li>
            <li>Open <strong class="text-gray-900 dark:text-white">Settings</strong> &rarr; <strong class="text-gray-900 dark:text-white">Connect</strong></li>
            <li>Enable Connect for your platform</li>
            <li>Configure your branding and platform profile. Event Schedule creates the connected account for the user and sends them through Stripe's own hosted onboarding, so your branding is what they see.</li>
            <li>Get your API keys from <strong class="text-gray-900 dark:text-white">Developers</strong> &rarr; <strong class="text-gray-900 dark:text-white">API keys</strong></li>
        </ol>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">2. Environment Configuration</h4>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Stripe Connect (for event creators to receive ticket payments)</span>
<span class="code-variable">STRIPE_KEY</span>=<span class="code-string">sk_live_your_stripe_secret_key</span>
<span class="code-variable">STRIPE_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_connect_webhook_secret</span></code></pre>
        </div>

        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">STRIPE_KEY</code>: Your platform's Stripe <strong class="text-gray-900 dark:text-white">secret</strong> key, despite the name. It is the key every Connect call is made with.</li>
            <li><code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code>: Signing secret of the Connect webhook endpoint (next step)</li>
        </ul>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">3. Webhook Configuration</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Developers</strong> &rarr; <strong class="text-gray-900 dark:text-white">Webhooks</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Add endpoint</strong> and choose to listen to events on <strong class="text-gray-900 dark:text-white">connected accounts</strong>, since the charges are created on your customers' accounts rather than yours</li>
            <li>Set URL to: <code class="doc-inline-code">https://yourdomain.com/stripe/webhook</code></li>
            <li>Select event: <code class="doc-inline-code">payment_intent.succeeded</code></li>
            <li>Save and copy the signing secret to <code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code></li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">One endpoint, two secrets</div>
            <p><code class="doc-inline-code">/stripe/webhook</code> tries the Connect secret first and the platform secret second, so the same URL serves both. Event Schedule then checks that the secret matches the kind of sale: a Connect sale confirmed with the platform key, or a direct sale confirmed with the Connect key, is logged and ignored rather than marked paid.</p>
        </div>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">What Your Event Creators Do</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <strong class="text-gray-900 dark:text-white">Settings</strong> &rarr; <strong class="text-gray-900 dark:text-white">Payment Methods</strong> &rarr; <strong class="text-gray-900 dark:text-white">Stripe</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Connect Stripe</strong></li>
            <li>Complete Stripe's onboarding</li>
            <li>They return to your platform, their Stripe business name is shown with an <strong class="text-gray-900 dark:text-white">Unlink Account</strong> link, and Stripe becomes selectable as an event payment method</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Onboarding that is started but not finished leaves the field labeled <strong class="text-gray-900 dark:text-white">Account ID [Pending]</strong>, and the event editor's <strong class="text-gray-900 dark:text-white">Payment</strong> tab shows a "Stripe is verifying your details" notice. Stripe only becomes selectable as a payment method once Stripe reports that charges are enabled.</p>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Connect API Endpoints</h4>
        <div class="doc-table-wrap">
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
                        <td>Start Stripe Connect onboarding (signed in)</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GET /stripe/complete</code></td>
                        <td>Complete onboarding callback (signed in)</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /stripe/unlink</code></td>
                        <td>Disconnect Stripe account (signed in)</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">POST /stripe/webhook</code></td>
                        <td>Handle <code class="doc-inline-code">payment_intent.succeeded</code> and <code class="doc-inline-code">checkout.session.completed</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Part B: Laravel Cashier -->
        <h3 class="doc-subheading">Part B: Laravel Cashier (subscription billing)</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Charges your customers for Pro and Enterprise plans on your own Stripe account.</p>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">1. Create Subscription Products</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In Stripe Dashboard, go to <strong class="text-gray-900 dark:text-white">Products</strong> &rarr; <strong class="text-gray-900 dark:text-white">Add product</strong></li>
            <li>Create a <strong class="text-gray-900 dark:text-white">Pro</strong> product with two recurring prices, one monthly and one yearly</li>
            <li>Optionally create an <strong class="text-gray-900 dark:text-white">Enterprise</strong> product the same way. The Enterprise tier is hidden from the subscribe page unless both of its price IDs are configured.</li>
            <li>Note each <strong class="text-gray-900 dark:text-white">Price ID</strong> (starts with <code class="doc-inline-code">price_</code>)</li>
        </ol>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">2. Environment Configuration</h4>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Laravel Cashier (for subscription billing)</span>
<span class="code-variable">STRIPE_PLATFORM_KEY</span>=<span class="code-string">pk_live_your_publishable_key</span>
<span class="code-variable">STRIPE_PLATFORM_SECRET</span>=<span class="code-string">sk_live_your_secret_key</span>
<span class="code-variable">STRIPE_PLATFORM_WEBHOOK_SECRET</span>=<span class="code-string">whsec_your_subscription_webhook_secret</span>
<span class="code-variable">STRIPE_PRICE_MONTHLY</span>=<span class="code-string">price_monthly_price_id</span>
<span class="code-variable">STRIPE_PRICE_YEARLY</span>=<span class="code-string">price_yearly_price_id</span></code></pre>
        </div>

        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">STRIPE_PLATFORM_KEY</code>: Publishable key for your platform, used by the card form on the subscribe page</li>
            <li><code class="doc-inline-code">STRIPE_PLATFORM_SECRET</code>: Secret key for your platform</li>
            <li><code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code>: Signing secret of the subscription webhook endpoint</li>
            <li><code class="doc-inline-code">STRIPE_PRICE_MONTHLY</code> and <code class="doc-inline-code">STRIPE_PRICE_YEARLY</code>: Pro price IDs</li>
            <li><code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_MONTHLY</code> and <code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_YEARLY</code>: Enterprise price IDs. Leave both unset to sell Pro only.</li>
            <li><code class="doc-inline-code">STRIPE_PRICE_MONTHLY_AMOUNT</code>, <code class="doc-inline-code">STRIPE_PRICE_YEARLY_AMOUNT</code>, <code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_MONTHLY_AMOUNT</code> and <code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_YEARLY_AMOUNT</code>: the amounts <em>displayed</em> in the app. They are labels only, so set them to match your Stripe prices or your pages will show the defaults of 5, 50, 15 and 150. A super-admin can override all four at <code class="doc-inline-code">/admin/settings</code> without touching <code class="doc-inline-code">.env</code>; keep these set anyway, because revenue reporting and renewal emails read the values here rather than the admin panel.</li>
            <li><code class="doc-inline-code">PLATFORM_CURRENCY</code>: the currency the platform quotes its own price in - a label there, like the amounts, so set it to match the currency of your Stripe prices. It does one real job beyond that: it is the currency a new event starts in when its schedule has no country set, or a country outside the built-in currency map. Defaults to <code class="doc-inline-code">USD</code>, and a super-admin can change it at <code class="doc-inline-code">/admin/settings</code> without touching <code class="doc-inline-code">.env</code>.</li>
            <li><code class="doc-inline-code">STRIPE_LEGACY_PRICE_MONTHLY</code>, <code class="doc-inline-code">STRIPE_LEGACY_PRICE_YEARLY</code>, <code class="doc-inline-code">STRIPE_LEGACY_ENTERPRISE_PRICE_MONTHLY</code> and <code class="doc-inline-code">STRIPE_LEGACY_ENTERPRISE_PRICE_YEARLY</code>: comma-separated retired price IDs. Stripe prices cannot be edited, so changing what a plan costs means creating a new price and pointing the variables above at it. List the old IDs here and anyone still billing on them keeps the tier and term they pay for. Leave empty until you have actually retired a price.</li>
            <li><code class="doc-inline-code">STRIPE_LEGACY_PRICE_AMOUNTS</code>: what those retired prices charge, as <code class="doc-inline-code">price_id:amount</code> pairs (for example <code class="doc-inline-code">price_abc:9,price_def:90</code>), so grandfathered subscribers are not counted at zero in reporting or quoted a price they are not paying. This one is <code class="doc-inline-code">.env</code> only by design: it records what existing customers are actually being charged, keyed by opaque Stripe IDs, and is never editable from the admin panel.</li>
        </ul>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">3. Subscription Webhook</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Developers</strong> &rarr; <strong class="text-gray-900 dark:text-white">Webhooks</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Add endpoint</strong> (this is a second webhook, separate from Connect, listening on your own account)</li>
            <li>Set URL to: <code class="doc-inline-code">https://yourdomain.com/stripe/subscription-webhook</code></li>
            <li>Select events:
                <ul class="doc-list mt-2 mb-2">
                    <li><code class="doc-inline-code">customer.subscription.created</code></li>
                    <li><code class="doc-inline-code">customer.subscription.updated</code></li>
                    <li><code class="doc-inline-code">customer.subscription.deleted</code></li>
                    <li><code class="doc-inline-code">customer.subscription.trial_will_end</code></li>
                    <li><code class="doc-inline-code">customer.updated</code></li>
                    <li><code class="doc-inline-code">customer.deleted</code></li>
                    <li><code class="doc-inline-code">invoice.payment_succeeded</code></li>
                    <li><code class="doc-inline-code">invoice.payment_failed</code></li>
                    <li><code class="doc-inline-code">invoice.payment_action_required</code></li>
                    <li><code class="doc-inline-code">payment_method.automatically_updated</code></li>
                </ul>
            </li>
            <li>Save and copy signing secret to <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code></li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">These events are what keep a schedule's plan in step with Stripe: a successful invoice sets the plan and term, a deleted subscription drops the schedule back to Free, and a failed payment emails the owner and sends them a push notification.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Stale price IDs silently downgrade Enterprise</div>
            <p>The plan tier is decided by matching the subscription's price ID against the four configured in your <code class="doc-inline-code">.env</code>. A price ID that matches none of them is treated as <strong>Pro monthly</strong> rather than being ignored, so an Enterprise customer whose price ID no longer matches quietly loses Enterprise. Re-check all four values whenever you change your Stripe products.</p>
        </div>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">4. Customer Portal Setup</h4>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <strong class="text-gray-900 dark:text-white">Settings</strong> &rarr; <strong class="text-gray-900 dark:text-white">Billing</strong> &rarr; <strong class="text-gray-900 dark:text-white">Customer portal</strong></li>
            <li>Enable subscription management features:
                <ul class="doc-list mt-2 mb-2">
                    <li>Subscription cancellation</li>
                    <li>Plan switching</li>
                    <li>Payment method updates</li>
                </ul>
            </li>
            <li>Customize branding to match your platform</li>
        </ol>

        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Subscription API Endpoints</h4>
        <div class="doc-table-wrap">
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

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Architecture note</div>
            <p>Cashier bills the <strong>schedule</strong>, not the user account. Each schedule carries its own Stripe customer and subscription, so one person can own several schedules on different plans, and cancelling one leaves the others alone.</p>
        </div>
    </section>

    <!-- Invoice Ninja -->
    <section id="invoice-ninja" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Invoice Ninja (Alternative Payment Method)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">In addition to Stripe, Event Schedule supports <a href="https://invoiceninja.com" target="_blank" rel="noopener noreferrer" class="doc-link">Invoice Ninja</a> as an alternative payment method for ticket sales and gift cards. Invoice Ninja is an open-source invoicing and payments platform that supports many payment gateways, Stripe among them.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">No server configuration required</div>
            <p>Unlike Stripe, Invoice Ninja needs no <code class="doc-inline-code">.env</code> configuration at all. Each user connects their own Invoice Ninja company from <strong>Settings &rarr; Payment Methods &rarr; Invoice Ninja</strong> in the admin portal, so different event owners on the same install can use different Invoice Ninja accounts.</p>
        </div>

        <h3 class="doc-subheading">Prerequisites</h3>
        <ul class="doc-list mb-6">
            <li>An <a href="https://invoiceninja.com" target="_blank" rel="noopener noreferrer" class="doc-link">Invoice Ninja</a> company, either selfhosted or on invoicing.co</li>
            <li>An API token from that company</li>
            <li>At least one payment gateway configured in Invoice Ninja</li>
        </ul>

        <h3 class="doc-subheading">Setup Steps</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In Invoice Ninja, go to <strong class="text-gray-900 dark:text-white">Settings &rarr; Account Management</strong> and create an API token</li>
            <li>In Event Schedule, open <strong class="text-gray-900 dark:text-white">Settings &rarr; Payment Methods &rarr; Invoice Ninja</strong></li>
            <li>Paste the token into <strong class="text-gray-900 dark:text-white">API Token</strong></li>
            <li>Fill in <strong class="text-gray-900 dark:text-white">API URL</strong> with the base address of your instance, for example <code class="doc-inline-code">https://invoicing.yourdomain.com</code>, without a trailing <code class="doc-inline-code">/api/v1</code>. Leave it blank to use invoicing.co.</li>
            <li>Save. Event Schedule verifies the credentials and registers a webhook in your Invoice Ninja company, so the connection either works or fails outright rather than saving a broken one.</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Once connected, the company name is shown with <strong class="text-gray-900 dark:text-white">Edit</strong> and <strong class="text-gray-900 dark:text-white">Unlink Account</strong> links. Editing the credentials replaces the old webhook rather than adding a second one, and leaving the token blank there means "keep the current token", so you can correct just the URL.</p>

        <h3 class="doc-subheading">How It Works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Edit an event, open <strong class="text-gray-900 dark:text-white">Tickets &rarr; Payment</strong> and choose <strong class="text-gray-900 dark:text-white">Invoice Ninja</strong> as the payment method</li>
            <li>At checkout the buyer is sent to Invoice Ninja: to an invoice they can pay, or to an Invoice Ninja purchase page, depending on the mode below</li>
            <li>Invoice Ninja processes the card through whichever gateway you configured there</li>
            <li>Invoice Ninja calls the webhook back so Event Schedule can mark the sale paid and email the tickets</li>
        </ol>

        <h3 class="doc-subheading">Invoice Ninja Modes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once the company is connected, a <strong class="text-gray-900 dark:text-white">Checkout mode</strong> setting appears in the same Invoice Ninja tab. It applies to every event this user sells through Invoice Ninja. The full comparison lives on the <a href="{{ route('marketing.docs.tickets') }}#invoiceninja-modes" class="doc-link">Tickets</a> page.</p>
        <div class="doc-fields doc-fields--2">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Invoice</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Ticket selection and promo codes are handled in Event Schedule, and an invoice is created in Invoice Ninja for each purchase. Supports several promo codes.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment link</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Buyers select tickets and enter promo codes on the Invoice Ninja purchase page, and invoices are grouped there. Event Schedule creates one Invoice Ninja product per ticket type and add-on the first time an event is bought, and passes one active promo code.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Payment link mode falls back</div>
            <p>If building the Invoice Ninja purchase page fails, that checkout quietly falls back to Invoice mode so the buyer can still pay. If your buyers keep landing on an invoice instead of the purchase page, check your application log for the Invoice Ninja warning.</p>
        </div>
    </section>

    <!-- Testing -->
    <section id="payfast" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Payfast (Alternative Payment Method)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6"><a href="https://payfast.io" target="_blank" rel="noopener noreferrer" class="doc-link">Payfast</a> is a South African gateway, useful where Stripe is not available. It settles in South African rand only. Setup is documented in the <a href="{{ route('marketing.docs.tickets') }}#payfast" class="doc-link">user guide</a>; the notes below are the parts specific to running your own install.</p>

        <h3 class="doc-subheading">Two ways to set it up</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Payfast works either way round, and you can mix the two on one install:</p>

        <div class="doc-fields doc-fields--2 mb-6">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">One account for the whole install</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Put your merchant details in <code class="doc-inline-code">.env</code>, exactly as you would <code class="doc-inline-code">STRIPE_PLATFORM_SECRET</code>. Every schedule can then sell tickets straight away, with no setup of their own, and all the money reaches your account.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Each user brings their own</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Leave <code class="doc-inline-code">.env</code> alone and each user connects a merchant account from <strong>Settings &rarr; Payment Methods &rarr; Payfast</strong>, so different event owners on the same install are paid into different Payfast accounts.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Install-wide configuration</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Take these from your Payfast dashboard, under <strong>Settings</strong>, and add them to your <code class="doc-inline-code">.env</code> file:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Payfast for every schedule on this install</span>
<span class="code-variable">PAYFAST_MERCHANT_ID</span>=<span class="code-string">your_merchant_id</span>
<span class="code-variable">PAYFAST_MERCHANT_KEY</span>=<span class="code-string">your_merchant_key</span>
<span class="code-variable">PAYFAST_PASSPHRASE</span>=<span class="code-string">your_passphrase</span>
<span class="code-variable">PAYFAST_SANDBOX</span>=<span class="code-string">false</span></code></pre>
        </div>

        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">PAYFAST_PASSPHRASE</code> is optional at Payfast, but required here. Without one, the payment notification signature is a plain MD5 that anyone could reproduce, so Payfast is simply not offered until all three values are set.</li>
            <li><code class="doc-inline-code">PAYFAST_SANDBOX</code> sends payments to Payfast's sandbox instead of taking real money. Sandbox tickets look completely normal, so leave it <code class="doc-inline-code">false</code> outside of testing. When it is on, "Test mode" is shown next to Payfast wherever an owner picks it.</li>
            <li><code class="doc-inline-code">PAYFAST_PAYMENT_TYPES</code> (optional) pins the checkout to a single instrument, for example <code class="doc-inline-code">ef</code> for Instant EFT. Leave it empty to let Payfast offer everything your account supports.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">If you cache your configuration, run <code class="doc-inline-code">php artisan config:clear</code> after editing <code class="doc-inline-code">.env</code> so the new values are picked up.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">A user's own account always wins</div>
            <p>The values above are a default, never an override. A user who has connected their own Payfast account in <strong>Settings &rarr; Payment Methods</strong> keeps using it, and their sales keep reaching them - so adding these to an install that has been running for a while cannot quietly re-route anybody's money. Users who have connected nothing see "Provided by this installation" on that tab instead, and can still enter their own account to opt out. Ignored entirely in hosted mode, where every owner must connect their own.</p>
        </div>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Payfast settles in rand only</div>
            <p>It is offered only on events priced in ZAR. Separately, a single payment below R5.00 is refused at checkout rather than hidden from the dropdown, because Payfast will not process it. If Payfast is not appearing in an event's Payment dropdown at all, check the event's ticket currency first: a schedule whose country is blank, or not in the built-in currency map, starts its events in your installation currency - <code class="doc-inline-code">PLATFORM_CURRENCY</code>, or whatever is set at <code class="doc-inline-code">/admin/settings</code> - which defaults to USD.</p>
        </div>

        <h3 class="doc-subheading">Making Payfast the default for new events</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">New events start on <strong>Cash</strong> unless you say otherwise. On an install where Payfast is the only way to take money, that means choosing it by hand every time. Name it once instead:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">DEFAULT_PAYMENT_METHOD</span>=<span class="code-string">payfast</span></code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Accepts any gateway key - <code class="doc-inline-code">cash</code>, <code class="doc-inline-code">stripe</code>, <code class="doc-inline-code">invoiceninja</code>, <code class="doc-inline-code">payment_url</code> or <code class="doc-inline-code">payfast</code> - and applies to events created through the API as well as the form. It only takes effect where the gateway can actually be used: an owner who has not connected it, or an event in a currency it cannot settle, still starts on Cash. On the event form a schedule's own saved ticket defaults take priority over it; the API has never read those, so there it applies whenever <code class="doc-inline-code">payment_method</code> is omitted.</p>

        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Your install must be publicly reachable</div>
            <p>Payfast confirms a payment by POSTing a notification to your server. It cannot reach <code class="doc-inline-code">localhost</code> or a private address, so on a laptop or an internal-only host a payment will be taken and the ticket will never be issued. Use a public hostname, or a tunnel, before taking any payment - including a sandbox one.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">A log warning you can safely ignore</div>
            <p>You may see <code class="doc-inline-code">Payfast ITN from an unrecognised source address - continuing, see confirmsPayment</code> in your logs on every successful payment. That is expected behind Cloudflare, a reverse proxy or Docker: the app sees your proxy's address rather than Payfast's, and the address check is advisory for exactly that reason. The notification is authenticated by its signature and by asking Payfast to confirm it, so no <code class="doc-inline-code">TRUSTED_PROXIES</code> configuration is needed for payments to work.</p>
        </div>
    </section>

    <section id="testing" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
            </svg>
            Testing
        </h2>

        <h3 class="doc-subheading">Test Mode Setup</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">For development and testing:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Toggle to <strong class="text-gray-900 dark:text-white">Test mode</strong> in the Stripe Dashboard</li>
            <li>Use test API keys (starting with <code class="doc-inline-code">pk_test_</code> and <code class="doc-inline-code">sk_test_</code>)</li>
            <li>Create test webhook endpoints pointing to your development environment</li>
            <li>Keep keys, prices and webhooks in one mode. Test keys with live price IDs, or the reverse, fail with a "No such price" error.</li>
        </ol>

        <h3 class="doc-subheading">Test Card Numbers</h3>
        <div class="doc-table-wrap">
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
        <p class="text-gray-600 dark:text-gray-400 text-sm mb-6">Use any future expiration date and any 3-digit CVC.</p>

        <h3 class="doc-subheading">Testing Webhooks Locally</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Use the <a href="https://stripe.com/docs/stripe-cli" target="_blank" rel="noopener noreferrer" class="doc-link">Stripe CLI</a> to forward webhooks:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
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

        <p class="text-gray-600 dark:text-gray-300 mb-4">The CLI prints a webhook signing secret for each listener. Put it in the variable that endpoint reads:</p>
        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">/stripe/webhook</code> on a selfhosted install: <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code></li>
            <li><code class="doc-inline-code">/stripe/webhook</code> for Connect: <code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code></li>
            <li><code class="doc-inline-code">/stripe/subscription-webhook</code>: <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code></li>
        </ul>

        <h3 class="doc-subheading">Trigger Test Events</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-comment"># Test checkout completion (selfhosted)</span>
stripe trigger checkout.session.completed

<span class="code-comment"># Test payment success (Connect)</span>
stripe trigger payment_intent.succeeded

<span class="code-comment"># Test subscription creation</span>
stripe trigger customer.subscription.created</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">A triggered event proves the endpoint is reachable and the signature verifies, but it carries no sale ID in its metadata, so no sale changes state. To test the full path, buy a ticket with a test card.</p>
    </section>

    <!-- Troubleshooting -->
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Troubleshooting
        </h2>

        <h3 class="doc-subheading">Common Issues</h3>

        <div class="doc-fields mb-8">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Stripe is missing from the payment method list</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2"><strong class="text-gray-900 dark:text-white">Applies to:</strong> All setups</p>
                <ul class="doc-list text-sm">
                    <li>Selfhosted: <code class="doc-inline-code">STRIPE_PLATFORM_SECRET</code> is not set, or the config cache is stale. <strong class="text-gray-900 dark:text-white">Settings &rarr; Payment Methods &rarr; Stripe</strong> tells you which, since it reads "Stripe is configured" only when the secret is present.</li>
                    <li>SaaS: the user has not finished Connect onboarding, so Stripe is not offered yet.</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Stripe account not connected" error</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2"><strong class="text-gray-900 dark:text-white">Applies to:</strong> SaaS operators (Connect)</p>
                <ul class="doc-list text-sm">
                    <li>User needs to complete Stripe Connect onboarding</li>
                    <li>Check if <code class="doc-inline-code">stripe_account_id</code> and <code class="doc-inline-code">stripe_completed_at</code> are set on the user. An account ID without a completion timestamp means Stripe has not enabled charges yet.</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Invalid signature" webhook error</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2"><strong class="text-gray-900 dark:text-white">Applies to:</strong> All setups</p>
                <ul class="doc-list text-sm">
                    <li>Verify the webhook secret matches the one in Stripe Dashboard</li>
                    <li>A missing secret produces the same error: with neither variable set, every webhook is rejected</li>
                    <li>Make sure you're using the correct secret for each endpoint:
                        <ul class="doc-list mt-2">
                            <li><code class="doc-inline-code">STRIPE_WEBHOOK_SECRET</code> for Connect webhooks</li>
                            <li><code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code> for selfhosted/subscription webhooks</li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payments not being recorded</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2"><strong class="text-gray-900 dark:text-white">Applies to:</strong> Selfhosted users, SaaS operators</p>
                <ul class="doc-list text-sm">
                    <li>Check webhook logs in Stripe Dashboard &rarr; Webhooks &rarr; View logs</li>
                    <li>Verify the webhook endpoint is accessible (not blocked by firewall)</li>
                    <li>Confirm the endpoint listens to the right event: <code class="doc-inline-code">checkout.session.completed</code> for direct payments, <code class="doc-inline-code">payment_intent.succeeded</code> for Connect</li>
                    <li>For Connect, confirm the endpoint listens on connected accounts, not only on your own</li>
                    <li>Check <code class="doc-inline-code">storage/logs/laravel.log</code> for errors, including a "webhook key mismatch" warning, which means the sale and the secret belong to different payment contexts</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">A sale is stuck as "amount mismatch"</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2"><strong class="text-gray-900 dark:text-white">Applies to:</strong> Selfhosted users, SaaS operators</p>
                <ul class="doc-list text-sm">
                    <li>Stripe reported a total more than a cent away from the order total, so the sale was deliberately not marked paid</li>
                    <li><code class="doc-inline-code">storage/logs/laravel.log</code> records the expected and received amounts side by side</li>
                    <li>Usual causes are a currency mismatch between the event and the Stripe account, or ticket prices edited after the buyer reached Stripe</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Subscription not updating after payment</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2"><strong class="text-gray-900 dark:text-white">Applies to:</strong> SaaS operators</p>
                <ul class="doc-list text-sm">
                    <li>Verify webhook events are being received</li>
                    <li>Check that all required subscription events are selected in Stripe</li>
                    <li>Confirm the subscribed price ID is one of the four configured in your <code class="doc-inline-code">.env</code>. An unrecognized price ID is treated as Pro monthly, which looks like an Enterprise customer being downgraded</li>
                    <li>Review <code class="doc-inline-code">storage/logs/laravel.log</code> for errors</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"No such price" error</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2"><strong class="text-gray-900 dark:text-white">Applies to:</strong> SaaS operators</p>
                <ul class="doc-list text-sm">
                    <li>Verify <code class="doc-inline-code">STRIPE_PRICE_MONTHLY</code> and <code class="doc-inline-code">STRIPE_PRICE_YEARLY</code> contain valid Price IDs</li>
                    <li>Ensure the prices exist in the same Stripe mode (test vs live)</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Enterprise is missing from the subscribe page</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2"><strong class="text-gray-900 dark:text-white">Applies to:</strong> SaaS operators</p>
                <ul class="doc-list text-sm">
                    <li>Both <code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_MONTHLY</code> and <code class="doc-inline-code">STRIPE_ENTERPRISE_PRICE_YEARLY</code> must be set. With one missing, the request is served as Pro.</li>
                </ul>
            </div>
        </div>

        <h3 class="doc-subheading">Debugging Logs</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Application:</strong> <code class="doc-inline-code">storage/logs/laravel.log</code></li>
            <li><strong class="text-gray-900 dark:text-white">Stripe API:</strong> Dashboard &rarr; Developers &rarr; Logs</li>
            <li><strong class="text-gray-900 dark:text-white">Webhooks:</strong> Dashboard &rarr; Developers &rarr; Webhooks &rarr; Select endpoint &rarr; View logs</li>
        </ul>
    </section>

    <!-- Security -->
    <section id="security" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            Security
        </h2>
        <ol class="doc-list doc-list-numbered">
            <li><span class="font-semibold text-gray-900 dark:text-white">API key security:</span> Secret keys belong in <code class="doc-inline-code">.env</code> only. Never put them in client-side code or commit them to version control.</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Webhook verification:</span> Both endpoints are public URLs, so both signing secrets matter. <code class="doc-inline-code">/stripe/webhook</code> fails closed: a signature it cannot verify, and any request at all when no secret is configured, is rejected with a 400. <code class="doc-inline-code">/stripe/subscription-webhook</code> fails open: the signature check is only installed once <code class="doc-inline-code">STRIPE_PLATFORM_WEBHOOK_SECRET</code> is set, so leaving it blank leaves an unauthenticated endpoint that can move a schedule between plans.</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">Amount and account checks:</span> A confirmed payment is also checked against the amount owed, and on Connect against the account that took the money, before anything is marked paid.</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">HTTPS required:</span> Stripe requires HTTPS for webhook endpoints in production.</li>
            <li><span class="font-semibold text-gray-900 dark:text-white">PCI compliance:</span> Using Stripe Checkout and Elements keeps you out of PCI scope. Card data never touches your server.</li>
        </ol>
    </section>
</x-docs-page>
