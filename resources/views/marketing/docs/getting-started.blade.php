<x-marketing-layout>
    <x-slot name="title">Getting Started - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Getting Started</x-slot>
    <x-slot name="description">Get started with Event Schedule. Learn how to create your account, set up your first schedule, and start sharing events.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Getting Started - Event Schedule",
        "description": "Get started with Event Schedule. Learn how to create your account, set up your first schedule, and start sharing events.",
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
            <x-docs-breadcrumb currentTitle="Getting Started" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Getting Started</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Go from zero to a live event calendar in under 5 minutes. No credit card required - it's free forever.
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
                        <a href="#create-account" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create Your Account</a>
                        <a href="#create-schedule" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Create Your Schedule</a>
                        <a href="#schedule-types" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Schedule Types</a>
                        <a href="#customize" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Customize Your Schedule</a>
                        <a href="#faq" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">FAQ</a>
                        <a href="#next-steps" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Next Steps</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Create Account -->
                        <section id="create-account" class="doc-section">
                            <h2 class="doc-heading">Create Your Account in Seconds</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule is free forever - no trials, no credit card, no catch. Create an account using your email or sign in with Google.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Visit <a href="{{ app_url('/sign_up') }}" class="text-cyan-400 hover:text-cyan-300">the registration page</a></li>
                                <li>Enter your name, email, and create a password (or use Google)</li>
                                <li>Verify your email address by clicking the link we send you</li>
                                <li>You're ready to create your first schedule</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Using Google login is the fastest way to get started - no email verification required. Your data is yours - we never share or sell your information.</p>
                            </div>
                        </section>

                        <!-- Create Schedule -->
                        <section id="create-schedule" class="doc-section">
                            <h2 class="doc-heading">Create Your Schedule</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">A schedule is your event calendar - it's where all your events live. Each schedule gets its own unique URL that you can share with your audience.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>After logging in, click <strong class="text-gray-900 dark:text-white">"New Schedule"</strong> from your dashboard</li>
                                <li>Choose a schedule type (see below)</li>
                                <li>Enter your schedule name and pick a unique URL</li>
                                <li>Add optional details like location, description, and logo</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"Create"</strong> to finish</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Your Schedule URL</div>
                                <p>Your schedule URL is how people find you. Choose something memorable and relevant to your brand. For example: <code class="doc-inline-code">{{ config('app.url') }}/your-schedule-name</code></p>
                            </div>
                        </section>

                        <!-- Schedule Types -->
                        <section id="schedule-types" class="doc-section">
                            <h2 class="doc-heading">Schedule Types</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule supports different types of schedules to match your needs:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Best For</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Talent</span></td>
                                            <td>Musicians, DJs, performers, speakers</td>
                                            <td>A band listing their upcoming shows</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Venue</span></td>
                                            <td>Bars, clubs, theaters, event spaces</td>
                                            <td>A club listing all their events</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Curator</span></td>
                                            <td>Promoters, bloggers, community organizers</td>
                                            <td>A local music blog listing concerts in the area</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Vendor</span></td>
                                            <td>Food trucks, mobile businesses, pop-up shops</td>
                                            <td>A food truck listing where they'll be parked</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300">The schedule type affects how your events are displayed and what information is shown. <strong class="text-gray-900 dark:text-white">Talent</strong> schedules emphasize where you'll be performing. <strong class="text-gray-900 dark:text-white">Venue</strong> schedules show your full address with map integration. You can change this later in your schedule settings.</p>
                        </section>

                        <!-- Customize -->
                        <section id="customize" class="doc-section">
                            <h2 class="doc-heading">Customize Your Schedule</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Make your schedule your own with customization options:</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Profile Information</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Add your logo, description, website, and social links so visitors know who you are.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Location</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">For venues, add your address. This helps visitors find you and enables map integration.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Display Settings</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose your timezone, date format, and language preferences. See <a href="{{ route('marketing.docs.schedule_basics') }}#settings" class="text-cyan-400 hover:text-cyan-300">Schedule Basics</a> for all available settings.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sub-schedules</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Create <a href="{{ route('marketing.docs.creating_schedules') }}#subschedules" class="text-cyan-400 hover:text-cyan-300">sub-schedules</a> to organize your events (e.g., "Live Music", "DJ Nights", "Comedy").</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Pro Feature</div>
                                <p>Upgrade to Pro to remove Event Schedule branding, use a custom domain, and access advanced features like ticketing and event graphics.</p>
                            </div>
                        </section>

                        <!-- FAQ -->
                        <section id="faq" class="doc-section">
                            <h2 class="doc-heading">Frequently Asked Questions</h2>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Can I have multiple schedules?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Yes, you can create unlimited schedules under one account. This is useful if you manage multiple venues, bands, or organizations.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How do I change my schedule URL?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> and update the URL/subdomain field. See <a href="{{ route('marketing.docs.schedule_basics') }}#settings" class="text-cyan-400 hover:text-cyan-300">Schedule Settings</a> for details. Note that changing your URL may break existing links.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">What's the difference between schedule types?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Talent</strong> schedules show where you'll be performing. <strong class="text-gray-900 dark:text-white">Venue</strong> schedules show what's happening at your location (with full address support). <strong class="text-gray-900 dark:text-white">Curator</strong> schedules aggregate events from multiple sources. <strong class="text-gray-900 dark:text-white">Vendor</strong> schedules are for mobile businesses like food trucks. You can change your type anytime.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Can I import events from my existing calendar?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Yes! Event Schedule supports <a href="{{ route('marketing.docs.creating_schedules') }}#calendar-integrations" class="text-cyan-400 hover:text-cyan-300">Google Calendar and CalDAV sync</a>. Connect your calendar in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> under Calendar Sync. You can also <a href="{{ route('marketing.docs.creating_events') }}#ai-import" class="text-cyan-400 hover:text-cyan-300">import events using AI</a> from text or images.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Is Event Schedule free?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Yes - free forever! The free plan includes unlimited events, unlimited schedules, a custom subdomain, and all core features. No credit card required. Pro features like custom domains, branding removal, and ticketing are available starting at just $5/month with a 7-day free trial.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Next Steps -->
                        <section id="next-steps" class="doc-section">
                            <h2 class="doc-heading">Next Steps</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Now that your schedule is set up, here's what to do next:</p>

                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.schedule_basics') }}" class="text-cyan-400 hover:text-cyan-300">Configure schedule basics</a> - Set up name, location, contact info, and settings</li>
                                <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Style your schedule</a> - Customize colors, fonts, and backgrounds</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Add your first events</a> - Learn how to create and import events</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Share your schedule</a> - Embed on your website and share on social media</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Set up ticketing</a> - Start selling tickets for your events</li>
                                <li><a href="{{ route('marketing.docs.account_settings') }}" class="text-cyan-400 hover:text-cyan-300">Account settings</a> - Manage your profile, payments, and API access</li>
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
        "name": "Getting Started with Event Schedule",
        "description": "Learn how to create your account, set up your first schedule, and start sharing events with Event Schedule.",
        "totalTime": "PT5M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Create Your Account",
                "text": "Visit the registration page, enter your name, email, and password (or use Google login), then verify your email address.",
                "url": "{{ url(route('marketing.docs.getting_started')) }}#create-account"
            },
            {
                "@type": "HowToStep",
                "name": "Create Your Schedule",
                "text": "Click 'New Schedule' from your dashboard, choose a schedule type, enter your schedule name and pick a unique URL, then click Create.",
                "url": "{{ url(route('marketing.docs.getting_started')) }}#create-schedule"
            },
            {
                "@type": "HowToStep",
                "name": "Choose Your Schedule Type",
                "text": "Select the appropriate schedule type: Talent for performers, Venue for event spaces, or Curator for promoters and organizers.",
                "url": "{{ url(route('marketing.docs.getting_started')) }}#schedule-types"
            },
            {
                "@type": "HowToStep",
                "name": "Customize Your Schedule",
                "text": "Add your logo, description, location, and configure display settings to make your schedule your own.",
                "url": "{{ url(route('marketing.docs.getting_started')) }}#customize"
            }
        ]
    }
    </script>
</x-marketing-layout>
