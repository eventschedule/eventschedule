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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Overview
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                Accessing /admin
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                                Dashboard
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0Zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0Z" />
                                </svg>
                                Users (Insights)
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                </svg>
                                Revenue (Insights)
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                                Analytics (Insights)
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                                Usage (Insights)
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                                Boost (Manage)
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                                Plans (Manage) <span class="doc-badge-saas">SaaS only</span>
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                                </svg>
                                Domains (Manage) <span class="doc-badge-saas">SaaS only</span>
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                Newsletters (Manage)
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                </svg>
                                Blog (Manage) <span class="doc-badge-saas">SaaS only</span>
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                                Audit Log (System)
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                                </svg>
                                Queue (System)
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                Logs (System)
                            </h2>
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
