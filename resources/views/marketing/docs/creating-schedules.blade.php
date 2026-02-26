<x-marketing-layout>
    <x-slot name="title">Advanced Schedule Settings - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Advanced Schedule Settings</x-slot>
    <x-slot name="description">Learn about advanced schedule features including sub-schedules, auto import, calendar integrations, and email settings.</x-slot>
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
                        <a href="#custom-domain" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Custom Domain</a>
                        <a href="#email-settings" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Email Settings</a>
                        <a href="#email-scheduling" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Email Scheduling</a>
                        <a href="#team-members" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Team Members</a>
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

                            <x-doc-screenshot id="creating-schedules--section-subschedules" alt="Sub-schedules settings" loading="eager" />

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
                                <p>Auto-imported events go to your pending queue if you have <a href="{{ route('marketing.docs.schedule_basics') }}#settings" class="text-cyan-400 hover:text-cyan-300">Require Approval</a> enabled, so you can review them before they appear publicly.</p>
                            </div>
                        </section>

                        <!-- Calendar Integrations -->
                        <section id="calendar-integrations" class="doc-section">
                            <h2 class="doc-heading">Calendar Integrations</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Sync your schedule with external calendar systems for smooth event management.</p>

                            <x-doc-screenshot id="creating-schedules--section-integrations" alt="Calendar integration settings" />

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

                        <!-- Custom Domain -->
                        <section id="custom-domain" class="doc-section">
                            <h2 class="doc-heading">Custom Domain <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Enterprise</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Use your own domain name instead of <code class="doc-inline-code">yoursubdomain.eventschedule.com</code>. With a custom domain, visitors access your schedule at an address like <code class="doc-inline-code">events.yourdomain.com</code> or <code class="doc-inline-code">yourdomain.com</code>.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">There are two ways to use a custom domain:</p>
                            <ul class="doc-list mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Redirect mode</strong> - Your domain redirects visitors to your <code class="doc-inline-code">eventschedule.com</code> URL using Cloudflare. Simple to set up and free.</li>
                                <li><strong class="text-gray-900 dark:text-white">Direct mode</strong> - Your schedule is served directly on your custom domain with automatic SSL. Visitors see your domain in the address bar at all times.</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Option 1: Direct Mode (CNAME)</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Direct mode serves your schedule directly on your custom domain. SSL is provisioned automatically.</p>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Go to your schedule settings</strong> - Navigate to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit &rarr; Schedule Settings</strong>.</li>
                                <li><strong class="text-gray-900 dark:text-white">Enter your custom domain</strong> - Type your full domain URL (e.g., <code class="doc-inline-code">https://events.yourdomain.com</code>) and select <strong class="text-gray-900 dark:text-white">Direct</strong> mode.</li>
                                <li><strong class="text-gray-900 dark:text-white">Add a CNAME record</strong> - Go to your domain registrar's DNS settings and add a CNAME record pointing to the hostname shown in the setup instructions.</li>
                                <li><strong class="text-gray-900 dark:text-white">Wait for DNS propagation</strong> - DNS changes can take up to 48 hours. SSL will be provisioned automatically once DNS is verified.</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip mb-6">
                                <div class="doc-callout-title">Tip</div>
                                <p>Direct mode is the recommended option. Your visitors will see your custom domain in their browser at all times, and SSL is handled automatically.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Option 2: Redirect Mode (Cloudflare)</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li><strong class="text-gray-900 dark:text-white">Create a free Cloudflare account</strong> - Sign up at <code class="doc-inline-code">cloudflare.com</code> if you don't already have one.</li>
                                <li><strong class="text-gray-900 dark:text-white">Add your custom domain to Cloudflare</strong> - In the Cloudflare dashboard, click "Add a site" and enter your domain name. Select the free plan.</li>
                                <li><strong class="text-gray-900 dark:text-white">Update your nameservers</strong> - Go to your domain registrar (where you purchased the domain) and change the nameservers to the ones Cloudflare provides. This may take up to 24 hours to propagate.</li>
                                <li><strong class="text-gray-900 dark:text-white">Configure DNS records</strong> - In Cloudflare DNS settings, remove any existing A records for your domain. Then add two new A records, both with the proxy enabled (orange cloud):
                                    <ul class="doc-list mt-2">
                                        <li><code class="doc-inline-code">@</code> pointing to <code class="doc-inline-code">192.0.2.1</code></li>
                                        <li><code class="doc-inline-code">*</code> pointing to <code class="doc-inline-code">192.0.2.1</code></li>
                                    </ul>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">The IP address doesn't matter since all requests will be redirected by the page rule in the next step.</p>
                                </li>
                                <li><strong class="text-gray-900 dark:text-white">Create a page rule for URL forwarding</strong> - In Cloudflare, go to <strong class="text-gray-900 dark:text-white">Rules &rarr; Page Rules</strong> and create a new rule:
                                    <ul class="doc-list mt-2">
                                        <li><strong class="text-gray-900 dark:text-white">URL:</strong> <code class="doc-inline-code">*yourcustomdomain.com/*</code></li>
                                        <li><strong class="text-gray-900 dark:text-white">Setting:</strong> Forwarding URL (301 Permanent Redirect)</li>
                                        <li><strong class="text-gray-900 dark:text-white">Destination:</strong> <code class="doc-inline-code">https://yoursubdomain.eventschedule.com/$2</code></li>
                                    </ul>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Replace <code class="doc-inline-code">yourcustomdomain.com</code> with your domain and <code class="doc-inline-code">yoursubdomain</code> with your Event Schedule subdomain.</p>
                                </li>
                                <li><strong class="text-gray-900 dark:text-white">Enter your custom domain in Event Schedule</strong> - Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit &rarr; Schedule Settings</strong> and enter your custom domain URL in the Custom Domain field.</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>DNS and nameserver changes can take up to 24 to 48 hours to fully propagate. If the redirect doesn't work immediately, give it some time and try again.</p>
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

                        <!-- Email Scheduling -->
                        <section id="email-scheduling" class="doc-section">
                            <h2 class="doc-heading">Email Scheduling</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Schedule automatic email delivery of your event graphics to keep your audience informed about upcoming events.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Automated Delivery</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Set a recurring schedule to automatically email your event graphic to subscribers. Choose daily, weekly, or custom intervals.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Graphic Preview</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Recipients receive a visual snapshot of your upcoming events as an <a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">event graphic</a>, making it easy to see what's coming up at a glance.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Setting Up Email Scheduling</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Graphic</strong></li>
                                <li>Click the <strong class="text-gray-900 dark:text-white">Email Scheduling</strong> button</li>
                                <li>Configure the delivery frequency and recipient list</li>
                                <li>Your event graphic will be emailed automatically on the chosen schedule</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Combine email scheduling with <a href="#email-settings" class="text-cyan-400 hover:text-cyan-300">custom email settings</a> to send from your own domain for a more professional look.</p>
                            </div>
                        </section>

                        <!-- Team Members -->
                        <section id="team-members" class="doc-section">
                            <h2 class="doc-heading">Team Members <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Enterprise</span></h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Add team members to your schedule so multiple people can collaborate on managing events.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Collaborative Management</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Invite team members by email to help manage your schedule. Each member gets their own login and can create, edit, and manage events.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Access Control</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Schedule owners maintain full control, including the ability to add or remove team members at any time.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Adding Team Members</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Team</strong></li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Add Member</strong></li>
                                <li>Enter the team member's email address</li>
                                <li>They'll receive an invitation and can start managing the schedule once they accept</li>
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
                "name": "Set Up a Custom Domain",
                "text": "Use Cloudflare to redirect your own domain to your Event Schedule URL for a professional branded experience.",
                "url": "{{ url(route('marketing.docs.creating_schedules')) }}#custom-domain"
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
