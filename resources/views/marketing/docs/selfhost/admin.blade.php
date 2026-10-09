<x-docs-page
    key="selfhost/admin"
    title="Admin Panel for Selfhosted Installs - Event Schedule"
    description="Run the admin panel of a selfhosted Event Schedule: the Needs attention list, revenue and refunds, and managing, releasing or restoring any schedule."
    lede="Monitor your install's users, revenue and analytics, manage any schedule on it, and change the settings that apply to every page."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#accessing">Accessing /admin</x-doc-nav-link>
        <x-doc-nav-link href="#dashboard">Dashboard</x-doc-nav-link>
        <x-doc-nav-link href="#realtime">Realtime</x-doc-nav-link>
        {{-- The three groups are the admin panel's own three tabs, and the pages inside each are
             in the order its second row lists them (admin/partials/_navigation: $adminNav). --}}
        <x-doc-nav-group label="Insights" expanded>
            <x-doc-nav-link href="#insights-users">Users</x-doc-nav-link>
            <x-doc-nav-link href="#insights-revenue">Revenue</x-doc-nav-link>
            <x-doc-nav-link href="#insights-analytics">Analytics</x-doc-nav-link>
            <x-doc-nav-link href="#insights-usage">Usage</x-doc-nav-link>
            <x-doc-nav-link href="#insights-growth">Growth</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Manage" expanded>
            <x-doc-nav-link href="#manage-boost">Boost</x-doc-nav-link>
            <x-doc-nav-link href="#manage-plans">Schedules</x-doc-nav-link>
            <x-doc-nav-link href="#manage-blocked">Blocked</x-doc-nav-link>
            <x-doc-nav-link href="#manage-feeds">Feeds</x-doc-nav-link>
            <x-doc-nav-link href="#manage-domains">Domains</x-doc-nav-link>
            <x-doc-nav-link href="#manage-referrals">Referrals</x-doc-nav-link>
            <x-doc-nav-link href="#manage-newsletters">Newsletters</x-doc-nav-link>
            <x-doc-nav-link href="#manage-blog">Blog</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="System" expanded>
            <x-doc-nav-link href="#system-audit-log">Audit Log</x-doc-nav-link>
            <x-doc-nav-link href="#system-queue">Queue</x-doc-nav-link>
            <x-doc-nav-link href="#system-logs">Logs</x-doc-nav-link>
            <x-doc-nav-link href="#system-app-update">App Update</x-doc-nav-link>
            <x-doc-nav-link href="#system-settings">Settings</x-doc-nav-link>
            <x-doc-nav-link href="#system-translations">Translations</x-doc-nav-link>
            <x-doc-nav-link href="#system-legal-pages">Legal Pages</x-doc-nav-link>
            <x-doc-nav-link href="#system-federation">Federation</x-doc-nav-link>
            <x-doc-nav-link href="#system-support">Support</x-doc-nav-link>
        </x-doc-nav-group>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The admin panel gives the operator of an installation platform-wide visibility and a small set of platform-wide controls. It lives at <code class="doc-inline-code">/admin</code>, opens from the <strong class="text-gray-900 dark:text-white">Admin</strong> entry of the sidebar, and is only there for <a href="#accessing" class="doc-link">accounts marked as admin</a>: it has nothing to do with plans. It is separate from a schedule owner's own admin portal, and nothing here is scoped to one schedule.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The navigation is five tabs. <strong class="text-gray-900 dark:text-white">Dashboard</strong> and <strong class="text-gray-900 dark:text-white">Realtime</strong> are each a page; <strong class="text-gray-900 dark:text-white">Insights</strong>, <strong class="text-gray-900 dark:text-white">Manage</strong> and <strong class="text-gray-900 dark:text-white">System</strong> each hold several, and which of those appear depends on how the install is configured. This guide follows the same order.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Tab</th>
                        <th>Pages, in the order the panel lists them</th>
                        <th>Not on every install</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Dashboard</td>
                        <td><a href="#dashboard" class="doc-link">The page itself</a>: the headline numbers, what is new, and the Needs attention list</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Realtime</td>
                        <td><a href="#realtime" class="doc-link">The page itself</a>: who is on the site right now, and the last 24 hours of sign-ups and orders</td>
                        <td>Every install has the tab. Recording starts switched off, except on eventschedule.com</td>
                    </tr>
                    <tr>
                        <td>Insights</td>
                        <td><a href="#insights-users" class="doc-link">Users</a>, <a href="#insights-revenue" class="doc-link">Revenue</a>, <a href="#insights-analytics" class="doc-link">Analytics</a>, <a href="#insights-usage" class="doc-link">Usage</a>, <a href="#insights-growth" class="doc-link">Growth</a></td>
                        <td>Growth: only when <code class="doc-inline-code">IS_HOSTED=true</code></td>
                    </tr>
                    <tr>
                        <td>Manage</td>
                        <td><a href="#manage-boost" class="doc-link">Boost</a>, <a href="#manage-plans" class="doc-link">Schedules</a>, <a href="#manage-domains" class="doc-link">Domains</a>, <a href="#manage-referrals" class="doc-link">Referrals</a>, <a href="#manage-newsletters" class="doc-link">Newsletters</a>, <a href="#manage-blog" class="doc-link">Blog</a></td>
                        <td>Domains and Referrals: only when <code class="doc-inline-code">IS_HOSTED=true</code>. Blog: eventschedule.com only</td>
                    </tr>
                    <tr>
                        <td>System</td>
                        <td><a href="#system-audit-log" class="doc-link">Audit Log</a>, <a href="#system-queue" class="doc-link">Queue</a>, <a href="#system-logs" class="doc-link">Logs</a>, <a href="#system-app-update" class="doc-link">App Update</a>, <a href="#system-settings" class="doc-link">Settings</a>, <a href="#system-translations" class="doc-link">Translations</a>, <a href="#system-legal-pages" class="doc-link">Legal Pages</a>, <a href="#system-federation" class="doc-link">Federation</a>, <a href="#system-support" class="doc-link">Support</a></td>
                        <td>App Update: every install except eventschedule.com. Federation (the moderation queue for other installs): eventschedule.com only. Support: only when <code class="doc-inline-code">IS_HOSTED=true</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The tabs run across the top of every admin page. A tab that holds several pages opens its first one, and a second row under the tabs lists them all, with the page you are on marked. On a phone the whole section is one dropdown, grouped the same way. A number beside a tab or a page counts what is waiting on you there; see <a href="#needs-attention" class="doc-link">Needs attention</a>.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A <strong class="text-gray-900 dark:text-white">Refresh</strong> button sits at the end of the navigation row and reloads the page. Realtime updates itself and Support checks for new messages on its own; every other page shows its numbers as of the last page load.</p>

        <h3 id="date-range" class="doc-subheading">Date range</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Six pages carry a date-range selector, at the end of the line that says what the page shows: Users, Revenue, Analytics, Usage, Growth and Boost. The choices are fixed: <strong class="text-gray-900 dark:text-white">Last 7 Days</strong>, <strong class="text-gray-900 dark:text-white">Last 30 Days</strong> (the default), <strong class="text-gray-900 dark:text-white">Last 90 Days</strong> and <strong class="text-gray-900 dark:text-white">All Time</strong>. There is no custom start and end date. Where a page shows a change percentage, it compares against the immediately preceding window of the same length. All Time has no preceding window, so read its change figures as noise: with nothing to compare against, every one of them reports +100%.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Dashboard has no selector: its windows are fixed at the last 24 hours, the last 30 days and the last 12 weeks. The Audit Log has its own From and To date filter instead, and the remaining pages are not date filtered at all.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Why the totals look low</div>
            <p>The counts across the Dashboard and Insights deliberately exclude demo data, users who never confirmed their email address, and schedules that verified neither an email address nor a phone number. A brand new install that has not verified anything therefore reports zero users and zero schedules even though rows exist in the database.</p>
        </div>
    </section>

    <!-- Accessing /admin -->
    <section id="accessing" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            Accessing /admin
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The admin panel lives at <code class="doc-inline-code">/admin</code>, which redirects to <code class="doc-inline-code">/admin/dashboard</code>. It is restricted to users whose <code class="doc-inline-code">is_admin</code> column is set to <code class="doc-inline-code">true</code>. There is no screen for granting that, on purpose, so it is done from the command line or in the database.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Grant the flag by running <code class="doc-inline-code">php artisan app:make-admin you@example.com</code> on the server, or by updating the <code class="doc-inline-code">users</code> table directly (see the query below). Run the command with no email to list who is an admin already.</li>
            <li>Make sure the account has a password. An account created through Google or Facebook sign-in has none, and the panel will send you to the <a href="{{ route('marketing.docs.account_settings') }}#password" class="doc-link">Security</a> tab of your settings to set one first.</li>
            <li>On an install with <code class="doc-inline-code">IS_HOSTED=true</code>, turn on <a href="{{ route('marketing.docs.account_settings') }}#two-factor" class="doc-link">two-factor authentication</a> for the account. The panel refuses an admin without it and sends them to the same Security tab. A plain selfhost does not ask for it unless you set <code class="doc-inline-code">ADMIN_REQUIRE_2FA=true</code>.</li>
            <li>Sign in and open <code class="doc-inline-code">/admin</code>, or use the <strong class="text-gray-900 dark:text-white">Admin</strong> entry that now appears in the main sidebar, just above <strong class="text-gray-900 dark:text-white">Settings</strong>.</li>
            <li>Re-enter your password when prompted. This confirmation is required once per session, on top of being signed in.</li>
        </ol>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>Terminal</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>php artisan app:make-admin you@example.com</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Or, if you would rather do it in the database:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>SQL</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-keyword">UPDATE</span> users <span class="code-keyword">SET</span> is_admin <span class="code-keyword">=</span> <span class="code-value">1</span> <span class="code-keyword">WHERE</span> email <span class="code-keyword">=</span> <span class="code-string">'your@email.com'</span>;</code></pre>
        </div>

        <h3 class="doc-subheading">Session protections</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Password confirmation</strong> - stored per session, so a rejoined session asks again. Failed and successful confirmations are both recorded in the audit log.</li>
            <li><strong class="text-gray-900 dark:text-white">Re-authentication window</strong> - a confirmation lasts 24 hours of inactivity, and loading any admin page restarts that clock, so an admin at work is not interrupted. Separately, a confirmation is never good for more than 30 days no matter how continuously the panel is used - only entering the password again resets that ceiling. Set <code class="doc-inline-code">ADMIN_REAUTH_TIMEOUT</code> and <code class="doc-inline-code">ADMIN_REAUTH_MAX_LIFETIME</code> (both in seconds) to change them. Neither can outlast <code class="doc-inline-code">SESSION_LIFETIME</code> (minutes), because both are stored in the session.</li>
            <li><strong class="text-gray-900 dark:text-white">Browser binding</strong> - the confirmed session is tied to the browser that confirmed it. If the user agent changes, the confirmation is dropped, an <code class="doc-inline-code">admin.session_changed</code> entry is written, and you are asked to confirm again.</li>
            <li><strong class="text-gray-900 dark:text-white">Rate limits</strong> - admin pages allow 30 requests per minute per user, and the password confirmation form allows 5 attempts per minute.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>Only grant admin access to trusted people. Admins can see all platform data including user email addresses, revenue, and system logs, and can change settings that affect every public page. Admin actions are written to the <a href="#system-audit-log" class="doc-link">audit log</a>.</p>
        </div>
    </section>

    <!-- Dashboard -->
    <section id="dashboard" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Dashboard
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The dashboard is the landing page of the admin panel and is read-only. Its windows are fixed: the last 24 hours, the last 30 days and the last 12 weeks. An install with no schedule, no event and no account but yours shows the headline numbers and a single card until there is something to report. From top to bottom it shows:</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Needs attention</h4>
                <p>Everything waiting on an admin, as a row of links, when there is anything (see <a href="#needs-attention" class="doc-link">below</a>). While <a href="#realtime" class="doc-link">Realtime</a> is on, a line in the same place gives the number of page views in the last 5 minutes, with a <strong>View realtime</strong> link. It is a count as of the page load, not a live figure.</p>
            </div>
            <div class="doc-field">
                <h4>Four headline numbers</h4>
                <p>New organizers in the last 24 hours and the last 30 days, active users in the last 7 days, monthly recurring revenue, and upcoming events. A selfhost has no billing, so its third number is the events added in 30 days. Each number jumps to the card that explains it, once those cards are on the page.</p>
            </div>
            <div class="doc-field">
                <h4>Recent schedules and Recent events</h4>
                <p>The twenty newest of each, eight shown until you choose <strong>Show more</strong>, with the schedule's photo or the event's flyer. Unlisted events and appointment bookings are left out. A draft, a guest submission and an imported event carry a flag, and a burst of events from one schedule is a single row. Rows added since your last visit in this browser are marked.</p>
            </div>
            <div class="doc-field">
                <h4>Sign-ups</h4>
                <p>One bar a day for 30 days, against the 30 days before. The headline counts organizers: accounts created to run a schedule. Accounts created by following a schedule, buying a ticket or submitting an event are counted beside them.</p>
            </div>
            <div class="doc-field">
                <h4>Active users</h4>
                <p>The people who used the app while signed in during the seven days up to each date, for 12 weeks. The days before this record began are an estimate from sign-ins and event edits. They are drawn dashed and run low.</p>
            </div>
            <div class="doc-field">
                <h4>Where sign-ups came from</h4>
                <p>The organizers' channel (search, AI assistants, social, the referral program, email, paid, a campaign, another website, direct) with its most common sources, and the first page they saw. Not recorded means no referrer or campaign was stored: a first visit to a marketing page is stored only with cookie consent.</p>
            </div>
            <div class="doc-field">
                <h4>Latest sign-ups</h4>
                <p>The eight newest organizers of the last 30 days, where each came from, and how far each has got: a schedule, an event, a ticket type.</p>
            </div>
            <div class="doc-field">
                <h4>Revenue</h4>
                <p>Hosted installs only. What Stripe is billing, as MRR and ARR, by plan, with the subscriptions still in their trial counted beside it and what they would add. At risk is the part of MRR that is past due or cancelling. Outside Stripe counts paid plans nobody is billed for: a plan granted by an admin or a referral, a Pro trial started without a card, and a selling trial. Boost markup revenue for the last 30 days closes the card. A plain selfhost has no subscriptions and charges no markup, so this card is left out there.</p>
            </div>
            <div class="doc-field">
                <h4>Upcoming events</h4>
                <p>Published events with a date still ahead, a recurring series counted once, split by how people attend (in person, online, hybrid, or no location), with the top five countries of the ones held at a venue (hybrid events included) and how many one-off events start in the next 24 hours, the next 7 days and the next 30. An event whose schedules have all been deleted is not counted.</p>
            </div>
            <div class="doc-field">
                <h4>Federation</h4>
                <p>On eventschedule.com, the selfhost installs that share their events, their live listings and the clicks sent on to them. On any other install, the state of its own sharing, once that is switched on.</p>
            </div>
            <div class="doc-field">
                <h4>Queue and accounts</h4>
                <p>One line at the foot of the page: jobs waiting and failed, and the number of accounts, with custom domains beside them on a hosted install. Use the <a href="#system-queue" class="doc-link">Queue</a> page to act on a failed job.</p>
            </div>
        </div>
        <x-doc-screenshot id="selfhost-admin--dashboard" alt="Admin dashboard on a selfhost install showing new organizers, active users, events added, upcoming events and the newest schedules and events" />

        <div class="doc-callout doc-callout-info mt-6">
            <div class="doc-callout-title">Selfhost: update your privacy policy</div>
            <p>To count active users, the app records the dates on which each signed-in person used it, with nothing about what they did, and keeps them for 120 days. Your own privacy policy has to say so: add it at <a href="#system-legal-pages" class="doc-link">Legal Pages</a>. The policy built into the app is eventschedule.com's and does not speak for your install, which is why the admin panel asks you to write one.</p>
        </div>

        <h3 id="needs-attention" class="doc-subheading">Needs attention</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Above the metrics sits <strong class="text-gray-900 dark:text-white">Needs attention</strong>: every queue in the admin panel that is waiting on a person, as a row of links, each going straight to the page where you deal with it. The first four are shown and the rest open from the last link. It is only rendered when there is something in it, so a dashboard without it means nothing needs you.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Rows are listed in the order below: breakage and held-up money first, then review queues, then rows that are informational. A row with a count of zero is omitted, and a row whose page does not exist on this install can never appear.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>What it means</th>
                        <th>Appears on</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Scheduled tasks are not running</td>
                        <td>No scheduler run has been recorded within <code class="doc-inline-code">SCHEDULER_STALE_MINUTES</code> (20 by default), so reminders, queued mail and every other timed job have stopped. Links to Queue.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Queued jobs are not draining</td>
                        <td>A job has been due for more than an hour and is still waiting, which normally means nothing is running the queue. It is held back while the row above is showing, because a stopped scheduler is the usual cause. Links to Queue.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Failed jobs</td>
                        <td>Rows in the failed job table. Links to Queue.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Venue map address lookups are failing</td>
                        <td>The address search behind the <a href="#venue-map" class="doc-link">venue map</a> has not answered for an hour, so maps that owners switched on are still waiting for their pins. Check that the address in <code class="doc-inline-code">MAP_GEOCODER_URL</code> is reachable from the server. Links to Queue.</td>
                        <td>Where <code class="doc-inline-code">MAP_GEOCODER_URL</code> is set</td>
                    </tr>
                    <tr>
                        <td>Feeds are failing, many at once</td>
                        <td>At least five of the sites that feeds read, and half of all the sites being read, could not be read in the last day. That many at once points at this server (its network, or an address of yours that other hosts block) and not at each site. While the row shows, no owner is emailed that their feed is broken and no feed is paused for it. Links to <a href="#manage-feeds" class="doc-link">Feeds</a>, filtered to the failing ones.</td>
                        <td>Every install with a feed</td>
                    </tr>
                    <tr>
                        <td>Realtime page views are not being deleted</td>
                        <td>A <a href="#realtime" class="doc-link">Realtime</a> record more than two hours old is still stored, although they are meant to go about an hour after a visitor's last activity. The scheduled task that deletes them is not running. Links to Queue.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Subscriptions still billing for deleted schedules</td>
                        <td>A subscription Stripe will keep charging although its schedule has been deleted, so its owner can no longer cancel it in the app. Links to a list on the Revenue page, where you cancel it.</td>
                        <td>Installs that sell plans through Stripe</td>
                    </tr>
                    <tr>
                        <td>Subscriptions on an unrecognized price</td>
                        <td>A live Stripe subscription whose price is none of the four plan prices this install sells, so the customer is charged for a plan the app cannot recognize. Links to a list on the Revenue page.</td>
                        <td>Installs that sell plans through Stripe</td>
                    </tr>
                    <tr>
                        <td>Custom domains failed to provision</td>
                        <td>A domain whose provisioning ended in failure. Links to Domains.</td>
                        <td>Hosted</td>
                    </tr>
                    <tr>
                        <td>Campaigns stuck awaiting payment</td>
                        <td>A boost campaign that has sat unpaid for more than 30 minutes, which usually means the payment callback never arrived.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Failed campaigns</td>
                        <td>Boost campaigns that failed in the last 30 days. Nothing ever moves a campaign out of this state, so the window keeps the badge from becoming permanent.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Sales with an amount mismatch</td>
                        <td>A ticket sale where the amount actually paid does not match the amount expected. You approve or refund it on the Revenue page.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Refunds awaiting confirmation</td>
                        <td>A refund sent to Stripe or PayPal whose outcome the provider still has not confirmed after 15 minutes. The money may already have moved, so nothing retries it: you settle it in the provider's own dashboard. Links to a list on the Revenue page.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Campaigns with an amount mismatch</td>
                        <td>The same check on a boost campaign's charge.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Promotions awaiting review</td>
                        <td>A paid on-network promotion waiting for approval before it can serve. Links to the queue on the Boost page.</td>
                        <td>When the promotions network is enabled</td>
                    </tr>
                    <tr>
                        <td>Approved instances changed their address</td>
                        <td>A federated instance whose site address no longer matches what was approved.</td>
                        <td>eventschedule.com only</td>
                    </tr>
                    <tr>
                        <td>Instances awaiting approval</td>
                        <td>A federated instance that has registered and is waiting to be moderated.</td>
                        <td>eventschedule.com only</td>
                    </tr>
                    <tr>
                        <td>Translation suggestions to review</td>
                        <td>Wording shared by another installation, waiting for a decision.</td>
                        <td>eventschedule.com only</td>
                    </tr>
                    <tr>
                        <td>Unread support messages</td>
                        <td>Support chat messages from customers that no admin has read.</td>
                        <td>Hosted</td>
                    </tr>
                    <tr>
                        <td>Campaigns with disapproved ads</td>
                        <td>An ad that Meta rejected, within the last 30 days.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Custom domains still provisioning</td>
                        <td>A domain that is pending, usually waiting on DNS or a certificate.</td>
                        <td>Hosted</td>
                    </tr>
                    <tr>
                        <td>Translations not shared yet</td>
                        <td>Your own translation edits that have not been offered back to the community.</td>
                        <td>Every install except eventschedule.com</td>
                    </tr>
                    <tr>
                        <td>Publish your own privacy policy</td>
                        <td>Visitors are still sent to eventschedule.com's privacy policy although this install collects personal data of its own: a consent-gated integration is configured in <code class="doc-inline-code">.env</code>, registration is open, or a sale has been made. It clears when you save a policy. Links to Legal Pages.</td>
                        <td>Every install except eventschedule.com</td>
                    </tr>
                    <tr>
                        <td>Update available</td>
                        <td>A newer release is published on GitHub. Informational: it clears when you update, not by working through a queue. Links to App Update.</td>
                        <td>Every install except eventschedule.com</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The same counts drive the badges on the Insights, Manage and System tabs and on the pages in the row under them (on a phone, the number in brackets after a page's name in the dropdown). A tab's badge takes the colour of the most serious row it contains, so a failed job is not softened by sitting next to an informational row such as an available update. Each count reuses the query the destination page runs, so a badge and the page it links to cannot disagree.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">A plain selfhost sees fewer rows</div>
            <p>Only rows that can apply to your install are counted. With <code class="doc-inline-code">IS_HOSTED=false</code> the domain and support rows always read zero, the two subscription rows stay empty unless you sell plans through Stripe, and the federation and translation-review rows only ever appear on eventschedule.com itself. Schedules that have not verified an email address or phone number are not a row at all, because they are waiting on their owner rather than on you: their count is the Unverified figure on the <a href="#manage-plans" class="doc-link">Schedules</a> page.</p>
        </div>
    </section>

    <!-- Realtime -->
    <section id="realtime" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.789m13.788 0c3.808 3.808 3.808 9.981 0 13.79M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Realtime
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Realtime shows who is on the site right now: the marketing site, schedule and event pages (custom domains included), the signed-in app, and the sign-up and log-in pages. It is switched on and off at <a href="#settings-realtime" class="doc-link">Settings</a>; it starts on for eventschedule.com and off on every other install. While it is off, the tab holds one card that says what would be recorded, with a <strong>Turn on in settings</strong> button.</p>

        <x-doc-screenshot id="selfhost-admin--realtime" alt="The admin Realtime page: visitors right now by where they are (marketing site, schedule pages, the app, sign-up and log-in), page views per minute, and the Activity rail" />
        <p class="text-gray-600 dark:text-gray-300 mb-6">The page updates itself every ten seconds and slows to once a minute while its tab is hidden; a dot at the top says <strong>Live</strong> or <strong>Reconnecting</strong>. If your <a href="#accessing" class="doc-link">password confirmation</a> lapses while it is open, it stops and asks you to confirm again. From top to bottom:</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Visitors right now</h4>
                <p>People with a page open and visible in the last couple of minutes, split into signed in and anonymous. The buttons beside the number say where those people are: on the marketing site, on schedule pages or in the app (and on the sign-up and log-in pages while someone is there). Each area has its own colour and icon wherever a person or a page appears on this page, and clicking a button filters the whole page to that area.</p>
                <p>Beneath it, the last 30 minutes: the total number of page views, then how many came from visitors who accepted cookies (and how many visitors that was) and how many from visitors who have not.</p>
            </div>
            <div class="doc-field">
                <h4>Page views per minute</h4>
                <p>The last 30 minutes, in the same two colours. A green dot above a minute marks a sign-up; point at that minute to see who.</p>
            </div>
            <div class="doc-field">
                <h4>Activity</h4>
                <p>The last 24 hours of sign-ups, new schedules and events, orders, plan changes and support chats, read from the <a href="#system-audit-log" class="doc-link">audit log</a>. The counts at the top (sign-ups, schedules, events, orders, and upgrades on a day that has one) are buttons: click one to list only that kind.</p>
            </div>
            <div class="doc-field">
                <h4>Sign-ups</h4>
                <p>Everyone who signed up in the last 24 hours, newest first: where they came from when that was recorded, when they signed up, and how far they have got since (saved a schedule, saved an event, added a ticket type), with a link to their schedule. The strip above the list counts how many of the day's sign-ups reached each step, and the small bars show which hours they arrived in. Someone who signed up to follow a schedule or to join a team is labelled that way. Activity and Sign-ups cover the whole install and ignore the filters below.</p>
            </div>
            <div class="doc-field">
                <h4>Visitors</h4>
                <p>One row per person, right now first, each with the icon of the area their page is in. Click a row for their last hour: each page in order with its area, how long they stayed, and for a signed-in visitor their schedules, an email link and their support chat. A brand-new user who has spent ten minutes in the app without creating an event is marked, so you can offer help. Admins are left out of the whole page unless you turn on <strong>Show admins</strong>, the switch at the top.</p>
            </div>
            <div class="doc-field">
                <h4>Top pages, Sources, Countries, Surfaces</h4>
                <p>Click any row to filter the whole page to it. The filters in force are listed as chips at the top, and they stay in the address, so a filtered view can be bookmarked.</p>
            </div>
        </div>

        <h3 id="realtime-consent" class="doc-subheading">Consent decides who is identified</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Turning Realtime on also turns on the cookie consent banner. A visitor who allows analytics (<strong>Allow all</strong>, or <strong>Analytics</strong> ticked under <strong>Choose</strong>) is shown as a person, with a daily-rotating one-way key and, when signed in, their account, including the pages they viewed in that browser just before signing in (not what the browser shows after they sign out). Anyone who declines, does not answer, or whose browser sends Global Privacy Control is only counted: their page views appear in the chart and the four lists, with no identifier and nothing that links one page view to another (pages behind sign-in are recorded by kind, such as "/{subdomain}/{tab}", rather than by address). Withdrawing consent later, including through the privacy page, strips the identifiers from what that browser still has on its current network, and from the account's records when they are signed in at the time. The Visitors card says what share of page views comes from visitors who accepted.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Records are deleted about an hour after a visitor's last activity. Realtime stores no IP address or user-agent string and sets no cookie of its own. Site administrators see all of its records. Schedule owners see none of them unless you also switch on <a href="#realtime-owner-view" class="doc-link">their own view</a>, and then only the part about their own pages and never who anyone is.</p>

        <h3 id="realtime-not-counted" class="doc-subheading">Who is never counted</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">REST API clients, including apps and AI agents using an API key; calendar feeds; email opens; visitors with JavaScript turned off; and embedded calendars on other websites, which appear only as a count under Surfaces. A schedule owner signed in on their own custom domain appears anonymous there, because their session belongs to the main domain. Identical phones with the same language setting on one network can count as one visitor.</p>

        <h3 id="realtime-owner-view" class="doc-subheading">What schedule owners see</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A second switch at <a href="#settings-realtime" class="doc-link">Settings</a>, <strong>Show schedule owners live traffic to their own pages</strong>, gives everyone who manages a schedule a Realtime tab on their Analytics page, and a Realtime number on their dashboard, described in the <a href="{{ route('marketing.docs.analytics') }}#realtime" class="doc-link">Analytics guide</a>. It is on for eventschedule.com and off on every other install until you turn it on, and it works only while Realtime itself is on.</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>What they see</h4>
                <p>Page views of their own schedule and event pages for the last 30 minutes, top pages, sources, countries and device types, and each visitor whose consent covers it (below) as a row with no name: a country, a device type, the page that is open and for how long, kept under Earlier for up to half an hour after they leave.</p>
            </div>
            <div class="doc-field">
                <h4>Their own last 24 hours</h4>
                <p>Beside the traffic the tab lists what people did on their schedules: sales, registrations, appointment bookings, new followers and newsletter subscribers, event requests, waitlist joins, ticket-interest sign-ups and audience comments, photos and videos, with no names (most rows open the list they belong to, such as Sales, Followers or Requests, where the names always were), a mark on the traffic chart on a minute a sale or a registration came in, and how many have arrived at an event that is on. These are the owner's own records and do not depend on anyone's cookie choice. With this switch off, owners have none of it; their dashboard's Recent Activity and Coming up cover the same ground.</p>
            </div>
            <div class="doc-field">
                <h4>What they never see</h4>
                <p>A name, an email address, an account, a browser or operating system, a visitor's history, or anything about a page that is not theirs. A row's id means nothing outside the session that is looking at it, so it cannot be used to follow a visitor from one day, one device or one owner to another. On a quiet schedule an owner who sees one unnamed visitor on an event's page and then a sale for that event can work out that it was the buyer, whose name is in Sales as it always was; eventschedule.com's privacy policy says so, and yours should if you turn this on.</p>
            </div>
            <div class="doc-field">
                <h4>Who is left out</h4>
                <p>Site administrators, and the owner and team of the schedule itself while they are signed in on its page, as in Analytics. On the schedule's own custom domain nobody is signed in, so an owner's visit there counts like anyone's.</p>
            </div>
            <div class="doc-field">
                <h4>Whose consent counts</h4>
                <p>While this switch is on, the cookie banner says on its first line that the organizer of a schedule page sees visits to it, and each choice records whether the banner it was made on said so. Only such a visitor is listed for an owner; anyone who answered an earlier banner, declined or did not answer is counted as a page view and never listed. There is nothing to wait for after you turn the switch on: the first visitor who accepts on the new banner is listed, and a list that looks thin at first fills as choices are made again.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Selfhost: update your privacy policy</div>
            <p>If you turn Realtime on, say so in your own privacy policy at <a href="#system-legal-pages" class="doc-link">Legal Pages</a>: what is recorded, for how long, and that only visitors who accept cookies are identified. If you also give schedule owners their own view, say that the organizer of a schedule sees visits to its pages, that the view shows no name, email address or account, and that a purchase made during a visit can let the organizer tell which unnamed visit it was.</p>
        </div>
    </section>

    <!-- Insights: Users -->
    <section id="insights-users" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0Zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0Z" />
            </svg>
            Users (Insights)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Users is the first page of the Insights tab: an aggregate report on who signed up in the period, how far they got, and where they came from. It is not a user directory: there is no search box, no per-user page to open, and no way to edit an account from here. Only confirmed accounts are counted, and the demo account is excluded. From top to bottom:</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Four totals</h4>
                <p><strong>Total Users</strong>, with how many joined in the period and the change against the previous one; <strong>Active users (7 days)</strong> and <strong>Active users (30 days)</strong>, the people who used the app while signed in, which is the figure the Dashboard charts (marked as an estimate until the exact record covers the whole window); and <strong>Newsletter Subscribers</strong>, with the number who unsubscribed.</p>
            </div>
            <div class="doc-field">
                <h4>Onboarding funnel</h4>
                <p>Three numbers first: the <strong>Signup to first event</strong> rate with its change against the previous period, the <strong>Biggest leak</strong> (the largest single drop between two stages), and, on eventschedule.com, the <strong>Visitor to first event</strong> rate. Under them the <strong>Funnel</strong> card draws every stage as a bar: visited the site, viewed the sign-up page, created an account, reached the schedule step, saved a schedule, reached the event step, saved an event, then the two ticket stages and, on hosted installs, the email-code step and the plan stages. It follows the accounts created to organize events; sign-ups that came to follow a schedule, buy a ticket or the like are counted in a note under the account bar.</p>
            </div>
            <div class="doc-field">
                <h4>Conversion over time</h4>
                <p>The same conversion rates per day, week or month. The most recent period is marked as still in progress, because its accounts have not had time to finish onboarding.</p>
            </div>
            <div class="doc-field">
                <h4>Signups by Method and Signup Method Breakdown</h4>
                <p>Email, Google and hybrid (an email sign-up with Google connected): a chart for the selected period, and the all-time split.</p>
            </div>
            <div class="doc-field">
                <h4>UTM Attribution, Top Campaigns, Top Sources, Top Referrers</h4>
                <p>How many of the period's sign-ups arrived from a campaign and their top UTM sources, then, all time, the ten biggest campaigns, sources and referring domains.</p>
            </div>
            <div class="doc-field">
                <h4>Onboarding progress</h4>
                <p>The newest organizer accounts, fifteen per page, with the steps each has reached and the furthest one. A row marked <strong>Stuck: no event yet</strong> saved a schedule and never added an event. The name is an email link, so this is the list to work through when you want to offer help.</p>
            </div>
            <div class="doc-field">
                <h4>Recent Signups</h4>
                <p>Twenty per page, with the sign-up intent, every UTM field, the referrer and the landing page.</p>
            </div>
        </div>
        <x-doc-screenshot id="selfhost-admin--users" alt="Admin Users page: total users, active users over 7 and 30 days and newsletter subscribers above the sign-up method charts" />

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">The funnel starts at the sign-up page on a selfhost</div>
            <p>Site visits come from marketing-site traffic, which is only recorded on eventschedule.com. On any other installation the funnel starts at "Viewed sign-up page", which every install counts, and the visitor-to-first-event rate is not shown. The email-code step and the plan stages (the paid-ticket paywall, checkout and subscribing) only appear on hosted installs, since a plain selfhost asks for no sign-up code and has no plans.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300">To act on a single account or schedule, use <a href="#manage-plans" class="doc-link">Manage &gt; Schedules</a>, which is where the search, filters, plan editing and manual verification live.</p>
    </section>

    <!-- Insights: Revenue -->
    <section id="insights-revenue" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            Revenue (Insights)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Revenue page reports on ticket sales across every schedule, plus subscription health where the install sells plans. Amounts are the payment amounts recorded on each paid sale. A partially refunded sale stays paid, so it counts at its full amount, and only fully refunded sales count toward the refund rate. The totals are all time; the <a href="#date-range" class="doc-link">date range</a> sets the trend and the figures marked "in period". From top to bottom:</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Total Revenue and Total Sales</h4>
                <p>All time, each with the figure for the selected period underneath. Total Sales also carries the <strong>Refund Rate</strong>: refunded sales as a share of paid plus refunded, turning red above 5 percent.</p>
            </div>
            <div class="doc-field">
                <h4>Pending Revenue</h4>
                <p>The value of the sales still marked unpaid, with how many there are.</p>
            </div>
            <div class="doc-field">
                <h4>Boost Markup Revenue</h4>
                <p>Your all-time margin on boost spend, with the figure for the period underneath. This tile sums charges only; the equivalent figure on the <a href="#manage-boost" class="doc-link">Boost</a> page is net of refunds, so the two do not have to match.</p>
            </div>
            <div class="doc-field">
                <h4>What a payment left to settle</h4>
                <p>Up to four panels, each rendered only while it has a row, and each the place a <a href="#needs-attention" class="doc-link">Needs attention</a> link lands: amount mismatches, subscriptions still billing for deleted schedules, subscriptions on an unrecognized price, and refunds awaiting confirmation. They are described below.</p>
            </div>
            <div class="doc-field">
                <h4>Revenue Trend</h4>
                <p>A chart over the selected range.</p>
            </div>
            <div class="doc-field">
                <h4>Subscription Health</h4>
                <p>Active, trialing, past-due and canceled subscriptions, schedules on a free trial, how many converted, and expired trials with no subscription. This whole card is only rendered when <code class="doc-inline-code">IS_HOSTED=true</code>.</p>
            </div>
            <div class="doc-field">
                <h4>Recent Sales</h4>
                <p>The fifty most recent, excluding demo schedules.</p>
            </div>
        </div>
        <x-doc-screenshot id="selfhost-admin--revenue" alt="Admin Revenue page: total revenue, total sales, pending revenue and boost markup revenue above the revenue trend chart" />

        <h3 id="revenue-amount-mismatch" class="doc-subheading">Amount mismatches</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">When the amount a payment provider reports does not match the amount the sale expected, the sale is parked as a mismatch rather than being treated as paid. Those sales, and any boost campaign in the same state, are listed in an amber panel, with two actions each:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Approve</strong> - accept it and mark it paid. For a ticket sale, the buyer is then sent their tickets</li>
            <li><strong class="text-gray-900 dark:text-white">Refund</strong> - for a ticket sale, send the whole payment back through the provider that took it, Stripe or PayPal, which the confirmation names. The button only appears when that provider can refund, so a mismatch paid through Invoice Ninja or Payfast offers Approve alone. A boost campaign's charge is refunded through Stripe</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Both ask you to confirm, both are recorded in the audit log, and the row disappears from Needs attention once the queue is empty. Schedule owners refund their own sales, in full or in part, from their Sales page; see <a href="{{ route('marketing.docs.tickets') }}#managing-sales" class="doc-link">Managing sales</a>.</p>

        <h3 id="revenue-orphaned-subscriptions" class="doc-subheading">Subscriptions still billing for deleted schedules</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A red <strong class="text-gray-900 dark:text-white">Subscriptions Still Billing for Deleted Schedules</strong> panel lists every subscription Stripe will keep charging although its schedule is gone, so the owner cannot cancel it from the app. Each row has a <strong class="text-gray-900 dark:text-white">Cancel in Stripe</strong> action, which asks first, then cancels the subscription in Stripe and writes the cancellation to the audit log. Cancelling refunds nothing: a charge made after the schedule was deleted has to be refunded in the Stripe dashboard.</p>

        <h3 id="revenue-unrecognized-price" class="doc-subheading">Subscriptions on an unrecognized price</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A red <strong class="text-gray-900 dark:text-white">Subscriptions on an Unrecognized Price</strong> panel lists live Stripe subscriptions whose price is none of the plan prices this install sells. The app cannot tell which plan such a customer is paying for, so the plan is withdrawn and the subscription counts as zero revenue while Stripe keeps charging. There is no button: either add that price ID to the <code class="doc-inline-code">STRIPE_PRICE_*</code> configuration, or move the subscription onto a current price in Stripe.</p>

        <h3 id="revenue-unconfirmed-refunds" class="doc-subheading">Refunds awaiting confirmation</h3>
        <p class="text-gray-600 dark:text-gray-300">A refund that was sent to Stripe or PayPal and still has no confirmed outcome after 15 minutes is listed in a red <strong class="text-gray-900 dark:text-white">Refunds Awaiting Confirmation</strong> panel, with its date, event, amount, status, reference and last error. The money may or may not have moved, and nothing in the app retries it, because a retry after the provider's idempotency key has expired is how one refund becomes two. Look the reference up in the provider's own dashboard and settle it there. Until then the refund holds its amount against the sale, so the schedule owner cannot refund that money again.</p>
    </section>

    <!-- Insights: Analytics -->
    <section id="insights-analytics" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Analytics (Insights)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Analytics page rolls up the same daily page-view data that each schedule owner sees on their own <a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a> page, across every non-demo schedule, for the selected <a href="#date-range" class="doc-link">date range</a>.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Device breakdown</strong> - total page views for the period split into desktop, mobile and tablet. Views whose device could not be determined are counted in the total only.</li>
            <li><strong class="text-gray-900 dark:text-white">Traffic sources</strong> - direct, search, social, email, newsletter and other</li>
            <li><strong class="text-gray-900 dark:text-white">Feature adoption</strong> - six bars showing how many verified schedules use Google Calendar sync, sell through Stripe, have a custom domain, use custom CSS, have sent a newsletter, or have run a boost campaign, each as a count and a share of every verified non-demo schedule. The <strong class="text-gray-900 dark:text-white">Stripe Payments</strong> bar counts schedules with an event actually priced in Stripe, not schedules that merely connected an account.</li>
            <li><strong class="text-gray-900 dark:text-white">Stripe funnel</strong> - the three stages that bar skips past: connected an account, finished onboarding, and priced an event in Stripe</li>
            <li><strong class="text-gray-900 dark:text-white">Top schedules by events</strong> - the ten schedules with the most events</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--analytics" alt="Admin Analytics page: page views by device and by traffic source above the feature adoption bars" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What is not here</div>
            <p>Page views here are counted per day and per device, not per visitor, so there is no unique-visitor figure and no per-visitor location. The nearest thing to a geographic view is the <strong class="text-gray-900 dark:text-white">In person, by country</strong> list under Upcoming events on the dashboard, which is based on the venue's country rather than the visitor's. For visitors and countries over the last 30 minutes, see <a href="#realtime" class="doc-link">Realtime</a>.</p>
        </div>
    </section>

    <!-- Insights: Usage -->
    <section id="insights-usage" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Usage (Insights)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Usage page counts calls to the services your install depends on, so you can see what is consuming your API quotas and mail allowance. Every operation is tallied per day and per schedule as it happens.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Seven categories are summarised in one strip, each with the total for the selected period, today's total against the configured daily limit, and the average per day:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Emails</strong>, <strong class="text-gray-900 dark:text-white">AI / Gemini</strong>, <strong class="text-gray-900 dark:text-white">Google Calendar</strong>, <strong class="text-gray-900 dark:text-white">Stripe</strong>, <strong class="text-gray-900 dark:text-white">Invoice Ninja</strong>, <strong class="text-gray-900 dark:text-white">CalDAV</strong> and <strong class="text-gray-900 dark:text-white">YouTube</strong></li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The daily limits come from <code class="doc-inline-code">config/usage.php</code> and its environment variables, not from this page. YouTube has no limit. When today's total for a category passes its limit, its figure turns red and a red <strong class="text-gray-900 dark:text-white">Usage Anomalies Detected Today</strong> notice appears at the top of the page.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Below the summaries:</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Operation Breakdown</h4>
                <p>Every individual operation, with today, the period total and the daily average.</p>
            </div>
            <div class="doc-field">
                <h4>Top Schedules by Usage</h4>
                <p>The twenty heaviest schedules, split by category.</p>
            </div>
            <div class="doc-field">
                <h4>Top newsletter senders</h4>
                <p>The twenty schedules sending the most newsletter email, whether each sends through its own SMTP server rather than yours and, on a hosted install, its plan.</p>
            </div>
            <div class="doc-field">
                <h4>Translation Backlog</h4>
                <p>Content still waiting for the scheduled translation run, by kind: how much is pending, how much of it has never been attempted, and how long the oldest has waited. A "never attempted" number that does not fall means the scheduler is not keeping up; the <a href="#queue-work-waiting" class="doc-link">Queue</a> page shows the rate it is clearing at.</p>
            </div>
            <div class="doc-field">
                <h4>Stuck Translation Records</h4>
                <p>Up to twenty each of the schedules, events, event parts and event listings that have been attempted at least three times (the threshold is <code class="doc-inline-code">USAGE_STUCK_THRESHOLD</code>) and still have no translation. Each row has a <strong class="text-gray-900 dark:text-white">Retry</strong> link that clears the attempt counter so the next scheduled run picks it up again.</p>
            </div>
        </div>
        <x-doc-screenshot id="selfhost-admin--usage" alt="Admin Usage page: one figure for each provider, from email to YouTube, above the operation breakdown" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Not the same as feature adoption</div>
            <p>This page measures external calls made in a window. How many schedules have <em>turned on</em> a feature is on the <a href="#insights-analytics" class="doc-link">Analytics</a> page instead.</p>
        </div>
    </section>

    <!-- Insights: Growth (hosted only) -->
    <section id="insights-growth" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="chart" />
            Growth (Insights)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Growth picks up where <a href="#insights-users" class="doc-link">Users</a> stops: where sign-ups turn into active schedules, and where those turn into paying ones. The page only exists when <code class="doc-inline-code">IS_HOSTED=true</code>, because a plain selfhost has no plans or subscriptions to report on. From top to bottom:</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Four numbers</h4>
                <p><strong>Signups</strong>, then the share of them that reached the schedule step, saved a schedule and saved an event, each with its count.</p>
            </div>
            <div class="doc-field">
                <h4>Free plan pressure</h4>
                <p>Free schedules grouped by the most paid tickets they sold in a single month, from before selling paid tickets became a Pro feature. Anything above zero is a schedule that has sold before and now sits on Free.</p>
            </div>
            <div class="doc-field">
                <h4>Revenue</h4>
                <p>How many schedules are on Free, Pro and Enterprise, then MRR, ARR and the average monthly revenue per paying subscription (ARPU). Subscriptions still in their trial are counted beside those figures and left out of them.</p>
            </div>
            <div class="doc-field">
                <h4>Activation nudges</h4>
                <p>The emails that prompt owners to take their next step, by kind: how many were sent in all, how many in the last 7 days, and when the last one went. Under them, the weekly owner digests. If the table is still empty a day after a deploy, check the Scheduler card on the <a href="#system-queue" class="doc-link">Queue</a> page.</p>
            </div>
            <div class="doc-field">
                <h4>Churn and selling trials</h4>
                <p>Cancelled subscriptions with the reasons people gave, and cancellations taken back. Then the selling trials: started, running now, sold during the trial, subscribed, and ended without subscribing.</p>
            </div>
            <div class="doc-field">
                <h4>Acquisition</h4>
                <p>The first page each sign-up landed on, with how many sign-ups it brought and what share of them saved a schedule, saved an event, added a ticket type and added a paid ticket.</p>
            </div>
            <div class="doc-field">
                <h4>Homepage headline test</h4>
                <p>eventschedule.com only. The headlines the marketing homepage is trying, with each one's visitors, click rate and sign-ups. <strong class="text-gray-900 dark:text-white">Reset stats</strong> starts a new round from zero.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">The date range changes nothing here</div>
            <p>The page carries the same <a href="#date-range" class="doc-link">date-range selector</a> as the other Insights pages, but every figure on it is all time. The numbers that follow the range are the funnel's, and that is on <a href="#insights-users" class="doc-link">Users</a>.</p>
        </div>
    </section>

    <!-- Manage: Boost -->
    <section id="manage-boost" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
            Boost (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Boost page is the first page of the Manage tab. It is where you oversee every paid promotion bought on the platform, whether it runs as a Meta ad or as a promotion served on your own pages. From top to bottom:</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Two strips of figures</h4>
                <p>Total and active campaigns, markup revenue net of refunds, ad spend that left for Meta, and refunds; then the average click-through rate, cost per click and cost per thousand impressions, and the <strong>Rejection Rate</strong>: rejected campaigns as a share of those with a settled outcome, turning red above 20 percent.</p>
            </div>
            <div class="doc-field">
                <h4>Promotions awaiting review</h4>
                <p>Up to fifty paid on-network promotions, oldest first, each showing the creative, the schedule, the buyer, the budget and the pricing model, with <strong class="text-gray-900 dark:text-white">Reject</strong>, an optional reason to send with it, and <strong class="text-gray-900 dark:text-white">Approve</strong>. Rejecting refunds the advertiser in full, back to their boost credit if that is how they paid and through Stripe otherwise, and emails and pushes the outcome to them. This card only appears when the promotions network is enabled and something is waiting.</p>
            </div>
            <div class="doc-field">
                <h4>Alerts</h4>
                <p>Campaigns stuck awaiting payment, campaigns that failed, and ads Meta disapproved, in one red notice that is only there while it has something to say.</p>
            </div>
            <div class="doc-field">
                <h4>Status Distribution and Top Boosters</h4>
                <p>A chart of campaigns by state, and the ten schedules with the largest total budget, each with its campaigns, spend, clicks and spending limit.</p>
            </div>
            <div class="doc-field">
                <h4>Revenue Trend</h4>
                <p>Ad spend against markup over the selected range.</p>
            </div>
            <div class="doc-field">
                <h4>Grant Boost Credit and Set Spending Limit</h4>
                <p>Two small forms, described <a href="#boost-credit-limit" class="doc-link">below</a>.</p>
            </div>
            <div class="doc-field">
                <h4>Campaigns</h4>
                <p>Twenty per page, newest first, with the campaign's name and event, the schedule and the buyer, its status and creation date, budget, spend, impressions and clicks. The dropdown in the card's heading filters by status: active, paused, completed, cancelled, failed, pending payment or rejected. <strong class="text-gray-900 dark:text-white">View</strong> leads to the campaign's own page, which opens only for the account that bought it or someone who can edit its event.</p>
            </div>
            <div class="doc-field">
                <h4>Recent Billing Records</h4>
                <p>The thirty most recent charges and refunds behind those numbers, each with its amount, markup, status and notes.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Billing sections are hosted only</div>
            <p>On a plain selfhost a boost runs on your own Meta account and charges no markup or fee, so the page leaves out markup revenue, refunds, the revenue trend, the two forms and the billing records. Total ad spend stays.</p>
        </div>

        <h3 id="boost-credit-limit" class="doc-subheading">Granting credit and capping budgets</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Below the revenue trend, two forms act on a single schedule, identified by its subdomain (the field autocompletes as you type):</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Grant Boost Credit</strong> - add up to 1,000 to a schedule's boost balance. A boost is paid from the balance when the balance covers its whole cost; otherwise the card is charged. Schedules holding a balance are listed underneath.</li>
            <li><strong class="text-gray-900 dark:text-white">Set Spending Limit</strong> - raise or lower the maximum budget that schedule may put on a single campaign. Schedules with a custom limit are listed underneath; when none has one, the card names the default that applies to everyone else instead.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Both are written to the audit log.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Nothing to manage until Boost is configured</div>
            <p>The page is always in the Manage tab, but Meta campaigns can only exist once the Meta app, ad account and access token are configured. See the <a href="{{ route('marketing.docs.selfhost.boost') }}" class="doc-link">Boost Setup</a> guide. On-network promotions are configured separately, in the Monetization card on the <a href="#settings-monetization" class="doc-link">Settings</a> page.</p>
        </div>
        <x-doc-screenshot id="selfhost-admin--boost" alt="Admin Boost page: campaign, spend and refund totals and the average rates above the status distribution and top boosters" />
    </section>

    <!-- Manage: Schedules and plans (every install; plan assignment is hosted only) -->
    <section id="manage-plans" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Schedules (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This is the <strong class="text-gray-900 dark:text-white">Schedules</strong> page of the Manage tab, at <code class="doc-inline-code">/admin/schedules</code>, and it is there on every install. On a hosted install the older <code class="doc-inline-code">/admin/plans</code> address redirects to it. It is the one place in the admin panel where you change something about an individual schedule. From top to bottom:</p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4>Counts</h4>
                <p>On a hosted install, how many verified, non-demo schedules resolve to <strong>Free</strong>, <strong>Pro</strong> and <strong>Enterprise</strong>, and in a second strip how many pay through Stripe, how many were granted a plan by hand, how many are on trial, and how many expire in the next 30 days. A plain selfhost shows one <strong>Verified</strong> figure instead. Beside them, <strong>Unverified</strong> counts the schedules with an owner that have verified neither an email address nor a phone number; it is a link that narrows the list to them.</p>
            </div>
            <div class="doc-field">
                <h4>Search</h4>
                <p>By schedule name, subdomain or email address.</p>
            </div>
            <div class="doc-field">
                <h4>Filters</h4>
                <p>Plan, status (active, expired, trial or deleted), owner, source (Stripe, manual or trial) and verification (verified or unverified). On a plain selfhost only the status filter's deleted option, owner and verification are offered, and the plan columns are left out of the table.</p>
            </div>
            <div class="doc-field">
                <h4>Owner filter</h4>
                <p><strong class="text-gray-900 dark:text-white">Claimed</strong>, the default, shows schedules with an owner. Switch it to <strong class="text-gray-900 dark:text-white">Unclaimed</strong> to reach the venue and performer schedules the app created automatically when an event named them: they have no owner, and their page is a claim page kept out of search engines, but they do hold a subdomain, so they are the usual reason a good name is unavailable. <strong class="text-gray-900 dark:text-white">All owners</strong> shows both.</p>
            </div>
            <div class="doc-field">
                <h4>The list</h4>
                <p>Twenty schedules per page, newest first, each with its address and which of its contacts is verified, and on a hosted install its plan, term, expiry, status and source. Demo schedules are left out, deleted ones appear only under the Deleted status, and unverified ones are listed even though the plan counts exclude them. Each row has <strong class="text-gray-900 dark:text-white">Edit</strong> and one quick action: <strong class="text-gray-900 dark:text-white">Delete</strong>, or on a deleted schedule <strong class="text-gray-900 dark:text-white">Restore</strong>, or <strong class="text-gray-900 dark:text-white">Release</strong> when it was deleted without giving up its name.</p>
            </div>
        </div>

        <h3 id="schedules-edit" class="doc-subheading">Editing one schedule</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4"><strong class="text-gray-900 dark:text-white">Edit</strong> opens the schedule on a page of its own, under a <strong class="text-gray-900 dark:text-white">Schedules</strong> link that leads back to the list. Its name and address head the page, and each card below saves on its own:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Card</th>
                        <th>What it does</th>
                        <th>Appears on</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Current Subscription Status</td>
                        <td>Read-only: the status, the Stripe customer, the trial end and whether a subscription is active.</td>
                        <td>Hosted installs</td>
                    </tr>
                    <tr>
                        <td>Schedule Details</td>
                        <td>Change the name, subdomain, email address and phone number, then choose <strong class="text-gray-900 dark:text-white">Save Changes</strong>. Changing the email address clears the verified mark and sends a fresh verification email, which the card says before you do. A subdomain that is reserved or already in use is refused with a message rather than quietly changed to something else, and a renamed subdomain is rewritten in every curator's approved list, so the trust a curator gave that schedule follows it to the new name.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Verification</td>
                        <td>Says whether the email address and the phone number are verified, and since when. <strong class="text-gray-900 dark:text-white">Mark Email as Verified</strong> and <strong class="text-gray-900 dark:text-white">Mark Phone as Verified</strong> vouch for one without the owner clicking a link or entering a code.</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Plan</td>
                        <td>Set <strong class="text-gray-900 dark:text-white">Plan Type</strong> to Free, Pro or Enterprise, <strong class="text-gray-900 dark:text-white">Plan Term</strong> to monthly or yearly, and <strong class="text-gray-900 dark:text-white">Plan Expires</strong>, which has <strong class="text-gray-900 dark:text-white">+30 days</strong>, <strong class="text-gray-900 dark:text-white">+90 days</strong>, <strong class="text-gray-900 dark:text-white">+1 year</strong> and <strong class="text-gray-900 dark:text-white">Clear</strong> shortcuts. A paid plan granted this way is tagged as an admin grant, which is what keeps the small Event Schedule credit on that schedule's public pages. Setting it back to Free, or editing a schedule that pays through Stripe, clears that tag.</td>
                        <td>Hosted installs. A plain selfhost already gives every schedule the Enterprise feature set</td>
                    </tr>
                    <tr>
                        <td>Mark as Deleted</td>
                        <td>Takes the schedule's public page down and releases its subdomain, so a newer schedule can use the name straight away. The schedule itself is kept, along with its events, ticket sales and statistics, and the action can be undone. For a schedule with no owner, this takes down the claim page that invites the performer or venue to take it over. Releasing a name also removes it from every curator's approved list, so the automatic approval a curator gave the old holder does not pass to whoever takes the name next. The card warns first when the schedule has an active subscription or a custom domain, because neither is removed.</td>
                        <td>Every install, on a schedule that is not deleted</td>
                    </tr>
                    <tr>
                        <td>Deleted</td>
                        <td><strong class="text-gray-900 dark:text-white">Restore Schedule</strong> brings a deleted schedule back. It takes its original subdomain back if nothing else has claimed it in the meantime; if something has, the schedule keeps the name it was given when it was deleted, and the card tells you which will happen before you click. A deleted schedule that still holds its original name, which some other ways of deleting a schedule leave behind, also offers <strong class="text-gray-900 dark:text-white">Release Subdomain</strong>: it frees the name and changes nothing else.</td>
                        <td>Every install, on a deleted schedule</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Restore is also the undo for a takedown you did not start: the same release runs when someone who holds the contact address on an unclaimed page signs in and chooses <strong class="text-gray-900 dark:text-white">This is not me</strong>, and when a schedule is deleted through the API. Every change here is recorded in the audit log with the values before and after, including both subdomains, so a release can be traced later.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Why deleting renames the schedule</div>
            <p>Only one schedule can hold a subdomain at a time, so a schedule cannot keep its name and let another schedule use it. Marking one deleted therefore moves it to a name like <code class="doc-inline-code">tel-aviv-deleted-42</code> and remembers what it was called. Two things are left in place: an active subscription keeps billing (cancel it separately), and a connected custom domain stops resolving but stays attached to the schedule until you remove it on the Domains page.</p>
        </div>

        <h3 id="schedules-plans" class="doc-subheading">Plans are the only hosted part</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This page is on every install, because a schedule can be deleted and its name released on a selfhosted install too, and this is where you undo it. Only plans behave differently: a plain selfhost resolves every schedule to the Enterprise feature set, so the plan counts, the plan filters and columns, and the card that assigns a plan only appear on hosted installs. The features in each tier come from the application itself and what a plan charges from your Stripe prices; neither can be edited here. The figures the site advertises are set in the <a href="#settings-plan-pricing" class="doc-link">Plan pricing</a> card on Settings.</p>
    </section>

    <!-- Manage: Blocked (every install) -->
    <section id="manage-blocked" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            Blocked (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Blocked page, at <code class="doc-inline-code">/admin/blocked</code>, is where you shut out an account that abuses the service, and where you keep the list of what new accounts are refused for. It is there on every install. Deleting a spam schedule on the <a href="#manage-plans" class="doc-link">Schedules</a> page leaves its owner free to make another one; blocking the account does not.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Three totals</strong> - blocked accounts, entries on the list, and how many sign-ups the list has refused</li>
            <li><strong class="text-gray-900 dark:text-white">Find an account</strong> - by name, email address or a schedule it owns. The owner card on a schedule's admin page and the names under Recent sign-ups on the <a href="#insights-users" class="doc-link">Users</a> page lead to the same place</li>
            <li><strong class="text-gray-900 dark:text-white">Blocked accounts</strong> - who is blocked, how many schedules the block took down, when and by whom, and your note</li>
            <li><strong class="text-gray-900 dark:text-white">Refused at sign-up</strong> - the list itself, with how often each entry has refused someone</li>
        </ul>

        <h3 id="blocked-account" class="doc-subheading">Blocking an account</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A block is decided on the account's own page, which shows what you need first: the schedules it owns, how it signs in, the network address it signed up from, and the other accounts that were made from that same address, each one press away. Press <strong class="text-gray-900 dark:text-white">Block account</strong> and:</p>
        <ul class="doc-list mb-6">
            <li>The account can no longer sign in, by password, Google or Facebook, or use its API key. Anyone signed in to it is signed out on their next page</li>
            <li>Every schedule it owns goes offline and its subdomain is released, exactly as <strong class="text-gray-900 dark:text-white">Delete</strong> on the Schedules page does. A paid plan on one of them is cancelled, and a newsletter waiting to be sent goes back to a draft</li>
            <li>Its email address goes on the list, so it cannot sign up again</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Two switches add more to the list, and both are off until you turn them on. <strong class="text-gray-900 dark:text-white">Also refuse new accounts from the address</strong> adds the network address the account signed up from (for an IPv6 address, the /64 it sits in). <strong class="text-gray-900 dark:text-white">Also refuse new accounts at the domain</strong> adds its email domain. Beside each is the number of other accounts that share it: a large number means an office, a mobile network or a mail provider that ordinary people use, and it should stay off.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">An administrator cannot be blocked, and you cannot block yourself. Schedules the account is only a member of are not touched.</p>

        <h3 id="blocked-unblock" class="doc-subheading">Unblocking</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6"><strong class="text-gray-900 dark:text-white">Unblock account</strong>, on the same page, undoes what the block did and nothing else. The account can sign in again, the schedules this block took down come back, and the entries it added leave the list. A schedule that was already deleted before the block stays deleted, a cancelled plan stays cancelled, and a subdomain somebody has taken in the meantime stays theirs: the schedule then comes back under its released name.</p>

        <h3 id="blocked-list" class="doc-subheading">The list</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A new account is refused when it matches an entry, whichever way it is being made: the sign-up form, Google or Facebook, the API, or the account a guest form offers. Accounts that already exist are never affected by the list, so adding a domain does not lock anyone out. An entry is one of:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Email address</strong> - one mailbox. A <code class="doc-inline-code">+tag</code>, and for Gmail the dots in the name, do not make a new address</li>
            <li><strong class="text-gray-900 dark:text-white">Email domain</strong> - everything at <code class="doc-inline-code">example.com</code> and its subdomains</li>
            <li><strong class="text-gray-900 dark:text-white">Network address</strong> - one IP address, or a range such as <code class="doc-inline-code">203.0.113.0/24</code>. The widest range allowed is /16 for IPv4 and /32 for IPv6</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">When you add an entry by hand, the page tells you how many existing accounts already match it. The address an account signed up from is kept for 90 days, or for as long as the account is blocked, so the address switch is not offered for an older account. Every block, unblock and change to the list is in the <a href="#system-audit-log" class="doc-link">Audit Log</a>.</p>
    </section>

    <!-- Manage: Feeds (every install) -->
    <section id="manage-feeds" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 19.5v-.75a7.5 7.5 0 0 0-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
            </svg>
            Feeds (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Feeds page, at <code class="doc-inline-code">/admin/feeds</code>, lists every <a href="{{ route('marketing.docs.managing_schedules') }}#feeds" class="doc-link">feed</a> on the install: the addresses schedules keep reading for events, which schedule reads what, and whether it is being read. A feed is shown by its site, never by its address, because for a private calendar the address is the key to it.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Three totals</strong> - all feeds, how many are failing, and how many are paused</li>
            <li><strong class="text-gray-900 dark:text-white">Search and filter</strong> - by schedule, subdomain, feed name or site, and by state: <strong class="text-gray-900 dark:text-white">Failing</strong>, <strong class="text-gray-900 dark:text-white">Paused</strong> or <strong class="text-gray-900 dark:text-white">Waiting for somebody</strong> (drafts to review, or a decision)</li>
            <li><strong class="text-gray-900 dark:text-white">The list</strong> - twenty per page, what is not being read first: the schedule, which opens its admin page, the feed with its site, kind and number of events, its status, its last good read and its next try</li>
            <li><strong class="text-gray-900 dark:text-white">Status</strong> - a failing feed shows a reason and a status code (<code class="doc-inline-code">http_error 503</code>) and how many tries have failed, never what the other server said. A paused one shows why it was paused. A feed on a schedule whose plan does not include feeds shows <strong class="text-gray-900 dark:text-white">Not being read</strong></li>
        </ul>

        <h3 class="doc-subheading">Actions</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Read now</strong> - reads the feed on the next run, whatever its wait after a failure says. Runs start every minute</li>
            <li><strong class="text-gray-900 dark:text-white">Resume</strong> - starts a paused feed again with a clean slate</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Feeds are read by the <code class="doc-inline-code">app:import-feeds</code> task, every minute, so they need the same cron entry as everything else on the <a href="#system-queue" class="doc-link">Queue</a> page. Each read is one request to the feed's address, and a feed of posts also opens each new post's own page. All of them go through the same guard as every outbound fetch: public addresses only, with every redirect checked again.</p>
    </section>

    <!-- Manage: Domains (hosted only) -->
    <section id="manage-domains" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            Domains (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Domains page lists every schedule that has connected a custom domain, in either mode: <strong class="text-gray-900 dark:text-white">Direct</strong>, where the domain serves the schedule itself, or <strong class="text-gray-900 dark:text-white">Redirect</strong>, where it forwards to the subdomain.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Four totals</strong> - all custom domains, how many are direct, and how many of those are active or still setting up</li>
            <li><strong class="text-gray-900 dark:text-white">Search and filters</strong> - by schedule, subdomain, domain or hostname, and by mode and status (<strong>Setting up...</strong>, <strong>Active</strong> or <strong>Setup failed</strong>)</li>
            <li><strong class="text-gray-900 dark:text-white">Status</strong> - for a direct domain, where Event Schedule believes its setup stands (<strong>Setting up...</strong>, <strong>Active</strong> or <strong>Setup failed</strong>, with the reason for a failure), and under it the phase read back from DigitalOcean when the DigitalOcean API is configured. A redirect has no setup to be in, so its status is empty</li>
            <li><strong class="text-gray-900 dark:text-white">Listing</strong> - twenty domains per page, newest first</li>
        </ul>

        <h3 class="doc-subheading">Actions</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Re-provision</strong> - registers the hostname with the hosting platform again and returns the domain to <strong>Setting up...</strong>. Changing the app's domains redeploys it briefly, which the confirmation says. If the hostname was already registered nothing is changed, and the page tells you to check its DNS record, or to remove the domain and add it back. Direct-mode domains only, and only when the DigitalOcean API is configured.</li>
            <li><strong class="text-gray-900 dark:text-white">Remove</strong> - clears the domain from the schedule and, for a direct-mode domain, removes the hostname from the hosting platform. The schedule falls back to its subdomain. If the hosting platform refuses the removal, the domain is kept and marked <strong>Setup failed</strong>, so you can try again.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Certificates themselves are issued by the hosting platform, not by Event Schedule. If a domain stays on Setting up, the usual cause is DNS that does not yet point at your install. Each owner connects their own domain from their schedule; see <a href="{{ route('marketing.docs.saas.custom_domains') }}" class="doc-link">Custom domains</a> for the operator's side of the setup.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Hosted installs only</div>
            <p>This page only exists when <code class="doc-inline-code">IS_HOSTED=true</code>. A single-tenant selfhosted install is already served from your own domain and has nothing to map.</p>
        </div>
    </section>

    <!-- Manage: Referrals (hosted only) -->
    <section id="manage-referrals" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="referral" />
            Referrals (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Referrals page lists everyone who signed up through a <a href="{{ route('marketing.docs.referral_program') }}" class="doc-link">referral link</a>, and where each referral stands. It is read-only, and it only exists when <code class="doc-inline-code">IS_HOSTED=true</code>, because the program pays its reward in plan credit and a plain selfhost has no plans.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Figures</strong> - total referrals, how many are <strong>Pending</strong>, <strong>Subscribed</strong>, <strong>Qualified</strong>, <strong>Credited</strong> and <strong>Expired</strong>, and the conversion rate: the share that got as far as subscribing</li>
            <li><strong class="text-gray-900 dark:text-white">Status filter</strong> - one pill per status, which narrows the list to it</li>
            <li><strong class="text-gray-900 dark:text-white">The list</strong> - fifty per page, newest first: the referred user and the referrer, each with name and email address, the plan the referred user took, the status, the schedule the credit went to, and the date</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">What each status means, and when a referral moves from one to the next, is in <a href="{{ route('marketing.docs.referral_program') }}#statuses" class="doc-link">Referral statuses</a>. A referrer sees their own referrals with the email address masked; this page shows it in full.</p>
    </section>

    <!-- Manage: Newsletters -->
    <section id="manage-newsletters" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
            Newsletters (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">These are platform newsletters to the people who have registered on your install, which is a different thing from the newsletters a schedule owner sends to their own followers. Only admins can create them, and they never go to a schedule's followers.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The section has three tabs of its own under the admin navigation: <strong class="text-gray-900 dark:text-white">Newsletters</strong>, <strong class="text-gray-900 dark:text-white">Segments</strong> and <strong class="text-gray-900 dark:text-white">Templates</strong>. The builder, a segment and a newsletter's statistics each open on a page of their own, with a link above the title that leads back to the tab they came from.</p>

        <h3 id="newsletters-composing" class="doc-subheading">Composing and sending</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Create</strong> - choose <strong class="text-gray-900 dark:text-white">Create Newsletter</strong> on the Newsletters tab, start from scratch or from a saved template, give it a subject, and build the body from ten block types: heading, text, button, image, video, divider, spacer, social links, quote and offer.</li>
            <li><strong class="text-gray-900 dark:text-white">Style it</strong> - pick one of five layouts (Modern, Classic, Minimal, Bold or Compact) and set the background, accent and text colours, the font, the button shape and the footer text.</li>
            <li><strong class="text-gray-900 dark:text-white">Choose the audience</strong> - select one or more segments. The recipient count for each is shown as you pick. Leave the selection empty and the newsletter goes to every confirmed account that has not opted out.</li>
            <li><strong class="text-gray-900 dark:text-white">Check it</strong> - preview the rendered email, and send a test to yourself.</li>
            <li><strong class="text-gray-900 dark:text-white">Send or schedule</strong> - send immediately, or schedule it for a future time in your own timezone. A scheduled newsletter can be cancelled while it is still waiting, which returns it to draft.</li>
        </ol>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">The events block is not available here</div>
            <p>A schedule owner's newsletter builder has extra blocks that pull from their schedule: events, profile image, header banner, sponsors and a poll. A platform newsletter has no schedule behind it, so those blocks are not offered. The <strong class="text-gray-900 dark:text-white">offer</strong> block is the reverse case: it is offered here and not to schedule owners.</p>
        </div>
        <h3 id="newsletters-list" class="doc-subheading">The Newsletters tab</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The list shows each newsletter's subject, its status with the time it went out or is due to, how many recipients it reached, its open and click rates and when it was created. A draft or a scheduled newsletter opens in the builder with <strong class="text-gray-900 dark:text-white">Edit</strong>; a sent one opens its full statistics with <strong class="text-gray-900 dark:text-white">Stats</strong>. <strong class="text-gray-900 dark:text-white">Clone</strong> copies any newsletter into a new draft, and <strong class="text-gray-900 dark:text-white">Delete</strong> is offered on everything except a newsletter that is being sent.</p>

        <h3 id="newsletters-segments" class="doc-subheading">Segments</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Segments</strong> tab lists the segments with the number of recipients each resolves to, above the form that creates one. Five kinds are available:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">All Platform Users</strong> - every confirmed account</li>
            <li><strong class="text-gray-900 dark:text-white">Plan Tier</strong> (hosted installs) - the owners and admins of schedules on Free, Pro or Enterprise</li>
            <li><strong class="text-gray-900 dark:text-white">Signup Date</strong> - accounts created between two dates</li>
            <li><strong class="text-gray-900 dark:text-white">Admins</strong> - every admin account, yours included, useful for testing</li>
            <li><strong class="text-gray-900 dark:text-white">Manual</strong> - a list you add people to by hand: open the segment, search for an account and choose <strong class="text-gray-900 dark:text-white">Add Subscriber</strong></li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Only the name of an existing segment can be edited; to change what a segment matches, create a new one. Every send drops anyone who has unsubscribed from platform newsletters or turned email off entirely, whichever segment produced them, so an opt-out is honoured even from a manual list. A segment cannot be deleted while a draft or scheduled newsletter still uses it.</p>

        <h3 id="newsletters-templates" class="doc-subheading">Templates</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A template is a saved design to start a newsletter from. The <strong class="text-gray-900 dark:text-white">Templates</strong> tab lists them, each to use, edit or delete, and <strong class="text-gray-900 dark:text-white">Create Template</strong> builds one from scratch. Any saved newsletter can also be kept as one with <strong class="text-gray-900 dark:text-white">Save as Template</strong> in its builder.</p>
        <x-doc-screenshot id="selfhost-admin--newsletters" alt="Admin Newsletters tab with the Create Newsletter button, before any newsletter has been written" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Large sends need a queue, not the web request</div>
            <p>With the default <code class="doc-inline-code">QUEUE_CONNECTION=sync</code> the send runs inside the web request, so sending to more than 50 recipients is refused and you are asked to schedule it instead. Scheduled sends are picked up by the <a href="{{ route('marketing.docs.selfhost.installation') }}#cron" class="doc-link">cron entry</a> every minute. To send large newsletters immediately, switch to a real queue connection such as <code class="doc-inline-code">QUEUE_CONNECTION=database</code>: the same cron entry then drains the queue every minute, so no separate worker process has to be kept running.</p>
        </div>
    </section>

    <!-- Manage: Blog (eventschedule.com only) -->
    <section id="manage-blog" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="book" />
            Blog (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300">The Blog page manages the posts of the marketing site's blog: a list with each post's status, views and word count, <strong class="text-gray-900 dark:text-white">Create Post</strong>, and edit, preview and delete on each row. The blog belongs to the marketing site, which only eventschedule.com serves, so this page and its entry in the Manage row are missing on every other install, selfhosted or SaaS.</p>
        <p class="text-gray-600 dark:text-gray-300 mt-4">A post the scheduled writer finished but its check would not publish is kept as a draft marked <strong class="text-gray-900 dark:text-white">Held</strong>, with the reason under its title: fix it and publish it, publish it as it is, or delete it. The check looks at what a post says about the product, the pages it links to, its length and its wording. On the create and edit forms, <strong class="text-gray-900 dark:text-white">Generate Content</strong> writes a post from a topic and shows what the check made of it above the form; nothing is saved until you save.</p>
        <p class="text-gray-600 dark:text-gray-300 mt-4"><strong class="text-gray-900 dark:text-white">Review</strong> goes through the posts already published: what the check finds in each, which posts are on the same subject, and what is proposed (keep, rewrite, merge into another post, or hide from search engines). It changes nothing by itself. <strong class="text-gray-900 dark:text-white">Merge</strong> on a row makes that post's address redirect to the other one, and keeps its text.</p>
    </section>

    <!-- System: Audit Log -->
    <section id="system-audit-log" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
            </svg>
            Audit Log (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Audit Log is the first page of the System tab: a record of sign-ins, changes and payments across the installation and who made them, newest first. Each row shows the time, the user, the action, the IP address and a line of details. An action that failed or was refused is marked in red.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Four figures</strong> - total entries, entries today, failed sign-in attempts today, and how many distinct IP addresses were seen today</li>
            <li><strong class="text-gray-900 dark:text-white">Filter by category</strong> - admin, api, auth, boost, event, google_calendar, profile, sale, schedule, stripe, subscription and webhook</li>
            <li><strong class="text-gray-900 dark:text-white">Filter by date</strong> - a From and To range</li>
            <li><strong class="text-gray-900 dark:text-white">Search</strong> - matches the action name, the details and the IP address</li>
            <li><strong class="text-gray-900 dark:text-white">Sort</strong> - press a column heading to sort by time, user, action, IP address or details, and again to reverse it</li>
            <li><strong class="text-gray-900 dark:text-white">Listing</strong> - fifty entries per page</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--audit-log" alt="Admin Audit Log: four figures and the category, date and search filters above the list of entries" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Entries are pruned after 90 days</div>
            <p>A daily scheduled task deletes audit entries older than 90 days, so the table cannot grow without bound. A few rare entries are kept for good, because they are the only record of what happened: subscription and plan changes, selling-trial starts, schedule claims, and payment gateway and calendar connections. Those lose their IP address and browser details at the same 90 days. If you need to keep the rest longer, export or replicate them yourself; the retention is set by the pruning command, not by a setting on this page.</p>
        </div>
    </section>

    <!-- System: Queue -->
    <section id="system-queue" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
            </svg>
            Queue (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Queue page reports on the scheduler and on background jobs: calendar syncs, newsletter batches, graphics generation and anything else the application defers. It reads from the top down in the order you would ask: is the runner alive (<strong class="text-gray-900 dark:text-white">Scheduler</strong>), how much has it left to do (<strong class="text-gray-900 dark:text-white">Work Waiting</strong>), then the jobs themselves. When the scheduler has stopped, a job has failed or the oldest due job has waited more than an hour, a red <strong class="text-gray-900 dark:text-white">Queue Health Issues</strong> notice says so above everything else.</p>

        <h3 id="queue-scheduler" class="doc-subheading">Scheduler</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The card at the top of the page answers one question: is timed work happening at all? Nothing else on this page can drain while the scheduler is stopped, so read it first.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Last tick</strong> - every scheduler run stamps a heartbeat, even on minutes when nothing was due, so a stale value means the scheduler itself stopped rather than that the queue is quiet. The threshold is <code class="doc-inline-code">SCHEDULER_STALE_MINUTES</code> (default 20).</li>
            <li><strong class="text-gray-900 dark:text-white">Rails</strong> - each way of running the schedule ages separately: a crontab or worker running <code class="doc-inline-code">schedule:run</code>, and the <code class="doc-inline-code">/translate_data</code> cron endpoint. They are listed apart on purpose, so a worker that has died is visible even while another rail keeps the overall heartbeat fresh. Label a dedicated scheduler container with <code class="doc-inline-code">SCHEDULER_RAIL=worker</code>.</li>
            <li><strong class="text-gray-900 dark:text-white">Scheduled Tasks</strong> - how many of the tasks that do something on this install are reporting, and under <strong class="text-gray-900 dark:text-white">Needs attention</strong> how many are failed or overdue. Those are listed in the card, with the error for a failure; the <strong class="text-gray-900 dark:text-white">Show all</strong> line under them opens every task with its cadence and when it last ran. Tasks that are a no-op on this kind of install, such as the subscription reminders on a plain selfhost, are left out. A task is only called overdue relative to its own schedule, so "20 hours ago" is fine for a daily task and a problem for an hourly one, and a task skipped because the previous run was still going is not a failure. While the scheduler is stopped the list is hidden, because none of it would mean anything.</li>
            <li><strong class="text-gray-900 dark:text-white">Runtime</strong> - the cache store that holds the heartbeat, whether every container can read it, and the host the last task ran on. A store that lives inside one container (<code class="doc-inline-code">file</code> on an install with more than one) hides a healthy scheduler from this page, and the card says so when that is what it is seeing: set <code class="doc-inline-code">CACHE_STORE</code> to a shared driver such as <code class="doc-inline-code">database</code>.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Installs driven only by the <code class="doc-inline-code">/translate_data</code> endpoint see a note instead of the task list: that route reports a single heartbeat rather than per-task results.</p>

        <h3 id="queue-work-waiting" class="doc-subheading">Work Waiting</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Most timed work never becomes a queued job: the scheduled command does it itself. This card counts the rows those commands still have to get through, which the job counts below cannot show.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Translation Backlog</strong> - what is still waiting to be translated, by kind, with what the last run got through, the rate that makes per hour and an estimate of the time to clear. Rows that have failed repeatedly and are waiting out a retry cooldown are named apart, because the next run will skip them</li>
            <li><strong class="text-gray-900 dark:text-white">Everything else with a countable backlog</strong> - one row each, such as <strong>Newsletters to send</strong>, <strong>Events to share</strong>, <strong>Installments to charge</strong>, <strong>Unpaid tickets to release</strong> and <strong>Waitlist holds to expire</strong>, beside the task that drains it and how often that runs</li>
            <li><strong class="text-gray-900 dark:text-white">Measure now</strong> - the counts are measured at most every five minutes, so the navigation's Refresh does not move them. The foot of the card says how old the measurement is, and this button takes a new one</li>
        </ul>

        <h3 id="queue-jobs" class="doc-subheading">Jobs</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Four figures</strong> - pending jobs (with a per-queue breakdown underneath the number), failed jobs, job batches, and the age of the oldest pending job</li>
            <li><strong class="text-gray-900 dark:text-white">Oldest Pending Job</strong> - the last of the four turns red once a job has been due for more than an hour, which normally means the scheduler is not draining the queue</li>
            <li><strong class="text-gray-900 dark:text-white">Pending Jobs by Class</strong> - which job type is backing up</li>
            <li><strong class="text-gray-900 dark:text-white">Failed jobs</strong> - the hundred most recent, each with its class, queue, failure time and exception; retry or delete them individually</li>
            <li><strong class="text-gray-900 dark:text-white">Pending jobs</strong> - the hundred most recent, with attempt count and when each becomes available</li>
            <li><strong class="text-gray-900 dark:text-white">Job batches</strong> - the fifty most recent, with progress and failure counts</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Three bulk actions sit in the heading of the list each one acts on, each behind a confirmation prompt. They are only rendered when they have something to act on:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Retry All Failed</strong> and <strong class="text-gray-900 dark:text-white">Clear All Failed</strong> - shown only while there is at least one failed job</li>
            <li><strong class="text-gray-900 dark:text-white">Flush Pending</strong> - shown only while there is at least one pending job. It truncates the job table, so the work is discarded permanently; use it only to clear a backlog you know is stale.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Failed jobs are also retried automatically: the <a href="{{ route('marketing.docs.selfhost.installation') }}#cron" class="doc-link">cron entry</a> pushes them back onto the queue for you, so a job that failed because of a passing problem (an unreachable mail server, a rate-limited API) usually recovers without anyone touching this page. Each job gets five automatic retries spaced fifteen minutes apart. After that it is left alone rather than retried forever, so a job that can never succeed stops consuming a worker on every cron run - it simply stays in the table with the exception that explains it.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Nothing is set aside permanently. The count resets after a day, so a job left over from a long outage is picked up again tomorrow, and pressing <strong class="text-gray-900 dark:text-white">Retry</strong> clears it immediately - which is what to do once you have fixed whatever the exception was pointing at. A job whose exception says it refers to a record that no longer exists cannot be retried at all, because the record it needs is gone; delete it.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">The cron entry runs the queue</div>
            <p>No separate worker has to be kept running. Every minute the <a href="{{ route('marketing.docs.selfhost.installation') }}#cron" class="doc-link">cron entry</a> works through the queue until it is empty, so a queued job starts within about a minute. With the default <code class="doc-inline-code">QUEUE_CONNECTION=sync</code> nothing is queued at all: work happens inside the web request and the job lists stay empty, which is normal for a small install.</p>
        </div>
        <x-doc-screenshot id="selfhost-admin--queue" alt="Admin Queue page: pending jobs, failed jobs, job batches and the oldest pending job above the failed and pending job lists" />
    </section>

    <!-- System: Logs -->
    <section id="system-logs" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
            </svg>
            Logs (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Logs page reads the application log at <code class="doc-inline-code">storage/logs/laravel.log</code> so you can diagnose problems without shell access. It parses only the last 5 MB of the file, which keeps a very large log from exhausting memory but also means older entries are not shown.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Four figures</strong> - the size of the file, the number of entries read, how many are errors (error level and above) and how many are warnings. When any errors are present, or the file has grown past 100 MB, a red <strong class="text-gray-900 dark:text-white">Log Health Issues</strong> notice says so first</li>
            <li><strong class="text-gray-900 dark:text-white">Repeated Errors</strong> - entries at error level and above, grouped by their message with the variable parts collapsed, showing the number of occurrences and when it was last and first seen. A message only appears once it has been logged at least twice, so this is usually the fastest way to find the one thing going wrong repeatedly. A row opens to its full message and stack trace, and a button copies the error</li>
            <li><strong class="text-gray-900 dark:text-white">Recent Log Entries</strong> - up to 200 shown at a time, newest first, each opening to its context and full stack trace. Narrow them to a single level, from emergency down to debug, and search the message text and the stack trace</li>
            <li><strong class="text-gray-900 dark:text-white">Download Log</strong> - at the end of the page's opening line, fetches the whole log file</li>
            <li><strong class="text-gray-900 dark:text-white">Clear Log</strong> - at the foot of the page, apart from Download on purpose: it asks first, empties the file, and cannot be undone</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--logs" alt="Admin Logs page: file size, total entries, errors and warnings above the recent log entries with their level filter and search" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Logs can contain personal data</div>
            <p>Stack traces and log messages may include email addresses and request details. Treat a downloaded log file as sensitive, and remember that clearing the file cannot be undone.</p>
        </div>
    </section>

    <!-- System: App Update -->
    <section id="system-app-update" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            App Update (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The App Update page shows the version this installation is running next to the latest release published on GitHub, and applies an update in one click. It never appears on eventschedule.com, which deploys from git. The same update is offered on the <a href="{{ route('marketing.docs.account_settings') }}#app-update" class="doc-link">App Update</a> tab of your own Settings page; the admin one is the operator's copy, and it is the one the System tab badges when a release is waiting.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Installed Version</strong> - what <code class="doc-inline-code">config/self-update.php</code> reports, which the release you are running ships. If this stays on an old number after a successful update, you have a cached config: run <code class="doc-inline-code">php artisan config:clear</code>.</li>
            <li><strong class="text-gray-900 dark:text-white">Latest Version</strong> - the newest tag on GitHub, refreshed once a day by a scheduled check so no page load has to wait on the network. It reads <strong class="text-gray-900 dark:text-white">Unknown</strong> if GitHub could not be reached, which is never treated as an update being available.</li>
            <li><strong class="text-gray-900 dark:text-white">Last Checked</strong> - how long ago the latest version was last read from GitHub.</li>
            <li><strong class="text-gray-900 dark:text-white">Check for Updates</strong> - ask GitHub now instead of waiting for the daily check. Limited to five requests a minute, because an unauthenticated install gets 60 GitHub calls an hour in total.</li>
            <li><strong class="text-gray-900 dark:text-white">Update</strong> - only there while a newer release is out, beside an <strong class="text-gray-900 dark:text-white">Update available</strong> mark. It asks first, then downloads and installs the new release and runs any new database migrations. Take a backup first.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">An update never replaces <code class="doc-inline-code">bootstrap/cache/</code>, so it clears the cached config, routes, events, views and package manifest for you once the new release is in place. If you run <code class="doc-inline-code">php artisan optimize</code> as part of your deployment, re-run it afterwards.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">If the page is missing, or the update times out</div>
            <p>Everything here is also available from the command line, which is the way out if the screen itself is unreachable: <code class="doc-inline-code">php artisan app:update</code> does the same download, install and migrate. A large update can outrun PHP's <code class="doc-inline-code">max_execution_time</code>, and the command has no such limit. Your uploads, custom translations and anything else under <code class="doc-inline-code">storage/app/</code> are excluded from the update by design.</p>
        </div>
    </section>

    <!-- System: Settings -->
    <section id="system-settings" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Settings page holds the handful of settings that apply to the whole installation. It is built from separate cards, each with its own Save button, and a card is only rendered when it can do something on this install. There are seven, and a plain selfhost sees four of them: Header / Footer Code, Event Schedule network, Platform currency and Realtime visitors, plus Accommodation affiliate when it is enabled. None of these settings can be changed while the install is in demo mode.</p>

        <x-doc-screenshot id="selfhost-admin--settings" alt="The admin Settings page, opening on the Header / Footer Code card" />

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Card, in the page's order</th>
                        <th>What it does</th>
                        <th>When it appears</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#settings-header-footer" class="doc-link">Header / Footer Code</a></td>
                        <td>Injects your own code into every public guest page</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#settings-network" class="doc-link">Event Schedule network</a></td>
                        <td>Shares your public events with the eventschedule.com listings</td>
                        <td>Every install except eventschedule.com</td>
                    </tr>
                    <tr>
                        <td><a href="#settings-monetization" class="doc-link">Monetization</a></td>
                        <td>Google AdSense and the on-network promotions marketplace</td>
                        <td><code class="doc-inline-code">ADS_ENABLED=true</code> on a multi-tenant hosted install other than eventschedule.com</td>
                    </tr>
                    <tr>
                        <td><a href="#settings-plan-pricing" class="doc-link">Plan pricing</a></td>
                        <td>What you advertise Pro and Enterprise at</td>
                        <td>A multi-tenant hosted install, or one serving the marketing pages</td>
                    </tr>
                    <tr>
                        <td><a href="#settings-currency" class="doc-link">Platform currency</a></td>
                        <td>The currency this installation shows its own prices in</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#settings-realtime" class="doc-link">Realtime visitors</a></td>
                        <td>Turns <a href="#realtime" class="doc-link">Realtime</a> on or off for the whole install, and the schedule owners' view of it</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><a href="#settings-accommodation" class="doc-link">Accommodation affiliate</a></td>
                        <td>A fallback affiliate ID for the nearby-lodging map</td>
                        <td><code class="doc-inline-code">STAY22_ENABLED=true</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="settings-header-footer" class="doc-subheading">Header and footer code</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This is where site-wide tracking such as <strong class="text-gray-900 dark:text-white">Google Tag Manager</strong> or Google Analytics goes.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Header Code</strong> - injected into the <code class="doc-inline-code">&lt;head&gt;</code> of public pages. Best for tag managers and analytics loaders.</li>
            <li><strong class="text-gray-900 dark:text-white">Footer Code</strong> - injected just before the closing <code class="doc-inline-code">&lt;/body&gt;</code> tag. Best for deferred scripts, chat widgets, and the Google Tag Manager <code class="doc-inline-code">&lt;noscript&gt;</code> snippet.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The code is applied to public schedule, event and ticket pages. It is never added to the admin portal or to the marketing site. Script tags you paste are given the request's security nonce automatically, so they are allowed to run.</p>
        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">Only paste trusted code</div>
            <p>Header and footer code runs on every public page exactly as entered. Only paste code from sources you trust. Access to this page is restricted to admin users, and every save is written to the audit log.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Google Tag Manager and Google Analytics are permitted by the built-in Content Security Policy and work out of the box. Scripts that load from other external domains may be blocked: add the domain to the <code class="doc-inline-code">script-src</code> directive in <code class="doc-inline-code">app/Http/Middleware/SecurityHeaders.php</code> if needed.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Injected analytics are not gated by the cookie consent banner: header and footer code runs before anyone is asked. Gate it yourself on the visitor's choice, which every page exposes as <code class="doc-inline-code">window.esConsent.has('analytics')</code> and <code class="doc-inline-code">window.esConsent.has('marketing')</code>, with an <code class="doc-inline-code">es:consent-change</code> event on <code class="doc-inline-code">document</code> when it changes, and write your own privacy policy under <a href="#system-legal-pages" class="doc-link">Legal Pages</a> to say what it does.</p>

        <h3 id="settings-network" class="doc-subheading">Event Schedule network</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">This card opts your installation into sharing its public events with the eventschedule.com listings. From top to bottom:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Where the install stands</strong> - once it has connected: pending review, approved with the number of events on the network and a link to them, or suspended, and when the last sync ran. A sync that failed is reported here too</li>
            <li><strong class="text-gray-900 dark:text-white">Share events with the network</strong> - the toggle itself. Turning it off withdraws the listings again</li>
            <li><strong class="text-gray-900 dark:text-white">Contact email</strong> - lets the moderators reach you with their decision and the steps to get listed</li>
            <li><strong class="text-gray-900 dark:text-white">Also list these schedules</strong> - the schedules you own that have not been listed yet, each with how many of its events would be shared, so switching sharing on and choosing what to share is one save</li>
            <li><strong class="text-gray-900 dark:text-white">The preview</strong> - exactly which schedules and events would be sent, each event marked sent, next sync, needs an image or not accepted, so nothing leaves your install unseen</li>
            <li><strong class="text-gray-900 dark:text-white">Two counts under the preview</strong> - why it may be shorter than you expect: schedules that have not verified an email address or phone number, and other owners' schedules that have not been listed yet, whose owners are asked on their dashboards</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">See <a href="{{ route('marketing.docs.selfhost.federation') }}" class="doc-link">Federation</a> for the full picture.</p>

        <h3 id="settings-monetization" class="doc-subheading">Monetization</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This card configures advertising on free schedules: whether to show AdSense, the publisher and ad slot IDs, whether personalized ads are allowed, whether to run your own promotions marketplace, whether promotions take priority over AdSense, and the prices you charge per thousand impressions and per click. It stays hidden unless <code class="doc-inline-code">ADS_ENABLED=true</code> and the install is a multi-tenant hosted platform other than eventschedule.com, because a selfhosted install resolves every schedule to Enterprise and so has no free tier for an ad to appear on. See <a href="{{ route('marketing.docs.saas.monetization') }}" class="doc-link">Monetization</a> for what each setting does.</p>

        <h3 id="settings-plan-pricing" class="doc-subheading">Plan pricing</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Four fields, the monthly and yearly amount for Pro and for Enterprise, decided in one place and used everywhere the platform quotes itself: the pricing page and the rest of the marketing site, the user guide, the referral program, the upgrade prompts and Plan tab inside the admin portal, and the structured data search engines read. Saving moves all of them at once, with no deploy and no <code class="doc-inline-code">php artisan config:cache</code>.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Leave a field empty to fall back to the matching <code class="doc-inline-code">STRIPE_PRICE_*_AMOUNT</code> variable in <code class="doc-inline-code">.env</code>; the value in force is shown as the field's placeholder, so an empty box is never ambiguous. Amounts take two decimal places and are printed in the platform currency set below, which means a currency with no minor unit, such as the yen, rounds them to whole numbers. The card is hidden on a single-tenant selfhosted install, where every schedule resolves to Enterprise and nothing quotes a plan price at all.</p>
        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">A label, not a price</div>
            <p>Nothing here charges anyone. What a customer pays comes from the Stripe price your <code class="doc-inline-code">STRIPE_PRICE_*</code> IDs point at, and nothing reconciles the two. Changing a price is three steps in order: create the new price in Stripe, point the price ID at it in <code class="doc-inline-code">.env</code>, then set the number here. Do only the last and your site advertises one figure and bills another.</p>
            <p>Keep the <code class="doc-inline-code">.env</code> amounts up to date as well. Revenue reporting and renewal emails read those, not this card, on purpose: an amount you change to run a promotion must not restate revenue you have already booked, or quote an existing subscriber a figure their card will never be charged.</p>
        </div>

        <h3 id="settings-currency" class="doc-subheading">Platform currency</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The currency this installation quotes <em>its own</em> prices in. It decides the symbol printed beside every plan price and upgrade prompt, and it is the currency a new event falls back to when its schedule has no country set. Pick from the same list the ticket and gift-card pickers offer. It defaults to <code class="doc-inline-code">PLATFORM_CURRENCY</code> from <code class="doc-inline-code">.env</code>, and to US dollars when that is unset.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">It does not touch money that already exists. A ticket is always shown in the currency it was priced in, a past sale in the currency it was taken in, and a schedule that has set a country keeps the currency that country implies. Changing this never rewrites a stored amount.</p>
        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">A label, not a price</div>
            <p>On a platform that sells plans, this decides what the interface prints, not what a customer is charged. The charge comes from the Stripe price your <code class="doc-inline-code">STRIPE_PRICE_*</code> IDs point at, exactly as with the <code class="doc-inline-code">*_AMOUNT</code> variables. Set the currency here, the amounts in <code class="doc-inline-code">.env</code> and the prices in Stripe, and keep all three in step, or your site will advertise one figure and bill another.</p>
        </div>

        <h3 id="settings-realtime" class="doc-subheading">Realtime visitors</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The first toggle, <strong>Record live page views</strong>, switches <a href="#realtime" class="doc-link">Realtime</a> on or off for the whole install. Turning it on also shows the cookie consent banner, since visitors are identified only after they accept. Turning it off deletes every live activity record straight away.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The second, <strong>Show schedule owners live traffic to their own pages</strong>, decides whether people who manage a schedule get <a href="#realtime-owner-view" class="doc-link">a Realtime tab of their own</a> on their Analytics page. With it off, Realtime is yours alone.</p>

        <h3 id="venue-map" class="doc-subheading">Venue map</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Schedules can show <a href="{{ route('marketing.docs.creating_schedules') }}#engagement-venue-map" class="doc-link">a map of their venues</a> on their public page. It is off until you name its address search in <code class="doc-inline-code">.env</code>, and it has no card on this page.</p>
        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">MAP_GEOCODER_URL</code> is the address search that finds each venue's position. Your server asks it, once per address: at most four addresses a minute from the scheduler, and one a second for a quarter of a minute after an owner switches a map on. An address is kept while a map still uses it and for 90 days after. It has to answer in Nominatim's <code class="doc-inline-code">/search</code> format, and a key it wants in its query string can be part of the address. Until it is set there is no map and no row in the schedule form.</li>
            <li><code class="doc-inline-code">MAP_TILE_URL</code> is where a visitor's browser fetches the street images, so that service sees the visitor's IP address. Nothing is fetched until the visitor has allowed marketing cookies or pressed <strong>Show map</strong> beside a sentence naming the service. Left unset, the map is pins on a plain ground and no third party is contacted from the browser.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">OpenStreetMap's own services work for both: <code class="doc-inline-code">https://nominatim.openstreetmap.org/search</code> and <code class="doc-inline-code">https://tile.openstreetmap.org/{z}/{x}/{y}.png</code>. Both are free and both have a usage policy you accept by using them, so a busy install should run its own or use a hosted provider. The credit printed on the map comes from <code class="doc-inline-code">MAP_ATTRIBUTION</code> and links to <code class="doc-inline-code">MAP_ATTRIBUTION_URL</code>.</p>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What to know before switching it on</div>
            <p>Positions never come from Google: its terms allow its coordinates on a Google map only, so the map keeps its own. Setting these does not raise the cookie banner; without a banner every visitor presses <strong>Show map</strong> each time they open a page, and <strong>Open the map on arrival</strong> is not offered to owners. If address lookups fail for an hour, the dashboard's <a href="#needs-attention" class="doc-link">Needs attention</a> list says so. If you replaced the privacy policy on the <a href="#system-legal-pages" class="doc-link">Legal Pages</a> page, add the two services to yours: the built-in policy lists them by itself.</p>
        </div>

        <h3 id="settings-accommodation" class="doc-subheading">Accommodation affiliate</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Setting <code class="doc-inline-code">STAY22_ENABLED=true</code> lets schedules show a map of lodging near their venues and earn affiliate commission. The card holds a single field, the <strong class="text-gray-900 dark:text-white">Fallback Stay22 affiliate ID</strong>, used only for schedules that enabled the map without supplying an ID of their own, and never on a schedule's own custom domain. See <a href="{{ route('marketing.docs.saas.monetization') }}#accommodation" class="doc-link">Accommodation affiliate</a> for the disclosure obligations it places on you.</p>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">The master switch is env only</div>
            <p>The Content Security Policy is rebuilt from <code class="doc-inline-code">STAY22_ENABLED</code> on every request, so it can only be set in <code class="doc-inline-code">.env</code> and never from the admin panel. If you cache your configuration, re-run <code class="doc-inline-code">php artisan config:cache</code> after changing it, or the affiliate ID will save while the map stays blocked.</p>
        </div>

        <h3 id="cookie-consent" class="doc-subheading">Cookie consent banner</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The banner has no card of its own: it follows from the settings above and from <code class="doc-inline-code">.env</code>. It appears only when something on the page actually needs consent: Google Analytics (<code class="doc-inline-code">ANALYTICS_ID</code>), advertising (<code class="doc-inline-code">ADS_ENABLED</code>), the accommodation map (<code class="doc-inline-code">STAY22_ENABLED</code>), the Meta Pixel for Boosted events (<code class="doc-inline-code">META_PIXEL_ID</code>) or <a href="#realtime" class="doc-link">Realtime</a>, which identifies visitors only after they accept. A plain install that has none of those shows no banner at all, and sets no cookie that is not needed to make the site work. It never appears inside an embedded calendar, where nothing that needs consent runs.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Visitors choose two categories separately, or both at once with <strong>Allow all</strong> and <strong>Decline</strong>. <strong>Analytics</strong> covers Google Analytics, which is not loaded at all until it is allowed, and the identified Realtime view (yours, and where you have switched it on, the <a href="#realtime-owner-view" class="doc-link">rows with no name</a> a schedule owner sees). <strong>Marketing and embedded content</strong> covers the attribution cookies, the Meta Pixel, AdSense, and maps, videos and booking widgets from other sites; until it is allowed, a YouTube video or a Google map shows a button that loads just that one item. A visitor whose browser sends Global Privacy Control declines both automatically and never sees the banner. The choice lasts twelve months, and a <strong>Cookie preferences</strong> link in the footer of the marketing site and of every schedule page reopens it.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Where you have switched on the <a href="#realtime-owner-view" class="doc-link">schedule owners' view</a>, the banner's first line also says that the organizer of a schedule page sees visits to it, so everyone reads it before choosing. The banner names Google Analytics only on an install that has <code class="doc-inline-code">ANALYTICS_ID</code> set; without it the Analytics option describes the Realtime view alone, and where neither is in use that option has nothing behind it. If you do set <code class="doc-inline-code">ANALYTICS_ID</code>, say so in your own privacy policy under <a href="#system-legal-pages" class="doc-link">Legal Pages</a>: until you publish one, the banner's <strong>Learn more</strong> link leads to eventschedule.com's policy, which describes eventschedule.com and may not mention Google Analytics at all.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The attribution cookies, <code class="doc-inline-code">utm_params</code>, <code class="doc-inline-code">utm_referrer_url</code> and <code class="doc-inline-code">utm_landing_page</code>, remember for 30 days which campaign or referring site brought a visitor in, so a later signup or ticket sale can be credited to it. They are written only after a visitor allows marketing. Without that they are never written, and attribution lasts for the current session only, which is enough for a visitor who buys on the same visit.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Set <code class="doc-inline-code">COOKIE_CONSENT_BANNER=true</code> in <code class="doc-inline-code">.env</code> to show the banner regardless, which is what you want if you run marketing campaigns and need attribution to survive across visits.</p>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Your own visit statistics need no consent</div>
            <p>The built-in analytics store daily totals only: views per device type, referrer, country and campaign tag. These totals hold no per-visitor record (Realtime, when on, is the one place that keeps a per-visitor record, for about an hour). IP address and user-agent are hashed with your <code class="doc-inline-code">APP_KEY</code> and a salt that rotates daily, purely to deduplicate and filter bots, and that hash lives in the cache until midnight rather than in the database. Nothing is read from or written to the visitor's device, so no banner is required for it.</p>
        </div>
    </section>

    <!-- System: Translations -->
    <section id="system-translations" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
            </svg>
            Translations (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Translations page lets you review and customize every piece of text the app shows, in any of the supported languages. Fix an awkward translation, adapt wording to your industry (for example rename "ticket" to "registration" or "booking"), or fill in missing translations - all without editing any files.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Pick a language and file</strong> - <code class="doc-inline-code">messages</code> holds the app's UI strings; <code class="doc-inline-code">accessibility</code> and <code class="doc-inline-code">marketing</code> are smaller companion files. English is editable too, which is handy for renaming built-in terms.</li>
            <li><strong class="text-gray-900 dark:text-white">Search and filter</strong> - find strings by key or text, and set <strong class="text-gray-900 dark:text-white">Status</strong> to <strong class="text-gray-900 dark:text-white">Customized</strong> for only the keys you have changed or to <strong class="text-gray-900 dark:text-white">Missing translations</strong> for those the selected language lacks.</li>
            <li><strong class="text-gray-900 dark:text-white">Edit and save</strong> - type your text beside the original. A bar at the bottom of the page counts the unsaved changes and holds <strong class="text-gray-900 dark:text-white">Save</strong>. Changes apply immediately, are stored in the database, and survive app updates. A per-row revert restores the shipped translation at any time, and clearing a field is the same as reverting it.</li>
            <li><strong class="text-gray-900 dark:text-white">Copy as PHP</strong> - at the end of the page's opening line. It copies your customizations as ready-to-paste language-file lines, useful for moving them into another install or contributing a pull request.</li>
        </ul>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Placeholders and plurals</div>
            <p>Some strings contain placeholders such as <code class="doc-inline-code">:name</code> or plural forms separated by <code class="doc-inline-code">|</code>. Keep them in your version so dynamic values keep working - the editor warns you if one goes missing, but never blocks the save.</p>
        </div>
        <h3 id="translations-sharing" class="doc-subheading">Sharing improvements with the community</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Many translation fixes are useful to every Event Schedule install. You can share yours with the community for review, and approved suggestions ship with future releases. Nothing is sent unless you choose it. After a save the bar at the bottom asks whether to share what you just saved (<strong class="text-gray-900 dark:text-white">Not now</strong>, <strong class="text-gray-900 dark:text-white">Always share</strong> or <strong class="text-gray-900 dark:text-white">Share now</strong>), and while anything is unshared a <strong class="text-gray-900 dark:text-white">Share</strong> button beside Copy as PHP opens the list, where you tick exactly which changes to send. The <strong class="text-gray-900 dark:text-white">Community sharing</strong> card under the table holds the <strong class="text-gray-900 dark:text-white">Automatically share translation improvements</strong> toggle, for saved changes to be submitted on their own. Keep it off if your wording is specific to your business. Unshared changes are also counted on the dashboard's Needs attention list, so nothing sits forgotten.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Sharing sends the language, the file, the translation key, your suggested text and the shipped text it replaces, plus your app version and a random anonymous install identifier, to eventschedule.com. No URLs, email addresses, or other personal data are included. On eventschedule.com itself there is nothing to share: the same place holds a <strong class="text-gray-900 dark:text-white">Review suggestions</strong> button, which opens the queue of what other installs have sent.</p>

        <h3 id="translations-storage" class="doc-subheading">Where customizations are kept</h3>
        <p class="text-gray-600 dark:text-gray-300">Customizations are stored in the database and published as override files under <code class="doc-inline-code">storage/app/lang</code>, or wherever <code class="doc-inline-code">LANG_OVERRIDES_PATH</code> points if you run several servers from a shared volume. Hand-made override files (the pre-existing <a href="{{ route('marketing.docs.selfhost.installation') }}#translations" class="doc-link">custom translations</a> approach) are adopted into the editor automatically. After restoring a database backup or cloning to a new server, run <code class="doc-inline-code">php artisan translations:publish</code> to rebuild the files.</p>
    </section>

    <!-- System: Legal Pages -->
    <section id="system-legal-pages" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z" />
            </svg>
            Legal Pages (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Your installation ships with a privacy policy and terms of service written for eventschedule.com. They are almost certainly not the documents you need: privacy law differs by country, and GDPR, PAIA and POPIA each ask for different disclosures. The Legal Pages screen lets you replace them with your own, and add a cookie policy, which the app has no page for otherwise.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">There is a card for each of the three documents, <strong class="text-gray-900 dark:text-white">Privacy Policy</strong>, <strong class="text-gray-900 dark:text-white">Terms of Service</strong> and <strong class="text-gray-900 dark:text-white">Cookie Policy</strong>, and each one offers two ways to supply it:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">External URL</strong> - link to a policy you already publish elsewhere, for example on your main company website. Every link in the app goes straight there.</li>
            <li><strong class="text-gray-900 dark:text-white">Document</strong> - write the policy here in Markdown. It is served from your own domain at <code class="doc-inline-code">/privacy</code>, <code class="doc-inline-code">/terms-of-service</code> or <code class="doc-inline-code">/cookie-policy</code>, styled to match your install, with a "last updated" date. Headings automatically get anchors, so you can link to individual clauses.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">If you fill in both, the external URL wins, and the card warns that your document is not being shown. If you leave both blank, the built-in page is used, exactly as before. The end of each card's title row says which of the three is in force (<strong class="text-gray-900 dark:text-white">Built-in page</strong>, <strong class="text-gray-900 dark:text-white">External URL</strong> or <strong class="text-gray-900 dark:text-white">Your own document</strong>), with a <strong class="text-gray-900 dark:text-white">View page</strong> link once you have supplied one. Each card saves on its own. Nothing ships pre-filled: the editors start empty, because a template written for one jurisdiction would be wrong for most.</p>

        <h3 id="legal-where" class="doc-subheading">Where your policies appear</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Saving a document changes every link to it across the whole app at once - the "I accept the terms and privacy policy" checkbox on signup, ticket checkout, RSVPs, booking requests and event submissions, the issued ticket, the admin portal's About menu, and the cookie banner's "Learn more" link, which prefers your cookie policy when you have one. Until you write one, those links continue to point at eventschedule.com.</p>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">This is not legal advice</div>
            <p>What you save here is what your users agree to when they register or buy a ticket. Event Schedule cannot tell you what your policies need to say - have a qualified professional review them for the jurisdictions you operate in.</p>
        </div>
    </section>

    <!-- System: Federation (eventschedule.com only) -->
    <section id="system-federation" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="share" />
            Federation (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Federation page is the other end of the <a href="#settings-network" class="doc-link">Event Schedule network</a> card: the moderation queue for the installs that share their events with eventschedule.com. It exists only on eventschedule.com, so it is not in your System row. What you see of it from your own install is the result: the status on the network card (pending review, approved or suspended) and the email the moderators send to your contact address.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Tabs</strong> - <strong>Pending</strong>, <strong>Approved</strong>, <strong>Suspended</strong> and <strong>All</strong>, plus <strong>Flagged</strong> while an approved install has changed its address</li>
            <li><strong class="text-gray-900 dark:text-white">Each install</strong> - its name, address and contact email, when it was last seen, its public schedules and a sample of the listings it would publish, so an approval is never made on a name alone</li>
            <li><strong class="text-gray-900 dark:text-white">Actions</strong> - <strong>Approve</strong>, after which the install's listings publish automatically, <strong>Suspend</strong>, which hides them, and <strong>Send welcome email</strong>. Ticking several installs applies one action to all of them</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">The <a href="{{ route('marketing.docs.selfhost.federation') }}" class="doc-link">Federation</a> guide describes the whole exchange from the selfhost side.</p>
    </section>

    <!-- System: Support (hosted only) -->
    <section id="system-support" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="phone" />
            Support (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Support page is the admin's side of the built-in support chat: conversations from people signed in to the app and, on eventschedule.com, from visitors on the website. It only exists when <code class="doc-inline-code">IS_HOSTED=true</code>. Unread messages are counted on the System tab and in <a href="#needs-attention" class="doc-link">Needs attention</a>.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Support availability</strong> - the switch at the top marks you as available for chat, and the words beside it say how you are shown right now</li>
            <li><strong class="text-gray-900 dark:text-white">Conversations</strong> - the list, newest message first, with a count on each one that has unread messages and a mark on those that are closed</li>
            <li><strong class="text-gray-900 dark:text-white">The open conversation</strong> - who it is (for an account, its email address and schedules), the messages, and the reply box. <strong>Close</strong> ends the conversation</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">The page checks for new messages on its own while it is open. How the chat is switched on, who is notified and when, is in <a href="{{ route('marketing.docs.saas.setup') }}#support-chat" class="doc-link">Support chat</a> in the SaaS guide.</p>
    </section>
</x-docs-page>
