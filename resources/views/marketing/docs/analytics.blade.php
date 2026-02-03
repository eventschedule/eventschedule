<x-marketing-layout>
    <x-slot name="title">Analytics Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Analytics</x-slot>
    <x-slot name="description">Learn how to track views, devices, traffic sources, and conversions with Event Schedule's built-in analytics dashboard.</x-slot>
    <x-slot name="keywords">analytics, event analytics, view tracking, traffic sources, conversion rate, device breakdown, event schedule</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

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
                    <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        <a href="#stats-cards" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Stats Cards</a>
                        <a href="#charts" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Charts</a>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Analytics tracks:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Schedule and event page views over time</li>
                                <li>Device and browser breakdown (desktop, mobile, tablet)</li>
                                <li>Traffic sources and referrers</li>
                                <li>Revenue and conversion metrics (if selling tickets)</li>
                                <li>Top-performing events and appearances</li>
                            </ul>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Basic analytics are available on the Free plan. Advanced analytics including revenue tracking, conversion rates, and detailed referrer data require the Pro plan.</p>
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
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Pick a start and end date to focus on a specific time period. The dashboard and all charts will update to reflect only data within the selected range.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Period Toggle</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Switch between <strong>Daily</strong> and <strong>Monthly</strong> grouping to view trends at different granularities. Daily is best for short ranges; monthly is better for spotting long-term patterns.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Stats Cards -->
                        <section id="stats-cards" class="doc-section">
                            <h2 class="doc-heading">Stats Cards</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The top of the dashboard shows summary cards with key metrics for the selected period:
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
                                            <td>Views on schedules where your events appear as a guest (curator or talent appearances)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Revenue</span></td>
                                            <td>Total ticket revenue earned during the selected period (Pro plan)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Conversion Rate</span></td>
                                            <td>Percentage of page views that resulted in a ticket purchase (Pro plan)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Revenue per View</span></td>
                                            <td>Average revenue generated per page view (Pro plan)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Use the comparison percentage to quickly identify whether your schedule is gaining or losing traction relative to the previous period.</p>
                            </div>
                        </section>

                        <!-- Charts -->
                        <section id="charts" class="doc-section">
                            <h2 class="doc-heading">Charts</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Below the stats cards, the dashboard displays several interactive charts:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Views Over Time</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A line chart showing daily or monthly page views. Hover over data points to see exact numbers for each date.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Device Breakdown</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A pie chart showing the split between desktop, mobile, and tablet visitors. Helps you understand how your audience accesses your schedule.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A ranked list of your most-viewed events during the selected period.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Traffic Sources</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Shows where your visitors come from: direct traffic, search engines, social media, or other websites.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Views by Schedule</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">If you manage multiple schedules, this chart breaks down views per schedule so you can compare performance.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Referrers</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A list of the specific websites and pages that send the most traffic to your schedule.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Appearances</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Shows which external schedules drive the most views for your events when you appear as a guest performer or vendor.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Top Events by Revenue</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Ranks your events by total ticket revenue, helping you identify your highest-earning events (Pro plan).</p>
                                </div>
                            </div>
                        </section>

                        <!-- No Data State -->
                        <section id="no-data" class="doc-section">
                            <h2 class="doc-heading">No Data State</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                If you see a "No analytics data" message, it means no views have been recorded for the selected date range or schedule. This can happen when:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Your schedule is newly created and has not received any visits yet</li>
                                <li>The selected date range does not contain any recorded views</li>
                                <li>You are filtering by a schedule that has no traffic</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Try adjusting the date range or sharing your schedule link to start collecting analytics data.
                            </p>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Increase traffic to your schedule</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Set up ticketing to track conversions and revenue</li>
                                <li><a href="{{ route('marketing.docs.newsletters') }}" class="text-cyan-400 hover:text-cyan-300">Newsletters</a> - Drive engagement with email campaigns</li>
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
</x-marketing-layout>
