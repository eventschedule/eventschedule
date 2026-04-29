<x-marketing-layout>
    <x-slot name="title">Sharing Your Schedule - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Sharing Your Schedule</x-slot>
    <x-slot name="description">Learn how to share your Event Schedule with the world. Embed on your website, share on social media, and grow your audience.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Sharing Your Schedule - Event Schedule",
        "description": "Learn how to share your Event Schedule with the world. Embed on your website, share on social media, and grow your audience.",
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
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Sharing Your Schedule" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Sharing Your Schedule</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Reach your audience wherever they are. Embed your schedule on your website, share on social media, and let fans subscribe to your events.
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
                        <a href="#schedule-url" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Your Schedule URL</a>
                        <a href="#embed" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Embedding on Your Website</a>
                        <a href="#social" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Social Media Sharing</a>
                        <a href="#followers" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Building Followers</a>
                        <a href="#calendar-feeds" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Calendar Subscriptions</a>
                        <a href="#qr-code" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">QR Codes</a>
                        <a href="#troubleshooting" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Embed Troubleshooting</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Schedule URL -->
                        <section id="schedule-url" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                </svg>
                                Your Schedule URL
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Every schedule gets a unique, shareable URL. This is the primary way people will find and view your events.</p>

                            <x-doc-screenshot id="sharing--guest-portal" alt="Public schedule page" loading="eager" />

                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Your schedule URL format:</p>
                                <code class="text-blue-400">{{ config('app.url') }}/your-schedule-name</code>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Share this link anywhere:</p>
                            <ul class="doc-list">
                                <li>Your website or bio</li>
                                <li>Social media profiles</li>
                                <li>Email signatures</li>
                                <li>Printed materials</li>
                            </ul>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Enterprise Feature: Custom Domain</div>
                                <p>With an Enterprise plan, you can use your own domain (e.g., <code class="doc-inline-code">events.yourdomain.com</code>) for a more professional look. Configure this in <a href="{{ route('marketing.docs.creating_schedules') }}#settings-general" class="text-cyan-400 hover:text-cyan-300">Schedule Settings</a>.</p>
                            </div>
                        </section>

                        <!-- Embedding -->
                        <section id="embed" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                </svg>
                                Embedding on Your Website
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Add your schedule directly to your website using an embed code. Your events will automatically update without any extra work.</p>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">See our <a href="{{ marketing_url('/features/embed-calendar') }}" class="doc-link">embed calendar feature page</a> for a full overview and demo.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Getting the Embed Code</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to your schedule's public page</li>
                                <li>Click the <strong class="text-gray-900 dark:text-white">"Embed"</strong> button (or look for the embed icon)</li>
                                <li>Copy the HTML code provided</li>
                                <li>Paste it into your website where you want the schedule to appear</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Embed Tickets Too</div>
                                <p>You can also embed a ticket purchase or RSVP form on your website. See the <a href="{{ route('marketing.docs.tickets') }}#embed-widget" class="text-cyan-400 hover:text-cyan-300">Embed Widget</a> section in the Selling Tickets guide.</p>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Embed Options</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Customize how your embedded schedule looks:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Option</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Height</span></td>
                                            <td>Set the height of the embed (in pixels or percentage)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Theme</span></td>
                                            <td>Light or dark mode to match your website</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Header</span></td>
                                            <td>Show or hide the schedule header</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- Social Media -->
                        <section id="social" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                                </svg>
                                Social Media Sharing
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Share your schedule and individual events on social media to reach more people.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sharing Your Schedule</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Simply share your schedule URL on any platform. Event Schedule automatically generates social media preview cards with your schedule name, description, and image.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sharing Individual Events</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Each event also has its own URL that you can share. The preview will show the event name, date, and flyer image.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Graphics</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Use the <a href="{{ route('marketing.docs.event_graphics') }}" class="text-blue-400 hover:text-blue-300">Event Graphics</a> feature to generate shareable images showing multiple upcoming events. Perfect for weekly social media posts.</p>
                        </section>

                        <!-- Followers -->
                        <section id="followers" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                                Building Followers
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Let fans follow your schedule to get notified about new events. This builds your audience over time.</p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How Following Works</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Visitors click <strong class="text-gray-900 dark:text-white">"Follow"</strong> on your schedule</li>
                                <li>They enter their email address</li>
                                <li>Your schedule appears on their dashboard for easy access</li>
                                <li>They can subscribe to your calendar feed to see events in their own calendar</li>
                                <li>They can unfollow at any time</li>
                            </ol>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Managing Followers</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">View and manage your followers from <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Followers</strong>. You can:</p>
                            <ul class="doc-list">
                                <li>See how many followers you have</li>
                                <li>View follower growth over time</li>
                                <li>Export your follower list</li>
                            </ul>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">Send <a href="{{ route('marketing.docs.newsletters') }}" class="text-cyan-400 hover:text-cyan-300">newsletters</a> to your followers to keep them engaged and promote upcoming events.</p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Privacy</div>
                                <p>Follower emails are only used for event notifications. We never share or sell email addresses.</p>
                            </div>
                        </section>

                        <!-- Calendar Subscriptions -->
                        <section id="calendar-feeds" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 19.5v-.75a7.5 7.5 0 00-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                                Calendar Subscriptions
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Let your audience subscribe to your events directly in their calendar apps. Events automatically stay in sync.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">iCal Feed</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Your schedule has an iCal feed URL that works with Google Calendar, Apple Calendar, Outlook, and any calendar app that supports subscriptions.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Find your iCal URL at: <code class="doc-inline-code">your-schedule-url/ical</code></p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">RSS Feed</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">For readers and aggregators that support RSS, your events are available as an RSS feed.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Find your RSS URL at: <code class="doc-inline-code">your-schedule-url/rss</code></p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Add to Calendar Buttons</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Each event page includes "Add to Calendar" buttons. Visitors can add individual events to their Google Calendar, Apple Calendar, or download an .ics file.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Subscribed calendars automatically update when you add or change events. No action needed from your subscribers - they always see your latest schedule.</p>
                            </div>
                        </section>

                        <!-- QR Codes -->
                        <section id="qr-code" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                                </svg>
                                QR Codes
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Generate a QR code for your schedule to use in printed materials, posters, or at your venue.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Followers</strong></li>
                                <li>Click <strong class="text-gray-900 dark:text-white">"QR Code"</strong></li>
                                <li>Download the QR code image</li>
                                <li>Use it on flyers, posters, table tents, or anywhere else</li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300">When scanned, the QR code takes people directly to your schedule where they can view events and follow you.</p>
                        </section>

                        <!-- Embed Troubleshooting -->
                        <section id="troubleshooting" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
                                </svg>
                                Embed Troubleshooting
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Common issues when embedding your schedule and how to fix them.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Embed appears too small or cut off</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">The embed iframe needs explicit height. Set a minimum height of 600px for comfortable viewing. Example: <code class="doc-inline-code">height="800"</code> or <code class="doc-inline-code">style="height: 800px;"</code></p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Embed doesn't resize on mobile</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Set the width to 100% and wrap the iframe in a responsive container. Example: <code class="doc-inline-code">width="100%"</code> and put it inside a <code class="doc-inline-code">&lt;div style="max-width: 100%;"&gt;</code></p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Scrollbars appear on the embed</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Increase the height of your iframe. The content may be taller than the container. For schedules with many events, try <code class="doc-inline-code">height="1000"</code> or higher.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Embed blocked by browser</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Some browsers or extensions block iframes for security. This is rare but can happen with strict privacy settings. Test in an incognito/private window to verify.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Embed shows wrong theme</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Check the embed code parameters. Add <code class="doc-inline-code">?dark=true</code> to the URL to force dark mode.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Responsive Embed Code</div>
                                <pre class="text-xs text-gray-600 dark:text-gray-300 mt-2 overflow-x-auto"><code>&lt;div style="position: relative; padding-bottom: 75%; height: 0; overflow: hidden;"&gt;
  &lt;iframe src="YOUR_SCHEDULE_URL/embed"
    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
    frameborder="0"&gt;&lt;/iframe&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                See Also
                            </h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Event Graphics</a> - Generate shareable images for social media</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events to your schedule</li>
                                <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Styling</a> - Customize your schedule's look before sharing</li>
                                <li><a href="{{ route('marketing.docs.analytics') }}" class="text-cyan-400 hover:text-cyan-300">Analytics</a> - Track how sharing drives views and engagement</li>
                                <li><a href="{{ route('marketing.docs.newsletters') }}" class="text-cyan-400 hover:text-cyan-300">Newsletters</a> - Send newsletters to engage your followers</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Sell tickets for your events</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}#embed-widget" class="text-cyan-400 hover:text-cyan-300">Embed Widget</a> - Embed a ticket form on your website</li>
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
        "name": "How to Share Your Event Schedule",
        "description": "Learn how to share your schedule with the world. Embed on your website, share on social media, and grow your audience.",
        "totalTime": "PT5M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Share Your Schedule URL",
                "text": "Every schedule gets a unique, shareable URL. Share this link on your website, social media profiles, email signatures, or printed materials.",
                "url": "{{ url(route('marketing.docs.sharing')) }}#schedule-url"
            },
            {
                "@type": "HowToStep",
                "name": "Embed on Your Website",
                "text": "Go to your schedule's public page, click the Embed button, copy the HTML code, and paste it into your website.",
                "url": "{{ url(route('marketing.docs.sharing')) }}#embed"
            },
            {
                "@type": "HowToStep",
                "name": "Share on Social Media",
                "text": "Share your schedule or individual event URLs on social media. Event Schedule automatically generates preview cards.",
                "url": "{{ url(route('marketing.docs.sharing')) }}#social"
            },
            {
                "@type": "HowToStep",
                "name": "Build Your Followers",
                "text": "Visitors can follow your schedule to get notified about new events. View and manage followers from Admin Panel.",
                "url": "{{ url(route('marketing.docs.sharing')) }}#followers"
            }
        ]
    }
    </script>
</x-marketing-layout>
