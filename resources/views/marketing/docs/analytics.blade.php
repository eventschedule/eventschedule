<x-docs-page
    key="analytics"
    description="Learn how to track views, devices, traffic sources, revenue, and check-ins with Event Schedule's built-in analytics dashboard."
    lede="Track how your audience discovers and interacts with your schedule. View trends, compare periods, and understand what drives engagement."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#filters">Filters</x-doc-nav-link>
        <x-doc-nav-group label="Web Analytics" href="#web-analytics">
            <x-doc-nav-link href="#web-stats">Stats Cards</x-doc-nav-link>
            <x-doc-nav-link href="#web-charts">Charts</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Revenue" href="#revenue">
            <x-doc-nav-link href="#revenue-stats">Stats Cards</x-doc-nav-link>
            <x-doc-nav-link href="#revenue-funnels">Funnels</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Check-ins" href="#checkins">
            <x-doc-nav-link href="#checkins-stats">Stats Cards</x-doc-nav-link>
            <x-doc-nav-link href="#checkins-charts">Charts</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#no-data">No Data State</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Analytics dashboard shows how your schedule pages are performing. Open it by clicking <strong>Analytics</strong> in the main navigation. The dashboard is built in, so there is nothing to install and no third-party tracking script to add.
        </p>

        <x-doc-screenshot id="analytics--dashboard" alt="Analytics dashboard" loading="eager" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The dashboard is organized into three tabs, which appear in this order:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Web Analytics</strong> - Page views over time, device breakdown, traffic sources, referrers, UTM parameters, visitor locations, social link clicks, and top events. This is the default tab.</li>
            <li><strong class="text-gray-900 dark:text-white">Revenue</strong> - Total revenue, conversion rate, revenue per view, promo code performance, boost and newsletter funnels, and top events by revenue.</li>
            <li><strong class="text-gray-900 dark:text-white">Check-Ins</strong> - Tickets sold, attendance rate, no-shows, arrival times, attendance by ticket type, and a per-event breakdown.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What each tab needs</div>
            <p>Web Analytics is available on every plan and starts collecting as soon as someone visits a schedule page. The Revenue tab fills in once you have a completed sale, a boost campaign or a newsletter send in the range, and selling tickets is free up to 25 paid tickets per calendar month per schedule (Pro removes that cap). The Check-Ins tab fills in once you have sold tickets for an event in the range, but its attendance figures only become meaningful once you scan tickets at the door. Scanning itself is free on every plan; the live check-in dashboard with its running count is the Pro half.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Counting is immediate, but every metric is stored as a daily total, so a visit always lands on the day it happened. Today's figures keep climbing until midnight.</p>
        </div>
    </section>

    <!-- Filters -->
    <section id="filters" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
            </svg>
            Filters
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The controls above the tabs decide which data is shown. Every filter is stored in the page URL, so you can bookmark a view or share it with a team member who has access to the same schedule.
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule selector</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose <strong>All Schedules</strong> or a single schedule. The dropdown only appears when you manage more than one schedule; if you manage exactly one, it is selected for you automatically. Switching schedules clears the event filter.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event selector</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Appears once a single schedule is selected, and narrows every tab to one event. It is searchable and lists published events that start in the last 30 days or later; drafts are never listed. On a curator schedule, only events the curator created are listed. Choose <strong>All events</strong> to clear it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Date range</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Last 7 days, Last 30 days, Last 90 days, This month, Last month, This year, or All time. The default is Last 30 days. All time reaches back ten years.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Daily / Weekly / Monthly</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Sets the grouping of the Views Over Time chart. These buttons only appear on the Web Analytics tab, and Daily is the default. Daily suits short ranges, weekly gives a balanced overview, and monthly is better for spotting long-term patterns.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">The date range means something different on each tab</div>
            <p>On Web Analytics it filters page views by the day of the visit. On Revenue it filters sales by the date the purchase was made. On Check-Ins it filters by the <em>event date</em> the ticket is for, not by the purchase or scan date, so a ticket bought in January for a March event lands in March.</p>
        </div>
    </section>

    <!-- Web Analytics -->
    <section id="web-analytics" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            Web Analytics <x-doc-badge plan="free" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Web Analytics tab is the default tab. It shows page view trends, device and traffic breakdowns, and your top-performing content. Views are counted on your public schedule and event pages and stored as daily totals.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What counts as a view</div>
            <p>Analytics is deliberately conservative, so its numbers are usually lower than a raw server log. A visit is <strong>not</strong> counted when:</p>
            <ul class="doc-list mb-0">
                <li>The visitor is a known bot, crawler, preview generator or automated tool.</li>
                <li>You or one of your team members is signed in to that schedule, or a site administrator is signed in.</li>
                <li>The page was loaded inside an <a href="{{ route('marketing.docs.sharing') }}#embed" class="doc-link">embedded calendar</a>.</li>
                <li>The same visitor has already had 10 views counted for that schedule that day. Views past the tenth are ignored until the count resets at midnight.</li>
            </ul>
        </div>

        <h3 id="web-stats" class="doc-subheading">Stats Cards</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The top of the tab shows summary cards. Which cards appear depends on the date range and the type of schedule you selected.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Card</th>
                        <th>What it shows</th>
                        <th>When it appears</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Total Views</span></td>
                        <td>All page views ever recorded, ignoring the date range</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Views in Period</span></td>
                        <td>Views inside the selected date range. On All time this card falls back to the current calendar month.</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Previous Period</span></td>
                        <td>Views in the equivalent range immediately before the one you selected</td>
                        <td>Every range except All time</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">vs Previous 30 Days</span></td>
                        <td>Percentage change against that previous period, green when up and red when down. The card label follows the range you picked, so it also reads vs Previous 7 Days, vs Last Month, vs Last Year and so on.</td>
                        <td>Every range except All time</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Appearance Views</span></td>
                        <td>Views your events picked up while appearing on someone else's schedule</td>
                        <td>Talent and venue schedules with at least one appearance view in range</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Use the comparison percentage to quickly identify whether your schedule is gaining or losing traction relative to the previous period. Because it compares equal-length windows, it is more useful than the raw view count when a range spans an unusually busy week.</p>
        </div>

        <h3 id="web-charts" class="doc-subheading">Charts</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Below the stats cards the tab lays out its charts in the order shown here. A chart is hidden entirely when it has no data for the selected filters, so an empty dashboard is normal on a new schedule.
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Views Over Time</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A line chart of page views grouped daily, weekly or monthly according to the period buttons. Hover a point to see the exact number. If you run <a href="{{ route('marketing.docs.boost') }}" class="doc-link">boost campaigns</a> or send <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletters</a>, extra dashed lines plot the views attributed to each of them against the same timeline, so you can see how much of a spike each one accounts for.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Device Breakdown</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The split between desktop, mobile and tablet visitors, based on the browser's user agent. Visits whose device could not be identified are grouped as unknown. Categories with no views are left out.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your ten most-viewed events in the selected period. Hidden when you have filtered down to a single event.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Traffic Sources</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Where your visitors come from, in eight categories: Direct, Search, Social, Email, Newsletter, Boost, Promo Code and Other. A colour key under the chart explains each one. Links carrying <code class="doc-inline-code">utm_source=boost</code>, <code class="doc-inline-code">utm_source=newsletter</code> or a <code class="doc-inline-code">promo</code> parameter are classified by that marker instead of by the referring site.</p>
            </div>
            <div id="web-geography" class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Visitor Locations</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your top ten countries by view count, resolved from the visitor's IP address. Use it to see whether your audience is local, regional or international. There is no city or region breakdown.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule Views</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Views broken down per schedule so you can compare them side by side. Shown only when you manage more than one schedule and have not filtered to a single event.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Referrers</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The ten domains that send you the most traffic. This is a domain-level list, not a list of individual pages. Visits referred by your own pages, whether from your schedule URL or your custom domain, are treated as Direct and never show up here.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top UTM Sources, Mediums and Campaigns</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Three separate charts, each listing the ten most common values of <code class="doc-inline-code">utm_source</code>, <code class="doc-inline-code">utm_medium</code> and <code class="doc-inline-code">utm_campaign</code> seen on incoming links. Tag the links you post yourself to tell your own channels apart. A chart is hidden when no link carried that parameter.</p>
            </div>
            <div id="web-social-clicks" class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Social Link Clicks</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Clicks on the social links configured in your <a href="{{ route('marketing.docs.creating_schedules') }}#videos-links" class="doc-link">schedule settings</a>, broken down by platform. It counts visitors leaving your schedule for Instagram, Facebook, TikTok and the rest, so it measures outbound interest rather than incoming traffic. The same bot filtering and daily per-visitor cap as page views applies.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Featured Talents &amp; Venues</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Which talents or venues appearing on your schedule drive the most views. Shown when a single schedule is selected and at least one talent or venue is attached to its events.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Schedules You Appeared On</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The external schedules that generate the most views for your events when you appear as a guest. Shown for talent and venue schedules only.</p>
            </div>
        </div>
    </section>

    <!-- Revenue -->
    <section id="revenue" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            Revenue <x-doc-badge plan="free" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Revenue tab tracks ticket sales performance: how much you earned, how well views convert to purchases, how your promo codes did, and what your boost and newsletter campaigns returned. It counts paid sales by the date of purchase.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling tickets</a> is free up to 25 paid tickets per calendar month per schedule, so the Revenue tab works on the Free plan; Pro removes the monthly cap. The Boost Funnel below is the one part of this tab that needs Pro, because boost campaigns are a Pro feature.</p>
        </div>

        <h3 id="revenue-stats" class="doc-subheading">Stats Cards</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Three cards summarise the period. They appear only once there is at least one paid sale in the selected range.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Card</th>
                        <th>What it shows</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Total Revenue</span></td>
                        <td>Ticket revenue taken during the period. If your sales span more than one currency, each currency is listed on its own line rather than added together.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Conversion Rate</span></td>
                        <td>Completed sales as a percentage of event page views in the same period. Free registrations and zero-price tickets are completed sales, so they raise this rate without raising revenue.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Revenue per View</span></td>
                        <td>Average revenue generated per event page view. Shows a dash when your sales span more than one currency, because averaging across currencies would be meaningless.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="revenue-funnels" class="doc-subheading">Funnels and Charts</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Below the cards, each panel appears only when it has data for the selected filters.
        </p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Promo Codes</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Two summary figures - promo sales as a share of all sales, and the total discount given - followed by a per-code table listing each <a href="{{ route('marketing.docs.tickets') }}#promo-codes" class="doc-link">promo code</a>, its discount, how many sales used it, and how much it cost you in discounts.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Boost Funnel <x-doc-badge plan="pro" /></h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The full path from ad impressions to clicks, page views and sales, with spend, click-through rate, cost per click, cost per view, cost per sale and a return-on-ad-spend figure. A table underneath lists each <a href="{{ route('marketing.docs.boost') }}" class="doc-link">boost campaign</a> running in the period with a link to its details.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Newsletter Performance</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The path from emails sent to opens, clicks, page views and sales, with open and click rates. A table underneath lists each <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletter</a> sent in the period with its subject, send date and per-newsletter open and click counts.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Events by Revenue</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your ten highest-earning events in the period. It renders as a bar chart in the usual single-currency case, and as a table when your sales span more than one currency. Events that took no money are left out, and the whole panel is hidden when you have filtered down to a single event.</p>
            </div>
        </div>
    </section>

    <!-- Check-ins -->
    <section id="checkins" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
            </svg>
            Check-Ins <x-doc-badge plan="free" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Check-Ins tab turns door scans into attendance analytics: how many ticket holders actually showed up, when they arrived, and which events and ticket types had the best turnout. It reads completed sales only, and it groups them by the <strong>event date</strong> the ticket is for.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>This tab has data only for events where tickets were scanned with the <a href="{{ route('marketing.docs.tickets') }}#check-in" class="doc-link">check-in feature</a>, which is free on every plan. Without a scan a ticket counts as sold but never as attended, so an unscanned event reads as 100% no-shows rather than as missing data.</p>
        </div>

        <h3 id="checkins-stats" class="doc-subheading">Stats Cards</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Card</th>
                        <th>What it shows</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Tickets Sold</span></td>
                        <td>Tickets from completed sales for events dated inside the selected range, including free registrations</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Checked In</span></td>
                        <td>How many of those tickets were scanned at the door</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Attendance Rate</span></td>
                        <td>Checked in as a percentage of tickets sold</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">No-Shows</span></td>
                        <td>The remaining percentage: 100% minus the attendance rate</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="checkins-charts" class="doc-subheading">Charts</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Arrival Times</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A bar chart of check-ins by hour of the day, from 12 AM to 11 PM, in the schedule's own timezone. Use it to see when the queue actually forms and to staff the door accordingly. It is not measured relative to each event's start time, so it is most useful when your events start at a consistent hour.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Attendance Rate by ticket type</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Check-in rates broken down by ticket type, so you can see which types turn up. Shown only when the period contains more than one ticket type.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A table of per-event check-in data - event name, date, sold, checked in and attendance rate - sorted with the most recent event date first.</p>
            </div>
        </div>
    </section>

    <!-- No Data State -->
    <section id="no-data" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            No Data State
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Each tab shows its own empty state when it has nothing to display: "No analytics data available yet", "No revenue data available yet" or "No check-in data". Common causes:
        </p>
        <ul class="doc-list mb-6">
            <li>The schedule is new and has not been visited yet.</li>
            <li>The selected date range contains no recorded activity. All time is the quickest way to rule this out.</li>
            <li>You are filtering by a schedule or an event with no traffic.</li>
            <li>You have only been checking the page yourself while signed in. Your own visits, and those of your team members and site administrators, are never counted.</li>
            <li>Your only traffic so far came through an embedded calendar, which is not counted, or from bots, which are filtered out.</li>
            <li>On the Revenue tab, no sale was completed in the range. If you see a conversion rate but no revenue, your sales were free registrations or zero-price tickets.</li>
            <li>On the Check-Ins tab, no tickets were scanned, or the events you scanned fall outside the selected range - remember this tab filters by event date.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            If the dashboard still looks empty, work through these in order:
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Set the date range to <strong>All time</strong> and clear the schedule and event filters.</li>
            <li>Open your public schedule page in a private or logged-out browser window and reload it, then check again the following day - views are stored as daily totals.</li>
            <li>Confirm the events you expect traffic on are published rather than drafts.</li>
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Share your schedule link</a> so real visitors start arriving.</li>
        </ol>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Sharing Your Schedule</a> - Increase traffic to your schedule</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - Set up ticketing to track conversions and revenue</li>
            <li><a href="{{ route('marketing.docs.tickets') }}#check-in" class="doc-link">Check-In</a> - Scan tickets at the door so the Check-Ins tab has data</li>
            <li><a href="{{ route('marketing.docs.newsletters') }}#analytics" class="doc-link">Newsletter Analytics</a> - Track open rates, clicks, and engagement for email campaigns</li>
            <li><a href="{{ route('marketing.docs.boost') }}" class="doc-link">Boosting Events</a> - Run paid ad campaigns that feed the boost funnel</li>
            <li><a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">Event Graphics</a> - Create shareable images to boost visibility</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Use Event Schedule Analytics",
            "description": "Learn how to track views, devices, traffic sources, revenue, and check-ins with Event Schedule's built-in analytics dashboard.",
            "totalTime": "PT5M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Access the Analytics Dashboard",
                    "text": "Click Analytics in the main navigation to open the analytics dashboard and view your schedule performance.",
                    "url": "{{ url(route('marketing.docs.analytics')) }}#overview"
                },
                {
                    "@type": "HowToStep",
                    "name": "Apply Filters",
                    "text": "Use the schedule selector, event selector and date range dropdown to filter the data displayed.",
                    "url": "{{ url(route('marketing.docs.analytics')) }}#filters"
                },
                {
                    "@type": "HowToStep",
                    "name": "Review Web Analytics",
                    "text": "Check page views, device breakdown, traffic sources, visitor locations and top events on the Web Analytics tab.",
                    "url": "{{ url(route('marketing.docs.analytics')) }}#web-analytics"
                },
                {
                    "@type": "HowToStep",
                    "name": "Track Revenue",
                    "text": "Switch to the Revenue tab to see total revenue, conversion rate, promo code stats, and boost and newsletter funnels.",
                    "url": "{{ url(route('marketing.docs.analytics')) }}#revenue"
                },
                {
                    "@type": "HowToStep",
                    "name": "Monitor Check-ins",
                    "text": "Use the Check-Ins tab to track attendance rates, arrival times, and per-event check-in data.",
                    "url": "{{ url(route('marketing.docs.analytics')) }}#checkins"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
