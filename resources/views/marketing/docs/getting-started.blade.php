<x-marketing-layout>
    <x-slot name="title">Getting Started - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Getting Started</x-slot>
    <x-slot name="description">Get started with Event Schedule. Learn how to create your account, set up your first schedule, and start sharing events.</x-slot>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                                </svg>
                                Create Your Account in Seconds
                            </h2>
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

                            <x-doc-screenshot id="getting-started--dashboard" alt="Event Schedule dashboard with schedule list" loading="eager" />
                        </section>

                        <!-- Create Schedule -->
                        <section id="create-schedule" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                Create Your Schedule
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                                Schedule Types
                            </h2>
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
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300">The schedule type affects how your events are displayed and what information is shown. <strong class="text-gray-900 dark:text-white">Talent</strong> schedules emphasize where you'll be performing. <strong class="text-gray-900 dark:text-white">Venue</strong> schedules show your full address with map integration. You can change this later in your schedule settings.</p>
                        </section>

                        <!-- Customize -->
                        <section id="customize" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                                </svg>
                                Customize Your Schedule
                            </h2>
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
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose your timezone, date format, and language preferences. See <a href="{{ route('marketing.docs.creating_schedules') }}#settings" class="text-cyan-400 hover:text-cyan-300">Creating Schedules</a> for all available settings.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sub-schedules</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Create <a href="{{ route('marketing.docs.creating_schedules') }}#customize-subschedules" class="text-cyan-400 hover:text-cyan-300">sub-schedules</a> to organize your events (e.g., "Live Music", "DJ Nights", "Comedy").</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Pro Feature</div>
                                <p>Upgrade to Pro to remove Event Schedule branding and access advanced features like ticketing and event graphics. Custom domains are available on the Enterprise plan.</p>
                            </div>
                        </section>

                        <!-- FAQ -->
                        <section id="faq" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                </svg>
                                Frequently Asked Questions
                            </h2>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Can I have multiple schedules?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Yes, you can create unlimited schedules under one account. This is useful if you manage multiple venues, bands, or organizations.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How do I change my schedule URL?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> and update the URL/subdomain field. See <a href="{{ route('marketing.docs.creating_schedules') }}#settings" class="text-cyan-400 hover:text-cyan-300">Schedule Settings</a> for details. Note that changing your URL may break existing links.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">What's the difference between schedule types?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Talent</strong> schedules show where you'll be performing. <strong class="text-gray-900 dark:text-white">Venue</strong> schedules show what's happening at your location (with full address support). <strong class="text-gray-900 dark:text-white">Curator</strong> schedules aggregate events from multiple sources. You can change your type anytime.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Can I import events from my existing calendar?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Yes! Event Schedule supports <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="text-cyan-400 hover:text-cyan-300">Google Calendar and CalDAV sync</a>. Connect your calendar in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong> under Calendar Sync. You can also <a href="{{ route('marketing.docs.ai_import') }}" class="text-cyan-400 hover:text-cyan-300">import events using AI</a> from text or images.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Is Event Schedule free?</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Yes - free forever! The free plan includes unlimited events, unlimited schedules, a custom subdomain, and all core features. No credit card required. Pro features like branding removal and ticketing are available starting at just $5/month with a 7-day free trial. Custom domains are available on the Enterprise plan.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Next Steps -->
                        <section id="next-steps" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 15l3-3m0 0l-3-3m3 3h-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Next Steps
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Now that your schedule is set up, here's what to do next:</p>

                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Configure your schedule</a> - Set up details, settings, sub-schedules, and integrations</li>
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

    <!-- FAQPage Schema -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Can I have multiple schedules?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, you can create unlimited schedules under one account. This is useful if you manage multiple venues, bands, or organizations."
                }
            },
            {
                "@type": "Question",
                "name": "How do I change my schedule URL?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Go to Admin Panel, Profile, Edit and update the URL/subdomain field. Note that changing your URL may break existing links."
                }
            },
            {
                "@type": "Question",
                "name": "What's the difference between schedule types?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Talent schedules show where you'll be performing. Venue schedules show what's happening at your location (with full address support). Curator schedules aggregate events from multiple sources. You can change your type anytime."
                }
            },
            {
                "@type": "Question",
                "name": "Can I import events from my existing calendar?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes! Event Schedule supports Google Calendar and CalDAV sync. Connect your calendar in Admin Panel, Profile, Edit under Calendar Sync. You can also import events using AI from text or images."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, free forever! The free plan includes unlimited events, unlimited schedules, a custom subdomain, and all core features. No credit card required. Pro features like branding removal and ticketing are available starting at just $5/month with a 7-day free trial. Custom domains are available on the Enterprise plan."
                }
            }
        ]
    }
    </script>

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
