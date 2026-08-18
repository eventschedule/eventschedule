<x-docs-page
    key="selfhost/admin"
    description="Learn how to use the Event Schedule admin panel to monitor users, revenue, analytics, and manage platform settings for your selfhosted installation."
    lede="Monitor your platform's users, revenue, and analytics, and manage system settings from the admin panel."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#accessing">Accessing /admin</x-doc-nav-link>
        <x-doc-nav-link href="#dashboard">Dashboard</x-doc-nav-link>
        <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2 mt-4 pt-3 border-t border-gray-200 dark:border-white/10">Insights</div>
        <x-doc-nav-link href="#insights-users">Users</x-doc-nav-link>
        <x-doc-nav-link href="#insights-revenue">Revenue</x-doc-nav-link>
        <x-doc-nav-link href="#insights-analytics">Analytics</x-doc-nav-link>
        <x-doc-nav-link href="#insights-usage">Usage</x-doc-nav-link>
        <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2 mt-4 pt-3 border-t border-gray-200 dark:border-white/10">Manage</div>
        <x-doc-nav-link href="#manage-boost">Boost</x-doc-nav-link>
        <x-doc-nav-link href="#manage-plans">Schedules and plans</x-doc-nav-link>
        <x-doc-nav-link href="#manage-domains">Domains</x-doc-nav-link>
        <x-doc-nav-link href="#manage-newsletters">Newsletters</x-doc-nav-link>
        <x-doc-nav-link href="#manage-blog">Blog</x-doc-nav-link>
        <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider mb-2 mt-4 pt-3 border-t border-gray-200 dark:border-white/10">System</div>
        <x-doc-nav-link href="#system-audit-log">Audit Log</x-doc-nav-link>
        <x-doc-nav-link href="#system-queue">Queue</x-doc-nav-link>
        <x-doc-nav-link href="#system-logs">Logs</x-doc-nav-link>
        <x-doc-nav-link href="#system-settings">Settings</x-doc-nav-link>
        <x-doc-nav-link href="#system-translations">Translations</x-doc-nav-link>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">The admin panel gives the operator of an installation platform-wide visibility and a small set of platform-wide controls. It is separate from a schedule owner's own admin portal: nothing here is scoped to one schedule. The navigation is one plain tab plus three dropdowns, and which items appear depends on how the install is configured.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Pages</th>
                        <th>Availability</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Dashboard</td>
                        <td>Key metrics, growth trends, and the Needs attention list</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Insights</td>
                        <td>Users, Revenue, Analytics, Usage</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Manage</td>
                        <td>Boost, Newsletters</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>Manage</td>
                        <td>Schedules, Domains, Referrals, Blog</td>
                        <td>Only when <code class="doc-inline-code">IS_HOSTED=true</code></td>
                    </tr>
                    <tr>
                        <td>System</td>
                        <td>Audit Log, Queue, Logs, Settings, Translations</td>
                        <td>Every install</td>
                    </tr>
                    <tr>
                        <td>System</td>
                        <td>Support</td>
                        <td>Only when <code class="doc-inline-code">IS_HOSTED=true</code></td>
                    </tr>
                    <tr>
                        <td>System</td>
                        <td>Federation (the moderation queue for other instances)</td>
                        <td>eventschedule.com only</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">A <strong class="text-gray-900 dark:text-white">Refresh</strong> button sits at the end of the navigation row. No admin page polls or auto-refreshes, so the numbers are always as of the last page load.</p>

        <h3 class="doc-subheading">Date range</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Six pages carry a date-range selector: the Dashboard, Users, Revenue, Analytics, Usage and Boost. The choices are fixed: <strong class="text-gray-900 dark:text-white">Last 7 Days</strong>, <strong class="text-gray-900 dark:text-white">Last 30 Days</strong> (the default), <strong class="text-gray-900 dark:text-white">Last 90 Days</strong> and <strong class="text-gray-900 dark:text-white">All Time</strong>. There is no custom start and end date. Where a page shows a change percentage, it compares against the immediately preceding window of the same length. All Time has no preceding window, so read its change figures as noise: with nothing to compare against, every one of them reports +100%.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Audit Log has its own From and To date filter instead, and the remaining pages are not date filtered at all.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Why the totals look low</div>
            <p>The counts across the Dashboard and Insights deliberately exclude demo data, users who never confirmed their email address, and schedules that verified neither an email address nor a phone number. A brand new install that has not verified anything therefore reports zero users and zero schedules even though rows exist in the database.</p>
        </div>
    </section>

    <!-- Accessing /admin -->
    <section id="accessing" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            Accessing /admin
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The admin panel lives at <code class="doc-inline-code">/admin</code>, which redirects to <code class="doc-inline-code">/admin/dashboard</code>. It is restricted to users whose <code class="doc-inline-code">is_admin</code> column is set to <code class="doc-inline-code">true</code>, and there is no screen for granting that: it is set in the database.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Grant the flag by updating the <code class="doc-inline-code">users</code> table directly (see the query below).</li>
            <li>Make sure the account has a password. An account created through Google sign-in has none, and the panel will send you to your profile settings to set one first.</li>
            <li>Sign in and open <code class="doc-inline-code">/admin</code>, or use the <strong class="text-gray-900 dark:text-white">Admin</strong> item that now appears at the bottom of the main sidebar.</li>
            <li>Re-enter your password when prompted. This confirmation is required once per session, on top of being signed in.</li>
        </ol>

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
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Dashboard
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The dashboard is the landing page of the admin panel and is read-only. From top to bottom it shows:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Needs attention</strong> - everything waiting on an admin, when there is anything (see below)</li>
            <li><strong class="text-gray-900 dark:text-white">Total users, schedules and events</strong> - each with the change against the previous period and how many were added inside the selected range</li>
            <li><strong class="text-gray-900 dark:text-white">Activity</strong> - active users in the last 7 and 30 days, upcoming online events, and private events with how many of those are password protected</li>
            <li><strong class="text-gray-900 dark:text-white">Money</strong> - schedules paying through Stripe, annual recurring revenue (the tile is labelled <strong class="text-gray-900 dark:text-white">ARR</strong>), active boost campaigns, and boost markup revenue for the period</li>
            <li><strong class="text-gray-900 dark:text-white">Upcoming events by country</strong> - the top ten countries, taken from the venue's country, and hidden when no upcoming event has a venue country set</li>
            <li><strong class="text-gray-900 dark:text-white">Growth trends</strong> - users, schedules and events over the selected range, grouped by day, week or month depending on its length</li>
            <li><strong class="text-gray-900 dark:text-white">Recent signups</strong> - the ten newest accounts with the source they arrived from</li>
            <li><strong class="text-gray-900 dark:text-white">Recent schedules and recent events</strong> - the twenty newest of each; private events are left out of the event list</li>
            <li><strong class="text-gray-900 dark:text-white">Signups by method</strong> - email, Google, and hybrid (an account that has both a password and Google connected)</li>
            <li><strong class="text-gray-900 dark:text-white">Custom domains</strong> - every schedule with a custom domain, then how many direct-mode domains are active and how many are still pending</li>
            <li><strong class="text-gray-900 dark:text-white">Queue health</strong> - pending and failed job counts, with the failed figure turning red as soon as it is not zero. These are read-only tiles; use the <a href="#system-queue" class="doc-link">Queue</a> page to act on them.</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--dashboard" alt="Admin dashboard showing key metrics and recent activity" />

        <h3 class="doc-subheading">Needs attention</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Above the metrics sits a <strong class="text-gray-900 dark:text-white">Needs attention</strong> panel that collects every queue in the admin panel that is waiting on a person into one list, each row linking straight to the page where you deal with it. It is only rendered when there is something in it, so a dashboard without it means nothing needs you.</p>
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
                        <td>Failed jobs</td>
                        <td>Rows in the failed job table. Links to Queue.</td>
                        <td>Every install</td>
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
                        <td>Unverified schedules</td>
                        <td>A claimed schedule that has verified neither an email address nor a phone number.</td>
                        <td>Hosted</td>
                    </tr>
                    <tr>
                        <td>Referrals not converted yet</td>
                        <td>A referral whose invited user has not subscribed.</td>
                        <td>Hosted</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The same counts drive the badges on the Insights, Manage and System menus and on the items inside them. A group's badge takes the colour of the most serious row it contains, so a failed queue is not softened by sitting next to a routine unverified schedule. Each count reuses the query the destination page runs, so a badge and the page it links to cannot disagree.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">A plain selfhost sees fewer rows</div>
            <p>Only rows that can apply to your install are counted. With <code class="doc-inline-code">IS_HOSTED=false</code> the domain, support, unverified-schedule and referral rows always read zero, and the federation and translation-review rows only ever appear on eventschedule.com itself.</p>
        </div>
    </section>

    <!-- Insights: Users -->
    <section id="insights-users" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0Zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0Z" />
            </svg>
            Users (Insights)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Users page is an aggregate report about how people arrive and whether they get their first event published. It is not a user directory: there is no search box, no per-user row to open, and no way to edit an account from here. Only confirmed accounts are counted, and the demo account is excluded.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Onboarding funnel</strong> - visited the site, viewed the sign-up page, created an account, reached the schedule step, saved a schedule, reached the event step, saved an event. Highlighted above it are the signup-to-first-event rate with its change against the previous period, the biggest single drop between stages, and the visitor-to-first-event rate.</li>
            <li><strong class="text-gray-900 dark:text-white">Funnel over time</strong> - the same conversion rates per day, week or month. The most recent period is marked as still in progress, because its accounts have not had time to finish onboarding.</li>
            <li><strong class="text-gray-900 dark:text-white">Totals</strong> - total users, active users in the last 7 and 30 days, and newsletter subscribers with the number who unsubscribed.</li>
            <li><strong class="text-gray-900 dark:text-white">Signup method</strong> - email, Google, and hybrid, both all time and for the selected period.</li>
            <li><strong class="text-gray-900 dark:text-white">Attribution</strong> - top UTM sources for the period, top campaigns and sources all time, top referring domains, and how many signups arrived with no UTM data at all.</li>
            <li><strong class="text-gray-900 dark:text-white">Onboarding progress</strong> - recent accounts with how far each one got.</li>
            <li><strong class="text-gray-900 dark:text-white">Recent signups</strong> - twenty per page, with the sign-up intent, every UTM field, the referrer and the landing page.</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--users" alt="Admin users list with search and user details" />

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">The first funnel stage is blank on a selfhost</div>
            <p>The two anonymous stages come from marketing-site traffic, which is only recorded on eventschedule.com. On any other installation the funnel notes that site traffic is not tracked and starts at "Created account", and the visitor-to-first-event rate is left empty.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300">To act on a single account or schedule, use <a href="#manage-plans" class="doc-link">Manage &gt; Schedules</a>, which is where the search, filters, plan editing and manual verification live.</p>
    </section>

    <!-- Insights: Revenue -->
    <section id="insights-revenue" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            Revenue (Insights)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Revenue page reports on ticket sales across every schedule, plus subscription health where the install sells plans. Amounts are the payment amounts recorded on each sale.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Total revenue</strong> and <strong class="text-gray-900 dark:text-white">total sales</strong> - all time, each with the figure for the selected period underneath</li>
            <li><strong class="text-gray-900 dark:text-white">Refund rate</strong> - refunded sales as a share of paid plus refunded, turning red above 5 percent</li>
            <li><strong class="text-gray-900 dark:text-white">Pending revenue</strong> - the value of the sales still marked unpaid, with how many there are</li>
            <li><strong class="text-gray-900 dark:text-white">Boost markup revenue</strong> - your all-time margin on boost spend, with the figure for the period underneath. This tile sums charges only; the equivalent figure on the <a href="#manage-boost" class="doc-link">Boost</a> page is net of refunds, so the two do not have to match.</li>
            <li><strong class="text-gray-900 dark:text-white">Subscription health</strong> - active, trialing, canceled and past-due subscriptions, schedules on trial, how many converted, and expired trials with no subscription. This whole panel is only rendered when <code class="doc-inline-code">IS_HOSTED=true</code>.</li>
            <li><strong class="text-gray-900 dark:text-white">Revenue trend</strong> - a chart over the selected range</li>
            <li><strong class="text-gray-900 dark:text-white">Recent sales</strong> - the fifty most recent, excluding demo schedules</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--revenue" alt="Admin revenue dashboard with sales charts" />

        <h3 class="doc-subheading">Amount mismatches</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">When the amount a payment provider reports does not match the amount the sale expected, the sale is parked as a mismatch rather than being treated as paid. Those sales, and any boost campaign in the same state, are listed in an amber panel on this page, which is only rendered when something is in it, with two actions each:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Approve</strong> - accept it and mark it paid</li>
            <li><strong class="text-gray-900 dark:text-white">Refund</strong> - return the money through Stripe</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300">Both actions are recorded in the audit log, and the row disappears from Needs attention once the queue is empty.</p>
    </section>

    <!-- Insights: Analytics -->
    <section id="insights-analytics" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Analytics (Insights)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Analytics page rolls up the same daily page-view data that each schedule sees on its own Analytics tab, across every non-demo schedule.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Device breakdown</strong> - total page views for the period split into desktop, mobile and tablet. Views whose device could not be determined are counted in the total only.</li>
            <li><strong class="text-gray-900 dark:text-white">Traffic sources</strong> - direct, search, social, email, newsletter and other</li>
            <li><strong class="text-gray-900 dark:text-white">Feature adoption</strong> - six bars showing how many verified schedules use Google Calendar sync, sell through Stripe, have a custom domain, use custom CSS, have sent a newsletter, or have run a boost campaign, each as a count and a share of every verified non-demo schedule. The <strong class="text-gray-900 dark:text-white">Stripe Payments</strong> bar counts schedules with an event actually priced in Stripe, not schedules that merely connected an account.</li>
            <li><strong class="text-gray-900 dark:text-white">Stripe funnel</strong> - the three stages that bar skips past: connected an account, finished onboarding, and priced an event in Stripe</li>
            <li><strong class="text-gray-900 dark:text-white">Top schedules by events</strong> - the ten schedules with the most events</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--analytics" alt="Admin analytics dashboard with traffic charts" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What is not here</div>
            <p>Page views are counted per day and per device, not per visitor, so there is no unique-visitor figure and no per-visitor location. The nearest thing to a geographic view is <strong class="text-gray-900 dark:text-white">Upcoming events by country</strong> on the dashboard, which is based on the venue's country rather than the visitor's.</p>
        </div>
    </section>

    <!-- Insights: Usage -->
    <section id="insights-usage" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Usage (Insights)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Usage page counts calls to the services your install depends on, so you can see what is consuming your API quotas and mail allowance. Every operation is tallied per day and per schedule as it happens.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Seven categories are summarised, each with today's total, the total for the selected period, the average per day, and the configured daily limit:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Emails</strong>, <strong class="text-gray-900 dark:text-white">AI / Gemini</strong>, <strong class="text-gray-900 dark:text-white">Google Calendar</strong>, <strong class="text-gray-900 dark:text-white">Stripe</strong>, <strong class="text-gray-900 dark:text-white">Invoice Ninja</strong>, <strong class="text-gray-900 dark:text-white">CalDAV</strong> and <strong class="text-gray-900 dark:text-white">YouTube</strong></li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The daily limits come from <code class="doc-inline-code">config/usage.php</code> and its environment variables, not from this page. YouTube has no limit. When today's total for a category passes its limit, a red anomaly banner appears at the top of the page.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Below the summaries:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Operation breakdown</strong> - every individual operation, with today, the period total and the daily average</li>
            <li><strong class="text-gray-900 dark:text-white">Top schedules by usage</strong> - the twenty heaviest schedules, split by category</li>
            <li><strong class="text-gray-900 dark:text-white">Top newsletter senders</strong> - who is sending the most newsletter email, with their plan and whether they send through their own SMTP server rather than yours</li>
            <li><strong class="text-gray-900 dark:text-white">Stuck translation records</strong> - up to twenty each of the schedules, events, event parts and event listings that have been attempted at least three times (the threshold is <code class="doc-inline-code">USAGE_STUCK_THRESHOLD</code>) and still have no translation. Each row has a <strong class="text-gray-900 dark:text-white">Retry</strong> link that clears the attempt counter so the next scheduled run picks it up again.</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--usage" alt="Admin usage dashboard showing feature adoption" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Not the same as feature adoption</div>
            <p>This page measures external calls made in a window. How many schedules have <em>turned on</em> a feature is on the <a href="#insights-analytics" class="doc-link">Analytics</a> page instead.</p>
        </div>
    </section>

    <!-- Manage: Boost -->
    <section id="manage-boost" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
            Boost (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Boost page is where you oversee every paid promotion bought on the platform, whether it runs as a Meta ad or as a promotion served on your own pages.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Summary</strong> - total and active campaigns, markup revenue net of refunds, ad spend that left for Meta, refunds, and average click-through rate, cost per click and cost per thousand impressions</li>
            <li><strong class="text-gray-900 dark:text-white">Rejection rate</strong> - rejected campaigns as a share of those with a settled outcome, turning red above 20 percent</li>
            <li><strong class="text-gray-900 dark:text-white">Promotions awaiting review</strong> - up to fifty paid on-network promotions, oldest first, each showing the creative, the schedule, the buyer, the budget and the pricing model, with <strong class="text-gray-900 dark:text-white">Approve</strong> and <strong class="text-gray-900 dark:text-white">Reject</strong> on each and an optional reason to send with a rejection. Rejecting refunds the advertiser in full, back to their boost credit if that is how they paid and through Stripe otherwise, and emails and pushes the outcome to them. This panel only appears when the promotions network is enabled and something is waiting.</li>
            <li><strong class="text-gray-900 dark:text-white">Alerts</strong> - campaigns stuck awaiting payment, campaigns that failed, and ads Meta disapproved, in one red panel</li>
            <li><strong class="text-gray-900 dark:text-white">Status distribution</strong> and <strong class="text-gray-900 dark:text-white">top boosters</strong> - the ten schedules with the largest total budget</li>
            <li><strong class="text-gray-900 dark:text-white">Revenue trend</strong> - ad spend against markup over the selected range</li>
        </ul>

        <h3 class="doc-subheading">Granting credit and capping budgets</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Below the revenue trend, two forms act on a single schedule, identified by its subdomain (the field autocompletes as you type):</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Grant Boost Credit</strong> - add up to 1,000 in boost credit to a schedule, which it spends before its card is charged. Schedules holding a balance are listed underneath.</li>
            <li><strong class="text-gray-900 dark:text-white">Set Spending Limit</strong> - raise or lower the maximum budget that schedule may put on a single campaign. Schedules with a custom limit are listed underneath; when none has one, the panel names the default that applies to everyone else instead.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Both are written to the audit log. Two tables close the page:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Campaigns</strong> - twenty per page, newest first, with the campaign name, buyer, event, schedule, status, budget, spend, impressions, clicks and creation date. A single dropdown filters by status: active, paused, completed, cancelled, failed, pending payment or rejected.</li>
            <li><strong class="text-gray-900 dark:text-white">Recent Billing Records</strong> - the thirty most recent charges and refunds behind those numbers, each with its amount, markup, status and notes</li>
        </ul>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Nothing to manage until Boost is configured</div>
            <p>The page is always in the menu, but Meta campaigns can only exist once the Meta app, ad account and access token are configured. See the <a href="{{ route('marketing.docs.selfhost.boost') }}" class="doc-link">Boost Setup</a> guide. On-network promotions are configured separately, in the Monetization card on the <a href="#system-settings" class="doc-link">Settings</a> page.</p>
        </div>
        <x-doc-screenshot id="selfhost-admin--boost" alt="Admin boost management page" />
    </section>

    <!-- Manage: Schedules and plans (hosted only) -->
    <section id="manage-plans" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
            </svg>
            Schedules and plans (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This is the <strong class="text-gray-900 dark:text-white">Schedules</strong> item in the Manage menu, at <code class="doc-inline-code">/admin/schedules</code>; the older <code class="doc-inline-code">/admin/plans</code> address redirects to it. It is the one place in the admin panel where you change something about an individual schedule.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Plan counts</strong> - how many verified, non-demo schedules resolve to Free, Pro and Enterprise, plus how many pay through Stripe, how many were granted a plan by hand, how many are on trial, and how many expire in the next 30 days</li>
            <li><strong class="text-gray-900 dark:text-white">Search</strong> - by schedule name, subdomain or email address</li>
            <li><strong class="text-gray-900 dark:text-white">Filters</strong> - plan type, status (active, expired or trial), source (Stripe, manual or trial) and verification (verified or unverified)</li>
            <li><strong class="text-gray-900 dark:text-white">Listing</strong> - twenty schedules per page, newest first. Demo schedules are left out, but unverified ones are listed even though the counts above exclude them.</li>
        </ul>

        <h3 class="doc-subheading">Editing one schedule</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Opening a schedule shows a read-only <strong class="text-gray-900 dark:text-white">Current Subscription Status</strong> panel (status, Stripe customer, trial end and whether a subscription is active) and gives you three actions, and nothing else:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Assign a plan</strong> - set <strong class="text-gray-900 dark:text-white">Plan Type</strong> to Free, Pro or Enterprise, set <strong class="text-gray-900 dark:text-white">Plan Term</strong> to monthly or yearly, and set <strong class="text-gray-900 dark:text-white">Plan Expires</strong>. The expiry field has <strong class="text-gray-900 dark:text-white">+30 days</strong>, <strong class="text-gray-900 dark:text-white">+90 days</strong>, <strong class="text-gray-900 dark:text-white">+1 year</strong> and <strong class="text-gray-900 dark:text-white">Clear</strong> shortcuts. A paid plan granted this way is tagged as an admin grant, which is what keeps the small Event Schedule credit on that schedule's public pages. Setting it back to Free, or editing a schedule that pays through Stripe, clears that tag.</li>
            <li><strong class="text-gray-900 dark:text-white">Mark Email as Verified</strong> - mark the schedule's email address as verified without the owner clicking the link.</li>
            <li><strong class="text-gray-900 dark:text-white">Mark Phone as Verified</strong> - the same for a phone number.</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">All three are recorded in the audit log with the values before and after.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Hosted installs only</div>
            <p>This page only exists when <code class="doc-inline-code">IS_HOSTED=true</code>. A selfhosted install resolves every schedule to the Enterprise feature set, so there is no plan to assign and nothing to gate. Prices, terms and the features in each tier come from your Stripe configuration and the application itself; they cannot be edited from the admin panel.</p>
        </div>
    </section>

    <!-- Manage: Domains (hosted only) -->
    <section id="manage-domains" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            Domains (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Domains page lists every schedule that has connected a custom domain, in either mode: <strong class="text-gray-900 dark:text-white">Direct</strong>, where the domain serves the schedule itself, or <strong class="text-gray-900 dark:text-white">Redirect</strong>, where it forwards to the subdomain.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Totals</strong> - all custom domains, how many are direct, and how many of those are active or pending</li>
            <li><strong class="text-gray-900 dark:text-white">Search and filters</strong> - by schedule, subdomain, domain or hostname, and by mode and status (pending, active or failed)</li>
            <li><strong class="text-gray-900 dark:text-white">Status columns</strong> - the status Event Schedule recorded, alongside the live status read back from DigitalOcean when the DigitalOcean API is configured</li>
            <li><strong class="text-gray-900 dark:text-white">Listing</strong> - twenty domains per page, newest first</li>
        </ul>

        <h3 class="doc-subheading">Actions</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Re-provision</strong> - removes the hostname from the hosting platform and adds it again, which restarts certificate issuing, and returns the domain to Pending. Direct-mode domains only, and only when the DigitalOcean API is configured.</li>
            <li><strong class="text-gray-900 dark:text-white">Remove</strong> - clears the domain from the schedule and, for a direct-mode domain, removes the hostname from the hosting platform. The schedule falls back to its subdomain.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Certificates themselves are issued by the hosting platform, not by Event Schedule. If a domain stays pending, the usual cause is DNS that does not yet point at your install.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Hosted installs only</div>
            <p>This page only exists when <code class="doc-inline-code">IS_HOSTED=true</code>. A single-tenant selfhosted install is already served from your own domain and has nothing to map.</p>
        </div>
    </section>

    <!-- Manage: Newsletters -->
    <section id="manage-newsletters" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
            Newsletters (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">These are platform newsletters to the people who have registered on your install, which is a different thing from the newsletters a schedule owner sends to their own followers. Only admins can create them, and they never go to a schedule's followers.</p>

        <h3 class="doc-subheading">Composing and sending</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Create</strong> - start from scratch or from a saved template, give it a subject, and build the body from ten block types: heading, text, button, image, video, divider, spacer, social links, quote and offer.</li>
            <li><strong class="text-gray-900 dark:text-white">Style it</strong> - pick one of five layouts (Modern, Classic, Minimal, Bold or Compact) and set the background, accent and text colours, the font, the button shape and the footer text.</li>
            <li><strong class="text-gray-900 dark:text-white">Choose the audience</strong> - select one or more segments. The recipient count for each is shown as you pick. Leave the selection empty and the newsletter goes to every confirmed account that has not opted out.</li>
            <li><strong class="text-gray-900 dark:text-white">Check it</strong> - preview the rendered email, and send a test to yourself.</li>
            <li><strong class="text-gray-900 dark:text-white">Send or schedule</strong> - send immediately, or schedule it for a future time in your own timezone. A scheduled newsletter can be cancelled while it is still waiting, which returns it to draft.</li>
        </ol>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">The events block is not available here</div>
            <p>A schedule owner's newsletter builder has extra blocks that pull from their schedule: events, profile image, header banner, sponsors and a poll. A platform newsletter has no schedule behind it, so those blocks are not offered. The <strong class="text-gray-900 dark:text-white">offer</strong> block is the reverse case: it is offered here and not to schedule owners.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A sent newsletter can be cloned into a new draft, and any newsletter can be saved as a reusable template from the <strong class="text-gray-900 dark:text-white">Templates</strong> button on the listing. The listing shows each one's subject, status, how many were sent, its open and click rates and when it was created; opening a sent newsletter shows the full statistics.</p>

        <h3 class="doc-subheading">Segments</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Segments are managed from the <strong class="text-gray-900 dark:text-white">Segments</strong> button on the newsletter listing. Five kinds are available:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">All Platform Users</strong> - every confirmed account</li>
            <li><strong class="text-gray-900 dark:text-white">Plan Tier</strong> - the owners and admins of schedules on Free, Pro or Enterprise</li>
            <li><strong class="text-gray-900 dark:text-white">Signup Date</strong> - accounts created between two dates</li>
            <li><strong class="text-gray-900 dark:text-white">Admins</strong> - other admins, useful for testing</li>
            <li><strong class="text-gray-900 dark:text-white">Manual</strong> - a list you add people to by hand</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Only the name of an existing segment can be edited; to change what a segment matches, create a new one. Every send drops anyone who has unsubscribed from platform newsletters or turned email off entirely, whichever segment produced them, so an opt-out is honoured even from a manual list. A segment cannot be deleted while a draft or scheduled newsletter still uses it.</p>
        <x-doc-screenshot id="selfhost-admin--newsletters" alt="Admin newsletters management page" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Large sends need a queue worker</div>
            <p>With the default <code class="doc-inline-code">QUEUE_CONNECTION=sync</code> the send runs inside the web request, so sending to more than 50 recipients is refused and you are asked to schedule it instead. Scheduled sends are picked up by the cron entry every minute. To send large newsletters immediately, switch to a real queue connection and keep <code class="doc-inline-code">php artisan queue:work</code> running.</p>
        </div>
    </section>

    <!-- Manage: Blog (hosted only) -->
    <section id="manage-blog" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
            </svg>
            Blog (Manage)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Blog section publishes posts to your public blog, with an RSS feed. Each post has:</p>
        <ul class="doc-list mb-6">
            <li>A title, a Markdown body, and an optional short excerpt</li>
            <li>Comma-separated tags</li>
            <li>A published state and a publish date, so a post can be written ahead of time</li>
            <li>A meta title and meta description for search results</li>
            <li>A header image chosen from the set that ships with the application</li>
            <li>An author name</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">If AI is configured, a <strong class="text-gray-900 dark:text-white">Generate</strong> action drafts a post from a topic you type in, which you then edit before publishing. See <a href="{{ route('marketing.docs.selfhost.ai') }}" class="doc-link">AI Setup</a> for the API key it needs.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Authoring is hosted only</div>
            <p>The Blog item only appears when <code class="doc-inline-code">IS_HOSTED=true</code>. A selfhosted install still serves the public blog routes at <code class="doc-inline-code">/blog</code> and <code class="doc-inline-code">/blog/feed</code>, but has no screen for writing posts, so they stay empty.</p>
        </div>
    </section>

    <!-- System: Audit Log -->
    <section id="system-audit-log" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
            </svg>
            Audit Log (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Audit Log is a chronological record of security-relevant and administrative actions. Each entry stores the time, the user, the action, the IP address, and a details field holding the values before and after the change.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Summary</strong> - total entries, entries today, failed sign-in attempts today, and how many distinct IP addresses were seen today</li>
            <li><strong class="text-gray-900 dark:text-white">Filter by category</strong> - admin, api, auth, boost, event, google_calendar, profile, sale, schedule, stripe, subscription and webhook</li>
            <li><strong class="text-gray-900 dark:text-white">Filter by date</strong> - a From and To range</li>
            <li><strong class="text-gray-900 dark:text-white">Search</strong> - matches the action name, the details and the IP address</li>
            <li><strong class="text-gray-900 dark:text-white">Sort</strong> - by time, user, action, IP address or details, in either direction</li>
            <li><strong class="text-gray-900 dark:text-white">Listing</strong> - fifty entries per page</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--audit-log" alt="Admin audit log showing platform activity" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Entries are pruned after 90 days</div>
            <p>A daily scheduled task deletes audit entries older than 90 days, so the table cannot grow without bound. If you need to keep them longer, export or replicate them yourself; the retention is set by the pruning command, not by a setting on this page.</p>
        </div>
    </section>

    <!-- System: Queue -->
    <section id="system-queue" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
            </svg>
            Queue (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Queue page reports on background jobs: calendar syncs, newsletter batches, graphics generation and anything else the application defers.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Health cards</strong> - pending jobs (with a per-queue breakdown underneath the number), failed jobs, job batches, and the age of the oldest pending job</li>
            <li><strong class="text-gray-900 dark:text-white">Warning banner</strong> - shown when anything has failed, or when the oldest pending job has waited more than an hour, which normally means no worker is running</li>
            <li><strong class="text-gray-900 dark:text-white">Pending Jobs by Class</strong> - which job type is backing up</li>
            <li><strong class="text-gray-900 dark:text-white">Failed jobs</strong> - the hundred most recent, each with its class, queue, failure time and exception; retry or delete them individually</li>
            <li><strong class="text-gray-900 dark:text-white">Pending jobs</strong> - the hundred most recent, with attempt count and when each becomes available</li>
            <li><strong class="text-gray-900 dark:text-white">Job batches</strong> - the fifty most recent, with progress and failure counts</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Three bulk actions sit above the tables, each behind a confirmation prompt. They are only rendered when they have something to act on:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Retry All Failed</strong> and <strong class="text-gray-900 dark:text-white">Clear All Failed</strong> - shown only while there is at least one failed job</li>
            <li><strong class="text-gray-900 dark:text-white">Flush Pending</strong> - shown only while there is at least one pending job. It truncates the job table, so the work is discarded permanently; use it only to clear a backlog you know is stale.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">Failed jobs are also retried automatically: the <a href="{{ route('marketing.docs.selfhost.installation') }}#cron" class="doc-link">cron entry</a> pushes them back onto the queue for you, so a job that failed because of a passing problem (an unreachable mail server, a rate-limited API) usually recovers without anyone touching this page. Each job gets five automatic retries spaced fifteen minutes apart. After that it is left alone rather than retried forever, so a job that can never succeed stops consuming a worker on every cron run - it simply stays in the table with the exception that explains it.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Nothing is set aside permanently. The count resets after a day, so a job left over from a long outage is picked up again tomorrow, and pressing <strong class="text-gray-900 dark:text-white">Retry</strong> clears it immediately - which is what to do once you have fixed whatever the exception was pointing at. A job whose exception says it refers to a record that no longer exists cannot be retried at all, because the record it needs is gone; delete it.</p>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Jobs need a worker, not the cron entry</div>
            <p>Queued jobs are run by a worker process (<code class="doc-inline-code">php artisan queue:work</code>), which is separate from the <a href="{{ route('marketing.docs.selfhost.installation') }}#cron" class="doc-link">cron entry</a> that runs scheduled commands. With the default <code class="doc-inline-code">QUEUE_CONNECTION=sync</code> nothing is queued at all: work happens inside the web request and this page stays empty, which is normal for a small install.</p>
        </div>
        <x-doc-screenshot id="selfhost-admin--queue" alt="Admin queue showing background job status" />
    </section>

    <!-- System: Logs -->
    <section id="system-logs" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
            </svg>
            Logs (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Logs page reads the application log at <code class="doc-inline-code">storage/logs/laravel.log</code> so you can diagnose problems without shell access. It parses only the last 5 MB of the file, which keeps a very large log from exhausting memory but also means older entries are not shown.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Level counts</strong> - how many entries there are at each severity: emergency, alert, critical, error, warning, notice, info and debug</li>
            <li><strong class="text-gray-900 dark:text-white">Repeated errors</strong> - entries at error level and above, grouped by their message with the variable parts collapsed, showing the number of occurrences and when it was first and last seen. A message only appears once it has been logged at least twice, so this is usually the fastest way to find the one thing going wrong repeatedly.</li>
            <li><strong class="text-gray-900 dark:text-white">Filter and search</strong> - narrow to a single level, and search the message text and the stack trace</li>
            <li><strong class="text-gray-900 dark:text-white">Entries</strong> - up to 200 shown at a time, newest first, each expandable to its full stack trace</li>
            <li><strong class="text-gray-900 dark:text-white">Download</strong> - fetch the whole log file, and <strong class="text-gray-900 dark:text-white">Clear</strong> to empty it after you have finished with it</li>
        </ul>
        <x-doc-screenshot id="selfhost-admin--logs" alt="Admin logs viewer showing application log entries" />

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Logs can contain personal data</div>
            <p>Stack traces and log messages may include email addresses and request details. Treat a downloaded log file as sensitive, and remember that clearing the file cannot be undone.</p>
        </div>
    </section>

    <!-- System: Settings -->
    <section id="system-settings" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Settings page holds the handful of settings that apply to the whole installation. It is built from separate cards, each with its own Save button, and a card is only rendered when it can do something on this install. Two of the five are gated on an <code class="doc-inline-code">.env</code> switch, so a plain selfhost usually sees the first three. None of these settings can be changed while the install is in demo mode.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Card</th>
                        <th>What it does</th>
                        <th>When it appears</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Header / Footer Code</td>
                        <td>Injects your own code into every public guest page</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td>Event Schedule network</td>
                        <td>Shares your public events with the eventschedule.com listings</td>
                        <td>Every install except eventschedule.com</td>
                    </tr>
                    <tr>
                        <td>Monetization</td>
                        <td>Google AdSense and the on-network promotions marketplace</td>
                        <td><code class="doc-inline-code">ADS_ENABLED=true</code> on a multi-tenant hosted install</td>
                    </tr>
                    <tr>
                        <td>Platform currency</td>
                        <td>The currency this installation shows its own prices in</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td>Accommodation affiliate</td>
                        <td>A fallback affiliate ID for the nearby-lodging map</td>
                        <td><code class="doc-inline-code">STAY22_ENABLED=true</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Header and footer code</h3>
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
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Content Security Policy</div>
            <p>Google Tag Manager and Google Analytics are permitted by the built-in Content Security Policy and work out of the box. Scripts that load from other external domains may be blocked - add the domain to the <code class="doc-inline-code">script-src</code> directive in <code class="doc-inline-code">app/Http/Middleware/SecurityHeaders.php</code> if needed.</p>
        </div>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Privacy &amp; consent</div>
            <p>Injected analytics are not automatically gated by the cookie-consent banner. You are responsible for configuring consent (for example, Google consent mode) to comply with the privacy regulations in your region.</p>
        </div>

        <h3 class="doc-subheading">Cookie consent banner</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The banner appears only when something on the page actually needs consent: Google Analytics (<code class="doc-inline-code">ANALYTICS_ID</code>), advertising (<code class="doc-inline-code">ADS_ENABLED</code>) or the accommodation map (<code class="doc-inline-code">STAY22_ENABLED</code>). A plain install that has none of those shows no banner at all, and sets no cookie that is not needed to make the site work.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">That includes the three attribution cookies, <code class="doc-inline-code">utm_params</code>, <code class="doc-inline-code">utm_referrer_url</code> and <code class="doc-inline-code">utm_landing_page</code>, which remember for 30 days which campaign or referring site brought a visitor in, so a later signup or ticket sale can be credited to it. They are written only after a visitor clicks Allow. Without the banner they are never written, and attribution lasts for the current session only, which is enough for a visitor who buys on the same visit.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Set <code class="doc-inline-code">COOKIE_CONSENT_BANNER=true</code> in <code class="doc-inline-code">.env</code> to show the banner regardless, which is what you want if you run marketing campaigns and need attribution to survive across visits.</p>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Your own visit statistics need no consent</div>
            <p>The built-in analytics store daily totals only: views per device type, referrer, country and campaign tag. There is no per-visitor record. IP address and user-agent are hashed with your <code class="doc-inline-code">APP_KEY</code> and a salt that rotates daily, purely to deduplicate and filter bots, and that hash lives in the cache until midnight rather than in the database. Nothing is read from or written to the visitor's device, so no banner is required for it.</p>
        </div>

        <h3 class="doc-subheading">Event Schedule network</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">One toggle opts your installation into sharing its public events with the eventschedule.com listings, and a contact email lets the moderators reach you. The card also shows the connection state (not connected, pending review, approved or suspended), when the last sync ran, and a preview of exactly which events would be sent, so nothing leaves your install unseen. Two counts under the preview explain why it may be shorter than you expect: schedules that have not verified an email address or phone number, and schedules that have not yet decided whether to take part. Turning the toggle off withdraws the listings again. See <a href="{{ route('marketing.docs.selfhost.federation') }}" class="doc-link">Federation</a> for the full picture.</p>

        <h3 class="doc-subheading">Monetization</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This card configures advertising on free schedules: whether to show AdSense, the publisher and ad slot IDs, whether personalized ads are allowed, whether to run your own promotions marketplace, whether promotions take priority over AdSense, and the prices you charge per thousand impressions and per click. It stays hidden unless <code class="doc-inline-code">ADS_ENABLED=true</code> and the install is a multi-tenant hosted platform, because a selfhosted install resolves every schedule to Enterprise and so has no free tier for an ad to appear on.</p>

        <h3 class="doc-subheading">Platform currency</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The currency this installation quotes <em>its own</em> prices in. It decides the symbol printed beside every plan price and upgrade prompt, and it is the currency a new event falls back to when its schedule has no country set. Pick from the same list the ticket and gift-card pickers offer. It defaults to <code class="doc-inline-code">PLATFORM_CURRENCY</code> from <code class="doc-inline-code">.env</code>, and to US dollars when that is unset.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">It does not touch money that already exists. A ticket is always shown in the currency it was priced in, a past sale in the currency it was taken in, and a schedule that has set a country keeps the currency that country implies. Changing this never rewrites a stored amount.</p>
        <div class="doc-callout doc-callout-warning mb-6">
            <div class="doc-callout-title">A label, not a price</div>
            <p>On a platform that sells plans, this decides what the interface prints, not what a customer is charged. The charge comes from the Stripe price your <code class="doc-inline-code">STRIPE_PRICE_*</code> IDs point at, exactly as with the <code class="doc-inline-code">*_AMOUNT</code> variables. Set the currency here, the amounts in <code class="doc-inline-code">.env</code> and the prices in Stripe, and keep all three in step, or your site will advertise one figure and bill another.</p>
        </div>

        <h3 class="doc-subheading">Accommodation affiliate</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Setting <code class="doc-inline-code">STAY22_ENABLED=true</code> lets schedules show a map of lodging near their venues and earn affiliate commission. The card holds a single field, the <strong class="text-gray-900 dark:text-white">Fallback Stay22 affiliate ID</strong>, used only for schedules that enabled the map without supplying an ID of their own, and never on a schedule's own custom domain. See <a href="{{ route('marketing.docs.saas.monetization') }}#accommodation" class="doc-link">Accommodation affiliate</a> for the disclosure obligations it places on you.</p>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">The master switch is env only</div>
            <p>The Content Security Policy is rebuilt from <code class="doc-inline-code">STAY22_ENABLED</code> on every request, so it can only be set in <code class="doc-inline-code">.env</code> and never from the admin panel. If you cache your configuration, re-run <code class="doc-inline-code">php artisan config:cache</code> after changing it, or the affiliate ID will save while the map stays blocked.</p>
        </div>
    </section>

    <!-- System: Translations -->
    <section id="system-translations" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
            </svg>
            Translations (System)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The Translations page lets you review and customize every piece of text the app shows, in any of the supported languages. Fix an awkward translation, adapt wording to your industry (for example rename "ticket" to "registration" or "booking"), or fill in missing translations - all without editing any files.</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Pick a language and file</strong> - <code class="doc-inline-code">messages</code> holds the app's UI strings; <code class="doc-inline-code">accessibility</code> and <code class="doc-inline-code">marketing</code> are smaller companion files. English is editable too, which is handy for renaming built-in terms.</li>
            <li><strong class="text-gray-900 dark:text-white">Search and filter</strong> - find strings by key or text, and filter to only your customized keys or to translations that are missing in the selected language.</li>
            <li><strong class="text-gray-900 dark:text-white">Edit and save</strong> - type your text next to the original and save. Changes apply immediately, are stored in the database, and survive app updates. A per-row revert restores the shipped translation at any time, and clearing a field is the same as reverting it.</li>
            <li><strong class="text-gray-900 dark:text-white">Copy as PHP</strong> - copies your customizations as ready-to-paste language-file lines, useful for moving them into another install or contributing a pull request.</li>
        </ul>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Placeholders and plurals</div>
            <p>Some strings contain placeholders such as <code class="doc-inline-code">:name</code> or plural forms separated by <code class="doc-inline-code">|</code>. Keep them in your version so dynamic values keep working - the editor warns you if one goes missing, but never blocks the save.</p>
        </div>
        <h3 class="doc-subheading">Sharing improvements with the community</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Many translation fixes are useful to every Event Schedule install. You can share yours with the community for review, and approved suggestions ship with future releases. Nothing is ever sent automatically: use the <strong class="text-gray-900 dark:text-white">Share</strong> button to pick exactly which changes to send, or enable the <strong class="text-gray-900 dark:text-white">auto-share</strong> toggle if you want saved changes submitted on their own. Keep auto-share off if your wording is specific to your business. Unshared changes are also counted on the dashboard's Needs attention list, so nothing sits forgotten.</p>
        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">What sharing sends</div>
            <p>Sharing sends the language, the file, the translation key, your suggested text and the shipped text it replaces, plus your app version and a random anonymous install identifier, to eventschedule.com. No URLs, email addresses, or other personal data are included.</p>
        </div>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Behind the scenes</div>
            <p>Customizations are stored in the database and published as override files under <code class="doc-inline-code">storage/app/lang</code>, or wherever <code class="doc-inline-code">LANG_OVERRIDES_PATH</code> points if you run several servers from a shared volume. Hand-made override files (the pre-existing <a href="{{ route('marketing.docs.selfhost.installation') }}#translations" class="doc-link">custom translations</a> approach) are adopted into the editor automatically. After restoring a database backup or cloning to a new server, run <code class="doc-inline-code">php artisan translations:publish</code> to rebuild the files.</p>
        </div>
    </section>
</x-docs-page>
