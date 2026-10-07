<x-docs-page
    key="analytics"
    title="Analytics Guide: Views, Revenue, Check-Ins - Event Schedule"
    description="Read your schedule's built-in analytics: views, traffic sources, short-link clicks, broken links, revenue and check-ins, with no tracking script to add."
    lede="See how people find your schedule, what they open and click, what they buy, and who turns up at the door."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#filters">Filters</x-doc-nav-link>
        <x-doc-nav-group label="Web Analytics" href="#web-analytics">
            <x-doc-nav-link href="#web-counting">What Counts as a View</x-doc-nav-link>
            <x-doc-nav-link href="#web-stats">Stats Cards</x-doc-nav-link>
            <x-doc-nav-link href="#web-charts">Charts</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-group label="Realtime" href="#realtime">
            <x-doc-nav-link href="#realtime-traffic">Live Traffic</x-doc-nav-link>
            <x-doc-nav-link href="#realtime-door">At the Door Today</x-doc-nav-link>
            <x-doc-nav-link href="#realtime-activity">Activity</x-doc-nav-link>
            <x-doc-nav-link href="#realtime-privacy">Who Is Counted</x-doc-nav-link>
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
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Analytics shows how your schedule pages are performing. Open it by clicking <strong>Analytics</strong> in the sidebar. It is built in and on every plan, so there is nothing to install and no third-party tracking script to add.
        </p>

        <x-doc-screenshot id="analytics--dashboard" alt="The Analytics page: the schedule and date range filters, four stats cards, and the Views Over Time and Device Breakdown charts" loading="eager" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <a href="#filters" class="doc-link">filters</a> sit at the top of the page, and under them the tabs, in this order:
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Tab</th>
                        <th>What it shows</th>
                        <th>Has data</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><a href="#web-analytics" class="doc-link font-semibold sm:whitespace-nowrap">Web Analytics</a></td>
                        <td>Page views over time, device breakdown, top events, broken links, traffic sources, referrers, UTM parameters, visitor locations and social link clicks. This is the tab the page opens on.</td>
                        <td>As soon as someone visits a schedule page</td>
                    </tr>
                    <tr>
                        <td><a href="#realtime" class="doc-link font-semibold sm:whitespace-nowrap">Realtime</a></td>
                        <td>The traffic to your pages as it happens, what people did on your schedules in the last 24 hours, and arrivals at an event that is on</td>
                        <td>At once. The tab is there when the site offers it: on eventschedule.com, and on a selfhosted site once its administrator switches it on.</td>
                    </tr>
                    <tr>
                        <td><a href="#revenue" class="doc-link font-semibold sm:whitespace-nowrap">Revenue</a></td>
                        <td>Total revenue, conversion rate, revenue per view, promo code performance, boost and newsletter funnels, and top events by revenue</td>
                        <td>Once the range holds a completed sale, a boost campaign or a newsletter send. Selling a ticket that carries a price is a Pro feature; free registration and ticket types priced at zero are unlimited on every plan, but neither brings in revenue to report.</td>
                    </tr>
                    <tr>
                        <td><a href="#checkins" class="doc-link font-semibold sm:whitespace-nowrap">Check-Ins</a></td>
                        <td>Tickets sold, attendance rate, no-shows, arrival times, attendance by ticket type, and a per-event breakdown</td>
                        <td>Once tickets are sold for an event dated in the range. The attendance figures need tickets scanned at the door: scanning is free on every plan, and the live check-in dashboard with its running count is the Pro half.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300">
            A page view is counted the moment it happens and stored in that day's total, so today's figures keep climbing until midnight. For the last half hour, minute by minute, use the Realtime tab.
        </p>
    </section>

    <!-- Filters -->
    <section id="filters" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
            </svg>
            Filters
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The controls above the tabs decide which data is shown. Every filter is stored in the page URL, so you can bookmark a view or share it with a team member who has access to the same schedule. The <a href="#realtime" class="doc-link">Realtime</a> tab always covers the last half hour of every page, so there only the schedule selector is shown.
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule selector</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose <strong>All Schedules</strong> or a single schedule. The dropdown only appears when you manage more than one schedule; if you manage exactly one, it is selected for you automatically. Switching schedules clears the event filter.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event selector</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Appears once a single schedule is selected, and narrows Web Analytics, Revenue and Check-Ins to one event. It is searchable and lists events that start in the last 30 days or later, and recurring events that are still running; drafts are never listed. On a curator schedule, Web Analytics lists the events the curator created or accepted, while Revenue and Check-Ins list only the ones it created, because sales belong to the schedule that created the event. Choose <strong>All events</strong> to clear it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Date range</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Last 7 Days, Last 30 Days, Last 90 Days, This Month, Last Month, This Year or All Time. The default is Last 30 Days, which is today and the 29 days before it. All Time reaches back ten years.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Daily / Weekly / Monthly</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Sets the grouping of the Views Over Time chart. These buttons only appear on the Web Analytics tab, and Daily is the default. Daily suits short ranges, weekly gives a balanced overview, and monthly is better for spotting long-term patterns.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">The date range means something different on each tab</div>
            <p>On Web Analytics it filters page views by the day of the visit. On Revenue it filters sales by the date the purchase was made. On Check-Ins it filters by the <em>event date</em> the ticket is for, not by the purchase or scan date, so a ticket bought in January for a March event lands in March. There, only Last Month has an end date: every other range also takes in events still to come, so an upcoming show appears as soon as it has sold a ticket.</p>
        </div>
    </section>

    <!-- Web Analytics -->
    <section id="web-analytics" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
            </svg>
            Web Analytics <x-doc-badge plan="free" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Web Analytics tab is the default tab. It shows page view trends, device and traffic breakdowns, and your top-performing content. Views are counted on your public schedule and event pages and stored as daily totals.
        </p>

        <h3 id="web-counting" class="doc-subheading">What Counts as a View</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Analytics is deliberately conservative, so its numbers are usually lower than a raw server log. A visit is <strong>not</strong> counted when:
        </p>
        <ul class="doc-list mb-6">
            <li>The visitor is a known bot, crawler, preview generator or automated tool.</li>
            <li>You or one of your team members is signed in to that schedule, or a site administrator is signed in.</li>
            <li>The page was loaded inside an <a href="{{ route('marketing.docs.sharing') }}#embed" class="doc-link">embedded calendar</a>.</li>
            <li>The same visitor has already been counted 10 times on that schedule that day. Their later visits stop adding to the schedule's totals, traffic sources, locations and UTM figures until midnight, though each event they open still counts for that event.</li>
            <li>The same visitor has already viewed that event 3 times that day. Each event keeps this count separately.</li>
        </ul>

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
                        <td>Views inside the selected date range. On All Time this card falls back to the current calendar month.</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Previous Period</span></td>
                        <td>Views in the equivalent range immediately before the one you selected</td>
                        <td>Every range except All Time</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">vs Previous 30 Days</span></td>
                        <td>Percentage change against that previous period, green when up and red when down. The card label follows the range you picked, so it also reads vs Previous 7 Days, vs Last Month, vs Last Year and so on.</td>
                        <td>Every range except All Time</td>
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

        <div class="doc-fields doc-fields--grouped">
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
            <div id="web-broken-links" class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Broken links</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Addresses on your schedule that visitors opened and that match no event or page, with how many times each was opened in the period, busiest first, up to ten. Such an address shows a not-found page instead of quietly landing on your schedule, so this is where a mistyped link on a flyer or an old event address in a post turns up. Shown only when a single schedule is selected and no event is. Bots are filtered out, and one visitor can add at most five new addresses a day, so a real broken link outranks one-off noise.</p>
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
                <p class="text-sm text-gray-500 dark:text-gray-400">Clicks on the links in your <a href="{{ route('marketing.docs.creating_schedules') }}#videos-links" class="doc-link">schedule settings</a>, one row per platform, or per site for a link to somewhere the app does not recognise. Every link has a short address on your schedule's own URL, such as <code class="doc-inline-code">/instagram</code>, or the site's name for other sites, and the icons on your schedule page go through it, so a click there or on a short link you printed is counted. A link left without a short address, because its name was already taken, is not counted until you give it one. This measures visitors leaving your schedule rather than arriving. Bots are filtered out, one visitor counts at most ten clicks a day per schedule, and clicks by you, your team members and site administrators are skipped.</p>
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

    <!-- Realtime -->
    <section id="realtime" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.789m13.788 0c3.808 3.808 3.808 9.981 0 13.79M12 12h.008v.008H12V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Realtime <x-doc-badge plan="free" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Realtime</strong> tab shows the traffic to your own schedule and event pages as it happens and, beside it, what people did on your schedules in the last 24 hours, so there is something to read on a quiet afternoon too. It is the second tab of Analytics, beside Web Analytics, and the <strong class="text-gray-900 dark:text-white">Realtime</strong> tile on your dashboard opens it. It is free on every plan.
        </p>

        <x-doc-screenshot id="analytics--realtime" alt="The Realtime tab: page views in the last 5 minutes and visitors now, a bar for each of the last 30 minutes, the Visitors list, and Activity with its counts and latest sales" />
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The tab covers every schedule you own, and each one you run as an admin for somebody else while that schedule is on the Enterprise plan, the plan that includes team members. A notice at the top names any schedule left out for that reason. With several schedules, the schedule selector at the top of the page narrows the tab to one. The event selector and the date range do not apply here and are not shown.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The traffic refreshes every 15 seconds while the browser tab is in view, and once a minute while it is hidden, for up to half an hour. Activity and the door card refresh every minute while the tab is in view, and within about 15 seconds of a sale or a registration made on your pages. Everything catches up at once when you come back to the tab. If an update fails, <strong class="text-gray-900 dark:text-white">Reconnecting</strong> takes the place of <strong class="text-gray-900 dark:text-white">Live</strong> and the page keeps trying; if the next one fails too, a line at the top says so as well. If you have been signed out, or the tab is no longer yours to see, it says <strong class="text-gray-900 dark:text-white">This page stopped updating.</strong> and offers <strong class="text-gray-900 dark:text-white">Reload</strong>.
        </p>

        <h3 id="realtime-traffic" class="doc-subheading">Live Traffic</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The first card and the lists under it cover the last 30 minutes of visits to your pages.
        </p>
        <div class="doc-fields doc-fields--grouped">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Right now</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Two figures, because they answer two questions. <em>Page views, last 5 minutes</em> counts everybody. <em>Visitors on your pages now</em> counts the people who have a page open and whose cookie choice lets them be listed (see <a href="#realtime-privacy" class="doc-link">Who is counted</a>): a visitor who declined sends one page view and nothing after it, so whether they are still there cannot be known. When nobody has visited in half an hour the card says <strong>Quiet right now</strong> instead and, if you have one schedule, shows its address with a <strong>Copy Link</strong> button.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Last 30 minutes</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A short ledger beside the two figures. <em>Page views</em> is always there. <em>Embedded calendar views</em> appears when your calendar was viewed on another website, and is counted apart. <em>Last page view</em> appears once nobody has opened a page for five minutes. While someone is buying there are two more lines: <em>Checkouts started</em> (ticket checkouts begun in the half hour that cost something; a cash order is complete when it is placed, so it is not one) and <em>Checkouts paid</em> (how many of those are paid by now). In a very busy half hour a line says the figures cover the newest 5,000 page views.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Page views per minute</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A bar for each of the last 30 minutes. A green dot on a minute means a sale or a registration came in during it; point at the dot to see how many of each. A sale taken at the box office, a cash order you marked as paid and an imported attendee are never a dot, because nobody on a page made them.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Visitors</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">One row for each visitor who may be listed: a country, a device type, the page they have open and how long they have been on it. Under <em>Earlier in the last 30 minutes</em> come the ones who have left, with how long ago. The card lists up to 50 people who are here now and the 10 who left most recently, and says so when there are more. No name, email address or account is ever shown, and a row does not open into anything.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top pages, Countries and Devices</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Page views of the last 30 minutes. Each card lists its six largest rows and folds the rest into an <em>Other</em> row, along with any view whose country is not known, so every card adds up to the page views above. A green "3 now" beside a page is how many of the visitors who can be listed have it open at this moment.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sources</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Counts visits, not page views: each visit is counted once, by where it began. A row is the site the visit came from with its kind beside it (Search, Social, Email, AI assistants, Paid, Campaign or Other websites), or Direct when it came from nowhere.</p>
            </div>
        </div>

        <h3 id="realtime-door" class="doc-subheading">At the Door Today</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            This card appears only while one of your events is on: from the start of its day until it ends. An event with no length set stays for six hours after it starts. A late show stays after midnight, and a festival through its last day. Only events that sell tickets or take registrations are listed, up to four of them, soonest first, with a line saying how many more are on today. On a phone the card is the first thing on the tab.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Line</th>
                        <th>What it shows</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Checked in</span></td>
                        <td>Ticket holders scanned so far out of the tickets sold for that date, with a bar</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Last 30 minutes</span></td>
                        <td>How many of them arrived in the last half hour</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Sold</span></td>
                        <td>Tickets sold out of the capacity, when the event has a limit</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Pressing an event opens <a href="{{ route('marketing.docs.tickets') }}#checkin-dashboard" class="doc-link">Check-In</a> on it. A registration has nothing to scan, and the check-in dashboard is a Pro feature, so for an event that takes registrations, or on the Free plan, the card shows a single line, <strong class="text-gray-900 dark:text-white">Registered</strong> or <strong class="text-gray-900 dark:text-white">Sold</strong>, and opens Sales.
        </p>

        <h3 id="realtime-activity" class="doc-subheading">Activity</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Activity lists what people did on your schedules in the last 24 hours, newest first. It is read from your own sales, followers and requests and not from the traffic, so it does not depend on anyone's cookie choice. The counts above the list are buttons that filter it: <strong class="text-gray-900 dark:text-white">Sales</strong>, <strong class="text-gray-900 dark:text-white">Registrations</strong>, <strong class="text-gray-900 dark:text-white">Bookings</strong> (only on a day that has one), <strong class="text-gray-900 dark:text-white">Followers</strong> and <strong class="text-gray-900 dark:text-white">Requests</strong>. The list holds the newest 20 rows and says when there are more; below a laptop's width it starts at five, with <strong class="text-gray-900 dark:text-white">Show all</strong>.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            No row shows a name. A row says what happened and to which event or schedule, and opens the page where the person is:
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>What it is</th>
                        <th>Opens</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Sale</span></td>
                        <td>A purchase that took money, with the amount and the number of tickets. A box office sale and a cash order you marked as paid are listed too.</td>
                        <td>Sales</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Registration</span></td>
                        <td>A free registration, or tickets that came to nothing to pay</td>
                        <td>Sales</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Booking</span></td>
                        <td>An appointment booking, named by its appointment type and never by its guest, with the time that was booked</td>
                        <td>The schedule's Appointments tab</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">New follower, New newsletter subscriber</span></td>
                        <td>Someone followed the schedule, or confirmed a sign-up for its emails</td>
                        <td>The schedule's Followers tab</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Event request</span></td>
                        <td>An event made in the last 24 hours and sent to your schedule, which still waits for your answer. It leaves the list once you accept or decline it.</td>
                        <td>The schedule's Requests tab</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Waitlist</span></td>
                        <td>Someone joined an event's waitlist</td>
                        <td>Waitlist</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Wants to hear about tickets</span></td>
                        <td>Someone confirmed an address on an event's interest list</td>
                        <td>Nothing</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Comment, Photo, Video</span></td>
                        <td>Sent in by your audience, and marked <em>Waiting for approval</em> while it waits for you. What you post yourself is not listed.</td>
                        <td>The Engagement tab of the event</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Imported attendees are never listed: an import is not a sale.
        </p>

        <h3 id="realtime-privacy" class="doc-subheading">Who Is Counted, and Who Is Listed</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Page views count every visit except the ones in the first row below. A person is a row under Visitors, and one of the <em>visitors on your pages now</em>, only when their own cookie choice covers it.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Visit</th>
                        <th>Who</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Not counted</span></td>
                        <td>You and your team while signed in, site administrators, and known bots. On a custom domain nobody is signed in, so a visit you make to your own page there counts like anyone's.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Counted, never listed</span></td>
                        <td>A visitor who declined cookies, has not answered, or whose browser sends Global Privacy Control. So is one who accepted on a notice that did not say a schedule's organizer sees visits to its pages, which includes anyone who answered an earlier notice.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Listed, without a name</span></td>
                        <td>A visitor who accepted analytics cookies on a notice that said so. The row is a country, a device type, a page and a time: never a name, an email address, an account, a browser or a history. Its id means nothing outside your own sign-in session, so it cannot follow a visitor from one day or one device to another.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A visitor who has left stays under Earlier for up to half an hour, and the record of a visit is deleted about an hour after the visitor's last activity. Activity is your own sales, followers and requests, which stay where they always were. For daily totals over weeks and months, use the <a href="#web-analytics" class="doc-link">Web Analytics</a> tab.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Nothing on the tab names anyone, but it sits beside records that do. On a quiet schedule, one unnamed visitor on an event's page followed by a sale for that event is very likely the buyer, whose name is in Sales as it always was.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Selfhost</div>
            <p>The tab appears for schedule owners once the site's administrator has switched on both <strong>Record live page views</strong> and <strong>Show schedule owners live traffic to their own pages</strong>. See the <a href="{{ route('marketing.docs.selfhost.admin') }}#realtime-owner-view" class="doc-link">admin guide</a>.</p>
        </div>
    </section>

    <!-- Revenue -->
    <section id="revenue" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            Revenue <x-doc-badge plan="free" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Revenue tab tracks ticket sales performance: how much you earned, how well views convert to purchases, how your promo codes did, and what your boost and newsletter campaigns returned. It counts paid sales by the date of purchase.
        </p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">The tab is on every plan; taking money is Pro</div>
            <p><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling a ticket</a> that carries a price is a Pro feature, and so is taking payment for an <a href="{{ route('marketing.docs.appointments') }}" class="doc-link">appointment</a>, so revenue appears once you are on Pro or Enterprise. Free registration and ticket types priced at zero stay unlimited on the Free plan: they are completed sales, so the cards below still appear, with revenue at zero. The Boost Funnel also needs Pro, because boost campaigns are a Pro feature.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Paid appointment bookings are sales, so they count in the revenue figures alongside tickets. They are left out of the conversion rate, because a booking is not made from an event page.
        </p>

        <h3 id="revenue-stats" class="doc-subheading">Stats Cards</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Three cards summarise the period. They appear only once there is at least one completed sale in the selected range, and a free registration is one.
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
                        <td>What the paid sales made during the period took, by purchase date. It is not net of refunds: a partially refunded sale stays paid and counts in full, and only a fully refunded or deleted sale drops out. If your sales span more than one currency, each currency is listed on its own line rather than added together.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Conversion Rate</span></td>
                        <td>Completed sales as a percentage of event page views in the same period. Free registrations and zero-price tickets are completed sales, so they raise this rate without raising revenue. Appointment bookings are left out, because they are not made from an event page; their money still counts in Total Revenue.</td>
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
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
            </svg>
            Check-Ins <x-doc-badge plan="free" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The Check-Ins tab turns door scans into attendance analytics: how many ticket holders actually showed up, when they arrived, and which events and ticket types had the best turnout. It reads paid sales only, leaves out deleted ones and appointment bookings, and groups them by the <strong>event date</strong> the ticket is for.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>Attendance comes from scanning tickets with the <a href="{{ route('marketing.docs.tickets') }}#check-in" class="doc-link">check-in feature</a>, which is free on every plan. Without a scan a ticket counts as sold but never as attended, so an unscanned event reads as 100% no-shows rather than as missing data. Appointment bookings are left out of this tab, since nobody scans a booking at a door, so a schedule that takes bookings keeps a clean attendance rate.</p>
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
                        <td>Tickets from paid sales for events dated in the selected range, including free registrations and events still to come</td>
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
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Attendance Rate</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A chart with the same name as the card above it: check-in rates broken down by ticket type, so you can see which types turn up. Shown only when the period contains more than one ticket type.</p>
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
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            No Data State
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Each tab shows its own empty state when it has nothing to display: "No analytics data available yet", "No revenue data available yet", "No check-in data" or, on Realtime, "Quiet right now". Common causes:
        </p>
        <ul class="doc-list mb-6">
            <li>The schedule is new and has not been visited yet.</li>
            <li>The selected date range contains no recorded activity. All Time is the quickest way to rule this out.</li>
            <li>You are filtering by a schedule or an event with no traffic.</li>
            <li>You have only been checking the page yourself while signed in. Your own visits, and those of your team members and site administrators, are never counted.</li>
            <li>Your only traffic so far came through an embedded calendar, which is not counted, or from bots, which are filtered out.</li>
            <li>On the Revenue tab, no sale was completed in the range. If you see a conversion rate but no revenue, your sales were free registrations or zero-price tickets.</li>
            <li>On the Check-Ins tab, no paid ticket or registration exists for an event dated in the range. Appointment bookings do not count here, and scans are not needed for the tab to fill in; remember it filters by event date.</li>
            <li>You expected views on an event whose address is wrong. An address that matches no event opens a not-found page, and its visits appear under <a href="#web-broken-links" class="doc-link">Broken links</a> rather than on any event.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            If the dashboard still looks empty, work through these in order:
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Set the date range to <strong>All Time</strong> and clear the schedule and event filters.</li>
            <li>Open your public schedule page in a private or logged-out browser window, then reload Analytics. The visit is counted at once: it is in today's total on Web Analytics, and on the Realtime tab within seconds.</li>
            <li>Confirm the events you expect traffic on are published rather than drafts.</li>
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Share your schedule link</a> so real visitors start arriving.</li>
        </ol>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
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
            "description": "Track views, devices, traffic sources, social link clicks, broken links, revenue, and check-ins with Event Schedule's built-in analytics dashboard.",
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
                    "text": "Check page views, device breakdown, top events, broken links, traffic sources, visitor locations and social link clicks on the Web Analytics tab.",
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
