<x-marketing-layout>
    <x-slot name="title">Schedule Basics - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Schedule Basics</x-slot>
    <x-slot name="description">Learn the basics of setting up your schedule in Event Schedule. Configure name, location, contact info, and schedule settings.</x-slot>
    <x-slot name="keywords">schedule setup, schedule settings, schedule configuration, event calendar basics, schedule types</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Schedule Basics" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Schedule Basics</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Set up your schedule's core information including name, type, location, contact details, and basic settings.
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
                        <a href="#introduction" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Introduction</a>
                        <a href="#schedule-types" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Schedule Types</a>
                        <a href="#basic-information" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Basic Information</a>
                        <a href="#location" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Location & Address</a>
                        <a href="#contact" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Contact Information</a>
                        <a href="#settings" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Schedule Settings</a>
                        <a href="#slug-variables" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">URL Pattern Variables</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Introduction -->
                        <section id="introduction" class="doc-section">
                            <h2 class="doc-heading">Introduction</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">A schedule is your event calendar - it's where all your events live. Each schedule gets its own unique URL that you can share with your audience. Before diving into the settings, make sure you've <a href="{{ route('marketing.docs.getting_started') }}" class="text-cyan-400 hover:text-cyan-300">created your account</a>.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">This page covers the essential setup: schedule type, basic information, location, contact details, and core settings. For visual customization (colors, fonts, backgrounds), see <a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Styling</a>.</p>
                        </section>

                        <!-- Schedule Types -->
                        <section id="schedule-types" class="doc-section">
                            <h2 class="doc-heading">Schedule Types</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule supports four types of schedules, each designed for different use cases:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Best For</th>
                                            <th>Key Features</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Talent</span></td>
                                            <td>Musicians, DJs, performers, speakers</td>
                                            <td>Events display venues, focused on "where you'll be"</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Venue</span></td>
                                            <td>Bars, clubs, theaters, event spaces</td>
                                            <td>Full address support, map integration</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Curator</span></td>
                                            <td>Promoters, bloggers, community organizers</td>
                                            <td>Aggregate events from multiple sources</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Vendor</span></td>
                                            <td>Food trucks, mobile businesses, pop-up shops</td>
                                            <td>Location-focused for mobile operations</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>You can change your schedule type at any time. Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> and select a new type. The type affects how your events are displayed and what information is shown.</p>
                            </div>
                        </section>

                        <!-- Basic Information -->
                        <section id="basic-information" class="doc-section">
                            <h2 class="doc-heading">Basic Information</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure your schedule's core identity in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule Name</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your schedule's display name. This appears at the top of your schedule page and in search results. Use your band name, venue name, or organization name.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">English Name</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">If your schedule name is in a non-English language, you can provide an English translation. This helps with discoverability and accessibility for international visitors.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Description</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A bio or description of your schedule. Supports <strong class="text-gray-900 dark:text-white">Markdown formatting</strong> for links, bold text, lists, and more. Tell visitors what you're about and what kind of events they can expect.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Location & Address -->
                        <section id="location" class="doc-section">
                            <h2 class="doc-heading">Location & Address</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">For <strong class="text-gray-900 dark:text-white">Venue</strong> schedules, you can add a full physical address. This enables map integration and helps visitors find your location.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Street Address</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your venue's street address (e.g., "123 Main Street").</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">City, State/Province, Postal Code</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Fill in your city, state or province, and postal/zip code for complete address information.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Country</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Select your country from the dropdown. This is used for address formatting and map display.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Address Validation</div>
                                <p>When you enter an address, Event Schedule validates it and generates map coordinates. This powers the interactive map on your schedule page.</p>
                            </div>
                        </section>

                        <!-- Contact Information -->
                        <section id="contact" class="doc-section">
                            <h2 class="doc-heading">Contact Information</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Add contact details in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> so visitors can reach you. These appear on your public schedule page.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Email Address</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A public contact email for inquiries. Use the <strong class="text-gray-900 dark:text-white">"Show email"</strong> toggle to control whether this is visible to visitors.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Phone Number</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A contact phone number. Displayed as a clickable link on mobile devices.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Website URL</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Link to your main website. Opens in a new tab when visitors click it.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">City/Country</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">For non-Venue schedules (Talent, Curator), you can specify your city and country. This appears on your profile without requiring a full street address.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Schedule Settings -->
                        <section id="settings" class="doc-section">
                            <h2 class="doc-heading">Schedule Settings</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure how your schedule works in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule URL / Subdomain</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your unique URL identifier. On the hosted version, this becomes <code class="doc-inline-code">yourname.eventschedule.com</code>. Choose something memorable and easy to type.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom Domain</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><span class="text-cyan-400 text-xs font-medium">PRO</span> Use your own domain (e.g., <code class="doc-inline-code">events.yourbrand.com</code>) instead of a subdomain. Requires DNS configuration.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Language</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose from 9 supported languages: English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, and Arabic. This affects the interface language on your schedule page.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Timezone</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Set your schedule's timezone. All event times are displayed in this timezone. Important for audiences in multiple regions.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Time Format</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose between 12-hour (2:00 PM) or 24-hour (14:00) time format based on your audience's preference.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event URL Pattern</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Customize how event URLs are generated. Use variables like <code class="doc-inline-code">{event_name}</code>, <code class="doc-inline-code">{date_dmy}</code>, <code class="doc-inline-code">{venue}</code>, etc.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Example: <code class="doc-inline-code">{event_name}-{date_dmy}</code> creates URLs like <code class="doc-inline-code">my-event-27-1</code></p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Accept Event Requests</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Allow others to submit events to your schedule. Perfect for:</p>
                                    <ul class="text-sm text-gray-500 dark:text-gray-400 list-disc list-inside space-y-1">
                                        <li><strong class="text-gray-900 dark:text-white">Venues:</strong> Accept booking requests from bands and performers</li>
                                        <li><strong class="text-gray-900 dark:text-white">Curators:</strong> Let the community submit local events</li>
                                        <li><strong class="text-gray-900 dark:text-white">Organizations:</strong> Collect event submissions from members</li>
                                    </ul>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require Approval</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">When enabled, submitted events go to a pending queue for your approval before appearing publicly. Review requests in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Pending</strong>.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Request Terms</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Add custom terms or guidelines that submitters must agree to when requesting events. Use this to set expectations about your booking policies, technical requirements, or submission guidelines.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Unlisted Schedule</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Make your schedule private - it won't appear in search results or public listings. Only people with the direct link can access it.</p>
                                </div>
                            </div>
                        </section>

                        <!-- URL Pattern Variables -->
                        <section id="slug-variables" class="doc-section">
                            <h2 class="doc-heading">URL Pattern Variables</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Use these variables in your Event URL Pattern. All values are automatically converted to URL-safe format (lowercase, spaces become dashes).
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Date & Time</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Description</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">{day_name}</code></td>
                                            <td>Full day name (translated)</td>
                                            <td>wednesday</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{day_short}</code></td>
                                            <td>Short day name (translated)</td>
                                            <td>wed</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{date_dmy}</code></td>
                                            <td>Day-month format</td>
                                            <td>15-3</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{date_mdy}</code></td>
                                            <td>Month-day format</td>
                                            <td>3-15</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{date_full_dmy}</code></td>
                                            <td>Full date (day-month-year)</td>
                                            <td>15-03-2025</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{date_full_mdy}</code></td>
                                            <td>Full date (month-day-year)</td>
                                            <td>03-15-2025</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{month}</code></td>
                                            <td>Month number</td>
                                            <td>3</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{month_name}</code></td>
                                            <td>Full month name (translated)</td>
                                            <td>march</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{month_short}</code></td>
                                            <td>Short month name (translated)</td>
                                            <td>mar</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{day}</code></td>
                                            <td>Day of month</td>
                                            <td>15</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{year}</code></td>
                                            <td>Year</td>
                                            <td>2025</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{time}</code></td>
                                            <td>Start time</td>
                                            <td>20-00 or 8-00-pm</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{end_time}</code></td>
                                            <td>End time</td>
                                            <td>22-00 or 10-00-pm</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{duration}</code></td>
                                            <td>Duration in hours</td>
                                            <td>2</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Information</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Description</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">{event_name}</code></td>
                                            <td>Event name (uses English name if available)</td>
                                            <td>summer-concert</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Venue Information</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Description</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">{venue}</code></td>
                                            <td>Venue name (translated)</td>
                                            <td>central-park</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{city}</code></td>
                                            <td>City</td>
                                            <td>new-york</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{address}</code></td>
                                            <td>Street address</td>
                                            <td>123-main-st</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{state}</code></td>
                                            <td>State/Province</td>
                                            <td>ny</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{country}</code></td>
                                            <td>Country</td>
                                            <td>us</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ticket Information</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Variable</th>
                                            <th>Description</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code class="doc-inline-code">{currency}</code></td>
                                            <td>Currency code</td>
                                            <td>usd</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{price}</code></td>
                                            <td>Lowest ticket price (or price range)</td>
                                            <td>10 or 10-25</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Custom Fields</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                If you have defined <a href="{{ marketing_url('/features/custom-fields') }}" class="text-cyan-400 hover:text-cyan-300">Event Custom Fields</a> in your schedule settings, you can include their values using numbered variables.
                            </p>

                            @if (!empty($customFieldsData))
                                {{-- Dynamic: Show user's actual custom fields --}}
                                @foreach ($customFieldsData as $scheduleData)
                                    <h4 class="text-md font-medium text-gray-200 mb-2">{{ $scheduleData['role_name'] }}</h4>
                                    <div class="overflow-x-auto mb-6">
                                        <table class="doc-table">
                                            <thead>
                                                <tr>
                                                    <th>Variable</th>
                                                    <th>Field Name</th>
                                                    <th>Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($scheduleData['fields'] as $index => $field)
                                                <tr>
                                                    <td><code class="doc-inline-code">{custom_{{ $loop->iteration }}}</code></td>
                                                    <td>{{ $field['name'] }}</td>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $field['type'] ?? 'string')) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endforeach
                            @else
                                {{-- Static: Generic documentation for logged-out users or users without custom fields --}}
                                <div class="overflow-x-auto mb-6">
                                    <table class="doc-table">
                                        <thead>
                                            <tr>
                                                <th>Variable</th>
                                                <th>Description</th>
                                                <th>Example</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><code class="doc-inline-code">{custom_1}</code></td>
                                                <td>Value of the 1st custom field</td>
                                                <td>john-smith</td>
                                            </tr>
                                            <tr>
                                                <td><code class="doc-inline-code">{custom_2}</code></td>
                                                <td>Value of the 2nd custom field</td>
                                                <td>room-101</td>
                                            </tr>
                                            <tr>
                                                <td><code class="doc-inline-code">{custom_3}</code></td>
                                                <td>Value of the 3rd custom field</td>
                                                <td>workshop</td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-gray-400 text-sm">...up to {custom_8}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">URL-Safe Formatting</div>
                                <p>All variable values are automatically converted to URL-safe slugs: lowercase letters, numbers, and dashes only. For example, "Summer Concert" becomes "summer-concert" and "New York" becomes "new-york".</p>
                            </div>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Styling</a> - Customize colors, fonts, backgrounds, and visual appearance</li>
                                <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Advanced Schedule Settings</a> - Subschedules, calendar integrations, and auto-import</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events to your schedule</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Embed and share your schedule</li>
                                <li><a href="{{ route('marketing.docs.availability') }}" class="text-cyan-400 hover:text-cyan-300">Availability Calendar</a> - Mark available and unavailable dates (Talent and Venue schedules)</li>
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
        "name": "How to Set Up Your Event Schedule",
        "description": "Learn the basics of setting up your schedule including type, name, location, contact information, and core settings.",
        "totalTime": "PT5M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Choose Your Schedule Type",
                "text": "Select the appropriate schedule type: Talent for performers, Venue for event spaces, Curator for promoters, or Vendor for mobile businesses.",
                "url": "{{ url(route('marketing.docs.schedule_basics')) }}#schedule-types"
            },
            {
                "@type": "HowToStep",
                "name": "Enter Basic Information",
                "text": "Configure your schedule name, English name (if applicable), and description with Markdown formatting in Admin Panel, then Profile, then Edit.",
                "url": "{{ url(route('marketing.docs.schedule_basics')) }}#basic-information"
            },
            {
                "@type": "HowToStep",
                "name": "Set Your Location",
                "text": "For Venue schedules, add your full address including street, city, state, postal code, and country for map integration.",
                "url": "{{ url(route('marketing.docs.schedule_basics')) }}#location"
            },
            {
                "@type": "HowToStep",
                "name": "Configure Settings",
                "text": "Set your schedule URL, language, timezone, time format, and configure event request and approval settings.",
                "url": "{{ url(route('marketing.docs.schedule_basics')) }}#settings"
            }
        ]
    }
    </script>
</x-marketing-layout>
