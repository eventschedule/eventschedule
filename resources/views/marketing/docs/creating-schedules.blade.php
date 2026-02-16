<x-marketing-layout>
    <x-slot name="title">Advanced Schedule Settings - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Advanced Schedule Settings</x-slot>
    <x-slot name="description">Learn about advanced schedule features including sub-schedules, auto import, calendar integrations, and email settings.</x-slot>
    <x-slot name="keywords">sub-schedules, categories, auto import, google calendar, caldav, email settings, schedule configuration</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Advanced Schedule Settings - Event Schedule",
        "description": "Learn about advanced schedule features including sub-schedules, auto import, calendar integrations, and email settings.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        }
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
            <x-docs-breadcrumb currentTitle="Advanced Schedule Settings" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Advanced Schedule Settings</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Automate your workflow and never manually add an event again. Configure sub-schedules, auto import, calendar integrations, and more.
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
                        <a href="#subschedules" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Sub-schedules</a>
                        <a href="#auto-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Auto Import</a>
                        <a href="#calendar-integrations" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Calendar Integrations</a>
                        <a href="#email-settings" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Email Settings</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">This page covers advanced schedule features. If you're just getting started, see these pages first:</p>

                            <div class="space-y-3 mb-6">
                                <a href="{{ route('marketing.docs.schedule_basics') }}" class="block bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 hover:border-cyan-500/30 transition-colors">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-1">Schedule Basics</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Set up your schedule name, type, location, contact info, and core settings.</p>
                                </a>
                                <a href="{{ route('marketing.docs.schedule_styling') }}" class="block bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 hover:border-blue-500/30 transition-colors">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-1">Schedule Styling</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Customize colors, fonts, backgrounds, and visual appearance.</p>
                                </a>
                            </div>
                        </section>

                        <!-- Sub-schedules -->
                        <section id="subschedules" class="doc-section">
                            <h2 class="doc-heading">Sub-schedules</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Organize your events into sub-schedules (categories). This helps visitors filter and find events that interest them.</p>

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

                        <!-- Auto Import -->
                        <section id="auto-import" class="doc-section">
                            <h2 class="doc-heading">Auto Import</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Automatically import events from external sources to keep your schedule up-to-date without manual entry.</p>

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
                                <p>Auto-imported events go to your pending queue if you have <a href="{{ route('marketing.docs.schedule_basics') }}#settings" class="text-cyan-400 hover:text-cyan-300">Require Approval</a> enabled, so you can review them before they appear publicly.</p>
                            </div>
                        </section>

                        <!-- Calendar Integrations -->
                        <section id="calendar-integrations" class="doc-section">
                            <h2 class="doc-heading">Calendar Integrations</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Sync your schedule with external calendar systems for smooth event management.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Google Calendar Sync</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Connect your Google Calendar for bidirectional sync. Events created in either place stay synchronized automatically. Supports webhook-based real-time updates.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">CalDAV Sync</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Connect to any CalDAV-compatible calendar (Apple Calendar, Fastmail, Nextcloud, etc.) for cross-platform synchronization.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sync Direction Options</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose one-way sync (import only or export only) or two-way sync to keep both calendars in perfect harmony.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Connecting Google Calendar</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">First, make sure you've connected your Google account in <a href="{{ route('marketing.docs.account_settings') }}#google" class="text-cyan-400 hover:text-cyan-300">Account Settings</a>. Then:</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong></li>
                                <li>Scroll to <strong class="text-gray-900 dark:text-white">Calendar Sync</strong></li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Connect Google Calendar</strong></li>
                                <li>Authorize Event Schedule to access your Google Calendar</li>
                                <li>Select which calendar to sync and choose sync direction</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Selfhost Note</div>
                                <p>Google Calendar integration requires API credentials configuration. See the <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="text-cyan-400 hover:text-cyan-300">selfhost Google Calendar docs</a> for setup instructions.</p>
                            </div>
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
                                <li><a href="{{ route('marketing.docs.schedule_basics') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Basics</a> - Name, type, location, and core settings</li>
                                <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Styling</a> - Colors, fonts, backgrounds, and visual customization</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events to your schedule</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Embed and share your schedule</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Set up ticketing for your events</li>
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
        "name": "How to Set Up Auto Import and Calendar Sync",
        "description": "Configure sub-schedules, auto import from URLs, and connect Google Calendar or CalDAV for smooth event synchronization.",
        "totalTime": "PT10M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Create Sub-schedules",
                "text": "Go to Admin Panel, then Profile, then Edit and scroll to Sub-schedules. Create categories like Live Music, DJ Nights, or Comedy Shows.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#subschedules"
            },
            {
                "@type": "HowToStep",
                "name": "Set Up Auto Import",
                "text": "Scroll to Auto Import section and add URLs or city names to automatically import events from external sources.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#auto-import"
            },
            {
                "@type": "HowToStep",
                "name": "Connect Google Calendar",
                "text": "Scroll to Calendar Sync, click Connect Google Calendar, authorize access, and select which calendar to sync.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#calendar-integrations"
            },
            {
                "@type": "HowToStep",
                "name": "Configure Email Settings",
                "text": "Scroll to Email Settings to configure SMTP and custom sender address for your schedule's notifications.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#email-settings"
            }
        ]
    }
    </script>
</x-marketing-layout>
