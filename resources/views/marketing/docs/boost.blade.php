<x-marketing-layout>
    <x-slot name="title">Boost Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Boost</x-slot>
    <x-slot name="description">Learn how to promote your events with automated Facebook and Instagram ad campaigns using Event Schedule's Boost feature.</x-slot>
    <x-slot name="socialImage">social/docs.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Boost Documentation - Event Schedule",
        "description": "Learn how to promote your events with automated Facebook and Instagram ad campaigns using Event Schedule's Boost feature.",
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

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-amber-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Boost" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-orange-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Boost</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Promote your events with automated Facebook and Instagram ad campaigns. Set a budget, pick your audience, and launch in minutes.
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
                        <a href="#quick-mode" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Quick Mode</a>
                        <a href="#advanced-mode" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Advanced Mode</a>
                        <a href="#smart-defaults" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Smart Defaults</a>
                        <a href="#managing-campaigns" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Managing Campaigns</a>
                        <a href="#spending-limits" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Spending Limits</a>
                        <a href="#analytics" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Analytics</a>
                        <a href="#billing" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Billing & Refunds</a>
                        <a href="#tips" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Tips</a>
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
                                Boost turns your event details into live Facebook and Instagram ads. It is designed for event organizers who want to reach a wider audience without needing ad manager experience.
                            </p>

                            <div class="my-6 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-sm">
                                <picture>
                                    <source srcset="{{ url('images/docs/boost--page.webp') }}" type="image/webp">
                                    <img src="{{ url('images/docs/boost--page.png') }}" alt="Boost campaigns page" class="w-full h-auto" loading="eager">
                                </picture>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                With Boost you can:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Create ad campaigns from any upcoming event in your schedule</li>
                                <li>Set your own budget from $10 to $1,000</li>
                                <li>Target audiences by location, age range, and interests</li>
                                <li>Run ads on Facebook Feed, Instagram Feed, Stories, and Reels</li>
                                <li>Track impressions, reach, clicks, and conversions in real time</li>
                            </ul>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Pro Feature</div>
                                <p>Boost requires a <a href="{{ marketing_url('/pricing') }}" class="text-cyan-400 hover:text-cyan-300">Pro plan</a> or higher. You also need a verified phone number on your account. Add and verify your phone in <a href="{{ route('marketing.docs.account_settings') }}#profile" class="text-cyan-400 hover:text-cyan-300">Account Settings</a>.</p>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Boost offers two modes: <strong>Quick Mode</strong> for a streamlined experience and <strong>Advanced Mode</strong> for full control over every parameter.
                            </p>
                        </section>

                        <!-- Quick Mode -->
                        <section id="quick-mode" class="doc-section">
                            <h2 class="doc-heading">Quick Mode</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Quick Mode is the fastest way to launch a campaign. Follow these steps:
                            </p>

                            <ul class="doc-list doc-list-numbered mb-6">
                                <li><strong>Select an event</strong> - Choose any upcoming event from your schedule. Boost pulls in the title, date, location, and image automatically.</li>
                                <li><strong>Set your budget</strong> - Slide from $10 to $1,000. The estimated reach updates as you adjust.</li>
                                <li><strong>Review the cost breakdown</strong> - See your ad budget and the service fee before you commit. The total is shown upfront.</li>
                                <li><strong>Launch</strong> - Your ad goes live on Facebook and Instagram. Boost handles the creative, targeting, and delivery.</li>
                            </ul>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Quick Mode uses smart defaults based on your event's details. For most events, this is all you need.</p>
                            </div>
                        </section>

                        <!-- Advanced Mode -->
                        <section id="advanced-mode" class="doc-section">
                            <h2 class="doc-heading">Advanced Mode</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Advanced Mode gives you full control over your campaign. Toggle it on when creating a boost to access additional settings.
                            </p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Setting</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Budget Type</span></td>
                                            <td>Choose between a daily budget (spend per day) or a lifetime budget (total spend over the campaign duration)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Campaign Objective</span></td>
                                            <td>Select awareness, traffic, or engagement depending on your goal</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Custom Dates</span></td>
                                            <td>Set specific start and end dates for your campaign</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Age Range</span></td>
                                            <td>Narrow your audience to a specific age bracket</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Interest Targeting</span></td>
                                            <td>Add or remove interest categories to refine your audience</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Placements</span></td>
                                            <td>Choose where your ad appears: Facebook Feed, Instagram Feed, Stories, or Reels</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Custom Headline</span></td>
                                            <td>Write your own ad headline instead of using the event title</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Custom Text</span></td>
                                            <td>Write custom primary text for the ad body</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Custom Description</span></td>
                                            <td>Add a link description shown below the headline</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Call to Action</span></td>
                                            <td>Choose the CTA button text (e.g., Learn More, Get Tickets, Sign Up)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Smart Defaults -->
                        <section id="smart-defaults" class="doc-section">
                            <h2 class="doc-heading">Smart Defaults</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Boost automatically configures your campaign based on your event's details:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">In-person events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Targeting is set to a radius around the event's location. The CTA defaults to "Get Tickets" if tickets are available, or "Learn More" otherwise.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Online events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Location-based targeting is expanded or removed. The ad creative emphasizes the online format and timezone.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Hybrid events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A combination of local and broader targeting is used. The creative highlights both attendance options.</p>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Event type detection is automatic based on your event's location and settings. You can always override any default in Advanced Mode.
                            </p>
                        </section>

                        <!-- Managing Campaigns -->
                        <section id="managing-campaigns" class="doc-section">
                            <h2 class="doc-heading">Managing Campaigns</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Active and past campaigns are listed in your admin panel under Boost. You can pause, resume, or cancel a campaign at any time.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Campaign Statuses</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Active</span></td>
                                            <td>The campaign is running and ads are being delivered</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Paused</span></td>
                                            <td>Delivery is temporarily stopped. You can resume at any time</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Completed</span></td>
                                            <td>The campaign has finished. Budget was fully spent or the end date was reached</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Cancelled</span></td>
                                            <td>You cancelled the campaign. Unspent budget is refunded</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Rejected</span></td>
                                            <td>Meta rejected the ad. A full refund is issued automatically</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Failed</span></td>
                                            <td>A technical error prevented the campaign from running</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>The number of concurrent campaigns allowed depends on your boost history. New schedules start with 1, increasing to 2 after 3 completed campaigns and 3 after 10.</p>
                            </div>
                        </section>

                        <!-- Spending Limits -->
                        <section id="spending-limits" class="doc-section">
                            <h2 class="doc-heading">Spending Limits</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                To build trust on both sides, boost spending limits start low and grow automatically as you complete successful campaigns.
                            </p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Completed Campaigns</th>
                                            <th>Max Budget per Campaign</th>
                                            <th>Max Concurrent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">0 (new)</span></td>
                                            <td>$10</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">1</span></td>
                                            <td>$25</td>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">3</span></td>
                                            <td>$50</td>
                                            <td>2</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">5</span></td>
                                            <td>$100</td>
                                            <td>2</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">10</span></td>
                                            <td>$250</td>
                                            <td>3</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">20</span></td>
                                            <td>$500</td>
                                            <td>3</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">50+</span></td>
                                            <td>$1,000</td>
                                            <td>3</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Your limit increases automatically after each completed campaign. You can see your current limit when creating a new boost.
                            </p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Start with a small campaign to build your history. After just one successful campaign, your limit increases to $25.</p>
                            </div>
                        </section>

                        <!-- Analytics -->
                        <section id="analytics" class="doc-section">
                            <h2 class="doc-heading">Analytics</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Track your campaign's performance in real time from your dashboard. Metrics update automatically as your ad runs.
                            </p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Impressions</span></td>
                                            <td>Total number of times your ad was shown</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Reach</span></td>
                                            <td>Number of unique people who saw your ad</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Clicks</span></td>
                                            <td>Number of clicks on your ad</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Conversions</span></td>
                                            <td>Actions tracked via Meta Pixel (e.g., ticket purchases, page views)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">CTR</span></td>
                                            <td>Click-through rate (clicks divided by impressions)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">CPC</span></td>
                                            <td>Cost per click</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">CPM</span></td>
                                            <td>Cost per 1,000 impressions</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                A daily performance chart shows how your campaign performs over time. If your campaign creates multiple ad variants, you can view stats for each variant individually.
                            </p>
                        </section>

                        <!-- Billing & Refunds -->
                        <section id="billing" class="doc-section">
                            <h2 class="doc-heading">Billing & Refunds</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Boost uses transparent pricing with no hidden costs. Your total is your ad budget plus a 20% service fee.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How Pricing Works</h3>
                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-5 border border-gray-200 dark:border-white/10 mb-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Ad budget (you choose)</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">$75.00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Service fee (20%)</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">$15.00</span>
                                    </div>
                                    <div class="border-t border-gray-200 dark:border-white/10 pt-3 flex justify-between">
                                        <span class="font-bold text-gray-900 dark:text-white">Total charged</span>
                                        <span class="font-bold text-orange-600 dark:text-orange-400">$90.00</span>
                                    </div>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Refund Policy</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Ad rejected by Meta</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Full refund of the entire amount (ad budget + service fee).</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cancelled before any spend</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Full refund of the entire amount.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Campaign completes with unspent budget</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">The remaining ad budget and the proportional service fee are automatically refunded.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Email Notifications</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You receive email notifications at each stage of your campaign:
                            </p>
                            <ul class="doc-list mb-6">
                                <li><strong>Campaign created</strong> - Confirmation that your boost has been submitted</li>
                                <li><strong>75% budget alert</strong> - A heads-up that most of your budget has been spent</li>
                                <li><strong>Campaign completed</strong> - Final stats summary with impressions, reach, clicks, and any refund details</li>
                            </ul>
                        </section>

                        <!-- Tips -->
                        <section id="tips" class="doc-section">
                            <h2 class="doc-heading">Tips</h2>
                            <ul class="doc-list mb-6">
                                <li><strong>Add images to your events</strong> - Events with images produce more engaging ads and higher click-through rates.</li>
                                <li><strong>Write detailed descriptions</strong> - Boost uses your event description to generate ad copy. The more detail you provide, the better the ad.</li>
                                <li><strong>Boost 3 or more days before the event</strong> - Give the campaign time to reach people and build momentum.</li>
                                <li><strong>Try Advanced Mode for fine-tuning</strong> - If Quick Mode results are good but not great, switch to Advanced Mode to adjust targeting, placements, or ad copy.</li>
                                <li><strong>Start with a smaller budget</strong> - Test with $10 to $25 to see how your audience responds, then scale up for future events.</li>
                            </ul>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.analytics') }}" class="text-orange-500 hover:text-orange-400">Analytics</a> - Track how Boost campaigns drive schedule views</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-orange-500 hover:text-orange-400">Selling Tickets</a> - Set up ticketing for boosted events</li>
                                <li><a href="{{ route('marketing.docs.newsletters') }}" class="text-orange-500 hover:text-orange-400">Newsletters</a> - Combine Boost with email campaigns</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-orange-500 hover:text-orange-400">Sharing Your Schedule</a> - More ways to grow your audience</li>
                            </ul>
                        </section>

                        @include('marketing.docs.partials.navigation')
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')

    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to Boost Events with Event Schedule",
        "description": "Promote your events with automated Facebook and Instagram ad campaigns using Event Schedule's Boost feature.",
        "totalTime": "PT5M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Select an Event",
                "text": "Choose any upcoming event from your schedule. Boost pulls in the title, date, location, and image automatically.",
                "url": "{{ url(route('marketing.docs.boost')) }}#quick-mode"
            },
            {
                "@type": "HowToStep",
                "name": "Set Your Budget",
                "text": "Slide from $10 to $1,000. See estimated reach and the full cost breakdown before you commit.",
                "url": "{{ url(route('marketing.docs.boost')) }}#quick-mode"
            },
            {
                "@type": "HowToStep",
                "name": "Launch Your Campaign",
                "text": "Your ad goes live on Facebook and Instagram. Track impressions, reach, clicks, and conversions in real time.",
                "url": "{{ url(route('marketing.docs.boost')) }}#analytics"
            }
        ]
    }
    </script>
</x-marketing-layout>
