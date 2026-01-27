<x-marketing-layout>
    <x-slot name="title">Schedule Basics - Event Schedule</x-slot>
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
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

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
                                <p>You can change your schedule type at any time. Go to <strong class="text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> and select a new type. The type affects how your events are displayed and what information is shown.</p>
                            </div>
                        </section>

                        <!-- Basic Information -->
                        <section id="basic-information" class="doc-section">
                            <h2 class="doc-heading">Basic Information</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure your schedule's core identity in <strong class="text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>.</p>

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
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A bio or description of your schedule. Supports <strong class="text-white">Markdown formatting</strong> for links, bold text, lists, and more. Tell visitors what you're about and what kind of events they can expect.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Location & Address -->
                        <section id="location" class="doc-section">
                            <h2 class="doc-heading">Location & Address</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">For <strong class="text-white">Venue</strong> schedules, you can add a full physical address. This enables map integration and helps visitors find your location.</p>

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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Add contact details in <strong class="text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> so visitors can reach you. These appear on your public schedule page.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Email Address</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A public contact email for inquiries. Use the <strong class="text-white">"Show email"</strong> toggle to control whether this is visible to visitors.</p>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure how your schedule works in <strong class="text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>.</p>

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
                                        <li><strong class="text-white">Venues:</strong> Accept booking requests from bands and performers</li>
                                        <li><strong class="text-white">Curators:</strong> Let the community submit local events</li>
                                        <li><strong class="text-white">Organizations:</strong> Collect event submissions from members</li>
                                    </ul>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require Approval</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">When enabled, submitted events go to a pending queue for your approval before appearing publicly. Review requests in <strong class="text-white">Admin Panel &rarr; Schedule &rarr; Pending</strong>.</p>
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

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Styling</a> - Customize colors, fonts, backgrounds, and visual appearance</li>
                                <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Advanced Schedule Settings</a> - Subschedules, calendar integrations, and auto-import</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events to your schedule</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Embed and share your schedule</li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
