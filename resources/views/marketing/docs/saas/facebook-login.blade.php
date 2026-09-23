<x-docs-page
    key="saas/facebook-login"
    title="Facebook Login Setup - Event Schedule"
    description="Set up Continue with Facebook sign-in for your Event Schedule platform: create the Meta app, register the redirect URIs, configure the credentials and go Live."
    lede="Let your customers sign up and log in with Facebook. Create a Meta app, register three redirect URIs, and set two environment variables."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#create-app">1. Create the app</x-doc-nav-link>
        <x-doc-nav-link href="#basic-settings">2. Basic settings</x-doc-nav-link>
        <x-doc-nav-link href="#login-settings">3. Facebook Login settings</x-doc-nav-link>
        <x-doc-nav-link href="#permissions">4. Permissions</x-doc-nav-link>
        <x-doc-nav-link href="#configure">5. Configure Event Schedule</x-doc-nav-link>
        <x-doc-nav-link href="#go-live">6. Test, then go Live</x-doc-nav-link>
        <x-doc-nav-link href="#account-matching">How accounts are matched</x-doc-nav-link>
        <x-doc-nav-link href="#disable">Turning it off</x-doc-nav-link>
    </x-slot:toc>

    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Adds <span class="font-semibold text-gray-900 dark:text-white">Continue with Facebook</span> to the login and sign-up pages, and a <span class="font-semibold text-gray-900 dark:text-white">Facebook Settings</span> section where your customers connect or disconnect Facebook, or verify with it before setting a password. It is optional and <span class="font-semibold text-gray-900 dark:text-white">off by default</span>. Until both values under <a href="#configure" class="doc-link">Configure Event Schedule</a> are set there is no Facebook button, settings section or sidebar link, no Facebook entry in the bundled privacy policy's processor list, and every <code class="doc-inline-code">/auth/facebook</code> URL returns 404.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This is a separate Meta app from the one Boost uses (<code class="doc-inline-code">META_APP_ID</code>). Do not reuse those credentials.</p>
    </section>

    <section id="create-app" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            1. Create the app
        </h2>
        <ol class="doc-list doc-list-numbered">
            <li>At <a href="https://developers.facebook.com/apps" target="_blank" rel="noopener noreferrer" class="doc-link">developers.facebook.com</a>, click <span class="font-semibold text-gray-900 dark:text-white">Create App</span>.</li>
            <li>Choose the use case <span class="font-semibold text-gray-900 dark:text-white">Authenticate and request data from users with Facebook Login</span>.</li>
            <li>If you are asked for an app type, choose <span class="font-semibold text-gray-900 dark:text-white">Consumer</span>. A Business app needs business verification before the email permission works for anyone outside the app's roles.</li>
        </ol>
    </section>

    <section id="basic-settings" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            2. Basic settings
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Under <span class="font-semibold text-gray-900 dark:text-white">App settings &rarr; Basic</span>:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">App domains</span></td>
                        <td>Your domain, e.g. <code class="doc-inline-code">yourdomain.com</code></td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Privacy policy URL</span></td>
                        <td>Your privacy policy. If you replaced it at <code class="doc-inline-code">/admin/legal</code>, use that one.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Terms of service URL</span></td>
                        <td>Your terms of service</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">User data deletion</span></td>
                        <td>Choose <span class="font-semibold text-gray-900 dark:text-white">Data deletion instructions URL</span> and point it at your privacy policy. Customers delete their account under Settings &rarr; Delete Account, and only the Facebook account ID is stored, so no callback endpoint is needed.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">App icon and category</span></td>
                        <td>Both are required before the app can go Live</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Copy the <span class="font-semibold text-gray-900 dark:text-white">App ID</span> and <span class="font-semibold text-gray-900 dark:text-white">App Secret</span>.</p>
    </section>

    <section id="login-settings" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            3. Facebook Login settings
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Under <span class="font-semibold text-gray-900 dark:text-white">Use cases &rarr; Facebook Login &rarr; Settings</span>, turn on Client OAuth login, Web OAuth login, Enforce HTTPS and Strict Mode for redirect URIs. Then add these three <span class="font-semibold text-gray-900 dark:text-white">Valid OAuth Redirect URIs</span>. In SaaS mode signing in lives on the <code class="doc-inline-code">app</code> subdomain, so use that host:</p>
        <pre class="rounded-xl bg-gray-100 dark:bg-[#1A1A1A] p-4 text-sm overflow-x-auto"><code>https://app.yourdomain.com/auth/facebook/callback
