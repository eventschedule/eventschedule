<x-marketing-layout>
    <x-slot name="title">Creating Schedules - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Creating Schedules</x-slot>
    <x-slot name="description">Learn how to create and configure your schedule in Event Schedule. Set up details, address, contact info, settings, sub-schedules, auto import, calendar integrations, and more.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Creating Schedules - Event Schedule",
        "description": "Learn how to create and configure your schedule in Event Schedule. Set up details, address, contact info, settings, sub-schedules, auto import, calendar integrations, and more.",
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
        "dateModified": "2026-02-27"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Creating Schedules" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Creating Schedules</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Set up and configure your schedule - from basic details and contact info to sub-schedules, calendar integrations, and auto import.
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
                        <a href="#schedule-types" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Schedule Types</a>
                        <a href="#details" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Details</a>
                        <a href="#address" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Address</a>
                        <a href="#contact-info" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Contact Info</a>
                        <a href="#style" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Style</a>
                        <a href="#subschedules" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Sub-schedules</a>
                        <a href="#settings" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Settings</a>
                        <a href="#settings-general" class="doc-nav-link block px-3 py-1.5 pl-6 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">General</a>
                        <a href="#settings-custom-fields" class="doc-nav-link block px-3 py-1.5 pl-6 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Custom Fields</a>
                        <a href="#settings-requests" class="doc-nav-link block px-3 py-1.5 pl-6 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Requests</a>
                        <a href="#settings-advanced" class="doc-nav-link block px-3 py-1.5 pl-6 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Advanced</a>
                        <a href="#auto-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Auto Import</a>
                        <a href="#integrations" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Integrations</a>
                        <a href="#integrations-google" class="doc-nav-link block px-3 py-1.5 pl-6 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Google Calendar</a>
                        <a href="#integrations-caldav" class="doc-nav-link block px-3 py-1.5 pl-6 text-xs text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">CalDAV</a>
                        <a href="#email-settings" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Email Settings</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Schedule Types -->
                        <section id="schedule-types" class="doc-section">
                            <h2 class="doc-heading">Schedule Types</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule supports three types of schedules, each designed for different use cases:</p>

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
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Details -->
                        <section id="details" class="doc-section">
                            <h2 class="doc-heading">Details</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure your schedule's core identity in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>.</p>

                            <x-doc-screenshot id="creating-schedules--section-details" alt="Schedule details settings" loading="eager" />

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
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Short Description</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A brief subtitle for your schedule (up to 200 characters). This appears below your schedule name on the schedule page.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Description</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A bio or description of your schedule. Supports <strong class="text-gray-900 dark:text-white">Markdown formatting</strong> for links, bold text, lists, and more. Tell visitors what you're about and what kind of events they can expect.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Address -->
                        <section id="address" class="doc-section">
                            <h2 class="doc-heading">Address</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">For <strong class="text-gray-900 dark:text-white">Venue</strong> schedules, you can add a full physical address. This enables map integration and helps visitors find your location.</p>

                            <x-doc-screenshot id="creating-schedules--section-address" alt="Schedule address settings" />

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

                        <!-- Contact Info -->
                        <section id="contact-info" class="doc-section">
                            <h2 class="doc-heading">Contact Info</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Add contact details in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> so visitors can reach you. These appear on your public schedule page.</p>

                            <x-doc-screenshot id="creating-schedules--section-contact-info" alt="Schedule contact information settings" />

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Email Address</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A public contact email for inquiries. Use the <strong class="text-gray-900 dark:text-white">"Show email"</strong> toggle to control whether this is visible to visitors.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Phone Number</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">A contact phone number in international format. Use the <strong class="text-gray-900 dark:text-white">"Show phone number"</strong> toggle to control whether this is visible to visitors. On the hosted platform, the phone number must be verified via SMS before it will be displayed publicly.</p>
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

                        <!-- Style -->
                        <section id="style" class="doc-section">
                            <h2 class="doc-heading">Style</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Customize your schedule's visual appearance including colors, fonts, backgrounds, and layout. See the full <a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Styling</a> guide for all customization options.</p>
                        </section>

                        <!-- Sub-schedules -->
                        <section id="subschedules" class="doc-section">
                            <h2 class="doc-heading">Sub-schedules</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Organize your events into sub-schedules (categories). This helps visitors filter and find events that interest them.</p>

                            <x-doc-screenshot id="creating-schedules--section-subschedules" alt="Sub-schedules settings" />

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Creating Sub-schedules</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">To create a sub-schedule, go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> and scroll to the Sub-schedules section.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Use Cases</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Create sub-schedules like "Live Music", "DJ Nights", "Comedy Shows", or "Workshops". Each sub-schedule gets its own URL and can be filtered.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sub-schedule Name & English Name</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Like schedules, sub-schedules can have localized names with English translations for multilingual support.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">URL Slugs</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Each sub-schedule gets a URL slug (e.g., <code class="doc-inline-code">/live-music</code>) so visitors can bookmark and share filtered views.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assigning Events to Sub-schedules</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">When creating or editing an event, select a sub-schedule from the dropdown. Events can belong to one sub-schedule at a time.</p>
                        </section>

                        <!-- Settings -->
                        <section id="settings" class="doc-section">
                            <h2 class="doc-heading">Settings</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure how your schedule works in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>. Settings are organized into four tabs.</p>

                            <x-doc-screenshot id="creating-schedules--section-settings" alt="Schedule settings" />

                            <!-- General Tab -->
                            <h3 id="settings-general" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">General</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule URL / Subdomain</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your unique URL identifier. On the hosted version, this becomes <code class="doc-inline-code">yourname.eventschedule.com</code>. Choose something memorable and easy to type.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom Domain <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-1">Enterprise</span></h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Use your own domain (e.g., <code class="doc-inline-code">events.yourbrand.com</code>) instead of a subdomain. A custom domain gives your <a href="{{ route('marketing.docs.sharing') }}#schedule-url" class="text-cyan-400 hover:text-cyan-300">shared schedule URL</a> a more professional look.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">There are two modes: <strong class="text-gray-900 dark:text-white">Direct mode</strong> (CNAME - your schedule is served on your domain with automatic SSL) and <strong class="text-gray-900 dark:text-white">Redirect mode</strong> (Cloudflare - your domain redirects to your eventschedule.com URL).</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Language</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose from 11 supported languages: English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, Arabic, Estonian, and Russian. This affects the interface language on your schedule page.</p>
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
                            </div>

                            <!-- URL Pattern Variables -->
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">URL Pattern Variables</h4>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Use these variables in your Event URL Pattern. All values are automatically converted to URL-safe format (lowercase, spaces become dashes).
                            </p>

                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Date & Time</h5>
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

                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Event Information</h5>
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
                                            <td>Event Name</td>
                                            <td>summer-concert</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Venue Information</h5>
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

                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3"><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Ticket</a> Information</h5>
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
                                        <tr>
                                            <td><code class="doc-inline-code">{coupon_code}</code></td>
                                            <td>Coupon code</td>
                                            <td>SAVE20</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Custom Fields Tab -->
                            <h3 id="settings-custom-fields" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Custom Fields</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Define <a href="{{ marketing_url('/features/custom-fields') }}" class="text-cyan-400 hover:text-cyan-300">Event Custom Fields</a> to add extra data to your events. Custom field values can also be used as URL pattern variables.
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

                            <!-- Requests Tab -->
                            <h3 id="settings-requests" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Requests</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Accept Event Requests</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Allow others to submit events to your schedule. Submitted events can be reviewed in the <a href="{{ route('marketing.docs.creating_events') }}#manual" class="text-cyan-400 hover:text-cyan-300">pending queue</a>. Perfect for:</p>
                                    <ul class="text-sm text-gray-500 dark:text-gray-400 list-disc list-inside space-y-1">
                                        <li><strong class="text-gray-900 dark:text-white">Venues:</strong> Accept booking requests from bands and performers</li>
                                        <li><strong class="text-gray-900 dark:text-white">Curators:</strong> Let the community submit local events</li>
                                        <li><strong class="text-gray-900 dark:text-white">Organizations:</strong> Collect event submissions from members</li>
                                    </ul>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require Approval</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">When enabled, submitted events go to a <a href="{{ route('marketing.docs.creating_events') }}#manual" class="text-cyan-400 hover:text-cyan-300">pending queue</a> for your approval before appearing publicly. Review requests in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Pending</strong>.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Request Terms</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Add custom terms or guidelines that submitters must agree to when requesting events. Use this to set expectations about your booking policies, technical requirements, or submission guidelines.</p>
                                </div>
                            </div>

                            <!-- Advanced Tab -->
                            <h3 id="settings-advanced" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Advanced</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">First Day of Week</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose whether your calendar starts on Sunday or Monday.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Import Form Fields</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Customize which fields are shown on the event request form. This lets you control what information submitters provide when requesting events.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Link Directly to Registration</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">When enabled, clicking events on the calendar or scanning QR codes in event graphics will link directly to the event's registration URL instead of showing the event detail page first. Only affects events that have a registration URL configured.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Unlisted Schedule <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-1">Enterprise</span></h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Make your schedule private - it won't appear in search results or public listings. Only people with the direct link can access it. For per-event privacy with password protection, see <a href="{{ route('marketing.docs.creating_events') }}#privacy" class="text-cyan-400 hover:text-cyan-300">Privacy</a>.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Auto Import -->
                        <section id="auto-import" class="doc-section">
                            <h2 class="doc-heading">Auto Import <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 ml-2">Selfhost</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Automatically import events from external sources to keep your schedule up-to-date without manual entry.</p>

                            <x-doc-screenshot id="creating-schedules--section-auto-import" alt="Auto import settings" />

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Import from URLs</h4>
                                    <p class="text-sm text-gray-400 mb-3">Add URLs of event pages, venue calendars, or artist websites. Event Schedule's AI will automatically parse and import events from these sources on a regular schedule.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Works great with:</strong> Venue event pages, artist tour pages, Facebook event listings, Eventbrite organizer pages, Bandsintown profiles, and most websites that list events.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Import by City Search</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Search for events by city name to automatically discover and import local events. Great for curators building comprehensive local calendars.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Setting Up Auto Import</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong></li>
                                <li>Scroll to the <strong class="text-gray-900 dark:text-white">Auto Import</strong> section</li>
                                <li>Add URLs or city names you want to import from</li>
                                <li>Events will be automatically checked and imported on a regular schedule</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Auto-imported events go to your pending queue if you have <a href="#settings-requests" class="text-cyan-400 hover:text-cyan-300">Require Approval</a> enabled, so you can review them before they appear publicly.</p>
                            </div>
                        </section>

                        <!-- Integrations -->
                        <section id="integrations" class="doc-section">
                            <h2 class="doc-heading">Integrations</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Sync your schedule with external calendar systems for smooth event management.</p>

                            <x-doc-screenshot id="creating-schedules--section-integrations" alt="Calendar integration settings" />

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sync Direction Options</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose one-way sync (import only or export only) or two-way sync to keep both calendars in perfect harmony.</p>
                                </div>
                            </div>

                            <!-- Google Calendar -->
                            <h3 id="integrations-google" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Google Calendar</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Connect your Google Calendar for bidirectional sync. Events created in either place stay synchronized automatically. Supports webhook-based real-time updates.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">First, make sure you've connected your Google account in <a href="{{ route('marketing.docs.account_settings') }}#google" class="text-cyan-400 hover:text-cyan-300">Account Settings</a>. Then:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong></li>
                                <li>Scroll to <strong class="text-gray-900 dark:text-white">Integrations</strong></li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Connect Google Calendar</strong></li>
                                <li>Authorize Event Schedule to access your Google Calendar</li>
                                <li>Select which calendar to sync and choose sync direction</li>
                            </ol>

                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">Selfhost Note</div>
                                <p>Google Calendar integration requires API credentials configuration. See the <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="text-cyan-400 hover:text-cyan-300">selfhost Google Calendar docs</a> for setup instructions.</p>
                            </div>

                            <!-- CalDAV -->
                            <h3 id="integrations-caldav" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">CalDAV</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Connect to any CalDAV-compatible calendar (Apple Calendar, Fastmail, Nextcloud, etc.) for cross-platform synchronization.</p>
                        </section>

                        <!-- Email Settings -->
                        <section id="email-settings" class="doc-section">
                            <h2 class="doc-heading">Email Settings</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure email delivery for your schedule's notifications and communications.</p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Availability</div>
                                <p>Custom email settings are available for selfhosted installations and Pro plans.</p>
                            </div>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">SMTP Configuration</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Configure your own SMTP server for sending emails. This gives you full control over deliverability and lets you use your email provider.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom Sender Address</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Send emails from your own domain (e.g., <code class="doc-inline-code">events@yourdomain.com</code>) instead of the default Event Schedule address.</p>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Custom email settings also apply to <a href="{{ route('marketing.docs.newsletters') }}" class="text-cyan-400 hover:text-cyan-300">newsletters</a> sent from your schedule.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Setting Up Custom Email</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong></li>
                                <li>Scroll to <strong class="text-gray-900 dark:text-white">Email Settings</strong></li>
                                <li>Enter your SMTP server details (host, port, username, password)</li>
                                <li>Set your custom sender name and email address</li>
                                <li>Send a test email to verify the configuration</li>
                            </ol>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Styling</a> - Colors, fonts, backgrounds, and visual customization</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events to your schedule</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Embed and share your schedule</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Set up ticketing for your events</li>
                                <li><a href="{{ route('marketing.docs.managing_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Managing Schedules</a> - View events, manage team, set availability, and more</li>
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
        "name": "How to Create and Configure Your Event Schedule",
        "description": "Set up your schedule with details, address, contact info, settings, sub-schedules, auto import, and calendar integrations.",
        "totalTime": "PT10M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Choose Your Schedule Type",
                "text": "Select the appropriate schedule type: Talent for performers, Venue for event spaces, or Curator for promoters.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#schedule-types"
            },
            {
                "@type": "HowToStep",
                "name": "Enter Schedule Details",
                "text": "Configure your schedule name, English name (if applicable), and description with Markdown formatting in Admin Panel, then Profile, then Edit.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#details"
            },
            {
                "@type": "HowToStep",
                "name": "Set Your Address",
                "text": "For Venue schedules, add your full address including street, city, state, postal code, and country for map integration.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#address"
            },
            {
                "@type": "HowToStep",
                "name": "Configure Settings",
                "text": "Set your schedule URL, language, timezone, time format, and configure event request and approval settings.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#settings"
            },
            {
                "@type": "HowToStep",
                "name": "Set Up Auto Import",
                "text": "Add URLs or city names to automatically import events from external sources.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#auto-import"
            },
            {
                "@type": "HowToStep",
                "name": "Connect Calendar Integrations",
                "text": "Sync with Google Calendar or CalDAV for smooth event management.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#integrations"
            }
        ]
    }
    </script>
</x-marketing-layout>
