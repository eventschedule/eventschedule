<x-marketing-layout>
    <x-slot name="title">Admin Panel Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Admin Panel</x-slot>
    <x-slot name="description">Learn how to use the Event Schedule admin panel to monitor users, revenue, analytics, and manage platform settings for your selfhosted installation.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Admin Panel Documentation - Event Schedule",
        "description": "Learn how to use the Event Schedule admin panel to monitor users, revenue, analytics, and manage platform settings for your selfhosted installation.",
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
        "dateModified": "2026-02-01"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <style {!! nonce_attr() !!}>
        .doc-badge-saas {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.3);
            vertical-align: middle;
            margin-inline-start: 0.5rem;
        }
        .dark .doc-badge-saas { background: rgba(59, 130, 246, 0.15); color: #93c5fd; border-color: rgba(59, 130, 246, 0.3); }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Admin Panel" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Admin Panel</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Monitor your platform's users, revenue, and analytics, and manage system settings from the admin panel.
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
                        <a href="#accessing" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Accessing /admin</a>
                        <a href="#dashboard" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Dashboard</a>

                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-4 pt-3 border-t border-gray-200 dark:border-white/10">Insights</div>
                        <a href="#insights-users" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Users</a>
                        <a href="#insights-revenue" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Revenue</a>
                        <a href="#insights-analytics" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Analytics</a>
                        <a href="#insights-usage" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Usage</a>

                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-4 pt-3 border-t border-gray-200 dark:border-white/10">Manage</div>
                        <a href="#manage-boost" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Boost</a>
                        <a href="#manage-plans" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Plans</a>
                        <a href="#manage-domains" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Domains</a>
                        <a href="#manage-newsletters" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Newsletters</a>
                        <a href="#manage-blog" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Blog</a>

                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-4 pt-3 border-t border-gray-200 dark:border-white/10">System</div>
                        <a href="#system-audit-log" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Audit Log</a>
                        <a href="#system-queue" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Queue</a>
                        <a href="#system-logs" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Logs</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The admin panel provides platform-wide visibility and management tools for your Event Schedule installation. It is organized into four areas:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Dashboard</strong> - at-a-glance key metrics for your platform</li>
                                <li><strong class="text-gray-900 dark:text-white">Insights</strong> - detailed breakdowns of users, revenue, analytics, and usage</li>
                                <li><strong class="text-gray-900 dark:text-white">Manage</strong> - configure boost settings, newsletters, and (in SaaS mode) plans, domains, and the blog</li>
                                <li><strong class="text-gray-900 dark:text-white">System</strong> - audit log, background queue, and application logs</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300">Every page in the admin panel supports a date-range filter so you can view data for any time period.</p>
                        </section>

                        <!-- Accessing /admin -->
                        <section id="accessing" class="doc-section">
                            <h2 class="doc-heading">Accessing /admin</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The admin panel is available at <code class="doc-inline-code">/admin</code> and is restricted to users whose <code class="doc-inline-code">is_admin</code> column is set to <code class="doc-inline-code">true</code> in the database.</p>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">To grant admin access to a user, update the <code class="doc-inline-code">users</code> table directly:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>SQL</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-keyword">UPDATE</span> users <span class="code-keyword">SET</span> is_admin <span class="code-keyword">=</span> <span class="code-value">1</span> <span class="code-keyword">WHERE</span> email <span class="code-keyword">=</span> <span class="code-string">'your@email.com'</span>;</code></pre>
                            </div>

                            <div class="doc-callout doc-callout-info mt-6">
                                <div class="doc-callout-title">Note</div>
                                <p>Only grant admin access to trusted users. Admins can see all platform data including user details, revenue, and system logs.</p>
                            </div>
                        </section>

                        <!-- Dashboard -->
                        <section id="dashboard" class="doc-section">
                            <h2 class="doc-heading">Dashboard</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The dashboard is the landing page of the admin panel. It shows key metrics at a glance including:</p>
                            <ul class="doc-list mb-6">
                                <li>Total users, schedules, and events with period-over-period change</li>
                                <li>New signups chart over the selected date range</li>
                                <li>Recent activity feed showing the latest user actions</li>
                            </ul>
                            <x-doc-screenshot id="selfhost-admin--dashboard" alt="Admin dashboard showing key metrics and recent activity" />
                        </section>

                        <!-- Insights: Users -->
                        <section id="insights-users" class="doc-section">
                            <h2 class="doc-heading">Users (Insights)</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Users page lists every registered user on the platform with search and filtering capabilities.</p>
                            <ul class="doc-list mb-6">
                                <li>Search users by name or email</li>
                                <li>View each user's schedules, plan tier, and registration date</li>
                                <li>See login activity and email verification status</li>
                                <li>Click a user row to view their full profile and associated schedules</li>
                            </ul>
                            <x-doc-screenshot id="selfhost-admin--users" alt="Admin users list with search and user details" />
                        </section>

                        <!-- Insights: Revenue -->
                        <section id="insights-revenue" class="doc-section">
                            <h2 class="doc-heading">Revenue (Insights)</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Revenue page provides a financial overview of ticket sales across the platform.</p>
                            <ul class="doc-list mb-6">
                                <li>Total revenue, ticket sales count, and average order value</li>
                                <li>Revenue chart over the selected date range</li>
                                <li>Breakdown by schedule showing which schedules generate the most sales</li>
                            </ul>
                            <x-doc-screenshot id="selfhost-admin--revenue" alt="Admin revenue dashboard with sales charts" />
                        </section>

                        <!-- Insights: Analytics -->
                        <section id="insights-analytics" class="doc-section">
                            <h2 class="doc-heading">Analytics (Insights)</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Analytics page shows platform-wide traffic and engagement data.</p>
                            <ul class="doc-list mb-6">
                                <li>Page views and unique visitors across all schedules</li>
                                <li>Traffic trends over the selected date range</li>
                                <li>Top schedules by page views</li>
                                <li>Geographic distribution of visitors</li>
                            </ul>
                            <x-doc-screenshot id="selfhost-admin--analytics" alt="Admin analytics dashboard with traffic charts" />
                        </section>

                        <!-- Insights: Usage -->
                        <section id="insights-usage" class="doc-section">
                            <h2 class="doc-heading">Usage (Insights)</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Usage page tracks how actively the platform's features are being used.</p>
                            <ul class="doc-list mb-6">
                                <li>Feature adoption rates (e.g. Google Calendar sync, ticket sales, event graphics)</li>
                                <li>Active schedules and events created over time</li>
                                <li>API usage and integration activity</li>
                            </ul>
                            <x-doc-screenshot id="selfhost-admin--usage" alt="Admin usage dashboard showing feature adoption" />
                        </section>

                        <!-- Manage: Boost -->
                        <section id="manage-boost" class="doc-section">
                            <h2 class="doc-heading">Boost (Manage)</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Boost management page lets you view and manage all active and past boost campaigns across the platform.</p>
                            <ul class="doc-list mb-6">
                                <li>View all boost campaigns with their status, budget, and performance metrics</li>
                                <li>Monitor active campaigns and their delivery progress</li>
                                <li>Review completed campaigns and their results</li>
                            </ul>
                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">Note</div>
                                <p>The boost feature requires Meta/Facebook API configuration. See the <a href="{{ route('marketing.docs.selfhost.boost') }}" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300 underline">Boost Setup</a> guide for configuration instructions.</p>
                            </div>
                            <x-doc-screenshot id="selfhost-admin--boost" alt="Admin boost management page" />
                        </section>

                        <!-- Manage: Plans (SaaS only) -->
                        <section id="manage-plans" class="doc-section">
                            <h2 class="doc-heading">Plans (Manage) <span class="doc-badge-saas">SaaS only</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Plans page lets you manage subscription plans and pricing tiers for your SaaS platform.</p>
                            <ul class="doc-list mb-6">
                                <li>View and edit plan tiers (Free, Pro, Enterprise)</li>
                                <li>Configure pricing, trial periods, and feature limits for each plan</li>
                                <li>See how many schedules are subscribed to each plan</li>
                            </ul>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">SaaS only</div>
                                <p>This section is only available when <code class="doc-inline-code">IS_HOSTED=true</code>. Selfhosted installations have all features unlocked by default and do not use plan tiers.</p>
                            </div>
                        </section>

                        <!-- Manage: Domains (SaaS only) -->
                        <section id="manage-domains" class="doc-section">
                            <h2 class="doc-heading">Domains (Manage) <span class="doc-badge-saas">SaaS only</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Domains page manages custom domain mappings for schedules on the SaaS platform.</p>
                            <ul class="doc-list mb-6">
                                <li>View all custom domains connected to schedules</li>
                                <li>Monitor DNS verification status for each domain</li>
                                <li>Manage SSL certificate provisioning</li>
                            </ul>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">SaaS only</div>
                                <p>This section is only available when <code class="doc-inline-code">IS_HOSTED=true</code>. Selfhosted installations use their own domain directly.</p>
                            </div>
                        </section>

                        <!-- Manage: Newsletters -->
                        <section id="manage-newsletters" class="doc-section">
                            <h2 class="doc-heading">Newsletters (Manage)</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Newsletters page lets you create and send email newsletters to platform users.</p>
                            <ul class="doc-list mb-6">
                                <li>Create newsletters with a rich text editor</li>
                                <li>Target specific user segments or send to all users</li>
                                <li>View delivery statistics and open rates for sent newsletters</li>
                                <li>Manage subscriber segments for targeted communication</li>
                            </ul>
                            <x-doc-screenshot id="selfhost-admin--newsletters" alt="Admin newsletters management page" />
                        </section>

                        <!-- Manage: Blog (SaaS only) -->
                        <section id="manage-blog" class="doc-section">
                            <h2 class="doc-heading">Blog (Manage) <span class="doc-badge-saas">SaaS only</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Blog section lets you publish and manage blog posts on the marketing site.</p>
                            <ul class="doc-list mb-6">
                                <li>Create and edit blog posts with a rich text editor</li>
                                <li>Manage post categories and tags</li>
                                <li>Control post visibility with draft and published states</li>
                            </ul>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">SaaS only</div>
                                <p>This section is only available when <code class="doc-inline-code">IS_HOSTED=true</code>. Selfhosted installations do not include a built-in blog.</p>
                            </div>
                        </section>

                        <!-- System: Audit Log -->
                        <section id="system-audit-log" class="doc-section">
                            <h2 class="doc-heading">Audit Log (System)</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Audit Log provides a chronological record of important actions taken across the platform.</p>
                            <ul class="doc-list mb-6">
                                <li>Track user logins, schedule changes, and administrative actions</li>
                                <li>Filter by action type, user, or date range</li>
                                <li>View detailed information for each log entry including IP address and user agent</li>
                            </ul>
                            <x-doc-screenshot id="selfhost-admin--audit-log" alt="Admin audit log showing platform activity" />
                        </section>

                        <!-- System: Queue -->
                        <section id="system-queue" class="doc-section">
                            <h2 class="doc-heading">Queue (System)</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Queue page shows the status of background jobs processed by the Laravel queue worker.</p>
                            <ul class="doc-list mb-6">
                                <li>View pending, processing, and failed jobs</li>
                                <li>Monitor queue throughput and processing times</li>
                                <li>Retry or delete failed jobs</li>
                                <li>See job details including payload and error messages for failed jobs</li>
                            </ul>
                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">Note</div>
                                <p>Make sure the Laravel scheduler is running (<code class="doc-inline-code">* * * * * php artisan schedule:run</code>) for background jobs to be processed.</p>
                            </div>
                            <x-doc-screenshot id="selfhost-admin--queue" alt="Admin queue showing background job status" />
                        </section>

                        <!-- System: Logs -->
                        <section id="system-logs" class="doc-section">
                            <h2 class="doc-heading">Logs (System)</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">The Logs page displays recent application log entries, useful for debugging issues on your selfhosted installation.</p>
                            <ul class="doc-list mb-6">
                                <li>View log entries with severity levels (info, warning, error, critical)</li>
                                <li>Filter logs by severity level</li>
                                <li>Search log entries by message content</li>
                                <li>Expand entries to view full stack traces for errors</li>
                            </ul>
                            <x-doc-screenshot id="selfhost-admin--logs" alt="Admin logs viewer showing application log entries" />
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
