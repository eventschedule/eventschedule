<x-marketing-layout>
    <x-slot name="title">Analytics Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Analytics</x-slot>
    <x-slot name="description">Learn how to track views, devices, traffic sources, revenue, and check-ins with Event Schedule's built-in analytics dashboard.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Analytics Documentation - Event Schedule",
        "description": "Learn how to track views, devices, traffic sources, revenue, and check-ins with Event Schedule's built-in analytics dashboard.",
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
        "dateModified": "2026-03-08"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Analytics" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Analytics</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Track how your audience discovers and interacts with your schedule. View trends, compare periods, and understand what drives engagement.
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
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#filters" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Filters</a>
                        <div class="doc-nav-group">
                            <a href="#web-analytics" class="doc-nav-group-header doc-nav-link">Web Analytics <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#web-stats" class="doc-nav-link">Stats Cards</a>
                                <a href="#web-charts" class="doc-nav-link">Charts</a>
                            </div>
                        </div>
                        <div class="doc-nav-group">
                            <a href="#revenue" class="doc-nav-group-header doc-nav-link">Revenue <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#revenue-stats" class="doc-nav-link">Stats Cards</a>
                                <a href="#revenue-funnels" class="doc-nav-link">Funnels</a>
                            </div>
                        </div>
                        <div class="doc-nav-group">
                            <a href="#checkins" class="doc-nav-group-header doc-nav-link">Check-ins <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#checkins-stats" class="doc-nav-link">Stats Cards</a>
                                <a href="#checkins-charts" class="doc-nav-link">Charts</a>
                            </div>
                        </div>
                        <a href="#no-data" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">No Data State</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The Analytics dashboard gives you a comprehensive view of how your schedule is performing. Access it by clicking <strong>Analytics</strong> in the main navigation.
                            </p>

                            <x-doc-screenshot id="analytics--dashboard" alt="Analytics dashboard" loading="eager" />

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The dashboard is organized into three tabs:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Web Analytics</strong> - Page views, devices, traffic sources, referrers, and top events</li>
                                <li><strong class="text-gray-900 dark:text-white">Revenue</strong> - Conversion stats, promo code performance, boost and newsletter funnels, and top events by revenue</li>
                                <li><strong class="text-gray-900 dark:text-white">Check-ins</strong> - Attendance rates, arrival time distribution, ticket type breakdown, and per-event check-in data</li>
                            </ul>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Basic web analytics including traffic sources and referrer data are available on the Free plan. Revenue tracking and check-in analytics require ticket sales, which are available on the Pro plan.</p>
                            </div>
                        </section>

                        <!-- Filters -->
                        <section id="filters" class="doc-section">
                            <h2 class="doc-heading">Filters</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Use the filters at the top of the analytics dashboard to control which data is displayed:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule Selector</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">If you manage multiple schedules, use the dropdown to view analytics for a specific schedule or across all schedules at once.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Date Range</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Select a predefined time period from the dropdown: Last 7 days, Last 30 days, Last 90 days, This month, Last month, This year, or All time. The dashboard and all charts update to reflect only data within the selected range.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Period Toggle</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">On the Web Analytics tab, switch between <strong>Daily</strong>, <strong>Weekly</strong>, and <strong>Monthly</strong> grouping to view the Views Over Time chart at different granularities. Daily is best for short ranges; weekly provides a balanced overview; monthly is better for spotting long-term patterns.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Web Analytics -->
                        <section id="web-analytics" class="doc-section">
                            <h2 class="doc-heading">Web Analytics</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                The Web Analytics tab (the default tab) shows page view trends, device and traffic breakdowns, and your top-performing content.
                            </p>

                            <h3 id="web-stats" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stats Cards</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The top of the tab shows summary cards with key metrics for the selected period:
                            </p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Card</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Total Views</span></td>
                                            <td>Total number of page views across all time</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Period Views</span></td>
                                            <td>Number of views within the selected date range</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Comparison %</span></td>
                                            <td>Percentage change compared to the previous period of equal length, shown as an increase or decrease</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Appearance Views</span></td>
                                            <td>Views on schedules where your events appear as a guest (shown for talent and venue schedules)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Use the comparison percentage to quickly identify whether your schedule is gaining or losing traction relative to the previous period.</p>
                            </div>

                            <h3 id="web-charts" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Charts</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Below the stats cards, the Web Analytics tab displays several interactive charts:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Views Over Time</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A line chart showing page views grouped by the selected period (daily or monthly). Hover over data points to see exact numbers. If you use <a href="{{ route('marketing.docs.newsletters') }}" class="text-cyan-400 hover:text-cyan-300">newsletters</a> or <a href="{{ route('marketing.docs.tickets') }}#boost" class="text-cyan-400 hover:text-cyan-300">boosts</a>, overlay markers show when those campaigns were sent so you can see their impact on traffic.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Device Breakdown</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A chart showing the split between desktop, mobile, and tablet visitors.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A ranked list of your most-viewed events during the selected period.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Traffic Sources</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Shows where your visitors come from, broken down into eight categories: Direct, Search, Social, Email, Newsletter, Boost, Promo Code, and Other.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Views by Schedule</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Breaks down views per schedule so you can compare performance (shown if you have multiple schedules).</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Referrers</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A list of the specific websites and pages that send the most traffic to your schedule.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Talents/Venues</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Shows which talents or venues appearing on your schedule drive the most views (shown for schedules with guest appearances).</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Schedules Appeared On</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Shows which external schedules generate the most views for your events when you appear as a guest (shown for talent and venue schedules).</p>
                                </div>
                            </div>
                        </section>

                        <!-- Revenue -->
                        <section id="revenue" class="doc-section">
                            <h2 class="doc-heading">Revenue <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-1">Pro</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                The Revenue tab tracks your ticket sales performance, including conversion metrics, promo code stats, and the effectiveness of your boost and newsletter campaigns.
                            </p>

                            <h3 id="revenue-stats" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stats Cards</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Card</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Revenue</span></td>
                                            <td>Total ticket revenue earned during the selected period</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Conversion Rate</span></td>
                                            <td>Percentage of page views that resulted in a ticket purchase</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Revenue per View</span></td>
                                            <td>Average revenue generated per page view</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Promo Discounts</span></td>
                                            <td>Total discount amount from promo code usage</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 id="revenue-funnels" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Funnels and Charts</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Promo Code Stats</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Shows how many views came through promo code links and how many converted to sales, helping you measure promo code effectiveness.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Boost Funnel</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Tracks the full funnel from boost impressions to clicks, page views, and sales. Helps you measure the ROI of your boost campaigns.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Newsletter Funnel</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Tracks the funnel from newsletters sent to opens, clicks, page views, and sales. Helps you measure newsletter-driven revenue.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Events by Revenue</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Ranks your events by total ticket revenue, helping you identify your highest-earning events.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Check-ins -->
                        <section id="checkins" class="doc-section">
                            <h2 class="doc-heading">Check-ins <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-1">Pro</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                The Check-ins tab provides attendance analytics for events where you use the <a href="{{ route('marketing.docs.tickets') }}#check-in" class="text-cyan-400 hover:text-cyan-300">check-in feature</a>. Track how many ticket holders actually attend your events.
                            </p>

                            <h3 id="checkins-stats" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stats Cards</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Card</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Tickets Sold</span></td>
                                            <td>Total number of tickets sold during the selected period</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Checked In</span></td>
                                            <td>Number of ticket holders who were checked in at the event</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Attendance Rate</span></td>
                                            <td>Percentage of sold tickets that were checked in</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">No-Shows</span></td>
                                            <td>Number and percentage of ticket holders who did not attend</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 id="checkins-charts" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Charts</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Arrival Time Distribution</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Shows when attendees check in relative to the event start time. Helps you understand arrival patterns and plan accordingly.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Ticket Type Attendance</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Breaks down check-in rates by ticket type, so you can see which ticket types have the highest attendance.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Events Breakdown</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A table showing per-event check-in data: tickets sold, checked in, and attendance rate for each event in the selected period.</p>
                                </div>
                            </div>
                        </section>

                        <!-- No Data State -->
                        <section id="no-data" class="doc-section">
                            <h2 class="doc-heading">No Data State</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                If no data appears on the dashboard, it means no views have been recorded for the selected date range or schedule. This can happen when:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Your schedule is newly created and has not received any visits yet</li>
                                <li>The selected date range does not contain any recorded views</li>
                                <li>You are filtering by a schedule that has no traffic</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Try adjusting the date range or <a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">sharing your schedule link</a> to start collecting analytics data.
                            </p>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Increase traffic to your schedule</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Set up ticketing to track conversions and revenue</li>
                                <li><a href="{{ route('marketing.docs.newsletters') }}#analytics" class="text-cyan-400 hover:text-cyan-300">Newsletter Analytics</a> - Track open rates, clicks, and engagement for email campaigns</li>
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Event Graphics</a> - Create shareable images to boost visibility</li>
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
                "text": "Use the schedule selector and date range dropdown to filter the data displayed.",
                "url": "{{ url(route('marketing.docs.analytics')) }}#filters"
            },
            {
                "@type": "HowToStep",
                "name": "Review Web Analytics",
                "text": "Check page views, device breakdown, traffic sources, and top events on the Web Analytics tab.",
                "url": "{{ url(route('marketing.docs.analytics')) }}#web-analytics"
            },
            {
                "@type": "HowToStep",
                "name": "Track Revenue",
                "text": "Switch to the Revenue tab to see conversion rates, promo code stats, and boost and newsletter funnels.",
                "url": "{{ url(route('marketing.docs.analytics')) }}#revenue"
            },
            {
                "@type": "HowToStep",
                "name": "Monitor Check-ins",
                "text": "Use the Check-ins tab to track attendance rates, arrival patterns, and per-event check-in data.",
                "url": "{{ url(route('marketing.docs.analytics')) }}#checkins"
            }
        ]
    }
    </script>
</x-marketing-layout>
