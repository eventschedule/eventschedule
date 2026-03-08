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
                        <div class="doc-nav-group">
                            <a href="#details" class="doc-nav-group-header doc-nav-link">Details <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#details-general" class="doc-nav-link">General</a>
                                <a href="#details-localization" class="doc-nav-link">Localization</a>
                                <a href="#contact-info" class="doc-nav-link">Contact Info</a>
                            </div>
                        </div>
                        <a href="#ai-details-generator" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">AI Details Generator</a>
                        <a href="#address" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Address</a>
                        <a href="#style" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Style</a>
                        <a href="#videos-links" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Videos & Links</a>
                        <div class="doc-nav-group">
                            <a href="#customize" class="doc-nav-group-header doc-nav-link">Customize <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#customize-subschedules" class="doc-nav-link">Sub-schedules</a>
                                <a href="#customize-custom-fields" class="doc-nav-link">Custom Fields</a>
                                <a href="#customize-sponsors" class="doc-nav-link">Sponsors</a>
                                <a href="#customize-custom-labels" class="doc-nav-link">Custom Labels</a>
                            </div>
                        </div>
                        <div class="doc-nav-group">
                            <a href="#settings" class="doc-nav-group-header doc-nav-link">Settings <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#settings-general" class="doc-nav-link">General</a>
                                <a href="#custom-domain" class="doc-nav-link">Custom Domain</a>
                                <a href="#settings-notifications" class="doc-nav-link">Notifications</a>
                                <a href="#settings-advanced" class="doc-nav-link">Advanced</a>
                            </div>
                        </div>
                        <div class="doc-nav-group">
                            <a href="#engagement" class="doc-nav-group-header doc-nav-link">Engagement <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#engagement-requests" class="doc-nav-link">Requests</a>
                                <a href="#engagement-fan-content" class="doc-nav-link">Fan Content</a>
                                <a href="#engagement-feedback" class="doc-nav-link">Feedback</a>
                            </div>
                        </div>
                        <a href="#auto-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Auto Import</a>
                        <div class="doc-nav-group">
                            <a href="#integrations" class="doc-nav-group-header doc-nav-link">Integrations <svg class="doc-nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg></a>
                            <div class="doc-nav-group-items">
                                <a href="#integrations-email" class="doc-nav-link">Email</a>
                                <a href="#integrations-google" class="doc-nav-link">Google Calendar</a>
                                <a href="#integrations-caldav" class="doc-nav-link">CalDAV</a>
                                <a href="#integrations-feeds" class="doc-nav-link">Feeds</a>
                            </div>
                        </div>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Details
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure your schedule's core identity in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>. Details are organized into three tabs: General, Localization, and Contact Info.</p>

                            <x-doc-screenshot id="creating-schedules--section-details" alt="Schedule details settings" loading="eager" />

                            <!-- General Tab -->
                            <h3 id="details-general" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">General</h3>
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

                            <!-- Localization Tab -->
                            <h3 id="details-localization" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Localization</h3>
                            <div class="space-y-4 mb-6">
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
                            </div>
                        </section>

                        <!-- AI Details Generator -->
                        <section id="ai-details-generator" class="doc-section">
                            <h2 class="doc-heading">AI Details Generator <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Enterprise</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Let AI generate a short description and description for your schedule based on its name and type.</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How It Works</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Click the <strong class="text-gray-900 dark:text-white">AI</strong> button in the Details section header, select which fields to generate, and the AI will create content based on your schedule's name and type.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Generated Fields</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Short description</strong> and <strong class="text-gray-900 dark:text-white">description</strong>. Fields with existing values show a blue dot indicator; generating will replace their content.</p>
                                </div>
                            </div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6">
                                <p class="text-sm text-blue-800 dark:text-blue-300"><strong>Note:</strong> Selfhosted installations require a <x-link href="https://ai.google.dev/" target="_blank">Gemini API key</x-link> configured in the environment settings for AI features to work.</p>
                            </div>
                        </section>

                        <!-- Address -->
                        <section id="address" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                                Address
                            </h2>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Add contact details in the <strong class="text-gray-900 dark:text-white">Details &rarr; Contact Info</strong> tab so visitors can reach you. These appear on your public schedule page.</p>

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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 3 3 0 005.78-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                                </svg>
                                Style
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Customize your schedule's visual appearance including colors, fonts, backgrounds, and layout. See the full <a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Styling</a> guide for all customization options.</p>
                        </section>

                        <!-- Videos & Links -->
                        <section id="videos-links" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                </svg>
                                Videos & Links
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Manage YouTube videos, social links, and payment links for your schedule. These are displayed on your public schedule page.</p>

                            <ul class="doc-list mb-6">
                                <li><strong>YouTube Videos</strong> - Link YouTube videos to showcase on your schedule page. Videos are displayed with thumbnails and titles.</li>
                                <li><strong>Social Links</strong> - Add social media profile URLs (Instagram, Facebook, Twitter, etc.) so visitors can find you on other platforms.</li>
                                <li><strong>Payment Links</strong> - Add payment URLs (Stripe, PayPal, Venmo, etc.) that are displayed on your public schedule page.</li>
                            </ul>
                        </section>

                        <!-- Customize -->
                        <section id="customize" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                </svg>
                                Customize
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Customize your schedule with sub-schedules, custom fields, and sponsors. Access these settings from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit &rarr; Customize</strong>.</p>

                            <h3 id="customize-subschedules" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Sub-schedules</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Organize your events into sub-schedules (categories). This helps visitors filter and find events that interest them.</p>

                            <x-doc-screenshot id="creating-schedules--section-subschedules" alt="Sub-schedules settings" />

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Creating Sub-schedules</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">To create a sub-schedule, go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit &rarr; Customize</strong> and select the Sub-schedules tab.</p>

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

                            <!-- Custom Fields -->
                            <h3 id="customize-custom-fields" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Custom Fields</h3>
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
                                                <td colspan="3" class="text-gray-400 text-sm">...up to {custom_10}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">URL-Safe Formatting</div>
                                <p>All variable values are automatically converted to URL-safe slugs: lowercase letters, numbers, and dashes only. For example, "Summer Concert" becomes "summer-concert" and "New York" becomes "new-york".</p>
                            </div>

                            <!-- Sponsors -->
                            <h3 id="customize-sponsors" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Sponsors</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Showcase your sponsors on your schedule page. Add sponsor logos, names, URLs, and assign tiers (Gold, Silver, Bronze). Sponsors are displayed in a dedicated section on your public schedule page.
                            </p>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Adding Sponsors</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Enter a sponsor name, optional URL, tier level, and upload a logo. You can add up to 12 sponsors per schedule.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Reordering</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Drag and drop sponsors to change their display order on the public schedule page.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Custom Labels -->
                        <section>
                            <h3 id="customize-custom-labels" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Custom Labels <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 ml-1">Pro</span></h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Override any of the default labels displayed on your public schedule page. For example, change "Events" to "Shows", "Follow" to "Subscribe", or "Free entry" to "No cover charge".
                            </p>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Adding a Custom Label</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Select a label from the dropdown and click Add. Enter your custom text in the "Custom Value" field. For non-English schedules, you can also provide an English translation or let it be auto-translated.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Available Labels</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">You can customize labels for buttons (Request to Book, Submit Event, Follow), navigation (Events, Filters, Past Events), event details (Free entry, Schedule, Category, Venue), and more.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Settings -->
                        <section id="settings" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </h2>
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
                                    <p class="text-sm text-gray-500 dark:text-gray-400">There are two modes: <strong class="text-gray-900 dark:text-white">Direct mode</strong> and <strong class="text-gray-900 dark:text-white">Redirect mode</strong>. See <a href="#custom-domain" class="text-cyan-400 hover:text-cyan-300">setup instructions</a> below.</p>
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

                            <!-- Custom Domain -->
                            <h3 id="custom-domain" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Custom Domain Setup <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-1">Enterprise</span></h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                There are two ways to connect a custom domain to your schedule. Choose the mode that best fits your needs.
                            </p>

                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Direct Mode (CNAME)</h4>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Your schedule is served directly on your custom domain with automatic SSL. This is the recommended option for most users.
                            </p>
                            <ol class="doc-list list-decimal mb-6">
                                <li>In your schedule settings, enter your domain (e.g., <code class="doc-inline-code">events.yourbrand.com</code>) and select <strong class="text-gray-900 dark:text-white">Direct</strong>.</li>
                                <li>Go to your domain registrar (e.g., GoDaddy, Namecheap, Cloudflare) and create a <strong class="text-gray-900 dark:text-white">CNAME record</strong> pointing your domain to <code class="doc-inline-code">{{ config('services.digitalocean.app_hostname') }}</code>.</li>
                                <li>Wait for DNS propagation (usually a few minutes, but can take up to 48 hours).</li>
                                <li>SSL is provisioned automatically. Once DNS has propagated, your schedule will be accessible at your custom domain over HTTPS.</li>
                            </ol>

                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Redirect Mode (Cloudflare)</h4>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Your custom domain redirects visitors to your <code class="doc-inline-code">eventschedule.com</code> URL. Use this if your domain's DNS is managed by Cloudflare. Cloudflare's free plan is sufficient.
                            </p>
                            <ol class="doc-list list-decimal mb-6">
                                <li>In your schedule settings, enter your domain and select <strong class="text-gray-900 dark:text-white">Redirect</strong>.</li>
                                <li>
                                    <strong class="text-gray-900 dark:text-white">Add your domain to Cloudflare</strong> (if not already). After adding the domain, Cloudflare will provide two nameservers. Go to your domain registrar and update your domain's nameservers to the ones Cloudflare provides. Wait for Cloudflare to confirm the domain is active.
                                </li>
                                <li>
                                    <strong class="text-gray-900 dark:text-white">Set up DNS records.</strong> In your Cloudflare dashboard, go to <strong class="text-gray-900 dark:text-white">DNS > Records</strong>:
                                    <ul class="list-disc ml-6 mt-2 mb-2">
                                        <li>Delete any existing A or AAAA records for the domain.</li>
                                        <li>Add an <strong class="text-gray-900 dark:text-white">A record</strong> with the name <code class="doc-inline-code">@</code> (root domain) pointing to <code class="doc-inline-code">192.0.2.1</code>.</li>
                                        <li>Add another <strong class="text-gray-900 dark:text-white">A record</strong> with the name <code class="doc-inline-code">*</code> (wildcard) pointing to <code class="doc-inline-code">192.0.2.1</code>.</li>
                                        <li>The IP address doesn't matter since traffic will be redirected. Make sure both records are set to <strong class="text-gray-900 dark:text-white">Proxied</strong> (orange cloud icon) so Cloudflare can intercept and redirect the requests.</li>
                                    </ul>
                                </li>
                                <li>
                                    <strong class="text-gray-900 dark:text-white">Create a Page Rule.</strong> In your Cloudflare dashboard, go to <strong class="text-gray-900 dark:text-white">Rules > Page Rules</strong> and create a new page rule:
                                    <ul class="list-disc ml-6 mt-2 mb-2">
                                        <li><strong class="text-gray-900 dark:text-white">URL pattern:</strong> <code class="doc-inline-code">*yourdomain.com/*</code></li>
                                        <li><strong class="text-gray-900 dark:text-white">Setting:</strong> Forwarding URL</li>
                                        <li><strong class="text-gray-900 dark:text-white">Status code:</strong> 301 - Permanent Redirect</li>
                                        <li><strong class="text-gray-900 dark:text-white">Destination URL:</strong> <code class="doc-inline-code">https://yourname.eventschedule.com/$2</code></li>
                                    </ul>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">The <code class="doc-inline-code">$2</code> wildcard preserves the URL path, so <code class="doc-inline-code">yourdomain.com/some-event</code> correctly redirects to <code class="doc-inline-code">yourname.eventschedule.com/some-event</code>.</p>
                                </li>
                                <li>Changes may take a few minutes to several hours to propagate. Once active, visitors who go to your custom domain will be seamlessly redirected to your schedule.</li>
                            </ol>

                            <!-- Notifications Tab -->
                            <h3 id="settings-notifications" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Notifications</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Configure which email notifications you receive for schedule activity.</p>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify New Request</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when someone submits a new event request to your schedule.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify New Fan Content</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when someone submits fan content (photos or videos) to one of your events.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Notify New Sale</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Receive an email when a ticket sale is completed for one of your events.</p>
                                </div>
                            </div>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Email Settings Required</div>
                                <p>On the hosted platform, notification emails require <a href="#integrations-email" class="text-cyan-400 hover:text-cyan-300">Email settings</a> to be configured. Without SMTP settings, notifications will be disabled.</p>
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

                        <!-- Engagement -->
                        <section id="engagement" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                                Engagement
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure how visitors interact with your schedule through event requests, fan content, and feedback.</p>

                            <x-doc-screenshot id="creating-schedules--section-engagement" alt="Schedule engagement settings" />

                            <!-- Requests Tab -->
                            <h3 id="engagement-requests" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Requests</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Accept Event Requests</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Allow others to submit events to your schedule. Submitted events can be reviewed in the <a href="{{ route('marketing.docs.creating_events') }}#manual" class="text-cyan-400 hover:text-cyan-300">pending queue</a>. Perfect for:</p>
                                    <ul class="text-sm text-gray-500 dark:text-gray-400 list-disc list-inside space-y-1">
                                        <li><strong class="text-gray-900 dark:text-white">Talent:</strong> Allow others to request to book you for events</li>
                                        <li><strong class="text-gray-900 dark:text-white">Venues:</strong> Accept booking requests from bands and performers</li>
                                        <li><strong class="text-gray-900 dark:text-white">Curators:</strong> Let the community submit local events</li>
                                        <li><strong class="text-gray-900 dark:text-white">Organizations:</strong> Collect event submissions from members</li>
                                    </ul>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require Account</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Require submitters to have an account before they can submit event requests. This helps identify who is submitting events and prevents anonymous submissions.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Require Approval</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">When enabled, submitted events go to a <a href="{{ route('marketing.docs.creating_events') }}#manual" class="text-cyan-400 hover:text-cyan-300">pending queue</a> for your approval before appearing publicly. Review requests in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Schedule &rarr; Pending</strong>.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Approved Schedules</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">When Require Approval is enabled, you can restrict submissions to only pre-approved schedules. Add specific schedules to the approved list, and only those schedules will be able to submit event requests.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Request Terms</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Add custom terms or guidelines that submitters must agree to when requesting events. Use this to set expectations about your booking policies, technical requirements, or submission guidelines.</p>
                                </div>
                            </div>

                            <!-- Fan Content Tab -->
                            <h3 id="engagement-fan-content" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Fan Content</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Enable Fan Content</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Allow visitors to submit photos and videos to your events. Submitted content requires your approval before appearing publicly on the event page.</p>
                                </div>
                            </div>

                            <!-- Feedback Tab -->
                            <h3 id="engagement-feedback" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Feedback</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Enable Feedback</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Automatically send feedback requests to attendees after events end. Attendees receive an email asking them to rate the event and leave comments.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Feedback Delay</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Set how long after an event ends before feedback requests are sent. Choose from 1 hour to 48 hours depending on your preference.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Email Settings Required</div>
                                <p>On the hosted platform, feedback emails require <a href="#integrations-email" class="text-cyan-400 hover:text-cyan-300">Email settings</a> to be configured.</p>
                            </div>
                        </section>

                        <!-- Auto Import -->
                        <section id="auto-import" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                                Auto Import <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 ml-2">Selfhost</span>
                            </h2>
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
                                <p>Auto-imported events go to your pending queue if you have <a href="#engagement-requests" class="text-cyan-400 hover:text-cyan-300">Require Approval</a> enabled, so you can review them before they appear publicly.</p>
                            </div>
                        </section>

                        <!-- Integrations -->
                        <section id="integrations" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.959.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z" />
                                </svg>
                                Integrations
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Sync your schedule with external calendar systems for smooth event management.</p>

                            <x-doc-screenshot id="creating-schedules--section-integrations" alt="Calendar integration settings" />

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sync Direction Options</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose one-way sync (import only or export only) or two-way sync to keep both calendars in perfect harmony.</p>
                                </div>
                            </div>

                            <!-- Email -->
                            <h3 id="integrations-email" class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-8">Email</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Configure email delivery for your schedule's notifications and communications.</p>

                            <x-doc-screenshot id="creating-schedules--section-email-settings" alt="Email settings" />

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

                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Setting Up Custom Email</h4>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong></li>
                                <li>Navigate to the <strong class="text-gray-900 dark:text-white">Integrations</strong> section and select the <strong class="text-gray-900 dark:text-white">Email</strong> tab</li>
                                <li>Enter your SMTP server details (host, port, username, password)</li>
                                <li>Set your custom sender name and email address</li>
                                <li>Send a test email to verify the configuration</li>
                            </ol>

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

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong></li>
                                <li>Scroll to <strong class="text-gray-900 dark:text-white">Integrations</strong></li>
                                <li>Enter your CalDAV <strong class="text-gray-900 dark:text-white">server URL</strong>, <strong class="text-gray-900 dark:text-white">username</strong>, and <strong class="text-gray-900 dark:text-white">password</strong></li>
                                <li>Select which <strong class="text-gray-900 dark:text-white">calendar</strong> to sync with</li>
                                <li>Choose your <strong class="text-gray-900 dark:text-white">sync direction</strong>: to CalDAV, from CalDAV, or bidirectional</li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Once connected, the integration status shows your server host. You can disconnect at any time to stop syncing.
                            </p>

                            <!-- Feeds -->
                            <h3 id="integrations-feeds" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Feeds</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">The Feeds tab provides read-only feed URLs that let others subscribe to your schedule's events from external applications.</p>

                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">iCal Feed</strong> - Subscribe to your schedule's events from any calendar app (Google Calendar, Apple Calendar, Outlook, etc.). The calendar will automatically stay up to date as you add or change events.</li>
                                <li><strong class="text-gray-900 dark:text-white">RSS Feed</strong> - Follow your schedule's events using any RSS reader. Useful for staying notified about new events.</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Each feed has a <strong class="text-gray-900 dark:text-white">copy</strong> button to quickly copy the URL to your clipboard for sharing or pasting into another app.
                            </p>

                            <div class="doc-callout doc-callout-info mb-6">
                                <div class="doc-callout-title">Note</div>
                                <p>Feed URLs are only available after the schedule has been saved. Create and save your schedule first, then return to the Feeds tab to find your URLs.</p>
                            </div>
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