https://app.yourdomain.com/auth/facebook/connect/callback
https://app.yourdomain.com/auth/facebook/set-password/callback</code></pre>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Register all three</div>
            <p>Facebook rejects any redirect that is not on the list, so a missing one breaks only its own flow (signing in, connecting from Settings, or verifying before setting a password), and Facebook's error does not say which. For local testing, add the same three paths on your local <code class="doc-inline-code">APP_URL</code>; Facebook accepts <code class="doc-inline-code">http://localhost</code> only while the app is in Development mode.</p>
        </div>
    </section>

    <section id="permissions" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            4. Permissions
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Under <span class="font-semibold text-gray-900 dark:text-white">Use cases &rarr; Facebook Login &rarr; Customize</span>, make sure <code class="doc-inline-code">email</code> and <code class="doc-inline-code">public_profile</code> are added. On a Consumer app both have Advanced Access by default, so no App Review is needed.</p>
    </section>

    <section id="configure" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            5. Configure Event Schedule
        </h2>
        <pre class="rounded-xl bg-gray-100 dark:bg-[#1A1A1A] p-4 text-sm overflow-x-auto"><code>FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret</code></pre>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Both values are required; with only one set, Facebook login stays off. Run <code class="doc-inline-code">php artisan config:clear</code> afterwards. <code class="doc-inline-code">FACEBOOK_REDIRECT_URI</code> can stay unset, because every request passes its own redirect URI.</p>
    </section>

    <section id="go-live" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            6. Test, then go Live
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">While the app is in Development mode only people with a role on it can sign in. Add yourself under <span class="font-semibold text-gray-900 dark:text-white">App roles &rarr; Roles</span> and create a test user under <span class="font-semibold text-gray-900 dark:text-white">App roles &rarr; Test users</span>, then check:</p>
        <ul class="doc-list">
            <li>A new sign-up with Facebook lands on the getting-started page with a verified email</li>
            <li>An existing account with the same email is asked to log in once the usual way, and Facebook is linked as soon as it does</li>
            <li>Pressing <span class="font-semibold text-gray-900 dark:text-white">Cancel</span> on the Facebook dialog returns to the login page with no error</li>
            <li>Unticking the email permission shows <span class="font-semibold text-gray-900 dark:text-white">Try again</span>, which asks for the email again</li>
            <li>Settings &rarr; Facebook Settings connects and disconnects, and refuses to disconnect when Facebook is the account's only way in</li>
            <li>A Facebook-only account can use <span class="font-semibold text-gray-900 dark:text-white">Verify with Facebook</span> under Settings &rarr; Set Password</li>
            <li>The login page marks the button last used with a <span class="font-semibold text-gray-900 dark:text-white">Last used</span> chip</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Then switch <span class="font-semibold text-gray-900 dark:text-white">App Mode</span> to <span class="font-semibold text-gray-900 dark:text-white">Live</span> at the top of the app dashboard. Until you do, everyone else sees "App not active".</p>
    </section>

    <section id="account-matching" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            How accounts are matched
        </h2>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Facebook sign-in finds</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>An account already linked to this Facebook account</td>
                        <td>Signed in</td>
                    </tr>
                    <tr>
                        <td>No email from Facebook</td>
                        <td>Back to the login page with a <span class="font-semibold text-gray-900 dark:text-white">Try again</span> that re-requests the email permission</td>
                    </tr>
                    <tr>
                        <td>An invited placeholder account with this email (no password, Google or Facebook yet)</td>
                        <td>Linked and signed in</td>
                    </tr>
                    <tr>
                        <td>An account with this email that has a password or Google</td>
                        <td>Asked to log in with those once. The Facebook account is linked on that login (including after two-factor), within 10 minutes and only if the signed-in email matches</td>
                    </tr>
                    <tr>
                        <td>An account with this email linked to a different Facebook account</td>
                        <td>Refused</td>
                    </tr>
                    <tr>
                        <td>Nobody</td>
                        <td>A new account, created under the same rules as the sign-up form</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Keep the same Meta app</div>
            <p>Facebook gives every app its own ID for each person, and that ID is what Event Schedule stores. Pointing the install at a different Meta app later disconnects everyone who linked Facebook: people with a password or Google are asked to log in once to re-link, and Facebook-only people have to use <span class="font-semibold text-gray-900 dark:text-white">Reset password</span>. To rotate credentials, reset the App Secret on the same app.</p>
        </div>
    </section>

    <section id="disable" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9" />
            </svg>
            Turning it off
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">To turn Facebook login off, unset either value. All Facebook UI disappears and the routes return 404, while stored links are kept, so turning it back on restores them. In the meantime, people who only ever signed in with Facebook get back in with <span class="font-semibold text-gray-900 dark:text-white">Reset password</span> on the login page.</p>
    </section>
</x-docs-page>
