<x-docs-page
    key="account-settings"
    description="Learn how to manage your profile, payment methods, API access, backups and connected calendars in Event Schedule."
    lede="Everything on the Settings page: your profile, how the admin portal looks, the payment method your tickets are sold through, API and webhook access, connected calendars, backups and account security."
>
    <x-slot:toc>
        <x-doc-nav-link href="#profile">Profile Information</x-doc-nav-link>
        <x-doc-nav-link href="#appearance">Appearance</x-doc-nav-link>
        <x-doc-nav-group label="Payment Methods" href="#payments">
            <x-doc-nav-link href="#stripe">Stripe</x-doc-nav-link>
            <x-doc-nav-link href="#invoice-ninja">Invoice Ninja</x-doc-nav-link>
            <x-doc-nav-link href="#payment-url">Payment Link</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#api">API Settings</x-doc-nav-link>
        <x-doc-nav-link href="#webhooks">Webhooks</x-doc-nav-link>
        <x-doc-nav-link href="#google">Google Settings</x-doc-nav-link>
        <x-doc-nav-link href="#microsoft">Outlook Calendar</x-doc-nav-link>
        <x-doc-nav-link href="#backup">Backup & Restore</x-doc-nav-link>
        <x-doc-nav-link href="#app-update">App Update</x-doc-nav-link>
        <x-doc-nav-link href="#password">Update Password</x-doc-nav-link>
        <x-doc-nav-link href="#two-factor">Two-Factor Authentication</x-doc-nav-link>
        <x-doc-nav-link href="#delete-account">Delete Account</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Profile Information -->
    <section id="profile" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            Profile Information
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Open <strong class="text-gray-900 dark:text-white">Settings</strong> from the main navigation. Every settings section is listed down the left side of the page (an accordion on small screens), and this guide follows them in that order. These settings belong to <em>you</em>, not to a schedule: anything that differs per schedule, such as calendar sync or a schedule's own sending address, lives in <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="doc-link">the schedule's settings</a> instead.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Profile Information itself is split into two tabs, <strong class="text-gray-900 dark:text-white">General</strong> and <strong class="text-gray-900 dark:text-white">Localization</strong>. A third tab, <strong class="text-gray-900 dark:text-white">Accessibility</strong>, appears only after you have hidden the accessibility widget, and its single button brings the widget back.
        </p>

        <x-doc-screenshot id="account-settings--settings" alt="Account settings page" loading="eager" />

        <h3 class="doc-subheading">General</h3>
        <div class="doc-table-wrap">
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
                        <td>Required. Your display name, shown to the other members of any schedule you belong to.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Email</span></td>
                        <td>Required. The address you sign in with and where account email is sent. Editing it clears your verification: the field then shows an unverified notice with a link to re-send the verification email.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Phone Number</span></td>
                        <td>Optional, in international format. On the hosted platform you can verify it by SMS, and a verified number is what unlocks sending <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletters</a> and running <a href="{{ route('marketing.docs.boost') }}" class="doc-link">ad campaigns</a> and on-network promotions. If one of your schedules uses the same number, that schedule's number counts as verified too.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Default Schedule</span></td>
                        <td>Only shown when you can edit more than one schedule. It decides which schedule a new event belongs to when you start from the general Add Event entry point rather than from a schedule.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Square Profile Image</span></td>
                        <td>Your avatar, as a PNG or JPEG. A square image works best: the form warns you if the picture is not square or is larger than 2.5MB. Use the small red cross on the thumbnail to remove the current image.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Localization</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Timezone</span></td>
                        <td>Controls how dates and times are shown to you throughout the app. Event times are stored in UTC and converted to this timezone while you are signed in.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Language</span></td>
                        <td>Sets the interface language. Twelve languages are available: Arabic, Dutch, English, Estonian, French, German, Hebrew, Italian, Portuguese, Romanian, Russian and Spanish.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Use 24-hour time format</span></td>
                        <td>Switches between 12-hour (AM/PM) and 24-hour clocks across the app.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Ask me before I follow a new schedule</span></td>
                        <td>On by default. Shows a short consent notice, explaining that the schedule will see your name and email, the first time you follow a schedule. Turn it off to follow in one click.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Appearance -->
    <section id="appearance" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" />
            </svg>
            Appearance
        </h2>

        <p class="doc-paragraph">
            The <span class="font-semibold text-gray-900 dark:text-white">Appearance</span> tab in Settings controls how the
            admin portal looks. You can also change it on any page from the
            <span class="font-semibold text-gray-900 dark:text-white">Theme</span> button in the row of icons pinned to the
            bottom of the sidebar, which opens the same Theme and Palette controls in a popup.
        </p>

        <p class="doc-paragraph">
            <span class="font-semibold text-gray-900 dark:text-white">Theme</span> chooses Light, Dark, or System. System
            follows your device's own light/dark setting and updates the moment your device switches.
        </p>

        <p class="doc-paragraph">
            <span class="font-semibold text-gray-900 dark:text-white">Palette</span> then picks the exact colours. There are
            three light palettes and three dark ones, and you set each side independently - the palette row shows the three
            options for whichever mode you are currently viewing, so switch Theme to Dark to choose your dark palette.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr><th>Palette</th><th>Description</th></tr>
                </thead>
                <tbody>
                    <tr><td><span class="font-semibold text-gray-900 dark:text-white">Sand</span></td><td>Light. Warm beige page with white panels.</td></tr>
                    <tr><td><span class="font-semibold text-gray-900 dark:text-white">Mist</span></td><td>Light. Cool blue-grey page with white panels. The default.</td></tr>
                    <tr><td><span class="font-semibold text-gray-900 dark:text-white">Paper</span></td><td>Light. Crisp white page with softly recessed panels.</td></tr>
                    <tr><td><span class="font-semibold text-gray-900 dark:text-white">Espresso</span></td><td>Dark. Warm deep brown.</td></tr>
                    <tr><td><span class="font-semibold text-gray-900 dark:text-white">Midnight</span></td><td>Dark. Cool charcoal-navy. The default.</td></tr>
                    <tr><td><span class="font-semibold text-gray-900 dark:text-white">Carbon</span></td><td>Dark. True black, easiest on OLED screens.</td></tr>
                </tbody>
            </table>
        </div>

        <p class="doc-paragraph">
            Your choice is stored in your browser rather than on your account, so it applies to the device you set it on.
            Set it again on your phone or another computer to match. Public schedule pages are unaffected - they keep the
            colours the schedule owner configured.
        </p>
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
            To sell tickets you need at least one payment method on your account. Event Schedule supports four - Stripe, Invoice Ninja, Payfast and a plain payment link - each on its own tab in this section. Which one an event uses is decided per event, on the event's <a href="{{ route('marketing.docs.tickets') }}#payment" class="doc-link">Payment</a> tab, so connecting more than one lets you route different events differently. Without any connected method the only option an event has is cash on the door.
        </p>

        <h3 id="stripe" class="doc-subheading">Stripe</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Stripe gives the most integrated experience: the buyer pays without leaving the checkout, the sale is marked paid automatically and the ticket with its QR code is emailed straight away.
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hosted platform</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Connect Stripe</strong> to start Stripe Connect onboarding, then complete the details on Stripe. Until Stripe finishes reviewing them the tab shows your account ID labelled <strong class="text-gray-900 dark:text-white">[Pending]</strong>. Buyers are charged on your own Stripe account, so payouts and Stripe's own processing fees are between you and Stripe. Event Schedule adds no platform fee of its own.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Selfhosted</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Stripe is configured once at the server level with the <code class="doc-inline-code">STRIPE_PLATFORM_KEY</code> and <code class="doc-inline-code">STRIPE_PLATFORM_SECRET</code> variables in your <code class="doc-inline-code">.env</code> file, not per account. The tab simply reports whether the server has Stripe configured. See the <x-link href="{{ route('marketing.docs.selfhost.stripe') }}">selfhosted Stripe setup guide</x-link>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Disconnecting</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Unlink Account</strong> under the account name in the Stripe tab and confirm. This only removes the connection to Event Schedule; your Stripe account and its history are untouched.</p>
            </div>
        </div>

        <h3 id="invoice-ninja" class="doc-subheading">Invoice Ninja</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Connect your <x-link href="https://invoiceninja.com" target="_blank">Invoice Ninja</x-link> account to take payment through any gateway Invoice Ninja supports, and to have each sale land in your books as an invoice. This is often the easiest route for a selfhosted deployment.
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Connecting</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Enter your <strong class="text-gray-900 dark:text-white">API Token</strong> (required) and, if you selfhost Invoice Ninja, the <strong class="text-gray-900 dark:text-white">API URL</strong>. The token is in Invoice Ninja under Settings &rarr; Account Management. Enter the base address of your installation, without <code class="doc-inline-code">/api/v1</code>, or leave the API URL blank to use the hosted invoicing.co service. Saving verifies the credentials against your installation, which can take up to 30 seconds.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Changing credentials</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Once connected, click <strong class="text-gray-900 dark:text-white">Edit</strong> next to the company name to correct the API URL or rotate the token. Leave the token blank to keep the existing one. Your current connection is only replaced once the new credentials work.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Checkout modes</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">After connecting, the tab offers two checkout modes: invoice mode (buyers pick tickets in Event Schedule) and payment link mode (buyers pick tickets on Invoice Ninja's purchase page, with grouped invoices). See <x-link href="{{ route('marketing.docs.tickets') }}#invoiceninja-modes">Invoice Ninja Modes</x-link> for a full comparison. If a payment link cannot be created for a sale, that checkout falls back to invoice mode automatically.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Troubleshooting</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">If the connection fails, a red panel at the top of the Invoice Ninja tab names the reason and shows the raw error, and the full detail is written to your application log. Common causes for selfhosted installations are a firewall, Cloudflare or other bot protection blocking API requests from your Event Schedule server, an <code class="doc-inline-code">http</code> URL that redirects to <code class="doc-inline-code">https</code>, and a self-signed TLS certificate the server does not trust.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Disconnecting</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Unlink Account</strong> next to the company name in the Invoice Ninja tab and confirm.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Tip</div>
            <p>Before you connect, the tab shows a special offer link: first-time Invoice Ninja users may be eligible for a free 1-year upgrade to Invoice Ninja Pro.</p>
        </div>

        <h3 id="payment-url" class="doc-subheading">Payment Link</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The tab is labelled <strong class="text-gray-900 dark:text-white">Payment Link</strong>. It points buyers at any external payment page you already have: a PayPal.me link, a bank transfer page, your own checkout, anything with a URL.
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How it works</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Enter the URL where buyers should pay and save. When somebody buys a ticket for an event using this method, the sale is recorded and the buyer is redirected to your URL. Because the money moves outside Event Schedule, the sale stays <strong class="text-gray-900 dark:text-white">unpaid</strong> until the payment is confirmed.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Removing it</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Unlink Account</strong> under the saved URL and confirm. The field is then empty and ready for a new URL.</p>
            </div>
        </div>

        <h3 id="payfast" class="doc-subheading">Payfast</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            <x-link href="https://payfast.io" target="_blank">Payfast</x-link> is a South African gateway, useful where Stripe is not available. Like Stripe it confirms the payment and releases the ticket on its own, with no manual step. It settles in South African rand only, so it appears as an option on events priced in ZAR and nowhere else.
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">What to enter</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your <strong class="text-gray-900 dark:text-white">Merchant ID</strong>, <strong class="text-gray-900 dark:text-white">Merchant Key</strong> and <strong class="text-gray-900 dark:text-white">passphrase</strong>, all three from <strong class="text-gray-900 dark:text-white">Settings</strong> in your Payfast dashboard. Set a passphrase there first if you have not already - it is required here, because it is what proves a payment notification genuinely came from Payfast.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Test mode</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Sends payments to Payfast's sandbox so nothing is really charged. While it is on, the payment method reads <strong class="text-gray-900 dark:text-white">(Test mode)</strong> on the event form and buyers see a notice on the payment page. Turn it off before you sell real tickets.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Payment methods</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">By default Payfast shows buyers everything your account supports - card, Instant EFT, Capitec Pay and the rest. Tick exactly one to send buyers straight to it; tick none, or several, and the choice stays with Payfast.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Removing it</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Unlink Account</strong> and confirm. Any event still set to Payfast keeps the setting but shows it as no longer available, so you can see it and pick something else.</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            For the full walkthrough, including the sandbox credentials and what happens to an event priced in another currency, see <a href="{{ route('marketing.docs.tickets') }}#payfast" class="doc-link">Connecting Payfast</a>.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>One payment method is enough. Stripe is the one to pick if you have a choice: it is the only method that confirms payment and delivers the ticket without any manual step.</p>
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
            This section issues the key that authenticates REST API requests for your account. The endpoints themselves are a <strong class="text-gray-900 dark:text-white">Pro</strong> feature: the key can be generated on any plan, but every call is checked against the schedule it touches and a schedule below Pro is refused. See the <a href="{{ route('marketing.docs.developer.api') }}" class="doc-link">API Reference</a> for the full endpoint list.
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Enable API Access</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A single toggle. Saving it on generates a key straight away. Saving it off deletes the key, so anything using it stops working immediately.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">API Key</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The full key is displayed exactly once, right after it is generated, next to a copy button. From then on the field shows dots and there is no way to reveal the key again, so store it somewhere safe the moment you create it. Send it as the <code class="doc-inline-code">X-API-Key</code> request header.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Expiry</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A key is valid for one year from the day it is issued. After that requests are rejected as expired, and you get a fresh key by turning the toggle off and on again.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Important</div>
            <p>The key grants the same access to your account that you have. If you suspect it has leaked, turn the API off and on again: that deletes the old key and issues a new one, and the old key stops working at once.</p>
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
            A webhook POSTs a JSON payload to a URL of yours whenever something happens in your schedules, such as a sale, an event change or a check-in. Webhooks are a <strong class="text-gray-900 dark:text-white">Pro</strong> feature: if none of your schedules are on Pro the section shows an upgrade notice, and activity on a schedule below Pro is never delivered. See the <a href="{{ route('marketing.docs.developer.webhooks') }}" class="doc-link">Webhook documentation</a> for payload formats and signature verification.
        </p>

        <h3 class="doc-subheading">Adding a webhook</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Enter the endpoint <strong class="text-gray-900 dark:text-white">URL</strong> that should receive the requests.</li>
            <li>Add an optional <strong class="text-gray-900 dark:text-white">Description</strong> so you can tell endpoints apart in the list.</li>
            <li>Under <strong class="text-gray-900 dark:text-white">Event types</strong>, leave every type switched on or turn off the ones you do not want. All types are on by default.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Add Webhook</strong>. The signing secret appears once at the top of the section with a copy button, and is never shown again.</li>
        </ol>

        <h3 class="doc-subheading">Managing webhooks</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Each saved webhook is a row with its description, URL and subscribed event types, and a row of icon buttons:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Enable or disable</strong> the endpoint. A disabled webhook is dimmed and receives nothing.</li>
            <li><strong class="text-gray-900 dark:text-white">Send a test ping</strong> to check the endpoint answers.</li>
            <li><strong class="text-gray-900 dark:text-white">Edit</strong> opens a panel to change the URL, description and event types, and holds the <strong class="text-gray-900 dark:text-white">Regenerate secret</strong> link.</li>
            <li><strong class="text-gray-900 dark:text-white">Delete</strong> the endpoint after a confirmation.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            <strong class="text-gray-900 dark:text-white">View recent deliveries</strong> under each webhook expands a log of the latest attempts, with the event type, the response status (or <code class="doc-inline-code">timeout</code>), how long the request took and when it ran. The row also shows when the endpoint last fired.
        </p>
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
            Two separate Google connections live here. Connecting one does not connect the other, and each can be disconnected on its own.
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Google Account</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Links your Google identity so you can sign in with Google. <strong class="text-gray-900 dark:text-white">Disconnect</strong> is only available once your account has a password, otherwise you would lock yourself out; set one first under <a href="#password" class="doc-link">Update Password</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Google Calendar</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Authorises two-way sync between Event Schedule and your Google Calendar. Every member of a schedule connects their own Google account here, so one shared schedule can sync into several personal calendars. Once connected, switch sync on for each schedule under <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-google" class="doc-link">Integrations</a>.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Note</div>
            <p>A synced calendar gets one entry per event date that Event Schedule pushes, not a repeating series: a recurring event arrives as a single entry on the date the series starts. Use the schedule's iCal subscription feed if you want every date of a recurring event in your calendar.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Connecting your account here does nothing on its own. Sync is enabled per schedule in the schedule's settings, and each team member controls their own connection.</p>
        </div>
    </section>

    <!-- Outlook Calendar -->
    <section id="microsoft" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Outlook Calendar
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The section is called <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong> in the settings navigation. It connects your Outlook or Microsoft 365 account through the Microsoft Graph API.
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Connecting</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Connect Outlook Calendar</strong> and approve the permissions on Microsoft. Changes then flow both ways: Microsoft notifies Event Schedule as they happen, and a catch-up sync runs every 15 minutes in case a notification is missed. Once connected, switch sync on for each schedule under <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-microsoft" class="doc-link">Integrations</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Selfhosted installations</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The integration needs Microsoft app credentials on the server. Without them the section reads "Outlook Calendar is not configured on this server" and there is no connect button. See the <x-link href="{{ route('marketing.docs.selfhost.microsoft_calendar') }}">selfhosted Outlook setup guide</x-link>.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Enable the Teams meetings option on a schedule to have a Microsoft Teams meeting created automatically for its online events.</p>
        </div>
    </section>

    <!-- Backup & Restore -->
    <section id="backup" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
            </svg>
            Backup &amp; Restore
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Export your schedules as a portable <code class="doc-inline-code">.zip</code> file, or restore one you exported earlier. A backup carries the schedule itself plus its sub-schedules, events, tickets, sales, promo codes and newsletters, and optionally the images. The section has two tabs, <strong class="text-gray-900 dark:text-white">Export</strong> and <strong class="text-gray-900 dark:text-white">Import</strong>.
        </p>

        <h3 class="doc-subheading">Exporting</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>On the <strong class="text-gray-900 dark:text-white">Export</strong> tab, review the schedule list. Every schedule you can edit is listed and all are selected, so clear the ones you do not want.</li>
            <li>Turn on <strong class="text-gray-900 dark:text-white">Include images</strong> to bundle profile images, flyers and photos. It is off by default and makes the file considerably larger.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Export</strong>. Progress is reported in place, item by item, and you can cancel while it runs.</li>
            <li>When it finishes, a <strong class="text-gray-900 dark:text-white">Download</strong> link appears. The link expires after 7 days. On installations that run a queue worker you also get an email when the export is ready.</li>
        </ol>

        <h3 class="doc-subheading">Importing</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>On the <strong class="text-gray-900 dark:text-white">Import</strong> tab, choose a <code class="doc-inline-code">.zip</code> export and click <strong class="text-gray-900 dark:text-white">Upload</strong>.</li>
            <li>A preview lists each schedule in the file with its type, its event, ticket and sale counts and its date range. All are selected, so clear any you do not want.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Start Import</strong>. Imported schedules are always created as new schedules, so nothing you already have is overwritten.</li>
            <li>When it finishes you get a report per schedule, counting what was imported for each kind of record and listing any warnings, with a link to the new schedule. On installations that run a queue worker the detailed report is also emailed to you.</li>
        </ol>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Note</div>
            <p>On the hosted platform, newsletter recipient emails, segment contacts and unsubscribe lists are left out of exports. They come from followers who did not share their address with your schedule directly.</p>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Important</div>
            <p>On selfhosted installations, exports do contain personal data such as names and email addresses, so store the files securely. To move a whole installation to another server, <code class="doc-inline-code">mysqldump</code> is a better tool than export and import.</p>
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
            On a selfhosted installation this section shows the <strong class="text-gray-900 dark:text-white">Installed Version</strong> next to the <strong class="text-gray-900 dark:text-white">Latest Version</strong> published on GitHub. If they match it simply says you are up to date. If they differ, an <strong class="text-gray-900 dark:text-white">Update</strong> button downloads and applies the new release in one click.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A large update can outrun PHP's execution limit. If it times out, run <code class="doc-inline-code">php artisan app:update</code> from the command line, raise <code class="doc-inline-code">max_execution_time</code> in <code class="doc-inline-code">php.ini</code>, or download the release zip linked under the button and extract it to <code class="doc-inline-code">/tmp/eventschedule/</code> before trying again.
        </p>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>This section never appears on eventschedule.com, where everyone is always on the latest version automatically. On a single-tenant selfhost any signed-in user sees it; on a selfhosted platform running in hosted mode it is limited to the instance administrator, so a customer cannot update the whole installation.</p>
            <p class="mt-3">Instance admins have the same panel at <strong class="text-gray-900 dark:text-white">Admin &gt; System &gt; App Update</strong>, which adds a last-checked time, a manual check and a badge on the System menu when a release is waiting. Either way, <code class="doc-inline-code">php artisan app:update</code> does the same job from the command line and works even when neither screen is available.</p>
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
            Enter your current password, choose a new one and save. A password must be at least 8 characters long.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            If your account was created by signing in with Google it has no password yet, and the section is titled <strong class="text-gray-900 dark:text-white">Set Password</strong> instead. It first asks you to confirm who you are with Google; after that you have a few minutes to choose a password, and only the new password field is shown.
        </p>
        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Setting a password is also what makes it possible to disconnect Google later, and it gives you a way in if you ever lose access to your Google account.</p>
        </div>
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
            Two-factor authentication (2FA) adds a second step to signing in, using a time-based one-time password from an authenticator app such as Google Authenticator, Authy or 1Password.
        </p>

        <h3 class="doc-subheading">Turning it on</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Enter your current password and click <strong class="text-gray-900 dark:text-white">Enable Two-Factor Authentication</strong>. Accounts with no password, created by signing in with Google, are not asked for one.</li>
            <li>Scan the QR code with your authenticator app. If you cannot scan, type the key printed under the code into the app by hand.</li>
            <li>Save the recovery codes shown on the same screen, somewhere other than the device holding the authenticator app.</li>
            <li>Type the 6-digit code from the app and click <strong class="text-gray-900 dark:text-white">Confirm</strong>. Two-factor authentication is not active until this step succeeds.</li>
        </ol>

        <h3 class="doc-subheading">Afterwards</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Recovery codes</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Each code works once and lets you sign in without the authenticator app. <strong class="text-gray-900 dark:text-white">Regenerate Codes</strong> issues a fresh set and displays it once; every earlier code stops working at that moment.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Turning it off</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Click <strong class="text-gray-900 dark:text-white">Disable</strong> and type your password into the prompt, which only appears if your account has one. Disabling removes the authenticator secret and every remaining recovery code, so switching 2FA back on later starts from a new QR code.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Important</div>
            <p>Store your recovery codes securely. If you lose the authenticator app and no longer have the codes, you will not be able to sign in.</p>
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
            Permanently deletes your account and everything attached to it. That means:
        </p>
        <ul class="doc-list mb-6">
            <li>Your user account, profile and profile image</li>
            <li>Every schedule you own, with its events, tickets and sales</li>
            <li>Connections to linked services such as Stripe and Google, including any calendar sync they were running</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Clicking <strong class="text-gray-900 dark:text-white">Delete Account</strong> opens a confirmation dialog with an optional <strong class="text-gray-900 dark:text-white">Why are you leaving?</strong> box. Anything you write there is emailed to the Event Schedule team as feedback and helps us improve the platform. Before that, download anything you want to keep, for example with <a href="#backup" class="doc-link">Backup &amp; Restore</a>.
        </p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Warning</div>
            <p>Deletion is permanent and cannot be undone. If your account has a password you are asked to type it in the dialog to confirm; accounts that only sign in with Google confirm without one.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>The Delete Account section is only shown on the hosted platform (eventschedule.com). On a selfhosted installation the administrator manages accounts on the server instead.</p>
        </div>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.getting_started') }}" class="doc-link">Getting Started</a> - Set up your first schedule</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - Choose a payment method per event once one is connected</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="doc-link">Advanced Schedule Settings</a> - Per-schedule calendar sync, sending address and other integrations</li>
            <li><a href="{{ route('marketing.docs.developer.api') }}" class="doc-link">API Reference</a> - Full API documentation for developers</li>
            <li><a href="{{ route('marketing.docs.developer.webhooks') }}" class="doc-link">Webhooks</a> - Payload formats and signature verification</li>
        </ul>
    </section>


    <x-slot:schema>
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
                    "text": "Connect Stripe, Invoice Ninja or Payfast, or set a payment link, to start selling tickets.",
                    "url": "{{ url(route('marketing.docs.account_settings')) }}#payments"
                },
                {
                    "@type": "HowToStep",
                    "name": "Connect Google Services",
                    "text": "Link your Google account and enable Google Calendar sync for two-way event synchronization.",
                    "url": "{{ url(route('marketing.docs.account_settings')) }}#google"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
